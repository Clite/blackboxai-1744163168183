<?php
require_once '../config/database.php';
require_once '../models/Sale.php';
require_once '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$sale = new Sale($db);

// Set default date range (current month)
$start_date = date('Y-m-01');
$end_date = date('Y-m-t');

// Handle date filter
if ($_POST) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
}

// Get filtered sales
$query = "SELECT s.*, c.name as customer_name 
         FROM sales s
         LEFT JOIN customers c ON s.customer_id = c.id
         WHERE s.sale_date BETWEEN ? AND ?
         ORDER BY s.sale_date DESC";
$stmt = $sale->conn->prepare($query);
$stmt->bindParam(1, $start_date);
$stmt->bindParam(2, $end_date);
$stmt->execute();
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$total_sales = 0;
$total_items = 0;
foreach ($sales as $sale) {
    $total_sales += $sale['total_amount'];
    $sale->id = $sale['id'];
    $sale->readOne();
    $total_items += count($sale->items);
}
?>

<h2>Sales Report</h2>

<div class="card mb-4">
    <div class="card-body">
        <form method="post" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" required>
            </div>
            <div class="col-md-5">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Sales</h5>
                <p class="card-text h4">$<?php echo number_format($total_sales, 2); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Transactions</h5>
                <p class="card-text h4"><?php echo count($sales); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Items Sold</h5>
                <p class="card-text h4"><?php echo $total_items; ?></p>
            </div>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Receipt #</th>
                <th>Date</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sales as $sale): ?>
            <tr>
                <td><?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></td>
                <td><?php echo (new DateTime($sale['sale_date']))->format('m/d/Y h:i A'); ?></td>
                <td><?php echo $sale['customer_name'] ?: 'Walk-in'; ?></td>
                <td><?php 
                    $sale_obj = new Sale($db);
                    $sale_obj->id = $sale['id'];
                    $sale_obj->readOne();
                    echo count($sale_obj->items);
                ?></td>
                <td>$<?php echo number_format($sale['total_amount'], 2); ?></td>
                <td><?php echo $sale['payment_method']; ?></td>
                <td>
                    <a href="../sales/receipt.php?id=<?php echo $sale['id']; ?>" 
                       class="btn btn-sm btn-primary" target="_blank">
                        View Receipt
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>
