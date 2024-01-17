<?php

namespace Tests\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Techart\ImageService\Paths;

class PathsTest extends TestCase
{
	protected ?array $fixtureNewImage;
	protected ?array $fixtureOriginImage;

	public function test_paths_getters()
	{
		$images = new Paths($this->fixtureNewImage, $this->fixtureOriginImage);

		$this->assertEquals($this->fixtureNewImage, $images->getInfo());
		$this->assertEquals($this->fixtureNewImage['real_path'], $images->getPath());
		$this->assertEquals($this->fixtureNewImage['url'], $images->getUrl());
		$this->assertEquals($this->fixtureOriginImage, $images->getOriginalInfo());
		$this->assertEquals($this->fixtureOriginImage['real_path'], $images->getOriginalPath());
		$this->assertEquals($this->fixtureOriginImage['url'], $images->getOriginalUrl());
	}

	protected function setUp(): void
	{
		$newPath = '/tests/Fixtures/images/modify/40/fit/webp/test.webp';
		$originPath = '/tests/Fixtures/images/test.jpg';

		$this->fixtureNewImage = array_merge(pathinfo($newPath), [
			'real_path' => $newPath,
			'url' => $newPath,
			'size' => ['w' => 100, 'h' => 100]
		]);

		$this->fixtureOriginImage = array_merge(pathinfo($originPath), [
			'real_path' => $originPath,
			'url' => $originPath,
			'size' => ['w' => 200, 'h' => 200]
		]);
	}

	protected function tearDown(): void
	{
		$this->fixtureNewImage = null;
		$this->fixtureOriginImage = null;
	}
}