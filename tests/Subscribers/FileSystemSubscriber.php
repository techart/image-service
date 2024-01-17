<?php

namespace Tests\Subscribers;

use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionFinishedSubscriber;

final class FileSystemSubscriber implements ExecutionFinishedSubscriber
{
	protected array $removableDirs = [
		'/tests/Fixtures/images/tmp/',
		'/tests/Fixtures/gumlet/save/',
		'/tests/Fixtures/intervention/save/',
		'/tests/Fixtures/service/modify/',
		'/tests/Fixtures/service/tmp/',
	];

	public function notify(ExecutionFinished $event): void
	{
		$this->removeTmpDirs();
	}

	protected function removeTmpDirs(): void
	{
		foreach ($this->removableDirs as $dir) {
			removeDivRecursive($_SERVER['DOCUMENT_ROOT'] . $dir);
		}
	}
}