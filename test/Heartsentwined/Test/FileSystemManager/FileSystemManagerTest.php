<?php
namespace Heartsentwined\Test\FileSystemManager;

use Heartsentwined\FileSystemManager\FileSystemManager;
use Heartsentwined\FileSystemManager\Exception;

class FileSystemManagerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        mkdir('foo');
        touch('foo/foo1');
        touch('foo/foo2');
        mkdir('foo/bar');
        mkdir('foo/bar/bar');
        touch('foo/bar/bar1');
        mkdir('foo/baz');
    }

    public function tearDown()
    {
        try { rmdir('foo/baz'); } catch (\Exception $e) {}
        try { unlink('foo/bar/bar1'); } catch (\Exception $e) {}
        try { rmdir('foo/bar/bar'); } catch (\Exception $e) {}
        try { rmdir('foo/bar'); } catch (\Exception $e) {}
        try { unlink('foo/foo1'); } catch (\Exception $e) {}
        try { unlink('foo/foo2'); } catch (\Exception $e) {}
        try { rmdir('foo'); } catch (\Exception $e) {}
    }

    public function testFileIterator()
    {
        $this->assertSame(array(
            'foo/bar/bar1',
            'foo/foo1',
            'foo/foo2',
        ), FileSystemManager::fileIterator('foo'));
    }

    public function testDirIterator()
    {
        $this->assertSame(array(
            'foo/bar/bar',
            'foo/bar',
            'foo/baz',
        ), FileSystemManager::dirIterator('foo'));
    }

    public function testRrmdir()
    {
        $this->assertTrue(FileSystemManager::rrmdir('foo'));
        $this->assertFalse(is_dir('foo'));
    }

    /**
     * @depends testRrmdir
     */
    public function testRcopy()
    {
        $this->assertTrue(FileSystemManager::rcopy('foo', 'bar'));
        $this->assertTrue(is_dir('bar'));
        $this->assertTrue(is_file('bar/foo1'));
        $this->assertTrue(is_file('bar/foo2'));
        $this->assertTrue(is_dir('bar/bar'));
        $this->assertTrue(is_dir('bar/bar/bar'));
        $this->assertTrue(is_file('bar/bar/bar1'));
        $this->assertTrue(is_dir('bar/baz'));

        FileSystemManager::rrmdir('bar');
    }

    /**
     * @depends testRrmdir
     */
    public function testRcopyExistingDir()
    {
        mkdir('bar');
        mkdir('bar/bar');

        $this->assertTrue(FileSystemManager::rcopy('foo', 'bar'));
        $this->assertTrue(is_dir('bar'));
        $this->assertTrue(is_file('bar/foo1'));
        $this->assertTrue(is_file('bar/foo2'));
        $this->assertTrue(is_dir('bar/bar'));
        $this->assertTrue(is_dir('bar/bar/bar'));
        $this->assertTrue(is_file('bar/bar/bar1'));
        $this->assertTrue(is_dir('bar/baz'));

        FileSystemManager::rrmdir('bar');
    }

    /**
     * @depends testRrmdir
     */
    public function testRcopyExistingFile()
    {
        mkdir('bar');
        touch('bar/foo1');

        $this->assertTrue(FileSystemManager::rcopy('foo', 'bar'));
        $this->assertTrue(is_dir('bar'));
        $this->assertTrue(is_file('bar/foo1'));
        $this->assertTrue(is_file('bar/foo2'));
        $this->assertTrue(is_dir('bar/bar'));
        $this->assertTrue(is_dir('bar/bar/bar'));
        $this->assertTrue(is_file('bar/bar/bar1'));
        $this->assertTrue(is_dir('bar/baz'));

        FileSystemManager::rrmdir('bar');
    }

    /**
     * @depends testRrmdir
     */
    public function testRchmod()
    {
        $this->assertFalse(is_dir('test'));
        $this->assertFalse(is_dir('test1'));
        $this->assertFalse(is_dir('test2.f'));
        mkdir('test/test1', 0755);
        touch('test/test1/test2.f');

        $this->assertTrue(
            FileSystemManager::rchmod('test/test1/test2.f', 0444));
        $this->assertTrue(1444,
            substr(sprintf('%o', fileperms('test')), -4));
        $this->assertTrue(1444,
            substr(sprintf('%o', fileperms('test/test1')), -4));
        $this->assertTrue(0444,
            substr(sprintf('%o', fileperms('test/test1/test2.f')), -4));

        FileSystemManager::rrmdir('test');
    }

    /**
     * @depends testRrmdir
     */
    public function testRchown()
    {
        $this->assertFalse(is_dir('test'));
        $this->assertFalse(is_dir('test1'));
        $this->assertFalse(is_dir('test2.f'));
        mkdir('test/test1', 0755);
        touch('test/test1/test2.f');

        $stat = stat('test');
        $user = posix_getpwuid($stat['uid']);
        $this->assertNotSame('www-data', $user['name']);
        $stat = stat('test/test1');
        $user = posix_getpwuid($stat['uid']);
        $this->assertNotSame('www-data', $user['name']);
        $stat = stat('test/test1/test2.f');
        $user = posix_getpwuid($stat['uid']);
        $this->assertNotSame('www-data', $user['name']);

        $this->assertTrue(
            FileSystemManager::rchown('test/test1/test2.f', 'www-data'));
        $stat = stat('test');
        $user = posix_getpwuid($stat['uid']);
        $this->assertSame('www-data', $user['name']);
        $stat = stat('test/test1');
        $user = posix_getpwuid($stat['uid']);
        $this->assertSame('www-data', $user['name']);
        $stat = stat('test/test1/test2.f');
        $user = posix_getpwuid($stat['uid']);
        $this->assertSame('www-data', $user['name']);

        FileSystemManager::rrmdir('test');
    }

    /**
     * @depends testRrmdir
     */
    public function testRchgrp()
    {
        $this->assertFalse(is_dir('test'));
        $this->assertFalse(is_dir('test1'));
        $this->assertFalse(is_dir('test2.f'));
        mkdir('test/test1', 0755);
        touch('test/test1/test2.f');

        $stat = stat('test');
        $user = posix_getgrgid($stat['gid']);
        $this->assertNotSame('www-data', $user['name']);
        $stat = stat('test/test1');
        $user = posix_getgrgid($stat['gid']);
        $this->assertNotSame('www-data', $user['name']);
        $stat = stat('test/test1/test2.f');
        $user = posix_getgrgid($stat['gid']);
        $this->assertNotSame('www-data', $user['name']);

        $this->assertTrue(
            FileSystemManager::rchgrp('test/test1/test2.f', 'www-data'));
        $stat = stat('test');
        $user = posix_getgrgid($stat['gid']);
        $this->assertSame('www-data', $user['name']);
        $stat = stat('test/test1');
        $user = posix_getgrgid($stat['gid']);
        $this->assertSame('www-data', $user['name']);
        $stat = stat('test/test1/test2.f');
        $user = posix_getgrgid($stat['gid']);
        $this->assertSame('www-data', $user['name']);

        FileSystemManager::rrmdir('test');
    }
}
