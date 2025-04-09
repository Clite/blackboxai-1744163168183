<?php
require_once '../config/database.php';
require_once '../models/Product.php';
require_once '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$stmt = $product->read();
?>

<div class="row">
    <div class="col">
        <h2>Product Management</h2>
        <a href="create.php" class="btn btn-primary mb-3">
            <i class="bi bi-plus-circle"></i> Add New Product
        </a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['description']); ?></td>
                <td>$<?php echo number_format($row['price'], 2); ?></td>
                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger btn-delete">
                        <i class="bi bi-trash"></i> Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
require_once '../includes/footer.php';
?>
