<?php

namespace Techart\ImageService;

use Techart\ImageService\Contracts\ManagerContract;
use Techart\ImageService\Contracts\StorageContract;
use Techart\ImageService\Exceptions\ImageConfigValidateException;

readonly class Processor
{
	protected string $newDir;
	protected string $newPath;

	public function __construct(
		protected ManagerContract $manager,
		protected StorageContract $storage,
		protected Config $config,
		protected array $imageManagerParams
	) { }

	public function setQuality(int $quality): static
	{
		$this->config->setQuality($quality);

		return $this;
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	public function setFormat(string $format): static
	{
		$this->config->setFormat($format);

		return $this;
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	public function setSizes(null|string|array $sizes): static
	{
		$this->config->setSize($sizes);

		return $this;
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	public function setMethod(string $method): static
	{
		$this->config->setMethod($method);

		return $this;
	}

	public function getParams(): array
	{
		return [
			'method' => $this->config->getMethod(),
			'format' => $this->config->getFormat(),
			'quality' => $this->config->getQuality(),
			'size' => $this->config->getSize(),
		];
	}

	public function process(): Paths
	{
		$this->setNewPaths();
		$this->makeDir();
		$this->makeImage();

		return $this->paths();
	}

	protected function makeDir(): void
	{
		if (!$this->storage->exists($this->newDir)) {
			$this->storage->makeDirectory($this->newDir);
		}
	}

	protected function makeImage(): void
	{
		if ($this->storage->exists($this->newPath)) {
			return;
		}

		$this->manager->makeImage($this->config->getPath());
		$this->manager->setQuality($this->config->getQuality());

		if ($this->config->isResize()) {
			$size = $this->config->getSize();

			$this->manager->resizeImage(
				$this->config->getMethod(),
				$size[0],
				$size[1],
				$this->imageManagerParams['resize'] ?? []
			);
		}

		if ($this->config->isConvert()) {
			$this->manager->convertImage(
				$this->config->getFormat(),
				$this->config->getQuality(),
				$this->imageManagerParams['convert'] ?? []
			);
		}

		$this->manager->saveImage(
			$this->storage->path($this->newPath)
		);
	}

	protected function paths(): Paths
	{
		$new = $this->storage->imageInfo($this->newPath);
		$new['url'] = $this->storage->url($this->newPath);

		$original = $this->config->getImageParams();
		$original['url'] = $this->storage->url($original['path']);

		return new Paths($new, $original);
	}

	protected function setNewPaths(): void
	{
		$this->setNewDir();
		$this->setNewPath();
	}

	protected function setNewDir(): void
	{
		$imageParams = $this->config->getImageParams();

		$newDir = sprintf(
			'%s/modify/%s/%s/',
			$imageParams['dirname'],
			$this->config->getQuality(),
			$this->config->getMethod(),
		);

		if ($this->config->isResize()) {
			$size = $this->config->getSize();
			$newDir .= $size[0] . 'x' . $size[1] . '/';
		}

		if ($this->config->isConvert()) {
			$newDir .= $this->config->getFormat() . '/';
		}

		$this->newDir = $newDir;
	}

	protected function setNewPath(): void
	{
		$imageParams = $this->config->getImageParams();

		$this->newPath = $this->config->isConvert()
			? $this->newDir . $imageParams['filename'] . '.' . $this->config->getFormat()
			: $this->newDir . $imageParams['basename'];
	}
}