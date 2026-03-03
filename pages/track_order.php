<?php
// track_order.php
// Handle order lookup
$order_number = trim($_GET['order_number'] ?? '');
$order = null;
$error = null;

if ($order_number !== '') {
    $order = get_order_by_number($order_number);
    if (!$order) {
        $error = "We couldn't find an order matching that number. Please check it and try again.";
    }
}

// Helper to determine active step for progress bar
function get_step_active($current_status, $target_status)
{
    $statuses = ['pending' => 1, 'processing' => 2, 'shipped' => 3, 'completed' => 4, 'cancelled' => -1, 'refunded' => -1];
    $current = $statuses[$current_status] ?? 0;
    $target = $statuses[$target_status] ?? 0;

    if ($current_status === 'cancelled' || $current_status === 'refunded') {
        return false;
    }
    return $current >= $target;
}
?>

<div class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-4 mb-4">
                    <div class="card-body p-4 p-md-5">
                        <h2 class="fw-bold mb-4 text-center">Track Your Order</h2>
                        <p class="text-muted text-center mb-4">Enter your tracking number or order number below to check the current status of your shipment.</p>
                        
                        <form method="get" action="index.php" class="mb-5">
                            <input type="hidden" name="page" value="track_order">
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-white border-end-0"><i class="fa fa-box text-muted"></i></span>
                                <input type="text" name="order_number" class="form-control border-start-0" placeholder="e.g. ORD-2026..." value="<?php echo htmlspecialchars($order_number); ?>" required>
                                <button class="btn btn-primary px-4" type="submit">Track Order</button>
                            </div>
                        </form>

                        <?php if ($error): ?>
                            <div class="alert alert-danger mb-0">
                                <i class="fa fa-exclamation-circle me-2"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php
endif; ?>

                        <?php if ($order): ?>
                            <div class="tracking-result mt-5">
                                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                    <h4 class="mb-0 fw-bold">Order <span class="text-primary">#<?php echo htmlspecialchars($order['order_number']); ?></span></h4>
                                    <span class="badge bg-<?php echo($order['status'] === 'completed') ? 'success' : (($order['status'] === 'cancelled') ? 'danger' : 'info'); ?> px-3 py-2 rounded-pill text-uppercase">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </div>

                                <?php if ($order['status'] !== 'cancelled' && $order['status'] !== 'refunded'): ?>
                                <!-- Tracking Progress Bar -->
                                <div class="position-relative m-4">
                                    <div class="progress" style="height: 6px;">
                                        <?php
        $progress = 0;
        if ($order['status'] === 'pending')
            $progress = 25;
        if ($order['status'] === 'processing')
            $progress = 50;
        if ($order['status'] === 'shipped')
            $progress = 75;
        if ($order['status'] === 'completed')
            $progress = 100;
