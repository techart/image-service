<?php

namespace Techart\ImageService;

readonly class Paths
{
	public function __construct(
		protected array $new,
		protected array $original
	) { }

	public function getOriginalPath(): string
	{
		return $this->original['real_path'];
	}

	public function getOriginalUrl(): string
	{
		return $this->original['url'];
	}

	public function getOriginalInfo(): array
	{
		return $this->original;
	}

	public function getPath(): string
	{
		return $this->new['real_path'];
	}

	public function getUrl(): string
	{
		return $this->new['url'];
	}

	public function getInfo(): array
	{
		return $this->new;
	}
}