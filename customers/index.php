<?php
require_once '../config/database.php';
require_once '../models/Customer.php';
require_once '../includes/header.php';

$database = new Database();
$db = $database->getConnection();

$customer = new Customer($db);
$stmt = $customer->read();
?>

<h2>Customer Management</h2>
<a href="create.php" class="btn btn-primary mb-3">Add New Customer</a>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td>
                    <a href="profile.php?id=<?php echo $row['id']; ?>">
                        <?php echo $row['name']; ?>
                    </a>
                </td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td>
                    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                    <a href="delete.php?id=<?php echo $row['id']; ?>" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this customer?')">
                        Delete
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
