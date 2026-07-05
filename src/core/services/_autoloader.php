<?php

if (!defined('BASE_DIR')) {
	define('BASE_DIR', dirname(__DIR__, 3));
}

$autoload_map = [];

$dirs = [
	BASE_DIR . '/src',
	BASE_DIR . '/common'
];

foreach ($dirs as $dir) {
	if (!is_dir($dir)) continue;

	$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS));

	foreach ($rii as $file) {
		if (!$file->isFile()) continue;
		if (strtolower($file->getExtension()) !== 'php') continue;

		$basename = $file->getBasename('.php');
		$key = strtolower($basename);
		if (!isset($autoload_map[$key])) {
			$autoload_map[$key] = $file->getPathname();
		}
	}
}

spl_autoload_register(function ($class) use ($autoload_map) {

	$short = $class;
	if (strpos($class, '\\') !== false) {
		$parts = explode('\\', $class);
		$short = end($parts);
	}

	$key = strtolower($short);

	if (isset($autoload_map[$key]) && file_exists($autoload_map[$key])) {
		require_once $autoload_map[$key];
		return true;
	}

	$candidate = BASE_DIR . '/src/' . str_replace('\\', '/', $class) . '.php';
	if (file_exists($candidate)) {
		require_once $candidate;
		return true;
	}

	foreach ($autoload_map as $path) {
		if (strcasecmp(basename($path, '.php'), $short) === 0) {
			require_once $path;
			return true;
		}
	}

	return false;
});

