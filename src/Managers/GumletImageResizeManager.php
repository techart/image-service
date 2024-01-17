<?php

namespace Techart\ImageService\Managers;

use Gumlet\ImageResize;
use Gumlet\ImageResizeException;
use Techart\ImageService\Contracts\ManagerContract;
use Techart\ImageService\Exceptions\ImageManagerException;

class GumletImageResizeManager implements ManagerContract
{
	private ImageResize $image;

	private int $quality = 95;
	private int $with = 0;
	private int $height = 0;
	private ?int $format = null;
    private bool $gamma = false;

    public function __construct($gamma = false)
    {
        $this->gamma = $gamma;
    }

    /**
	 * @throws ImageResizeException
	 */
	public function makeImage(string $path): void
	{
		$this->image = new ImageResize($path);
        $this->image->gamma($this->gamma);
	}

	/**
	 * @throws ImageManagerException
	 */
	public function resizeImage(string $method, int $with, int $height, array $params = []): void
	{
		$this->with = $with;
		$this->height = $height;

		if (method_exists($this, $method)) {
			$this->{$method}($params);
			return;
		}

		throw new ImageManagerException('The method ' . $method . 'not allow');
	}

	/**
	 * @param string $format
	 * @param int $quality
	 * @param array $params
	 * @throws ImageManagerException
	 */
	public function convertImage(string $format, int $quality = 95, array $params = []): void
	{
		$this->quality = $quality;

		switch (strtolower($format)) {
			case 'gif':
			case 'image/gif':
				$this->format = IMAGETYPE_GIF;
				break;

			case 'png':
			case 'image/png':
				$this->format = IMAGETYPE_PNG;
				break;

			case 'jpg':
			case 'jpeg':
			case 'image/jpg':
			case 'image/jpeg':
				$this->format = IMAGETYPE_JPEG;
				break;

			case 'webp':
			case 'image/webp':
				$this->format = IMAGETYPE_WEBP;
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

	/**
	 * @throws ImageResizeException
	 */
	public function saveImage(string $path): void
	{
		$this->image->save($path, $this->format, $this->quality);
	}

	private function resize(array $params = []): void
	{
		$allow_enlarge = $params['allow_enlarge'] ?? false;

		if ($this->height === 0) {
			$this->image->resizeToWidth($this->with, $allow_enlarge);
		} elseif ($this->with === 0) {
			$this->image->resizeToHeight($this->height, $allow_enlarge);
		} else {
			$this->image->resize($this->with, $this->height, $allow_enlarge);
		}
	}

	private function fit(array $params = []): void
	{
		$allow_enlarge = $params['allow_enlarge'] ?? false;

		$this->image->resizeToBestFit($this->with, $this->height, $allow_enlarge);
	}

	private function crop(array $params = []): void
	{
		$allow_enlarge = $params['allow_enlarge'] ?? false;
		$position = $params['position'] ?? ImageResize::CROPCENTER;

		$this->image->crop($this->with, $this->height, $allow_enlarge, $position);
	}
}