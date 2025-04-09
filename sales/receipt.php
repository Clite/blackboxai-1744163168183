<?php
require_once '../config/database.php';
require_once '../models/Sale.php';
require_once '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$sale = new Sale($db);
$sale->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing sale ID.');
$sale->readOne();

// Format date
$sale_date = new DateTime($sale->sale_date);
?>

<div class="receipt-container">
    <div class="text-center mb-4">
        <h2>Sales Receipt</h2>
        <p class="mb-1">Receipt #: <?php echo str_pad($sale->id, 6, '0', STR_PAD_LEFT); ?></p>
        <p class="mb-1">Date: <?php echo $sale_date->format('m/d/Y h:i A'); ?></p>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <h5>Customer</h5>
            <p><?php 
                $customer = new Customer($db);
                echo $customer->getNameById($sale->customer_id); 
            ?></p>
            <?php if ($sale->customer_id): ?>
            <p><strong>Points Earned:</strong> <?php echo floor($sale->total_amount); ?></p>
            <p><strong>Total Points:</strong> <?php echo $customer->getLoyaltyPoints($sale->customer_id); ?></p>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h5>Payment Method</h5>
            <p><?php echo $sale->payment_method; ?></p>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sale->items as $item): ?>
            <tr>
                <td><?php echo $item['product_name']; ?></td>
                <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>$<?php echo number_format($item['total_price'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Subtotal:</th>
                <th>$<?php echo number_format($sale->total_amount, 2); ?></th>
            </tr>
            <tr>
                <th colspan="3" class="text-end">Tax:</th>
                <th>$0.00</th>
            </tr>
            <tr>
                <th colspan="3" class="text-end">Total:</th>
                <th>$<?php echo number_format($sale->total_amount, 2); ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="text-center mt-4">
        <p>Thank you for your business!</p>
        <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
        <a href="index.php" class="btn btn-secondary">New Sale</a>
    </div>
</div>

<style>
.receipt-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background: white;
    border: 1px solid #ddd;
}

@media print {
    body * {
        visibility: hidden;
    }
    .receipt-container, .receipt-container * {
        visibility: visible;
    }
    .receipt-container {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        border: none;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<?php
require_once '../includes/footer.php';
?>
