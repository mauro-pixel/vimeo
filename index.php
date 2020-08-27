<?php


use Vimeo\Vimeo;

$config = require(__DIR__ . '/init.php');

$lib = new Vimeo($config['client_id'], $config['client_secret']);

if (!empty($config['access_token'])) {
    $lib->setToken($config['access_token']);
    $user = $lib->request('/users/dashron');
    // } else {
    //     $user = $lib->request('/users/dashron');
}

var_dump($user);
