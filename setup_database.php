<?php
require_once 'config/database.php';

try {
    $conn = new PDO("mysql:host=".DB_SERVER, DB_USERNAME, DB_PASSWORD);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database
    $conn->exec("CREATE DATABASE IF NOT EXISTS ".DB_NAME);
    $conn->exec("USE ".DB_NAME);
    
    // Create tables
    $sql = file_get_contents('sql/setup_tables.sql');
    $conn->exec($sql);
    
    echo "<div class='alert alert-success'>Database setup completed successfully!</div>";
} catch(PDOException $e) {
    echo "<div class='alert alert-danger'>Error setting up database: " . $e->getMessage() . "</div>";
}

// Show link to return to application
echo "<a href='index.php' class='btn btn-primary mt-3'>Return to Application</a>";
?>
