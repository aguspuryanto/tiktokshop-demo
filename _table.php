<?php
// Get orders with all statuses
// $allOrders = $tiktok->getAllOrders();
// echo json_encode($allOrders);

// Display orders in a table
echo '<div class="container mt-4">';
echo '<h2>Order List</h2>';

// Status filter dropdown
echo '<div class="mb-3">';
echo '<select class="form-select" style="width: 200px;" onchange="filterOrders(this.value)">';
echo '<option value="">All Statuses</option>';
$statuses = ['AWAITING_SHIPMENT', 'AWAITING_COLLECTION', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED'];
foreach ($statuses as $status) {
    $selected = (isset($_GET['status']) && $_GET['status'] === $status) ? 'selected' : '';
    echo "<option value='$status' $selected>" . ucwords(strtolower(str_replace('_', ' ', $status))) . "</option>";
}
echo '</select>';
echo '</div>';

// Orders table
echo '<div class="table-responsive">';
echo '<table class="table table-striped table-bordered">';
echo '<thead class="table-dark">';
echo '<tr>';
echo '<th>Order ID</th>';
echo '<th>Order Status</th>';
echo '<th>Order Date</th>';
echo '<th>Sync</th>';
echo '<th>Action</th>';
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// Check if there are orders
$hasOrders = false;

// foreach ($allOrders as $status => $orders) {
//     if (empty($orders)) continue;

    // First, collect all orders in a flat array
    $allOrdersFlat = [];
    foreach ($allOrders as $status => $orders) {
        if (empty($orders)) continue;
        foreach ($orders as $order) {
            $allOrdersFlat[] = $order;
        }
    }
    // Sort the orders by create_time in descending order
    usort($allOrdersFlat, function($a, $b) {
        $timeA = $a['create_time'] ?? 0;
        $timeB = $b['create_time'] ?? 0;
        return $timeB - $timeA; // For descending order
    });
    // Now display the sorted orders
    foreach ($allOrdersFlat as $order) {
        $hasOrders = true;
        $orderId = $order['id'] ?? 'N/A';
        $status = $order['status'] ?? 'N/A';
        $createTime = isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A';
        
        echo '<tr>';
        echo '<td>' . htmlspecialchars($orderId) . '</td>';
        echo '<td><span class="badge ' . getStatusBadgeClass($status) . '">' . 
             ucwords(strtolower(str_replace('_', ' ', $status))) . '</span></td>';
        echo '<td>' . $createTime . '</td>';
        echo '<td>TRUE</td>'; // Assuming synced is always true
        echo '<td>
                <button class="btn btn-sm btn-primary me-1" onclick="viewOrder(\'' . $orderId . '\')">View</button>
                <button class="btn btn-sm btn-success" onclick="syncOrder(\'' . $orderId . '\')">Sync to OBS</button>
              </td>';
        echo '</tr>';
    }
// }

if (!$hasOrders) {
    echo '<tr><td colspan="5" class="text-center">No orders found</td></tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>'; // End of table-responsive

// Add some JavaScript for the buttons
echo '
<script>
function viewOrder(orderId) {
    alert("Viewing order: " + orderId);
    // Implement view order functionality
}

function syncOrder(orderId) {
    if(confirm("Are you sure you want to sync order " + orderId + " to OBS?")) {
        // Implement sync to OBS functionality
        alert("Order " + orderId + " has been queued for sync.");
    }
}

function filterOrders(status) {
    const url = new URL(window.location.href);
    if (status) {
        url.searchParams.set("status", status);
    } else {
        url.searchParams.delete("status");
    }
    window.location.href = url.toString();
}
</script>';

// Helper function to get badge class based on status
function getStatusBadgeClass($status) {
    $status = strtoupper($status);
    switch ($status) {
        case 'AWAITING_SHIPMENT':
            return 'bg-warning text-dark';
        case 'AWAITING_COLLECTION':
            return 'bg-info text-dark';
        case 'IN_TRANSIT':
            return 'bg-primary';
        case 'DELIVERED':
            return 'bg-success';
        case 'CANCELLED':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}