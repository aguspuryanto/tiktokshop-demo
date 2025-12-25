<?php
// callback.php
session_start();
require __DIR__ . '/vendor/autoload.php';

// use EcomPHP\TiktokShop\Client;

// // Configuration (same as in your main file)
// $config = [
//     'test' => [
//         'app_key'      => '6frt0hopcl3dl',
//         'app_secret'   => '78fcd055db1f22403304b68500417f4f3561996e',
//     ]
// ];

// $client = new Client($config['test']['app_key'], $config['test']['app_secret']);

// if (isset($_GET['code'], $_GET['state'])) {
//     try {
//         $auth = $client->auth();
//         $tokenData = $auth->getToken($_GET['code']);
        
//         // Store tokens in session
//         $_SESSION['access_token'] = $tokenData['access_token'];
//         $_SESSION['refresh_token'] = $tokenData['refresh_token'];
//         $_SESSION['expires_in'] = time() + $tokenData['expires_in'];
        
//         // Redirect back to the main page
//         header('Location: index.php');
//         exit;
//     } catch (Exception $e) {
//         die('Error getting access token: ' . $e->getMessage());
//     }
// } else {
//     die('Missing required parameters');
// }

require __DIR__ . '/lib/TikTokShop.php';

use App\TikTokShop\TikTokShop;

$config = require __DIR__ . '/config.php';

if (isset($_GET['code'], $_GET['state'])) {
    if ($_GET['state'] !== $_SESSION['state']) {
        die('Invalid state parameter');
    }
    try {
        $tiktok = new TikTokShop($config);
        $tokenData = $tiktok->getAccessToken($_GET['code']);
        
        $_SESSION['access_token'] = $tokenData['access_token'];
        $_SESSION['refresh_token'] = $tokenData['refresh_token'];
        $_SESSION['expires_in'] = time() + $tokenData['expires_in'];
        
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        die($e->getMessage());
    }
} else {
    die('Missing required parameters');
}