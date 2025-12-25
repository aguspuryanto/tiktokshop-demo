<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

// use EcomPHP\TiktokShop\Client;

// // Start session
// session_start();

// // Configuration, v1 = 202212, v2 = 202309 
// $config = require __DIR__ . '/config.php';

// $app_key = $config['test']['app_key'];
// $app_secret = $config['test']['app_secret'];

// // Initialize client
// $client = new Client($app_key, $app_secret);
// $client->setShopCipher($config['test']['shop_cipher']);
// $client->useVersion($config['test']['api_version']);

// // Generate a random state parameter for security
// if (empty($_SESSION['state'])) {
//     $_SESSION['state'] = bin2hex(random_bytes(16));
// }

// // Check if this is the OAuth callback
// if (isset($_GET['code']) && isset($_GET['state'])) {
//     // Verify state to prevent CSRF
//     if ($_GET['state'] !== $_SESSION['state']) {
//         die('Invalid state parameter');
//     }

//     try {
//         // Exchange the authorization code for an access token
//         $auth = $client->auth();
//         $tokenData = $auth->getToken($_GET['code']);
        
//         // Store the access token in the session
//         $_SESSION['access_token'] = $tokenData['access_token'];
//         $_SESSION['refresh_token'] = $tokenData['refresh_token'];
//         $_SESSION['expires_in'] = time() + $tokenData['expires_in'];
        
//         // Redirect to remove the code from the URL
//         header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
//         exit;
//     } catch (Exception $e) {
//         die('Error getting access token: ' . $e->getMessage());
//     }
// }

// // If we have an access token, use it
// if (!empty($_SESSION['access_token'])) {
//     $client->setAccessToken($_SESSION['access_token']);
    
//     try {
//         // Now you can make API calls
//         $status = [
//             '100' => 'Unpaid',
//             '105' => 'On Hold',
//             '111' => 'Awaiting Shipment',
//             '112' => 'Awaiting Collection',
//             '114' => 'Partially Shipping',
//             '121' => 'In Transit',
//             '122' => 'Delivered',
//             '130' => 'Completed',
//             '140' => 'Cancelled'
//         ];

//         $statusNew = ['AWAITING_COLLECTION', 'AWAITING_SHIPMENT', 'IN_TRANSIT', 'CANCELLED'];
//         $allOrders = [];

//         foreach ($statusNew as $status) {
//             try {
//                 $orders = $client->Order->getOrderList([
//                     'order_status' => $status,
//                     'page_size' => 50,
//                 ]);
                
//                 if (!empty($orders['orders'])) {
//                     $allOrders[$status] = $orders['orders'];
//                 }
                
//                 // Add a small delay between API calls to avoid rate limiting
//                 usleep(200000); // 200ms delay
                
//             } catch (Exception $e) {
//                 echo "Error fetching orders for status {$status}: " . $e->getMessage() . "\n";
//                 continue;
//             }
//         }

//         echo "<pre>";
//         print_r($allOrders);
//         echo "</pre>";
        
//     } catch (Exception $e) {
//         // If token is expired, try to refresh it
//         if (strpos($e->getMessage(), 'access token is invalid') !== false && !empty($_SESSION['refresh_token'])) {
//             try {
//                 $auth = $client->auth();
//                 $tokenData = $auth->refreshNewToken($_SESSION['refresh_token']);
                
//                 $_SESSION['access_token'] = $tokenData['access_token'];
//                 $_SESSION['refresh_token'] = $tokenData['refresh_token'];
//                 $_SESSION['expires_in'] = time() + $tokenData['expires_in'];
                
//                 // Retry the request
//                 header('Refresh:0');
//                 exit;
//             } catch (Exception $refreshError) {
//                 // If refresh fails, restart auth flow
//                 unset($_SESSION['access_token'], $_SESSION['refresh_token']);
//                 header('Location: ' . $_SERVER['PHP_SELF']);
//                 exit;
//             }
//         }
        
//         echo "Error: " . $e->getMessage();
//         if (method_exists($e, 'getResponse')) {
//             echo "\nResponse: " . $e->getResponse()->getBody()->getContents();
//         }
//     }
// } else {
//     // Start OAuth flow
//     $auth = $client->auth();
//     $authUrl = $auth->createAuthRequest($_SESSION['state'], true);
    
