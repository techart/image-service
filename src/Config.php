<?php

namespace Techart\ImageService;

use InvalidArgumentException;
use Techart\ImageService\Exceptions\ImageConfigValidateException;

class Config
{
	protected int $quality;
	protected bool $convert;
	protected bool $resize;
	protected ?array $size;
	protected string $method;
	protected string $format;
	protected array $imageParams;
	protected array $config;
	protected int $defaultQuality = 95;
	protected string $defaultMethod = 'resize';

	/**
	 * @throws ImageConfigValidateException
	 */
	public function __construct
	(
		array $config,
		array $params,
		array $imageParams,
	) {
		$this->config = $config;
		$this->imageParams = $imageParams;

		$quality = $params['quality'] ?? null;
		$method = $params['method'] ?? null;

		if (!$quality) {
			$quality = $this->config['quality'] ?? $this->defaultQuality;
		}

		if (!$method) {
			$method = $this->defaultMethod;
		}

		$this->setFormat($params['format'] ?? $imageParams['extension']);
		$this->setSize($this->getResizeValues($params['resize'] ?? null));
		$this->setQuality($quality);
		$this->setMethod($method);
	}

	public function getQuality(): int
	{
		return $this->quality;
	}

	public function isConvert(): bool
	{
		return $this->convert;
	}

	public function isResize(): bool
	{
		return $this->resize;
	}

	public function getSize(): ?array
	{
		return $this->size;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getFormat(): string
	{
		return $this->format;
	}

	public function getImageParams(): array
	{
		return $this->imageParams;
	}

	public function getPath()
	{
		return $this->imageParams['real_path'];
	}

	public function setQuality(int $quality): void
	{
		$this->quality = $quality;
		$this->validateQuality();
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	public function setSize(null|string|array $size): void
	{
		$this->size = $this->getResizeValues($size);
		$this->validateSize();
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	public function setMethod(string $method): void
	{
		$this->method = $method;

		$this->validateMethod();
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	public function setFormat(string $format): void
	{
		$this->format = $format;
		$this->validateFormat();
		$this->setConvert();
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	protected function validateFormat(): void
	{
		if ($this->format && !in_array($this->format, $this->config['format'])) {
			throw new ImageConfigValidateException('The image format is not allowed');
		}
	}

	protected function setConvert(): void
	{
		if (!isset($this->format)) {
			$this->convert = false;
			return;
		}

		if (strtolower($this->imageParams['extension'] ?: '') === strtolower($this->format ?: '')) {
			$this->convert = false;
			return;
		}

		if (
			$this->imageParams['extension'] === 'jpg'
			&& $this->format === 'jpeg'
		) {
			$this->convert = false;
			return;
		}

		if (
			$this->imageParams['extension'] === 'jpeg'
			&& $this->format === 'jpg'
		) {
			$this->convert = false;
			return;
		}

		$this->convert = true;
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	protected function validateSize(): void
	{
		$this->resize = is_array($this->size)
			&& ($this->imageParams['size']['w'] !== $this->size[0] || $this->imageParams['size']['h'] !== $this->size[1]);

		if ($this->config['sizes'] === '*') {
			return;
		}

		if (!isset($this->config['sizes']) || !is_array($this->config['sizes'])) {
			throw new ImageConfigValidateException('Incorrect size key in image config');
		}

		if (!in_array($this->sizeToString($this->size), $this->config['sizes'])) {
			throw new ImageConfigValidateException('Image size is not allowed');
		}
	}

	protected function validateQuality(): void
	{
		if (!isset($this->quality)) {
			$this->quality = $this->config['quality'] ?? 95;
			return;
		}

		if ($this->quality < 1) {
			$this->quality = 1;
			return;
		}

		if ($this->quality > 100) {
			$this->quality = 100;
		}
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	protected function validateMethod(): void
	{
		if (!$this->method) {
			$this->method = 'resize';
		}

		if (!in_array($this->method, $this->config['methods'])) {
			throw new ImageConfigValidateException('The method for editing the image is not allowed');
		}
	}

	/**
	 * @throws ImageConfigValidateException
	 */
	protected function getResizeValues(null|array|string $sizes): ?array
	{
		if (!$sizes) {
			return null;
		}

		if (is_string($sizes)) {
			return array_map(function ($item) {
				return (int)$item;
			}, explode('x', $sizes));
		}

		if (is_array($sizes)) {
			return [
				(int)$sizes[0],
				(int)$sizes[1],
			];
		}

		throw new ImageConfigValidateException('The size format is not allowed');
	}

	protected function sizeToString(?array $size): string
	{
		if (!$size) {
			return '*';
		}

		return $size[0].'x'.$size[1];
	}
}