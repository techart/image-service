<?php

namespace Techart\ImageService\Exceptions;

use Exception;
use Throwable;

class ImageConfigValidateException extends Exception
{
	public function __construct(
		string $message = '',
		int $code = 0,
		?Throwable $previous = null,
	) {
		parent::__construct($message, $code, $previous);
	}
}