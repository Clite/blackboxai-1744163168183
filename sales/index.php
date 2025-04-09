<?php
require_once '../config/database.php';
require_once '../models/Sale.php';
require_once '../models/Product.php';
require_once '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$sale = new Sale($db);
$product = new Product($db);

// Handle form submission
if ($_POST) {
    $sale->customer_id = $_POST['customer_id'];
    $sale->total_amount = $_POST['total_amount'];
    $sale->payment_method = $_POST['payment_method'];
    
    // Process items
    $items = array();
    foreach ($_POST['product_id'] as $key => $product_id) {
        if ($product_id && $_POST['quantity'][$key] > 0) {
            $product->id = $product_id;
            $product->readOne();
            
            $items[] = array(
                'product_id' => $product_id,
                'quantity' => $_POST['quantity'][$key],
                'unit_price' => $product->price,
                'total_price' => $product->price * $_POST['quantity'][$key]
            );
        }
    }
    $sale->items = $items;
    
    try {
        $sale_id = $sale->create();
        header("Location: receipt.php?id=" . $sale_id);
        exit();
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error processing sale: " . $e->getMessage() . "</div>";
    }
}

// Get products for dropdown
$products = $product->read()->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>New Sale</h2>
<form method="post">
    <div class="row mb-3">
        <div class="col-md-6">
            <label class="form-label">Customer</label>
            <select name="customer_id" class="form-select" required>
                <option value="">Select Customer</option>
                <?php 
                $customer = new Customer($db);
                $customers = $customer->read()->fetchAll(PDO::FETCH_ASSOC);
                foreach ($customers as $customer): 
                ?>
                <option value="<?php echo $customer['id']; ?>">
                    <?php echo $customer['name']; ?>
                </option>
                <?php endforeach; ?>
                <option value="">Walk-in Customer</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Payment Method</label>
            <select name="payment_method" class="form-select" required>
                <option value="Cash">Cash</option>
                <option value="Credit Card">Credit Card</option>
                <option value="Mobile Payment">Mobile Payment</option>
            </select>
        </div>
    </div>

    <div class="row mb-3" id="points-section" style="display:none;">
        <div class="col-md-6">
            <label class="form-label">Available Points</label>
            <input type="text" class="form-control" id="available-points" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">Redeem Points (100 points = $1)</label>
            <input type="number" name="points_redeemed" class="form-control" 
                   id="points-redeemed" min="0" step="100" value="0">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table" id="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr class="item-row">
                    <td>
                        <select name="product_id[]" class="form-select product-select">
                            <option value="">Select Product</option>
                            <?php foreach ($products as $product): ?>
                            <option value="<?= $product['id'] ?>"><?= $product['name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td><input type="text" name="price[]" class="form-control price" readonly></td>
                    <td><input type="number" name="quantity[]" class="form-control quantity" min="1" value="1"></td>
                    <td><input type="text" name="total[]" class="form-control total" readonly></td>
                    <td><button type="button" class="btn btn-danger remove-row">Remove</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-secondary" id="add-row">Add Item</button>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <label class="form-label">Subtotal</label>
            <input type="text" class="form-control" id="subtotal" readonly>
        </div>
        <div class="col-md-6">
            <label class="form-label">Total Amount</label>
            <input type="text" name="total_amount" class="form-control" id="total-amount" readonly required>
        </div>
    </div>

    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Complete Sale</button>
        <a href="../index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add new row
    document.getElementById('add-row').addEventListener('click', function() {
        const newRow = document.querySelector('.item-row').cloneNode(true);
        newRow.querySelectorAll('input').forEach(input => input.value = '');
        newRow.querySelector('.product-select').value = '';
        document.querySelector('#items-table tbody').appendChild(newRow);
    });

    // Remove row
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            if (document.querySelectorAll('.item-row').length > 1) {
                e.target.closest('tr').remove();
                calculateTotal();
            }
        }
    });

    // Product selection change
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('product-select')) {
            const row = e.target.closest('tr');
            const productId = e.target.value;
            
            if (productId) {
                // In a real app, you would fetch the price via AJAX
                const price = <?= json_encode(array_column($products, 'price', 'id')) ?>[productId];
                row.querySelector('.price').value = price.toFixed(2);
                row.querySelector('.quantity').value = 1;
                row.querySelector('.total').value = price.toFixed(2);
                calculateTotal();
            }
        }
    });

    // Quantity change
    document.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity')) {
            const row = e.target.closest('tr');
            const price = parseFloat(row.querySelector('.price').value) || 0;
            const quantity = parseInt(e.target.value) || 0;
            row.querySelector('.total').value = (price * quantity).toFixed(2);
            calculateTotal();
        }
    });

    // Customer selection change
    document.querySelector('[name="customer_id"]').addEventListener('change', function() {
        const customerId = this.value;
        const pointsSection = document.getElementById('points-section');
        
        if (customerId) {
            fetch('../api/get_points.php?customer_id=' + customerId)
                .then(response => response.json())
                .then(data => {
                    if (data.points > 0) {
                        document.getElementById('available-points').value = data.points;
                        document.getElementById('points-redeemed').max = data.points;
                        pointsSection.style.display = 'block';
                    } else {
                        pointsSection.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error fetching points:', error);
                    pointsSection.style.display = 'none';
                });
        } else {
            pointsSection.style.display = 'none';
        }
    });

    // Calculate totals
    function calculateTotal() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const total = parseFloat(row.querySelector('.total').value) || 0;
            subtotal += total;
        });
        
        const pointsRedeemed = parseInt(document.getElementById('points-redeemed').value) || 0;
        const discount = pointsRedeemed / 100;
        const total = subtotal - discount;
        
        document.getElementById('subtotal').value = subtotal.toFixed(2);
        document.getElementById('total-amount').value = total > 0 ? total.toFixed(2) : '0.00';
    }

    // Update total when points change
    document.getElementById('points-redeemed').addEventListener('input', calculateTotal);
});
</script>

<?php
require_once '../includes/footer.php';
?>
