<?php

namespace Techart\ImageService\Managers;

use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Techart\ImageService\Contracts\ManagerContract;
use InvalidArgumentException;

class GumletImageResizeManager implements ManagerContract
{
	private ImageResize $image;

	private int $quality = 95;

	private int $with = 0;

	private int $height = 0;

	private int|null $format = null;

	/**
	 * @throws ImageResizeException
	 */
	public function makeImage(string $path): void
	{
		$this->image = new ImageResize($path);
        $this->image->gamma(true);
	}

	public function resizeImage(string $method, int $with, int $height): void
	{
		$this->with = $with;
		$this->height = $height;

		$this->{$method}();
	}

	public function convertImage(string $format, int $quality = 95): void
	{
		$this->quality = $quality;

		switch (strtolower($format)) {
			case 'gif':
			case 'image/gif':
				$this->format = IMAGETYPE_GIF;
				break;

			case 'png':
			case 'image/png':
			case 'image/x-png':
				$this->format = IMAGETYPE_PNG;
				break;

			case 'jpg':
			case 'jpeg':
			case 'jfif':
			case 'image/jp2':
			case 'image/jpg':
			case 'image/jpeg':
			case 'image/pjpeg':
			case 'image/jfif':
				$this->format = IMAGETYPE_JPEG;
				break;

			case 'bmp':
			case 'ms-bmp':
			case 'x-bitmap':
			case 'x-bmp':
			case 'x-ms-bmp':
			case 'x-win-bitmap':
			case 'x-windows-bmp':
			case 'x-xbitmap':
			case 'image/ms-bmp':
			case 'image/x-bitmap':
			case 'image/x-bmp':
			case 'image/x-ms-bmp':
			case 'image/x-win-bitmap':
			case 'image/x-windows-bmp':
			case 'image/x-xbitmap':
				$this->format = IMAGETYPE_BMP;
				break;

			case 'webp':
			case 'image/webp':
			case 'image/x-webp':
				$this->format = IMAGETYPE_WEBP;
				break;

			default:
				throw new InvalidArgumentException('Format ' . $format . 'not supported');
		}
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

	/**
	 * @throws ImageResizeException
	 */
	public function saveImage(string $path): void
	{
		$this->image->save($path, $this->format, $this->quality);
	}

	private function resize()
	{
		if ($this->height === 0) {
			$this->image->resizeToWidth($this->with);
		} elseif ($this->with === 0) {
			$this->image->resizeToHeight($this->height);
		} else {
			$this->image->resize($this->with, $this->height);
		}
	}

	private function fit()
	{
		$this->image->resizeToBestFit($this->with, $this->height);
	}

	private function crop()
	{
		$this->image->crop($this->with, $this->height);
	}
}