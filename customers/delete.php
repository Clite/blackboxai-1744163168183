<?php
require_once '../config/database.php';
require_once '../models/Customer.php';

$database = new Database();
$db = $database->getConnection();

$customer = new Customer($db);
$customer->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: Missing ID.');

if ($customer->delete()) {
    header('Location: index.php?action=deleted');
} else {
    die('Unable to delete customer.');
}
?>
