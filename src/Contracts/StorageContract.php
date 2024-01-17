<?php

namespace Techart\ImageService\Contracts;

interface StorageContract
{
	/**
	 * Возвращает aбсолютный путь до файла от диска
	 *
	 * @param string $path
	 * @return string
	 */
	public function path(string $path): string;

	/**
	 * Возвращает путь по которому файл может быть досутпен для получения из браузера
	 *
	 * @param string $path
	 * @return string
	 */
	public function url(string $path): string;

	/**
	 * Возвращает список фалйов в директории,
	 * при передачи второго аргумента как true, также возвращает файлы из вложенных директорий
	 *
	 * @param string $path
	 * @param bool $recursive
	 * @return array
	 */
	public function files(string $path, bool $recursive): array;

	/**
	 * Удаляет фалйл по заданному пути от диска
	 *
	 * @param string $path
	 * @return void
	 */
	public function delete(string $path): void;

	/**
	 * Проверяет существует ли файл
	 *
	 * @param string $path
	 * @return bool
	 */
	public function exists(string $path): bool;

	/**
	 * Создает директорию
	 *
	 * @param string $path
	 * @return void
	 */
	public function makeDirectory(string $path): void;

	public function imageInfo(string $path): array;
}