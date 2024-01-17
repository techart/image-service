<?php

namespace Tests\Tests\Unit\Managers;

use PHPUnit\Framework\TestCase;
use Techart\ImageService\Exceptions\ImageManagerException;
use Techart\ImageService\Managers\InterventionImageManager;

class InterventionImageManagerTest extends TestCase
{
	protected ?string $fixtureImagePath;
	protected ?string $fixtureImageNewDir;
	protected ?string $fixtureImageNewPath;
	protected ?string $fixtureImageNewPathWebp;

	public function test_intervention_manager_save()
	{
		$manager = new InterventionImageManager();
		$manager->makeImage($this->fixtureImagePath);

		mkdir($this->fixtureImageNewDir);
		$this->assertFalse(file_exists($this->fixtureImageNewPath));

		$manager->saveImage($this->fixtureImageNewPath);
		$this->assertTrue(file_exists($this->fixtureImageNewPath));
	}

	public function test_intervention_manager_resize()
	{
		$manager = new InterventionImageManager();
		$manager->makeImage($this->fixtureImagePath);
		$manager->resizeImage('resize', 400, 410);
		$manager->saveImage($this->fixtureImageNewPath);

		$this->assertTrue(file_exists($this->fixtureImageNewPath));
		$sizes = getimagesize($this->fixtureImageNewPath);

		$this->assertNotEmpty($sizes);
		$this->assertEquals(400, $sizes[0]);
		$this->assertEquals(410, $sizes[1]);
	}

	public function test_intervention_manager_convert()
	{
		$manager = new InterventionImageManager();
		$manager->makeImage($this->fixtureImagePath);
		$manager->convertImage('webp');
		$manager->saveImage($this->fixtureImageNewPathWebp);

		$this->assertTrue(file_exists($this->fixtureImageNewPathWebp));
		$type = exif_imagetype($this->fixtureImageNewPathWebp);
		$this->assertEquals(IMAGETYPE_WEBP, $type);
	}

	public function test_intervention_manager_convert_exception()
	{
		$this->expectException(ImageManagerException::class);
		$manager = new InterventionImageManager();
		$manager->makeImage($this->fixtureImagePath);
		$manager->convertImage('mp3');
	}

	public function test_intervention_manager_resize_exception()
	{
		$this->expectException(ImageManagerException::class);
		$manager = new InterventionImageManager();
		$manager->makeImage($this->fixtureImagePath);
		$manager->resizeImage('someCoolMethod', 300, 500);
	}

	public function test_intervention_manager_quality_less_one_exception()
	{
		$this->expectException(ImageManagerException::class);
		$manager = new InterventionImageManager();
		$manager->makeImage($this->fixtureImagePath);
		$manager->setQuality(-22);
	}

	public function test_intervention_manager_quality_more_hundred_exception()
	{
		$this->expectException(ImageManagerException::class);
		$manager = new InterventionImageManager();
		$manager->makeImage($this->fixtureImagePath);
		$manager->setQuality(120);
	}

	protected function setUp(): void
	{
		$this->fixtureImagePath = $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/intervention/test.jpg';
		$this->fixtureImageNewDir = $_SERVER['DOCUMENT_ROOT'] . '/tests/Fixtures/intervention/save/';
		$this->fixtureImageNewPath = $this->fixtureImageNewDir . 'test.jpg';
		$this->fixtureImageNewPathWebp = $this->fixtureImageNewDir . 'test.webp';
	}

	protected function tearDown(): void
	{
		$this->fixtureImagePath = null;
		$this->fixtureImageNewDir = null;
		$this->fixtureImageNewPath = null;
		$this->fixtureImageNewPathWebp = null;
	}
}