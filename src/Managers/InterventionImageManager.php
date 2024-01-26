<?php

namespace Techart\ImageService\Managers;

use Intervention\Image\AbstractEncoder;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Imagick\Encoder;
use Techart\ImageService\Contracts\ManagerContract;
use Techart\ImageService\Exceptions\ImageManagerException;

class InterventionImageManager implements ManagerContract
{
	private Image $image;
	private ImageManager $manager;
	private AbstractEncoder $encoder;
	private int $quality = 95;
	private array $allowFormats = [
		'gif',
		'image/gif',
		'png',
		'image/png',
		'jpg',
		'jpeg',
		'image/jpg',
		'image/jpeg',
		'webp',
		'image/webp',
	];

	public function __construct()
	{
		$this->manager = new ImageManager(['driver' => 'imagick']);
		$this->encoder = new Encoder();
	}

	public function makeImage(string $path): void
	{
		$this->image = $this->manager->make($path);
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
		if (!in_array(strtolower($format), $this->allowFormats)) {
			throw new ImageManagerException('Format ' . $format . 'not supported');
		}

		$this->quality = $quality;
		$this->image = $this->encoder->process($this->image, $format, $this->quality);
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
		$x = isset($params['x']) ? (int)$params['x'] : null;
		$y = isset($params['y']) ? (int)$params['y'] : null;

		$this->image->crop($w, $h, $x, $y);
	}

	protected function fit(int $w, int $h, array $params): void
	{
		$allow_enlarge = $params['allow_enlarge'] ?? false;
		$position = $params['position'] ?? 'center';


		$this->image->fit($w, $h === 0 ? null : $h,
			function ($constraint) use ($allow_enlarge) {
					if (!$allow_enlarge) {
						$constraint->upsize();
					}
			}, $position
		);
	}

	protected function resize(int $w, int $h, array $params): void
	{
		$allow_enlarge = $params['allow_enlarge'] ?? false;

		$this->image->resize(
			$w === 0 ? null : $w,
			$h === 0 ? null : $h,
			function ($c) use ($allow_enlarge, $w, $h) {
				if ($w === 0 || $h === 0) {
					$c->aspectRatio();
				}

				if (!$allow_enlarge) {
					$c->upsize();
				}
			}
		);
	}
}