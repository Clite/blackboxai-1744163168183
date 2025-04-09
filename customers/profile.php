<?php
require_once '../config/database.php';
require_once '../models/Customer.php';
require_once '../models/Sale.php';
require_once '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$customer = new Customer($db);
$customer->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing customer ID.');
$customer->readOne();

$purchase_history = $customer->getPurchaseHistory($customer->id);
?>

<h2>Customer Profile</h2>
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h4><?php echo $customer->name; ?></h4>
                <p><strong>Email:</strong> <?php echo $customer->email; ?></p>
                <p><strong>Phone:</strong> <?php echo $customer->phone; ?></p>
                <p><strong>Loyalty Tier:</strong> 
                    <span class="badge bg-<?php 
                        switch($customer->loyalty_tier) {
                            case 'gold': echo 'warning text-dark'; break;
                            case 'silver': echo 'secondary'; break;
                            default: echo 'light text-dark'; break;
                        }
                    ?>">
                        <?php echo ucfirst($customer->loyalty_tier); ?>
                    </span>
                </p>
                <p><strong>Loyalty Points:</strong> <?php echo number_format($customer->loyalty_points); ?></p>
            </div>
            <div class="col-md-6">
                <p><strong>Address:</strong></p>
                <p><?php echo nl2br($customer->address); ?></p>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h4>Redemption History</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Points Redeemed</th>
                            <th>Discount</th>
                            <th>Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $redemptions = $customer->getRedemptionHistory($customer->id);
                        while ($row = $redemptions->fetch(PDO::FETCH_ASSOC)):
                        ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($row['redemption_date'])) ?></td>
                            <td><?= number_format($row['points_redeemed']) ?></td>
                            <td>$<?= number_format($row['discount_amount'], 2) ?></td>
                            <td>
                                <?php if ($row['sale_id']): ?>
                                <a href="../sales/receipt.php?id=<?= $row['sale_id'] ?>">
                                    #<?= $row['sale_id'] ?> (<?= date('m/d/Y', strtotime($row['sale_date'])) ?>)
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<h3>Purchase History</h3>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Receipt #</th>
                <th>Date</th>
                <th>Items</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($sale = $purchase_history->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo str_pad($sale['id'], 6, '0', STR_PAD_LEFT); ?></td>
                <td><?php echo (new DateTime($sale['sale_date']))->format('m/d/Y h:i A'); ?></td>
                <td><?php echo $sale['item_count']; ?></td>
                <td>$<?php echo number_format($sale['total_amount'], 2); ?></td>
                <td>
                    <a href="../sales/receipt.php?id=<?php echo $sale['id']; ?>" 
                       class="btn btn-sm btn-primary" target="_blank">
                        View Receipt
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<a href="index.php" class="btn btn-secondary">Back to Customers</a>

<?php
require_once '../includes/footer.php';
?>
