<?php

use Vimeo\Vimeo;

if (php_sapi_name() != 'cli-server') {
    echo 'You must run the auth script via "php -S localhost:8080 auth.php"' . "\n";
    exit();
}

$config = require(__DIR__ . '/init.php');

session_start();
const REDIRECT_URI = 'http://localhost:8000/callback';

if (preg_match('%^/callback%', $_SERVER["REQUEST_URI"])) {
    handleCallback($config);
} elseif (preg_match('%^/reset%', $_SERVER["REQUEST_URI"])) {
    handleReset();
} else {
    handleDefault($config);
}


// When vimeo redirects the user back to your callback url, this method is executed
// It validates the state parameter, and then exchanges the authorization code for an access token
function handleCallback($config)
{
    // Callback url, respond to the information sent from vimeo and turn that into a usable access token
    if ($_SESSION['state'] != $_GET['state']) {
        echo 'Something is wrong. Vimeo sent back a different state than this script was expecting. Please let vimeo know that this has happened';
    }

    $lib = new Vimeo($config['client_id'], $config['client_secret']);
    $tokens = $lib->accessToken($_GET['code'], REDIRECT_URI);
    if ($tokens['status'] == 200) {
        $_SESSION['access_token'] = $tokens['body']['access_token'];
        echo 'Successful authentication. Please go to <a href="http://localhost:8000">localhost:8000</a>';
    } else {
        echo "Unsuccessful authentication";
        var_dump($tokens);
    }
}

// This is the default page view.
// If the user is authenticated we make an API request to /me, and dump out the details
// If the user is not authenticated we display the authorization endpoint, so the user can authenticate
function handleDefault($config)
{
    // Root url, check if the user has already authenticated or not
    if (empty($_SESSION['access_token'])) {
        echo "This is an unauthenticated request to /users/dashron<br />";
        $lib = new Vimeo($config['client_id'], $config['client_secret']);
        $_SESSION['state'] = base64_encode(openssl_random_pseudo_bytes(30));

        echo 'To authenticate you should click <a href="'
            . $lib->buildAuthorizationEndpoint(REDIRECT_URI, 'public', $_SESSION['state'])
            . '">here</a><br />';
    } else {
        echo "This is an authenticated request to /me<br />";
        echo 'To start over click <a href="http://localhost:8000/reset">here</a><br />';
        $lib = new Vimeo($config['client_id'], $config['client_secret'], $_SESSION['access_token']);
        $me = $lib->request('/me');
        var_dump($me);
    }

    // Kill the session and redirect to the homepage. This has no impact on the API, it just lets you re-authenticate
    function handleReset()
    {
        session_destroy();
        header('Location: http://localhost:8000');
        exit();
    }
}
