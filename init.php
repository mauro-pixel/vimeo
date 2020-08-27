<?php

// Show all errors and warnings
ini_set('display_errors', 'On');
error_reporting(E_ALL);

// Load the autoloader
if (file_exists('./vendor/autoload.php')) {
	// Composer
	require_once('./vendor/autoload.php');
} else {
	// Custom
	require_once(__DIR__ . '/autoload.php');
}

// Load the configuration file.
if (!function_exists('json_decode')) {
    throw new Exception(
		'We could not find `json_decode`. `json_decode` is found in PHP 5.2 and up, but not found on many Linux ' .
		'systems due to licensing conflicts. If you are running Ubuntu try `sudo apt-get install php5-json`.'
	);
}

$config = json_decode(file_get_contents(__DIR__ . '/config.json'), true);
if (empty($config['client_id']) || empty($config['client_secret'])) {
	throw new Exception(
		'We could not locate your client id or client secret in "' . __DIR__ . '/config.json". Please create one, ' .
		'and reference config.json.example'
	);
}

return $config;

?>