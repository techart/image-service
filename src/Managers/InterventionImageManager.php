<?php

namespace Techart\ImageService\Managers;

use Techart\ImageService\Contracts\ManagerContract;
use Intervention\Image\AbstractEncoder;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Imagick\Encoder;
use InvalidArgumentException;

class InterventionImageManager implements ManagerContract
{
	private Image $image;

	private ImageManager $manager;

	private AbstractEncoder $encoder;

	private int $quality = 95;

	public function __construct()
	{
		$this->manager = new ImageManager(['driver' => 'imagick']);
		$this->encoder = new Encoder();
	}

	public function makeImage(string $path): void
	{
		$this->image = $this->manager->make($path);
	}

	public function resizeImage(string $method, int $with, int $height): void
	{
		if ($height === 0) {
			$this->image->{$method}($with, null, function ($c) {
				$c->aspectRatio();
				$c->upsize();
			});
		} elseif ($with === 0) {
			$this->image->{$method}(null, $height, function ($c) {
				$c->aspectRatio();
				$c->upsize();
			});
		} else {
			$this->image->{$method}($with, $height);
		}
	}

	public function convertImage(string $format, int $quality = 95): void
	{
		$this->image = $this->encoder->process($this->image, $format, $quality);
	}

	public function setQuality(int $quality): void
	{
		if ($this->quality < 1) {
			throw new InvalidArgumentException('Quality must be more than 0');
		}

		if ($quality > 100) {
			throw new InvalidArgumentException('Quality must be less than 101');
		}

		$this->quality = $quality;
	}

	public function saveImage(string $path): void
	{
		$this->image->save($path, $this->quality);
	}
}