<?php

namespace Techart\ImageService\Contracts;

interface ManagerContract
{
	/**
	 * Создает объект менеджера для работы с картинкой
	 *
	 * @param string $path
	 * @return void
	 */
	public function makeImage(string $path): void;

	/**
	 * Изменяет размер изображения по заданым параметрам
	 *
	 * @param string $method
	 * @param int $with
	 * @param int $height
	 * @param array $params
	 * @return void
	 */
	public function resizeImage(string $method, int $with, int $height, array $params = []): void;

	/**
	 * Меняет формат изображения
	 *
	 * @param string $format
	 * @param int $quality
	 * @param array $params
	 * @return void
	 */
	public function convertImage(string $format, int $quality, array $params = []): void;

	/**
	 * Устанавливает качество модифицированного изображения
	 *
	 * @param int $quality
	 * @return void
	 */
	public function setQuality(int $quality): void;

	/**
	 * Сохраняет изображение
	 *
	 * @param string $path
	 * @return void
	 */
	public function saveImage(string $path): void;
}