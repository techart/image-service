<?php

namespace Tests\Tests\Unit;

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

	#[DataProvider('managersMap')]
	public function test_service_constructor(ManagerContract $manager)
	{
		$service = Service::getInstance($manager, $this->fixtureStorage, $this->fixtureConfig);
		$service2 = Service::getInstance($manager, $this->fixtureStorage, $this->fixtureConfig);

		$this->assertInstanceOf(Service::class, $service);
		$this->assertEquals($service, $service2);
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
	}

	protected function tearDown(): void
	{
		$this->fixtureStorage = null;
		$this->fixtureConfig = null;
	}
}
