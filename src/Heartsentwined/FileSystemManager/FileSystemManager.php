<?php
namespace Heartsentwined\FileSystemManager;

use Heartsentwined\ArgValidator\ArgValidator;
use Heartsentwined\FileSystemManager\Exception;

class FileSystemManager
{
    /**
     * recursively iterate a directory, listing all files
     *
     * @param string $dir path of directory
     * @return array each member being /path/to/file (child-last)
     */
    public static function fileIterator($dir)
    {
        ArgValidator::assert($dir, 'string');
        $files = array();
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $files[] = $file->getPathname();
        }
        sort($files);
        return $files;
    }

    /**
     * recursively iterate a directory, listing all directories
     *
     * @param string $dir path of directory
     * @return array each member being /path/to/dir (child-first)
     */
    public static function dirIterator($dir)
    {
        $dirs = array();
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FileSystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $childDir) {
            if ($childDir->isDir()) {
                $dirs[] = $childDir->getPathname();
            }
        }
        return $dirs;
    }

    /**
     * recursive rmdir
     * @see http://www.php.net/manual/en/function.rmdir.php
     *
     * @param string $dir directory
     * @return bool (true on success)
     */
    public static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            foreach (scandir($dir) as $file) {
                if ($file != '.' && $file != '..') {
                    self::rrmdir("$dir/$file");
                }
            }
            rmdir($dir);
        } elseif (file_exists($dir)) {
            unlink($dir);
        }

        return !is_dir($dir);
    }

    /**
     * recursive copy
     * @see http://www.php.net/manual/en/function.copy.php
     *
     * @param string $src source file or dir
     * @param string $dst destination file or dir
     * @param int $dstPerm destination dir permission, if dir not exists
     * @return bool (true on success)
     */
    public static function rcopy($src, $dst, $dstPerm = 0755)
    {
        $status = true;

        if (is_dir($src)) {
            if (!is_dir($dst)) {
                mkdir($dst, $dstPerm, true);
            }
            foreach (scandir($src) as $file) {
                if ($file != '.' && $file != '..') {
                    if (!self::rcopy("$src/$file", "$dst/$file")) {
                        $status = false;
                    }
                }
            }
        } elseif (file_exists($src)) {
            if (!copy($src, $dst)) {
                $status = false;
            }
        }

        return $status;
    }

    public static function rchmod($path, $mode)
    {
        ArgValidator::assert($path, 'string');
        ArgValidator::assert($mode, 'numeric');

        if (!is_dir($path)) return chmod($path, $mode);

        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $file = "$path/$file";

                if (is_link($file)) return false;
                if (!is_dir($file) && !chmod($file, $mode)) return false;
                if (!self::rchmod($file, $mode)) return false;
            }
        }
        closedir($dh);

        return chmod($path, $mode);
    }

    public static function rchown($path, $owner)
    {
        ArgValidator::assert($path, 'string');
        ArgValidator::assert($owner, 'numeric');

        if (!is_dir($path)) return chown($path, $owner);

        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $file = "$path/$file";

                if (is_link($file)) return false;
                if (!is_dir($file) && !chown($file, $owner)) return false;
                if (!self::rchown($file, $owner)) return false;
            }
        }
        closedir($dh);

        return chown($path, $owner);
    }

    public static function rchgrp($path, $group)
    {
        ArgValidator::assert($path, 'string');
        ArgValidator::assert($group, 'numeric');

        if (!is_dir($path)) return chgrp($path, $group);

        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file !== '.' && $file !== '..') {
                $file = "$path/$file";

                if (is_link($file)) return false;
                if (!is_dir($file) && !chgrp($file, $group)) return false;
                if (!self::rchgrp($file, $group)) return false;
            }
        }
        closedir($dh);

        return chgrp($path, $group);
    }
}
