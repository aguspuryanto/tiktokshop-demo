<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/lib/TikTokShop.php';

use App\TikTokShop\TikTokShop;

$config = require __DIR__ . '/config.php';

// Generate state if not exists
if (empty($_SESSION['state'])) {
    $_SESSION['state'] = bin2hex(random_bytes(16));
}

// Initialize TikTok Shop
$tiktok = new TikTokShop($config);

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$orderId = $_GET['id'];
$order = null;

// Find the order in the orders array
foreach ($allOrders as $status => $orders) {
    if (empty($orders)) continue;
    foreach ($orders as $o) {
        if (($o['id'] ?? '') === $orderId) {
            $order = $o;
            break 2; // Break both loops
        }
    }
}

// If order not found, redirect back
if (!$order) {
    header('Location: index.php');
    exit;
}

// Format some data
$createTime = isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A';
$cancelTime = isset($order['cancel_time']) ? date('Y-m-d H:i:s', $order['cancel_time']) : 'N/A';
$paidTime = isset($order['paid_time']) ? date('Y-m-d H:i:s', $order['paid_time']) : 'N/A';
$status = $order['status'] ?? 'N/A';
$totalAmount = $order['payment']['total_amount'] ?? 0;
$subTotal = $order['payment']['sub_total'] ?? 0;
$shippingFee = $order['payment']['shipping_fee'] ?? 0;
$discount = $order['payment']['seller_discount'] ?? 0;
$platformDiscount = $order['payment']['platform_discount'] ?? 0;
$shippingDiscount = $order['payment']['shipping_fee_platform_discount'] ?? 0 + $order['payment']['shipping_fee_seller_discount'] ?? 0;
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Order #<?php echo htmlspecialchars($orderId); ?></h2>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Orders
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Order Items</h5>
                    <span class="badge <?php echo getStatusBadgeClass($status); ?>">
                        <?php echo ucwords(strtolower(str_replace('_', ' ', $status))); ?>
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Qty</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order['line_items'] as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo htmlspecialchars($item['sku_image'] ?? ''); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>"
                                                 style="width: 60px; height: 60px; object-fit: cover;" 
                                                 class="me-3 rounded">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($item['product_name'] ?? 'N/A'); ?></div>
                                                <div class="text-muted small"><?php echo htmlspecialchars($item['sku_name'] ?? ''); ?></div>
                                                <div class="text-muted small">SKU: <?php echo htmlspecialchars($item['seller_sku'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">RM <?php echo number_format($item['sale_price'] ?? 0, 2); ?></td>
                                    <td class="text-end">1</td>
                                    <td class="text-end">RM <?php echo number_format($item['sale_price'] ?? 0, 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (!empty($order['packages']) && is_array($order['packages'])): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Shipping Information</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($order['packages'] as $package): ?>
                        <?php if (!empty($package['tracking_number'])): ?>
                        <div class="mb-3">
                            <div class="fw-bold">Tracking Number:</div>
                            <div><?php echo htmlspecialchars($package['tracking_number']); ?></div>
                        </div>
                        <div class="mb-3">
                            <div class="fw-bold">Shipping Provider:</div>
                            <div><?php echo htmlspecialchars($package['shipping_provider'] ?? $order['shipping_provider'] ?? 'N/A'); ?></div>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal</span>
                        <span>RM <?php echo number_format($subTotal, 2); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Shipping Fee</span>
                        <span>RM <?php echo number_format($shippingFee, 2); ?></span>
                    </div>
                    <?php if ($discount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Seller Discount</span>
                        <span>-RM <?php echo number_format($discount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($platformDiscount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Platform Discount</span>
                        <span>-RM <?php echo number_format($platformDiscount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ($shippingDiscount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-danger">
                        <span>Shipping Discount</span>
                        <span>-RM <?php echo number_format($shippingDiscount, 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total</span>
                        <span>RM <?php echo number_format($totalAmount, 2); ?></span>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Shipping Address</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <div class="fw-bold">Recipient</div>
                        <div><?php echo htmlspecialchars($order['recipient_address']['name'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="mb-2">
                        <div class="fw-bold">Phone</div>
                        <div><?php echo htmlspecialchars($order['recipient_address']['phone_number'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="mb-2">
                        <div class="fw-bold">Address</div>
                        <div><?php echo nl2br(htmlspecialchars($order['recipient_address']['full_address'] ?? 'N/A')); ?></div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <?php if ($createTime !== 'N/A'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <p class="mb-0 fw-bold">Order Placed</p>
                                <p class="text-muted small mb-0"><?php echo $createTime; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($paidTime !== 'N/A'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <p class="mb-0 fw-bold">Payment Received</p>
                                <p class="text-muted small mb-0"><?php echo $paidTime; ?></p>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($status === 'CANCELLED' && $cancelTime !== 'N/A'): ?>
                        <div class="timeline-item">
                            <div class="timeline-marker bg-danger"></div>
                            <div class="timeline-content">
                                <p class="mb-0 fw-bold">Order Cancelled</p>
                                <p class="text-muted small mb-0"><?php echo $cancelTime; ?></p>
                                <?php if (!empty($order['cancel_reason'])): ?>
                                <p class="mb-0 small">Reason: <?php echo ucfirst(strtolower($order['cancel_reason'])); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 1.5rem;
    margin: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    left: 0.5rem;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-bottom: 1.5rem;
    padding-left: 1.5rem;
}

.timeline-marker {
    position: absolute;
    left: -0.5rem;
    top: 0.25rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: #0d6efd;
    border: 2px solid white;
}

.timeline-marker.bg-danger {
    background: #dc3545;
}

.timeline-content {
    padding: 0;
}

.timeline-item:last-child {
    padding-bottom: 0;
}
</style>

<?php
// Add this function if not already defined in your _table.php
if (!function_exists('getStatusBadgeClass')) {
    function getStatusBadgeClass($status) {
        switch (strtoupper($status)) {
            case 'UNPAID':
                return 'bg-secondary';
            case 'AWAITING_SHIPMENT':
                return 'bg-warning text-dark';
            case 'PROCESSING':
                return 'bg-info';
            case 'SHIPPED':
                return 'bg-primary';
            case 'DELIVERED':
                return 'bg-success';
            case 'CANCELLED':
                return 'bg-danger';
            case 'RETURN_REQUESTED':
                return 'bg-warning';
            case 'RETURNED':
                return 'bg-dark';
            default:
                return 'bg-secondary';
        }
    }
}
?>