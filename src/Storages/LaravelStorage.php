<?php

namespace Techart\ImageService\Storages;

use Techart\ImageService\Contracts\StorageContract;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

class LaravelStorage implements StorageContract
{
    private static ?LaravelStorage $_instance = null;

    private Filesystem $storage;

    private  function __construct(string $disk)
    {
        $this->storage = Storage::disk($disk);
    }

    public static function getInstance(string $disk = 'public'): self
    {
        return self::$_instance ??= new self($disk);
    }

    public function path(string $path): string
    {
        return $this->storage->path($path);
    }

    public function url(string $path): string
    {
        return $this->storage->url($path);
    }

    public function files(string $path, bool $recursive): array
    {
        return $this->storage->files($path, $recursive);
    }

    public function delete(string $path): void
    {
        $this->storage->delete($path);
    }

    public function exists(string $path): bool
    {
        return $this->storage->exists($path);
    }

    public function makeDirectory(string $path): void
    {
        $this->storage->makeDirectory($path);
    }
}