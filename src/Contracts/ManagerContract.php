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
	 * @return void
	 */
	public function resizeImage(string $method, int $with, int $height): void;

	/**
	 * Меняет формат изображения
	 *
	 * @param string $format
	 * @param int $quality
	 * @return void
	 */
	public function convertImage(string $format, int $quality): void;

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