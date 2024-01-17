<?php

namespace Techart\ImageService;

use Techart\ImageService\Contracts\ManagerContract;
use Techart\ImageService\Contracts\StorageContract;
use Techart\ImageService\Exceptions\ImageConfigValidateException;

class Service
{
	protected static ?self $_instance = null;

	protected ManagerContract $manager;
	protected StorageContract $storage;
	protected array $config;
	protected array $params;
	protected array $imageParams;
	protected string $imagePath;

	protected function __construct(ManagerContract $manger, StorageContract $storage, array $config)
	{
		$this->manager = $manger;
		$this->storage = $storage;
		$this->config = $config;
	}

	public static function getInstance(ManagerContract $manger, StorageContract $storage, array $config): self
	{
		return self::$_instance ??= new self($manger, $storage, $config);
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	public function modify(string $path, array|string $params = []): Processor
	{
		$this->imagePath = $path;
		$this->imageParams = $this->storage->imageInfo($this->imagePath);
		$this->params = is_array($params) ? $params : $this->setParamsByString($params);

		$config = new Config(
			$this->config,
			$this->params,
			$this->imageParams
		);

		return new Processor(
			$this->manager,
			$this->storage,
			$config,
			$params['extra'] ?? []
		);
	}

	public function storage(string $path): Storage
	{
		$this->imagePath = $path;
		$this->imageParams = $this->storage->imageInfo($this->imagePath);

		return new Storage(
			$this->imageParams,
			$this->storage
		);
	}

	protected function setParamsByString(string $params): array
	{
		return [
			'resize' => $this->extractValue($params, 'r'),
			'format' => $this->extractValue($params, 'f'),
			'quality' => $this->extractValue($params, 'q'),
			'method' => $this->extractValue($params, 'm'),
		];
	}

	protected function extractValue($imageString, $char): ?string
	{
		preg_match('/' . $char . '\/(.*?)\//', $imageString, $matches);
		return $matches[1] ?? null;
	}
}