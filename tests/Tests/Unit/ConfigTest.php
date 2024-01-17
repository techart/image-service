<?php

namespace Tests\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Techart\ImageService\Config;
use Techart\ImageService\Exceptions\ImageConfigValidateException;

class ConfigTest extends TestCase
{
	protected ?array $fixtureConfig;
	protected ?array $fixtureParams;
	protected ?array $fixtureImageParams;

	public function test_config_getters()
	{
		$config = new Config($this->fixtureConfig, $this->fixtureParams, $this->fixtureImageParams);

		$this->assertTrue($config->isResize());
		$this->assertTrue($config->isConvert());
		$this->assertEquals($this->fixtureImageParams, $config->getImageParams());
		$this->assertEquals($this->fixtureImageParams['real_path'], $config->getPath());
		$this->assertEquals($this->fixtureParams['quality'], $config->getQuality());
		$this->assertEquals($this->fixtureParams['method'], $config->getMethod());
		$this->assertEquals($this->fixtureParams['format'], $config->getFormat());

		$size = $config->getSize();
		$this->assertSize($size, 100, 110);
	}

	public function test_config_setters()
	{
		$config = new Config($this->fixtureConfig, $this->fixtureParams, $this->fixtureImageParams);

		$config->setSize([200, 200]);
		$this->assertSize($config->getSize(), 200, 200);

		$config->setSize('150x250');
		$this->assertSize($config->getSize(), 150, 250);

		$config->setSize(null);
		$this->assertNull($config->getSize());

		$config->setFormat('gif');
		$this->assertEquals('gif', $config->getFormat());

		$config->setMethod('crop');
		$this->assertEquals('crop', $config->getMethod());

		$config->setQuality(50);
		$this->assertEquals(50, $config->getQuality());

		$config->setQuality(110);
		$this->assertEquals(100, $config->getQuality());

		$config->setQuality(0);
		$this->assertEquals(1, $config->getQuality());

		$config->setQuality(-43);
		$this->assertEquals(1, $config->getQuality());
	}

	public function test_config_default_params()
	{
		$params = [
			'format' => 'png'
		];

		$config = new Config($this->fixtureConfig, $params, $this->fixtureImageParams);

		$this->assertEquals(99, $config->getQuality());
		$this->assertEquals('resize', $config->getMethod());

		$configArray = [
			'sizes' => '*',
			'format' => ['jpg', 'jpeg',	'gif', 'png', 'webp'],
			'methods' => ['resize', 'crop', 'fit'],
		];

		$config = new Config($configArray, $params, $this->fixtureImageParams);

		$this->assertEquals(95, $config->getQuality());
	}

	public function test_config_format_exception()
	{
		$this->expectException(ImageConfigValidateException::class);
		$config = new Config($this->fixtureConfig, $this->fixtureParams, $this->fixtureImageParams);

		$config->setFormat('mp3');
	}

	public function test_config_size_exception()
	{
		$config = [
			'sizes' => ['100x200'],
			'format' => ['jpg', 'jpeg',	'gif', 'png', 'webp'],
			'methods' => ['resize', 'crop', 'fit'],
			'quality' => 99
		];

		$this->expectException(ImageConfigValidateException::class);
		$config = new Config($config, $this->fixtureParams, $this->fixtureImageParams);

		$config->setSize([200, 100]);
	}

	public function test_config_method_exception()
	{
		$this->expectException(ImageConfigValidateException::class);
		$config = new Config($this->fixtureConfig, $this->fixtureParams, $this->fixtureImageParams);

		$config->setMethod('doSomeCute');
	}

	protected function assertSize(array $size, int $expectedWidth, int $expectedHeight): void
	{
		$this->assertIsArray($size);
		$this->assertArrayHasKey(0, $size);
		$this->assertArrayHasKey(1, $size);
		$this->assertEquals($expectedWidth, $size[0]);
		$this->assertEquals($expectedHeight, $size[1]);
	}

	protected function setUp(): void
	{
		$this->fixtureConfig = [
			'sizes' => '*',
			'format' => ['jpg', 'jpeg',	'gif', 'png', 'webp'],
			'methods' => ['resize', 'crop', 'fit'],
			'quality' => 99
		];

		$this->fixtureParams = [
			'quality' => 95,
			'method' => 'fit',
			'format' => 'webp',
			'resize' => '100x110'
		];

		$this->fixtureImageParams = array_merge(
			pathinfo('/tests/Fixtures/images/test.jpg'),
			[
				'real_path' => '/tests/Fixtures/images/test.jpg',
				'size' => [
					'w' => 200,
					'h' => 200,
				]
			]
		);
	}

	protected function tearDown(): void
	{
		$this->fixtureConfig = null;
		$this->fixtureParams = null;
		$this->fixtureImageParams = null;
	}
}