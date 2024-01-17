<?php

namespace Techart\ImageService\Storages;

use Techart\ImageService\Contracts\StorageContract;

class StandardStorage implements StorageContract
{
	protected static ?StandardStorage $_instance = null;

	protected string $docRoot = '';

	public function __construct()
	{
		$this->docRoot = $_SERVER['DOCUMENT_ROOT'];
	}
	public static function getInstance(): self
	{
		return self::$_instance ??= new self;
	}

	public function path(string $path): string
	{
		return $this->withDocRoot($path);
	}

	public function url(string $path): string
	{
		if ($this->pathHasDocRoot($path)) {
			return $this->withoutDocRoot($path);
		}

		return $path;
	}

	public function files(string $path, bool $recursive = false): array
	{
		if (!$this->pathHasDocRoot($path)) {
			$path = $this->withDocRoot($path);
		}

		$files = [];

		if ($handle = opendir($path)) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					$file_path = '/' . $path . '/' . $entry;

					if (is_file($file_path)) {
						$files[] = $file_path;
					} elseif (is_dir($file_path) && $recursive) {
						$sub_files = $this->files($file_path, true);
						$files = array_merge($files, $sub_files);
					}
				}
			}

			closedir($handle);
		}

		return array_map([$this, 'withoutDocRoot'], $files);
	}

	public function delete(string $path, $withDocRoot = true): void
	{
		if ($withDocRoot) {
			$path = $this->withDocRoot($path);
		}

		unlink($path);
	}

	public function exists(string $path): bool
	{
		return file_exists($this->withDocRoot($path));
	}

	public function makeDirectory(string $path): void
	{
		$dir = $this->withDocRoot($path);
		mkdir($dir, 0755, true);
	}

	public function imageInfo(string $path): array
	{
		$params = getimagesize($this->path($path));

		return array_merge(
			pathinfo($path),
			[
				'real_path' => $this->path($path),
				'path' => $path,
				'mime' => $params['mime'],
				'size' => [
					'w' => $params[0],
					'h' => $params[1],
					'string' => $params[3]
				]
			]
		);
	}

	protected function withDocRoot(string $path): string
	{
		return $this->replaceSlash($this->docRoot . $path);
	}

	protected function withoutDocRoot(string $path): string
	{
		return $this->replaceSlash(str_replace($this->docRoot, '', $path));
	}

	private function pathHasDocRoot(string $path): bool
	{
		return str_contains($this->replaceSlash($path), $this->replaceSlash($this->docRoot));
	}

	protected function replaceSlash($path): string
	{
		return str_replace('//', '/', $path);
	}
}