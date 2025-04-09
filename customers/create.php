<?php
require_once '../config/database.php';
require_once '../models/Customer.php';
require_once '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$customer = new Customer($db);

if ($_POST) {
    $customer->name = $_POST['name'];
    $customer->email = $_POST['email'];
    $customer->phone = $_POST['phone'];
    $customer->address = $_POST['address'];

    if ($customer->create()) {
        echo "<div class='alert alert-success'>Customer was created successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Unable to create customer.</div>";
    }
}
?>

<h2>Create New Customer</h2>
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control">
    </div>
    <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control">
    </div>
    <div class="form-group">
        <label>Address</label>
        <textarea name="address" class="form-control" rows="3"></textarea>
    </div>
    <div class="form-group mt-3">
        <input type="submit" class="btn btn-primary" value="Save">
        <a href="index.php" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php
require_once '../includes/footer.php';
?>