?>
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $progress; ?>%" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between position-absolute w-100" style="top: -12px;">
                                        <!-- Step 1 -->
                                        <div class="text-center" style="width: 2rem;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-white <?php echo get_step_active($order['status'], 'pending') ? 'bg-primary' : 'bg-secondary'; ?>" style="width: 30px; height: 30px;">
                                                <i class="fa fa-file-invoice"></i>
                                            </div>
                                            <small class="fw-bold d-none d-sm-block">Pending</small>
                                        </div>
                                        <!-- Step 2 -->
                                        <div class="text-center" style="width: 2rem;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-white <?php echo get_step_active($order['status'], 'processing') ? 'bg-primary' : 'bg-secondary'; ?>" style="width: 30px; height: 30px;">
                                                <i class="fa fa-box-open"></i>
                                            </div>
                                            <small class="fw-bold d-none d-sm-block">Processing</small>
                                        </div>
                                        <!-- Step 3 -->
                                        <div class="text-center" style="width: 2rem;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-white <?php echo get_step_active($order['status'], 'shipped') ? 'bg-primary' : 'bg-secondary'; ?>" style="width: 30px; height: 30px;">
                                                <i class="fa fa-truck"></i>
                                            </div>
                                            <small class="fw-bold d-none d-sm-block">Shipped</small>
                                        </div>
                                        <!-- Step 4 -->
                                        <div class="text-center" style="width: 2rem;">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2 text-white <?php echo get_step_active($order['status'], 'completed') ? 'bg-primary' : 'bg-secondary'; ?>" style="width: 30px; height: 30px;">
                                                <i class="fa fa-home"></i>
                                            </div>
                                            <small class="fw-bold d-none d-sm-block">Delivered</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-5 d-block d-sm-none" style="height: 30px;"></div> <!-- Spacer for mobile -->
                                <?php
    endif; ?>

                                <!-- Order Information -->
                                <div class="row g-4 mt-4">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded border">
                                            <h6 class="fw-bold mb-3"><i class="fa fa-info-circle me-2 text-muted"></i>Order Details</h6>
                                            <p class="mb-1 text-muted text-sm">Date: <span class="text-dark fw-medium"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span></p>
                                            <p class="mb-1 text-muted text-sm">Total: <span class="text-dark fw-medium"><?php echo format_currency($order['total']); ?></span></p>
                                            <p class="mb-0 text-muted text-sm">Items: <span class="text-dark fw-medium"><?php echo count($order['items']); ?></span></p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded border h-100">
                                            <h6 class="fw-bold mb-3"><i class="fa fa-map-marker-alt me-2 text-muted"></i>Shipping Address</h6>
                                            <p class="mb-0 text-sm">
                                                <?php echo htmlspecialchars($order['shipping_line1'] ?? 'N/A'); ?><br>
                                                <?php if (!empty($order['shipping_line2']))
        echo htmlspecialchars($order['shipping_line2']) . '<br>'; ?>
                                                <?php echo htmlspecialchars($order['shipping_city'] ?? ''); ?>, <?php echo htmlspecialchars($order['shipping_state'] ?? ''); ?> <?php echo htmlspecialchars($order['shipping_postal_code'] ?? ''); ?><br>
                                                <?php echo htmlspecialchars($order['shipping_country'] ?? ''); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Ordered Items List -->
                                <h6 class="fw-bold mt-5 mb-3">Items in this order</h6>
                                <div class="table-responsive border rounded">
                                    <table class="table table-borderless mb-0 align-middle">
                                        <thead class="bg-light border-bottom">
                                            <tr>
                                                <th class="py-3 text-muted fw-semibold">Product</th>
                                                <th class="py-3 text-muted fw-semibold text-center">Qty</th>
                                                <th class="py-3 text-muted fw-semibold text-end">Price</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($order['items'] as $item): ?>
                                            <tr class="border-bottom">
                                                <td class="py-3">
                                                    <div class="d-flex align-items-center">
                                                        <?php
        $img_src = !empty($item['image_path']) ? htmlspecialchars($item['image_path']) : 'https://dummyimage.com/100x100/e0e0e0/636363.jpg&text=No+Image';
        // Fallback to resolving by name if image_path is missing and resolve product image is available
        if (empty($item['image_path'])) {
            $resolved = resolve_product_image(['id' => $item['product_id'], 'name' => $item['name']]);
            if ($resolved)
                $img_src = htmlspecialchars($resolved);
        }
?>
                                                        <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                        <div>
                                                            <p class="mb-0 fw-medium"><?php echo htmlspecialchars($item['name']); ?></p>
                                                            <small class="text-muted">SKU: <?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3 text-center fw-medium"><?php echo (int)$item['quantity']; ?></td>
                                                <td class="py-3 text-end fw-medium"><?php echo format_currency($item['price']); ?></td>
                                            </tr>
                                            <?php
    endforeach; ?>
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <td colspan="2" class="text-end py-3 fw-medium">Subtotal:</td>
                                                <td class="text-end py-3 fw-medium"><?php echo format_currency($order['total'] - $order['tax'] - $order['shipping']); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-end py-2 text-muted text-sm">Shipping:</td>
                                                <td class="text-end py-2 text-muted text-sm"><?php echo format_currency($order['shipping']); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-end py-2 text-muted text-sm">Tax:</td>
                                                <td class="text-end py-2 text-muted text-sm"><?php echo format_currency($order['tax']); ?></td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" class="text-end py-3 fw-bold border-top">Total:</td>
                                                <td class="text-end py-3 fw-bold border-top text-primary fs-5"><?php echo format_currency($order['total']); ?></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                            </div>
                        <?php
endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
