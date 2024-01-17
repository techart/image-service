<?php

namespace Techart\ImageService\Managers;

use Intervention\Image\Drivers\Imagick\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Techart\ImageService\Contracts\ManagerContract;
use Techart\ImageService\Exceptions\ImageManagerException;

class InterventionImageManager implements ManagerContract
{
	private ImageInterface|EncodedImageInterface $image;
	private ImageManager $manager;

	private int $quality = 95;

	public function __construct()
	{
		$this->manager = new ImageManager(new Driver());
	}

	public function makeImage(string $path): void
	{
		$this->image = $this->manager->read($path);
	}

	/**
	 * @throws ImageManagerException
	 */
	public function resizeImage(string $method, int $with, int $height, array $params = []): void
	{
		match($method) {
			'crop' => $this->crop($with, $height, $params),
			'fit' => $this->fit($with, $height, $params),
			'resize' => $this->resize($with, $height, $params),
			default => throw new ImageManagerException('The method ' . $method . 'not allow')
		};
	}

	/**
	 * @throws ImageManagerException
	 */
	public function convertImage(string $format, int $quality = 95, array $params = []): void
	{
		$this->quality = $quality;

		switch (strtolower($format)) {
			case 'gif':
			case 'image/gif':
				$this->image = $this->image->toGif($this->quality);
				break;

			case 'png':
			case 'image/png':
				$this->image = $this->image->toPng($this->quality);
				break;

			case 'jpg':
			case 'jpeg':
			case 'image/jpg':
			case 'image/jpeg':
				$this->image = $this->image->toJpg($this->quality);
				break;

			case 'webp':
			case 'image/webp':
				$this->image = $this->image->toWebp($this->quality);
				break;

			default:
				throw new ImageManagerException('Format ' . $format . 'not supported');
		}
	}

	/**
	 * @throws ImageManagerException
	 */
	public function setQuality(int $quality): void
	{
		if ($quality < 1) {
			throw new ImageManagerException('Quality must be more than 0');
		}

		if ($quality > 100) {
			throw new ImageManagerException('Quality must be less than 101');
		}

		$this->quality = $quality;
	}

	public function saveImage(string $path): void
	{
		$this->image->save($path, $this->quality);
	}

	protected function crop(int $w, int $h, array $params): void
	{
		$x = isset($params['x']) ? (int)$params['x'] : 0;
		$y = isset($params['y']) ? (int)$params['y'] : 0;
		$position = $params['position'] ?? 'top-left';

		$this->image->crop($w, $h, $x, $y, $position);
	}

	protected function fit(int $w, int $h, array $params): void
	{
		$allow_enlarge = $params['allow_enlarge'] ?? false;
		$position = $params['position'] ?? 'center';

		if ($allow_enlarge) {
			$this->image->cover($w, $h, $position);
			return;
		}

		$this->image->coverDown($w, $h, $position);
	}

	protected function resize(int $w, int $h, array $params): void
	{
		$allow_enlarge = $params['allow_enlarge'] ?? false;

		if ($allow_enlarge) {
			$this->resizeEnlarge($w, $h);
			return;
		}

		$this->resizeProportionally($w, $h);
	}

	protected function resizeEnlarge(int $w, int $h): void
	{
		if ($h === 0) {
			$this->image->scale(width: $w);
		} elseif ($w === 0) {
			$this->image->scale(height: $h);
		} else {
			$this->image->resize($w, $h);
		}
	}

	protected function resizeProportionally(int $w, int $h): void
	{
		if ($h === 0) {
			$this->image->scaleDown(width: $w);
		} elseif ($w === 0) {
			$this->image->scaleDown(height: $h);
		} else {
			$this->image->resizeDown($w, $h);
		}
	}
}