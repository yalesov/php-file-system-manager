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
}
