<?php

namespace Tests\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Techart\ImageService\Config;
use Techart\ImageService\Contracts\ManagerContract;
use Techart\ImageService\Managers\GumletImageResizeManager;
use Techart\ImageService\Managers\InterventionImageManager;
use Techart\ImageService\Processor;
use Techart\ImageService\Storages\StandardStorage;

class ProcessorTest extends TestCase
{
	protected ?StandardStorage $fixtureStorage;
	protected ?Config $fixtureConfig;
	protected ?array $fixtureImageManagerParams;

	#[DataProvider('managersMap')]
	public function test_processor_params(ManagerContract $manager)
	{
		$processor = new Processor($manager, $this->fixtureStorage, $this->fixtureConfig, $this->fixtureImageManagerParams);
		$params = $processor->getParams();

		$this->assertEquals($this->fixtureConfig->getMethod(), $params['method']);
		$this->assertEquals($this->fixtureConfig->getSize(), $params['size']);
		$this->assertEquals($this->fixtureConfig->getQuality(), $params['quality']);
		$this->assertEquals($this->fixtureConfig->getFormat(), $params['format']);
	}

	#[DataProvider('managersMap')]
	public function test_processor_setters(ManagerContract $manager)
	{
		$processor = new Processor($manager, $this->fixtureStorage, $this->fixtureConfig, $this->fixtureImageManagerParams);
		$processor->setSizes([300, 300]);
		$processor->setMethod('crop');
		$processor->setFormat('jpg');
		$processor->setQuality(10);

		$params = $processor->getParams();

		$this->assertEquals($this->fixtureConfig->getMethod(), $params['method']);
		$this->assertEquals($this->fixtureConfig->getSize(), $params['size']);
		$this->assertEquals($this->fixtureConfig->getQuality(), $params['quality']);
		$this->assertEquals($this->fixtureConfig->getFormat(), $params['format']);

		$this->assertEquals('crop', $params['method']);
		$this->assertEquals('jpg', $params['format']);
		$this->assertEquals([300, 300], $params['size']);
		$this->assertEquals(10, $params['quality']);
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
		$this->fixtureImageManagerParams = [];
		$this->fixtureStorage = StandardStorage::getInstance();
		$this->fixtureConfig = new Config(
			[
				'sizes' => '*',
				'format' => ['jpg', 'jpeg',	'gif', 'png', 'webp'],
				'methods' => ['resize', 'crop', 'fit'],
				'quality' => 99
			],
			[
				'quality' => 95,
				'method' => 'fit',
				'format' => 'webp',
				'resize' => '100x110'
			],
			array_merge(
				pathinfo('/tests/Fixtures/images/test.jpg'),
				[
					'real_path' => '/tests/Fixtures/images/test.jpg',
					'size' => [
						'w' => 200,
						'h' => 200,
					]
				]
			)
		);
	}

	protected function tearDown(): void
	{
		$this->fixtureStorage = null;
		$this->fixtureImageManagerParams = null;
		$this->fixtureConfig = null;
	}
}