<?php

namespace Techart\ImageService;

use Techart\ImageService\Contracts\StorageContract;

readonly class Storage
{
	public function __construct(
		private array $imageParams,
		private StorageContract $storage
	) { }

	public function getModifyImages(): array
	{
		$images = [];
		$name = $this->imageParams['filename'];
		$dir = $this->imageParams['dirname'];
		$originPath = ltrim($dir . '/' . $this->imageParams['basename'], '/');
		$files = $this->storage->files($dir, true);

		foreach ($files as $file) {
			$pathInfo = pathinfo($file);
			$path = $pathInfo['dirname'] . '/' . $pathInfo['basename'];

			if ($pathInfo['filename'] === $name && ltrim($path, '/') !== $originPath) {
				$images[] = $path;
			}
		}

		return $images;
	}

	public function getOriginalImage(): ?string
	{
		return $this->imageParams['path'];
	}

	public function haveModifyImages(): bool
	{
		$name = $this->imageParams['filename'];
		$dir = $this->imageParams['dirname'];
		$originPath = ltrim($dir . '/' . $this->imageParams['basename'], '/');
		$files = $this->storage->files($dir, true);

		foreach ($files as $file) {
			$pathInfo = pathinfo($file);
			$path = $pathInfo['dirname'] . '/' . $pathInfo['basename'];

			if ($pathInfo['filename'] === $name && ltrim($path, '/') !== $originPath) {
				return true;
			}
		}

		return false;
	}

	public function delete(bool $deleteOrigin = true): void
	{
		$images = $this->getModifyImages();

		foreach ($images as $image) {
			$this->storage->delete($image);
		}

		if ($deleteOrigin) {
			$this->storage->delete($this->imageParams['path']);
		}
	}
}