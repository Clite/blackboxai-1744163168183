<?php
require_once '../config/database.php';
require_once '../models/Product.php';
require_once '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$product->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

$product->readOne();

if ($_POST) {
    $product->name = $_POST['name'];
    $product->description = $_POST['description'];
    $product->price = $_POST['price'];
    $product->quantity = $_POST['quantity'];
    $product->category_id = $_POST['category_id'];

    if ($product->update()) {
        echo "<div class='alert alert-success'>Product was updated successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Unable to update product.</div>";
    }
}
?>

<h2>Edit Product</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]).'?id='.$product->id; ?>" method="post">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" value="<?php echo $product->name; ?>" required>
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" class="form-control"><?php echo $product->description; ?></textarea>
    </div>
    <div class="form-group">
        <label>Price</label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product->price; ?>" required>
    </div>
    <div class="form-group">
        <label>Quantity in Stock</label>
        <input type="number" name="quantity" class="form-control" value="<?php echo $product->quantity; ?>" required>
    </div>
    <div class="form-group">
        <label>Category</label>
        <select name="category_id" class="form-control">
            <option value="1" <?php echo $product->category_id==1 ? 'selected' : ''; ?>>Electronics</option>
            <option value="2" <?php echo $product->category_id==2 ? 'selected' : ''; ?>>Clothing</option>
            <option value="3" <?php echo $product->category_id==3 ? 'selected' : ''; ?>>Food</option>
        </select>
    </div>
    <div class="form-group mt-3">
        <input type="submit" class="btn btn-primary" value="Update">
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php
require_once '../includes/footer.php';
?>
