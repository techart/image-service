<?php

namespace Tests\Tests\Unit\Managers;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Techart\ImageService\Contracts\ManagerContract;
use Techart\ImageService\Exceptions\ImageManagerException;
use Techart\ImageService\Managers\GumletImageResizeManager;
use Techart\ImageService\Managers\InterventionImageManager;

class ManagersTest extends TestCase
{
	#[DataProvider('managersMap')]
	public function test_manager_save(ManagerContract $managerClass, array $paths)
	{
		$manager = new $managerClass();
		$manager->makeImage($paths['path']);

		mkdir($paths['new_dir']);

		if (file_exists($paths['new_path'])) {
			unlink($paths['new_path']);
		}

		$manager->saveImage($paths['new_path']);
		$this->assertTrue(file_exists($paths['new_path']));
	}

	#[DataProvider('managersMap')]
	public function test_manager_resize(ManagerContract $managerClass, array $paths)
	{
		$manager = new $managerClass();
		$manager->makeImage($paths['path']);
		$manager->resizeImage('resize', 400, 410);
		$manager->saveImage($paths['new_path']);

		$this->assertTrue(file_exists($paths['new_path']));
		$sizes = getimagesize($paths['new_path']);

		$this->assertNotEmpty($sizes);
		$this->assertEquals(400, $sizes[0]);
		$this->assertEquals(410, $sizes[1]);
	}

	#[DataProvider('managersMap')]
	public function test_manager_convert_webp(ManagerContract $managerClass, array $paths)
	{
		$manager = new $managerClass();
		$manager->makeImage($paths['path']);
		$manager->convertImage('webp');
		$manager->saveImage($paths['new_path_webp']);

		$this->assertTrue(file_exists($paths['new_path_webp']));
		$type = exif_imagetype($paths['new_path_webp']);
		$this->assertEquals(IMAGETYPE_WEBP, $type);
	}

	#[DataProvider('managersMap')]
	public function test_manager_convert_gif(ManagerContract $managerClass, array $paths)
	{
		$manager = new $managerClass();
		$manager->makeImage($paths['path']);
		$manager->convertImage('gif');
		$manager->saveImage($paths['new_path_gif']);

		$this->assertTrue(file_exists($paths['new_path_gif']));
		$type = exif_imagetype($paths['new_path_gif']);
		$this->assertEquals(IMAGETYPE_GIF, $type);
	}

	#[DataProvider('managersMap')]
	public function test_manager_convert_png(ManagerContract $managerClass, array $paths)
	{
		$manager = new $managerClass();
		$manager->makeImage($paths['path']);
		$manager->convertImage('png');
		$manager->saveImage($paths['new_path_png']);

		$this->assertTrue(file_exists($paths['new_path_png']));
		$type = exif_imagetype($paths['new_path_png']);
		$this->assertEquals(IMAGETYPE_PNG, $type);
	}

	#[DataProvider('managersMap')]
	public function test_manager_convert_jpeg(ManagerContract $managerClass, array $paths)
	{
		$manager = new $managerClass();
		$manager->makeImage($paths['path']);
		$manager->convertImage('jpeg');
		$manager->saveImage($paths['new_path_jpeg']);

		$this->assertTrue(file_exists($paths['new_path_jpeg']));
		$type = exif_imagetype($paths['new_path_jpeg']);
		$this->assertEquals(IMAGETYPE_JPEG, $type);
	}

	#[DataProvider('managersMap')]
	public function test_manager_convert_exception(ManagerContract $managerClass, array $paths)
	{
		$this->expectException(ImageManagerException::class);
		$manager = new $managerClass();
		$manager->makeImage($paths['path']);
		$manager->convertImage('mp3');
	}

	#[DataProvider('managersMap')]
	public function test_manager_resize_exception(ManagerContract $managerClass, array $paths)
	{
		$this->expectException(ImageManagerException::class);
		$manager = new $managerClass();
		$manager->makeImage($paths['path']);
		$manager->resizeImage('someCoolMethod', 300, 500);
	}

	#[DataProvider('managersMap')]
	public function test_manager_quality_less_one_exception(ManagerContract $managerClass, array $paths)
	{
		$this->expectException(ImageManagerException::class);
		$manager = new $managerClass();
		$manager->makeImage($paths['path']);
		$manager->setQuality(-22);
	}

	#[DataProvider('managersMap')]
	public function test_manager_quality_more_hundred_exception(ManagerContract $managerClass, array $paths)
	{
		$this->expectException(ImageManagerException::class);
		$manager = new $managerClass();
		$manager->makeImage($paths['path']);
		$manager->setQuality(120);
	}

	public static function managersMap(): array
	{
		return [
			[new InterventionImageManager(), [
				'path' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/intervention/test.jpg',
				'new_dir' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/intervention/save/',
				'new_path' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/intervention/save/test.jpg',
				'new_path_webp' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/intervention/save/test.webp',
				'new_path_gif' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/intervention/save/test.gif',
				'new_path_jpeg' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/intervention/save/test.jpeg',
				'new_path_png' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/intervention/save/test.png',
			]],
			[new GumletImageResizeManager(),[
				'path' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/gumlet/test.jpg',
				'new_dir' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/gumlet/save/',
				'new_path' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/gumlet/save/test.jpg',
				'new_path_webp' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/gumlet/save/test.webp',
				'new_path_gif' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/gumlet/save/test.gif',
				'new_path_png' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/gumlet/save/test.png',
				'new_path_jpeg' => $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/gumlet/save/test.jpeg',
			]]
		];
	}
}