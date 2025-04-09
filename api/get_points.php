<?php
require_once '../config/database.php';
require_once '../models/Customer.php';

header('Content-Type: application/json');

$database = new Database();
$db = $database->getConnection();

$customer_id = $_GET['customer_id'] ?? 0;

$customer = new Customer($db);
$points = $customer->getLoyaltyPoints($customer_id);

echo json_encode(['points' => $points]);
?>
