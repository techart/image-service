<?php
function removeDivRecursive(string $dir): void
{
	if (!$dir || !file_exists($dir)) {
		return;
	}

	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object === "." || $object === "..") {
				continue;
			}

			if (is_dir($dir. '/' .$object) && !is_link($dir. '/' .$object)) {
				removeDivRecursive($dir. '/' .$object);
				continue;
			}

			unlink($dir. '/' .$object);
		}
		rmdir($dir);
	}
}
