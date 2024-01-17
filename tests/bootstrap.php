<?php
$root = dirname(__DIR__);

if (!is_file(sprintf('%s/vendor/autoload.php', $root))) {
	$root = dirname(__DIR__, 4);
}

$_SERVER['DOCUMENT_ROOT'] = $root;

require sprintf('%s/vendor/autoload.php', $root);
require sprintf('%s/tests/helpers.php', $root);