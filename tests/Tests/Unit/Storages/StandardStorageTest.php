<?php

namespace Tests\Tests\Unit\Storages;

use PHPUnit\Framework\TestCase;
use Techart\ImageService\Storages\StandardStorage;

class StandardStorageTest extends TestCase
{
	protected ?string $fixtureTmpDir;
	protected ?string $fixtureFileDir;
	protected ?string $fixtureFilePath;
	protected ?string $fixtureFileCopyPath;
	protected ?string $fixtureFileNonExistentPath;

	public function test_standard_storage_constructor()
	{
		$storage = StandardStorage::getInstance();
		$storage2 = StandardStorage::getInstance();

		$this->assertInstanceOf(StandardStorage::class, $storage);
		$this->assertEquals($storage, $storage2);
	}

	public function test_standard_storage_get_dir_files()
	{
		$storage = StandardStorage::getInstance();

		$files = $storage->files($this->fixtureFileDir);
		$filesByRecursive = $storage->files($this->fixtureFileDir, true);

		$this->assertIsArray($files);
		$this->assertCount(2, $files);
		$this->assertContains($this->fixtureFilePath, $files);
		$this->assertContains($this->fixtureFileCopyPath, $files);

		$this->assertIsArray($filesByRecursive);
		$this->assertCount(4, $filesByRecursive);
	}

	public function test_standard_storage_file_exist()
	{
		$storage = StandardStorage::getInstance();

		$this->assertTrue($storage->exists($this->fixtureFilePath));
		$this->assertFalse($storage->exists($this->fixtureFileNonExistentPath));
	}

	public function test_standard_storage_file_path()
	{
		$filePath = $_SERVER['DOCUMENT_ROOT'].$this->fixtureFilePath;
		$storage = StandardStorage::getInstance();

		$this->assertEquals($filePath, $storage->path($this->fixtureFilePath));
	}

	public function test_standard_storage_file_url()
	{
		$storage = StandardStorage::getInstance();

		$this->assertEquals($this->fixtureFilePath, $storage->url($this->fixtureFilePath));
	}

	public function test_standard_storage_make_dir()
	{
		$storage = StandardStorage::getInstance();
		$tmpPath = $storage->path($this->fixtureTmpDir);

		$storage->makeDirectory($this->fixtureTmpDir);

		$this->assertTrue(file_exists($tmpPath));
		$this->assertTrue(is_dir($tmpPath));
	}

	public function test_standard_storage_image_info()
	{
		$storage = StandardStorage::getInstance();
		$info = $storage->imageInfo($this->fixtureFilePath);

		$this->assertIsArray($info);
		$this->assertNotEmpty($info);
		$this->assertCount(8, $info);
		$this->assertArrayHasKey('dirname', $info);
		$this->assertArrayHasKey('basename', $info);
		$this->assertArrayHasKey('extension', $info);
		$this->assertArrayHasKey('filename', $info);
		$this->assertArrayHasKey('real_path', $info);
		$this->assertArrayHasKey('path', $info);
		$this->assertArrayHasKey('mime', $info);
		$this->assertArrayHasKey('size', $info);

		$this->assertIsArray($info['size']);
		$this->assertNotEmpty($info['size']);
		$this->assertArrayHasKey('w', $info['size']);
		$this->assertArrayHasKey('h', $info['size']);
	}

	public function test_standard_storage_delete()
	{
		$storage = StandardStorage::getInstance();
		$fileName = 't-test.jpg';
		$tmpDir = $_SERVER['DOCUMENT_ROOT'] . $this->fixtureTmpDir;
		$tmpFile = $tmpDir . $fileName;
		$file = $_SERVER['DOCUMENT_ROOT'] . $this->fixtureFilePath;

		if (!file_exists($tmpDir)) {
			mkdir($tmpDir);
		}
		copy($file, $tmpFile);

		$this->assertTrue(file_exists($tmpFile));
		$storage->delete($this->fixtureTmpDir . $fileName);
		$this->assertFalse(file_exists($tmpFile));
	}

	protected function setUp(): void
	{
		$this->fixtureTmpDir = '/tests/Fixtures/images/tmp/';
		$this->fixtureFileDir = '/tests/Fixtures/images/';
		$this->fixtureFilePath = '/tests/Fixtures/images/test.jpg';
		$this->fixtureFileCopyPath = '/tests/Fixtures/images/test-copy.jpg';
		$this->fixtureFileNonExistentPath = '/tests/Fixtures/images/test-non-existent.jpg';
	}

	protected function tearDown(): void
	{
		$this->fixtureTmpDir = null;
		$this->fixtureFileDir = null;
		$this->fixtureFilePath = null;
		$this->fixtureFileCopyPath = null;
		$this->fixtureFileNonExistentPath = null;
	}
}