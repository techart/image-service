<?php

namespace Techart\ImageService;

use Exception;
use ImageService\Contracts\ManagerContract;
use ImageService\Contracts\StorageContract;
use InvalidArgumentException;

class ImageService
{
	/**
	 * Объект для работы непосредственно с созданием и сохранением изображения
	 *
	 * @var ManagerContract
	 */
	protected ManagerContract $manager;

	/**
	 * Массив с параметрами для работы сервиса.
	 * Может иметь следущие ключи: 'resize' | 'format' | 'method' | 'quality'
	 *
	 * @var array
	 */
	protected array $config;

	/**
	 * Массив с параметрами изображения
	 *
	 * @var array
	 */
	protected array $imageParams;

	/**
	 * Оригинальный путь изображения
	 *
	 * @var string
	 */
	protected string $imagePath;

	/**
	 * Массив с параметрами для обработки изображения
	 *
	 * @var array
	 */
	protected array $params;


	/**
	 * Объект для работы с хранилищем
	 *
	 * @var StorageContract
	 */
	protected StorageContract $storage;

	/**
	 * Новая директория для сохранения измененной картинки
	 *
	 * @var string
	 */
	protected string $newDir;

	/**
	 * Новый путь для сохранения измененной картинки
	 *
	 * @var string
	 */
	protected string $newPath;

	/**
	 * Нуждается ли картинка в конвертации в другой формат
	 *
	 * @var bool
	 */
	protected bool $needConvert;

	/**
	 * Нуждается ли картинка в изменении размера
	 *
	 * @var bool
	 */
	protected bool $needResize = true;

	/**
	 * Сущность класса
	 *
	 * @var ImageService|null
	 */
	protected static ?ImageService $_instance = null;

	/**
	 * @param ManagerContract $manger
	 * @param StorageContract $storage
	 * @param array $config
	 */
	private function __construct(ManagerContract $manger, StorageContract $storage, array $config)
	{
		$this->manager = $manger;
		$this->storage = $storage;
		$this->config = $config;
	}

	/**
	 * Получение сущности класса
	 *
	 * @param ManagerContract $manger
	 * @param StorageContract $storage
	 * @param array $config
	 * @return static
	 */
	public static function getInstance(ManagerContract $manger, StorageContract $storage, array $config): self
	{
		return self::$_instance ??= new self($manger, $storage, $config);
	}

	/**
	 * Подготавливает основные параметры для дальнейшей работы с картинкой
	 *
	 * @throws InvalidArgumentException|Exception
	 */
	public function setup(string $path, array|string $params = []): static
	{
		$this->imagePath = $path;
		$this->params = is_array($params) ? $params : $this->getParamsByString($params);

		$this->validateParams();
		$this->setImageParams();
		$this->setNeedConvert();

		return $this;
	}

	/**
	 * Возвращяет путь до модифицированного изображения
	 *
	 * @return string
	 */
	public function getBasePath(): string
	{
		return $this->newPath;
	}

	/**
	 * @see StorageContract::path()
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->storage->path($this->newPath);
	}

	/**
	 * @see StorageContract::url()
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->storage->url($this->newPath);
	}

	/**
	 * Возвращает модифицированные картинки по пути оригинального
	 *
	 * @param string $returned_path
	 * @return array
	 */
	public function getModifyImages(string $returned_path = 'path'): array
	{
		$this->setImageParams();

		$images = [];
		$name = $this->imageParams['path_info']['filename'];
		$dir = $this->imageParams['path_info']['dirname'];
		$originPath = $dir . '/' . $this->imageParams['path_info']['basename'];
		$files = $this->storage->files($dir, true);

		foreach ($files as $file) {
			$pathInfo = pathinfo($file);
			$path = $pathInfo['dirname'] . '/' . $pathInfo['basename'];
			if ($pathInfo['filename'] === $name && $originPath !== $path) {
				$images[] = match ($returned_path) {
					'path' => $path,
					'url' => $this->storage->url($path),
					'storage' => $this->storage->path($path),
				};
			}
		}

		return $images;
	}