//     echo "<h2>Authentication Required</h2>";
//     echo "<p>You need to authorize this application to access your TikTok Shop account.</p>";
//     echo "<a href='$authUrl' style='padding: 10px 15px; background-color: #25F4EE; color: #000; text-decoration: none; border-radius: 4px;'>Authorize with TikTok Shop</a>";
// }

require __DIR__ . '/lib/TikTokShop.php';

use App\TikTokShop\TikTokShop;

$config = require __DIR__ . '/config.php';

// Generate state if not exists
if (empty($_SESSION['state'])) {
    $_SESSION['state'] = bin2hex(random_bytes(16));
}

// Initialize TikTok Shop
$tiktok = new TikTokShop($config);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TikTok Shop Order Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .status-badge {
            font-size: 0.85rem;
            padding: 0.35em 0.65em;
        }
        .order-actions .btn {
            margin: 0 2px;
        }
        .app-header {
            background: linear-gradient(135deg, #25F4EE 0%, #000000 100%);
            color: white;
            padding: 1.5rem 0;
            margin-bottom: 2rem;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 2rem;
            border: none;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,.125);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="app-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="bi bi-shop me-2"></i>TikTok Shop Orders
                </h1>
                <?php if (!empty($_SESSION['access_token'])): ?>
                <div>
                    <span class="me-3">Welcome, Merchant</span>
                    <a href="logout.php" class="btn btn-light btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main class="container">
        <?php
        // Check if we have an access token
        if (empty($_SESSION['access_token'])) {
            // Start OAuth flow
            $authUrl = $tiktok->getAuthUrl($_SESSION['state']);
            ?>
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card">
                        <div class="card-body text-center p-5">
                            <div class="mb-4">
                                <i class="bi bi-shop" style="font-size: 3rem; color: #25F4EE;"></i>
                                <h2 class="h4 mt-3">Connect Your TikTok Shop</h2>
                                <p class="text-muted">Authorize this application to access your TikTok Shop account and manage your orders.</p>
                            </div>
                            <a href="<?php echo htmlspecialchars($authUrl); ?>" class="btn btn-primary btn-lg">
                                <i class="bi bi-shop me-2"></i>Authorize with TikTok Shop
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            exit;
        }
        try {
            // Set the access token
            $tiktok->getClient()->setAccessToken($_SESSION['access_token']);
            
            // Get orders with all statuses
            $allOrders = $tiktok->getAllOrders();
            include_once __DIR__ . '/_table.php';
        } catch (Exception $e) {
            // Handle token refresh if needed
            if (strpos($e->getMessage(), 'access token is invalid') !== false && !empty($_SESSION['refresh_token'])) {
                try {
                    $tokenData = $tiktok->refreshToken($_SESSION['refresh_token']);
                    
                    $_SESSION['access_token'] = $tokenData['access_token'];
                    $_SESSION['refresh_token'] = $tokenData['refresh_token'];
                    $_SESSION['expires_in'] = time() + $tokenData['expires_in'];
                    
                    header('Refresh:0');
                    exit;
                } catch (Exception $refreshError) {
                    unset($_SESSION['access_token'], $_SESSION['refresh_token']);
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                }
            }
            ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Error: <?php echo htmlspecialchars($e->getMessage()); ?>
            </div>
            <?php
        }
        ?>
    </main>
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function viewOrder(orderId) {
        // Implement view order functionality
        console.log('Viewing order:', orderId);
        // You can open a modal or redirect to order detail page
        // window.location.href = 'order_detail.php?id=' + orderId;
    }
    function syncOrder(orderId) {
        if(confirm('Are you sure you want to sync order #' + orderId + ' to OBS?')) {
            // Show loading state
            const btn = document.querySelector(`button[onclick="syncOrder('${orderId}')"]`);
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Syncing...';
            
            // Simulate API call
            setTimeout(() => {
                // Here you would make an actual API call to your backend
                console.log('Syncing order:', orderId);
                
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.role = 'alert';
                alert.innerHTML = `
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Order #${orderId} has been queued for sync.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                document.querySelector('main').insertBefore(alert, document.querySelector('main').firstChild);
                
                // Reset button
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                // Auto-dismiss alert after 5 seconds
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            }, 1500);
        }
    }
    function filterOrders(status) {
        const url = new URL(window.location.href);
        if (status) {
            url.searchParams.set("status", status);
        } else {
            url.searchParams.delete("status");
        }
        // Show loading state
        document.body.style.cursor = 'wait';
        window.location.href = url.toString();
    }
    </script>
</body>
</html>