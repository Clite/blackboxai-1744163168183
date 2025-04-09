<?php
require_once '../config/database.php';
require_once '../models/Product.php';

$database = new Database();
$db = $database->getConnection();

$product = new Product($db);
$product->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

if ($product->delete()) {
    header('Location: index.php?action=deleted');
} else {
    die('Unable to delete product.');
}
?>
