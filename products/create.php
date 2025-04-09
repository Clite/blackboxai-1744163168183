<?php
require_once '../config/database.php';
require_once '../models/Product.php';
require_once '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);

if ($_POST) {
    $product->name = $_POST['name'];
    $product->description = $_POST['description'];
    $product->price = $_POST['price'];
    $product->quantity = $_POST['quantity'];
    $product->category_id = $_POST['category_id'];

    if ($product->create()) {
        echo "<div class='alert alert-success'>Product was created successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Unable to create product.</div>";
    }
}
?>

<h2>Create Product</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="form-group">
        <label>Price</label>
        <input type="number" step="0.01" name="price" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Quantity in Stock</label>
        <input type="number" name="quantity" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Category</label>
        <select name="category_id" class="form-control">
            <option value="1">Electronics</option>
            <option value="2">Clothing</option>
            <option value="3">Food</option>
        </select>
    </div>
    <div class="form-group mt-3">
        <input type="submit" class="btn btn-primary" value="Submit">
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php
require_once '../includes/footer.php';
?>