	/**
	 * Проверяет есть ли у картинки изменненые копии
	 *
	 * @return bool
	 */
	public function haveModifyImages(): bool
	{
		$this->setImageParams();

		$name = $this->imageParams['path_info']['filename'];
		$dir = $this->imageParams['path_info']['dirname'];
		$originPath = $dir . '/' . $this->imageParams['path_info']['basename'];
		$files = $this->storage->files($dir, true);

		foreach ($files as $file) {
			$pathInfo = pathinfo($file);
			$path = $pathInfo['dirname'] . '/' . $pathInfo['basename'];

			if ($pathInfo['filename'] === $name && $originPath !== $path) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Удаляет все изображения, связанные с оригинальным
	 *
	 * @param bool $deleteOrigin
	 * @return void
	 */
	public function delete(bool $deleteOrigin = true): void
	{
		$images = $this->getModifyImages();

		foreach ($images as $image) {
			$this->storage->delete($image);
		}

		if ($deleteOrigin) {
			$this->storage->delete($this->imageParams['path_info']['dirname'] . '/' . $this->imageParams['path_info']['basename']);
		}
	}

	/**
	 * Проводит манипуляции с изображением в рамках имеющихся параметров
	 *
	 * @return static
	 */
	public function process(): static
	{
		$this->setNewDir();
		$this->setNewPath();

		if (!$this->storage->exists($this->newDir)) {
			$this->storage->makeDirectory($this->newDir);
		}

		if (!$this->storage->exists($this->newPath)) {
			$this->manager->makeImage($this->imageParams['real_path']);
			$this->manager->setQuality($this->params['quality']);

			if ($this->needResize) {
				[$w, $h] = $this->getResizeValues();

				$this->manager->resizeImage($this->params['method'], $w, $h);
			}

			if ($this->needConvert) {
				$this->manager->convertImage($this->params['format']);
			}

			$this->manager->saveImage($this->storage->path($this->newPath));
		}

		return $this;
	}

	/**
	 * Проверяет необоходимали конвертация в другой формат
	 *
	 * @return void
	 */
	protected function setNeedConvert(): void
	{
		if (!isset($this->params['format'])) {
			$this->needConvert = false;
			return;
		}

		if (strtolower($this->imageParams['path_info']['extension']) === strtolower($this->params['format'])) {
			$this->needConvert = false;
			return;
		}

		if (
			$this->imageParams['path_info']['extension'] === 'jpg'
			&& $this->params['format'] === 'jpeg'
		) {
			$this->needConvert = false;
			return;
		}

		if (
			$this->imageParams['path_info']['extension'] === 'jpeg'
			&& $this->params['format'] === 'jpg'
		) {
			$this->needConvert = false;
			return;
		}

		$this->needConvert = true;
	}

	/**
	 * Создает новый путь до картинки
	 *
	 * @return void
	 */
	protected function setNewPath(): void
	{
		$this->newPath = $this->needConvert
			? $this->newDir . $this->imageParams['path_info']['filename'] . '.' . $this->params['format']
			: $this->newDir . $this->imageParams['path_info']['basename'];
	}

	/**
	 * Создает директорию на основе параметров для манимуляции с изображением
	 *
	 * @return void
	 */
	protected function setNewDir(): void
	{
		$newDir = $this->imageParams['path_info']['dirname'] . '/modify/' . $this->params['method'] . '/';

		if ($this->needResize) {
			$newDir .= $this->params['resize'] . '/';
		}

		if ($this->needConvert) {
			$newDir .= $this->params['format'] . '/';
		}

		$this->newDir = $newDir;
	}

	/**
	 * Возвращает числовые значения ширины и высоты картинки
	 *
	 * @return array
	 */
	protected function getResizeValues(): array
	{
		return array_map(function ($item) {
			return (int)$item;
		}, explode('x', $this->params['resize']));
	}

	/**
	 * Устанавливает параметры картинки
	 *
	 * @return void
	 */
	protected function setImageParams(): void
	{
		$storageStr = 'storage/';
		$this->imageParams = [
			'path_info' => pathinfo(str_replace($storageStr, '', $this->imagePath)),
			'real_path' => $this->storage->path($this->imagePath)
		];
	}

	/**
	 * Устанавливает параметры в массив из переданной строки
	 *
	 * @param string $params
	 * @return array
	 */
	protected function getParamsByString(string $params): array
	{
		return [
			'resize' => $this->extractValue($params, 'r'),
			'format' => $this->extractValue($params, 'f'),
			'quality' => $this->extractValue($params, 'q'),
			'method' => $this->extractValue($params, 'm'),
		];
	}

	/**
	 * Проверяет значения параметров
	 *
	 * @throws Exception
	 */
	protected function validateParams(): void
	{
		if (isset($this->params['resize'])) {
			$this->validateSize();
		} else {
			$this->needResize = false;
		}

		if (isset($this->params['format'])) {
			$this->validateFormat();
		}

		$this->validateQuality();
		$this->validateMethod();
	}

	/**
	 * Проверяет доступность метода для обработки изображений
	 *
	 * @throws InvalidArgumentException
	 */
	protected function validateMethod(): void
	{
		if (!isset($this->params['method'])) {
			$this->params['method'] = 'resize';
		}

		if (!in_array($this->params['method'], $this->config['methods'])) {
			throw new InvalidArgumentException('The method for editing the image is not allowed');
		}
	}

	/**
	 * Проверяет доступность размеров изображения
	 *
	 * @throws InvalidArgumentException
	 */
	protected function validateSize(): void
	{
		if ($this->config['sizes'] === '*') {
			return;
		}

		if (!in_array($this->params['resize'], $this->config['sizes'])) {
			throw new InvalidArgumentException('Image size is not allowed');
		}
	}

	/**
	 * Проверяет доступность формата изображения
	 *
	 * @throws InvalidArgumentException
	 */
	protected function validateFormat(): void
	{
		if (!in_array($this->params['format'], $this->config['format'])) {
			throw new InvalidArgumentException('The image format is not allowed');
		}
	}

	/**
	 * Валидирует качество картинки,
	 * корректирует в случаее не правильного значения из параметров
	 *
	 * @return void
	 */
	protected function validateQuality(): void
	{
		if (!isset($this->params['quality'])) {
			$this->params['quality'] = 95;
		}

		if ($this->params['quality'] < 1) {
			$this->params['quality'] = 1;
		}

		if ($this->params['quality'] > 100) {
			$this->params['quality'] = 100;
		}
	}

	/**
	 * @param $imageString
	 * @param $char
	 * @return string|null
	 */
	protected function extractValue($imageString, $char): ?string
	{
		preg_match('/' . $char . '\/(.*?)\//', $imageString, $matches);
		return $matches[1] ?? null;
	}
}