<?php

namespace Tests\Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Techart\ImageService\Contracts\ManagerContract;
use Techart\ImageService\Managers\GumletImageResizeManager;
use Techart\ImageService\Managers\InterventionImageManager;
use Techart\ImageService\Service;
use Techart\ImageService\Storages\StandardStorage;

class ServiceTest extends TestCase
{
	protected ?StandardStorage $fixtureStorage;
	protected ?array $fixtureConfig;
	protected ?string $fixtureImagePath;

	#[DataProvider('configsMap')]
	public function test_service_image_save(array $config, string $expectedImagePath)
	{
		$expectedImagePath = $_SERVER['DOCUMENT_ROOT'].$expectedImagePath;

		foreach (self::managersMap() as $manager) {
			$service = Service::getInstance($manager[0], $this->fixtureStorage, $this->fixtureConfig);
			$newImagePath = $service->modify($this->fixtureImagePath, $config)
				->process()
				->getPath();

			$this->assertTrue(file_exists($newImagePath));
			$this->assertEquals($expectedImagePath, $newImagePath);
			$this->removeModifyDir();
		}
	}

	#[DataProvider('managersMap')]
	public function test_service_image_resize(ManagerContract $manager)
	{
		$service = Service::getInstance($manager, $this->fixtureStorage, $this->fixtureConfig);
		$expectedImagePath = $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/service/modify/99/resize/200x200/test.jpg';
		$newImagePath = $service->modify($this->fixtureImagePath)
			->setSizes([200, 200])
			->process()
			->getPath();

		$this->assertTrue(file_exists($newImagePath));
		$this->assertEquals($expectedImagePath, $newImagePath);
		$size = getimagesize($newImagePath);

		$this->assertNotEmpty($size);
		$this->assertEquals(200, $size[0]);
		$this->assertEquals(200, $size[1]);

		$this->removeModifyDir();
	}

	#[DataProvider('managersMap')]
	public function test_service_image_convert(ManagerContract $manager)
	{
		$service = Service::getInstance($manager, $this->fixtureStorage, $this->fixtureConfig);
		$expectedImagePath = $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/service/modify/99/resize/webp/test.webp';
		$newImagePath = $service->modify($this->fixtureImagePath)
			->setFormat('webp')
			->process()
			->getPath();

		$this->assertTrue(file_exists($newImagePath));
		$this->assertEquals($expectedImagePath, $newImagePath);
		$type = exif_imagetype($newImagePath);
		$this->assertEquals(IMAGETYPE_WEBP, $type);

		$this->removeModifyDir();
	}

	#[DataProvider('managersMap')]
	public function test_service_image_paths(ManagerContract $manager)
	{
		$expectedImageUrl = '/tests/Fixtures/service/modify/99/resize/webp/test.webp';
		$expectedImagePath = $_SERVER['DOCUMENT_ROOT'] . $expectedImageUrl;

		$service = Service::getInstance($manager, $this->fixtureStorage, $this->fixtureConfig);
		$paths = $service->modify($this->fixtureImagePath)
			->setFormat('webp')
			->process();

		$this->assertEquals($expectedImageUrl, $paths->getUrl());
		$this->assertEquals($expectedImagePath, $paths->getPath());
		$this->assertEquals($this->fixtureImagePath, $paths->getOriginalUrl());
		$this->assertEquals($_SERVER['DOCUMENT_ROOT'].$this->fixtureImagePath, $paths->getOriginalPath());

		$this->removeModifyDir();
	}

	#[DataProvider('managersMap')]
	public function test_service_image_storage(ManagerContract $manager)
	{
		$imageDir = '/tests/Fixtures/service/tmp/';
		$imagePath = $imageDir . 'test.jpg';
		$expectedImagePath = '/tests/Fixtures/service/tmp/modify/99/resize/webp/test.webp';

		mkdir($_SERVER['DOCUMENT_ROOT'].$imageDir);
		copy($_SERVER['DOCUMENT_ROOT'].$this->fixtureImagePath, $_SERVER['DOCUMENT_ROOT'].$imagePath);

		$service = Service::getInstance($manager, $this->fixtureStorage, $this->fixtureConfig);
		$newImagePath = $service->modify($imagePath)
			->setFormat('webp')
			->process()
			->getPath();

		$storage = $service->storage($imagePath);
		$images = $storage->getModifyImages();

		$this->assertEquals($_SERVER['DOCUMENT_ROOT'].$expectedImagePath, $newImagePath);
		$this->assertTrue(file_exists($newImagePath));
		$this->assertEquals($storage->getOriginalImage(), $imagePath);
		$this->assertTrue($storage->haveModifyImages());
		$this->assertNotEmpty($images);
		$this->assertContains($expectedImagePath, $images);

		$storage->delete(false);
		$this->assertFalse(file_exists($newImagePath));
		$this->assertFalse($storage->haveModifyImages());

		$newImagePath = $service->modify($imagePath)
			->setFormat('webp')
			->process()
			->getPath();

		$storage = $service->storage($imagePath);

		$this->assertTrue(file_exists($newImagePath));
		$this->assertTrue($storage->haveModifyImages());

		$storage->delete();
		$this->assertFalse(file_exists($newImagePath));
		$this->assertFalse(file_exists($imagePath));

		removeDivRecursive($_SERVER['DOCUMENT_ROOT'].$imageDir);
	}

	protected function removeModifyDir(): void
	{
		removeDivRecursive($_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/service/modify/');
	}

	public static function configsMap(): array
	{
		return [
			[
				['resize' => '200x110'],
				'/tests/Fixtures/service/modify/99/resize/200x110/test.jpg'
			],
			[
				['resize' => '200x120', 'quality' => 50],
				'/tests/Fixtures/service/modify/50/resize/200x120/test.jpg'
			],
			[
				['resize' => '200x130', 'quality' => 22, 'format' => 'png'],
				'/tests/Fixtures/service/modify/22/resize/200x130/png/test.png'
			],
			[
				['resize' => '300x100', 'quality' => 34, 'format' => 'png', 'method' => 'crop'],
				'/tests/Fixtures/service/modify/34/crop/300x100/png/test.png'
			],
			[
				['quality' => 55, 'format' => 'png'],
				'/tests/Fixtures/service/modify/55/resize/png/test.png'
			],
			[
				['format' => 'png'],
				'/tests/Fixtures/service/modify/99/resize/png/test.png'
			],
		];
	}

	public static function managersMap(): array
	{
		return [
			[new InterventionImageManager()],
			[new GumletImageResizeManager()]
		];
	}

	protected function setUp(): void
	{
		$this->fixtureStorage = StandardStorage::getInstance();
		$this->fixtureConfig = [
			'sizes' => '*',
			'format' => ['jpg', 'jpeg',	'gif', 'png', 'webp'],
			'methods' => ['resize', 'crop', 'fit'],
			'quality' => 99
		];

		$this->fixtureImagePath = '/tests/Fixtures/service/test.jpg';
	}

	protected function tearDown(): void
	{
		$this->fixtureStorage = null;
		$this->fixtureConfig = null;
		$this->fixtureImagePath = null;
	}
}