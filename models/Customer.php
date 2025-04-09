<?php
class Customer {
    private $conn;
    private $table_name = "customers";

    public $id;
    public $name;
    public $email;
    public $phone;
    public $address;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET name=:name, email=:email, phone=:phone, address=:address";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY name ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getNameById($id) {
        $query = "SELECT name FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['name'] : 'Walk-in Customer';
    }

    public function getPurchaseHistory($id) {
        $query = "SELECT s.id, s.sale_date, s.total_amount, 
                 COUNT(si.id) as item_count 
                 FROM sales s
                 JOIN sale_items si ON s.id = si.sale_id
                 WHERE s.customer_id = ?
                 GROUP BY s.id
                 ORDER BY s.sale_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        return $stmt;
    }

    public function addLoyaltyPoints($customer_id, $points, $sale_id) {
        $query = "UPDATE customers SET loyalty_points = loyalty_points + ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $points);
        $stmt->bindParam(2, $customer_id);
        
        if ($stmt->execute()) {
            $this->recordLoyaltyEarning($customer_id, $points, $sale_id);
            $this->updateLoyaltyTier($customer_id);
            return true;
        }
        return false;
    }

    private function recordLoyaltyEarning($customer_id, $points, $sale_id) {
        $balance = $this->getLoyaltyPoints($customer_id);
        $query = "INSERT INTO loyalty_earnings 
                 (customer_id, sale_id, points_earned, points_balance) 
                 VALUES (?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $customer_id);
        $stmt->bindParam(2, $sale_id);
        $stmt->bindParam(3, $points);
        $stmt->bindParam(4, $balance);
        $stmt->execute();
    }

    public function redeemLoyaltyPoints($customer_id, $points, $sale_id) {
        $query = "UPDATE customers SET loyalty_points = loyalty_points - ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $points);
        $stmt->bindParam(2, $customer_id);
        
        if ($stmt->execute()) {
            $this->recordLoyaltyRedemption($customer_id, $points, $sale_id);
            return true;
        }
        return false;
    }

    private function recordLoyaltyRedemption($customer_id, $points, $sale_id) {
        $balance = $this->getLoyaltyPoints($customer_id);
        $discount = $points / 100;
        $query = "INSERT INTO loyalty_redemptions 
                 (customer_id, sale_id, points_redeemed, discount_amount, points_balance) 
                 VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $customer_id);
        $stmt->bindParam(2, $sale_id);
        $stmt->bindParam(3, $points);
        $stmt->bindParam(4, $discount);
        $stmt->bindParam(5, $balance);
        $stmt->execute();
    }

    public function getRedemptionHistory($customer_id) {
        $query = "SELECT lr.*, s.total_amount, s.sale_date 
                 FROM loyalty_redemptions lr
                 LEFT JOIN sales s ON lr.sale_id = s.id
                 WHERE lr.customer_id = ?
                 ORDER BY lr.redemption_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $customer_id);
        $stmt->execute();
        return $stmt;
    }

    public function getLoyaltyPoints($customer_id) {
        $query = "SELECT loyalty_points FROM customers WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $customer_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['loyalty_points'];
    }

    private function updateLoyaltyTier($customer_id) {
        $points = $this->getLoyaltyPoints($customer_id);
        $tier = 'basic';
        
        if ($points >= 1000) {
            $tier = 'gold';
        } elseif ($points >= 500) {
            $tier = 'silver';
        }
        
        $query = "UPDATE customers SET loyalty_tier = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tier);
        $stmt->bindParam(2, $customer_id);
        $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->name = $row['name'];
        $this->email = $row['email'];
        $this->phone = $row['phone'];
        $this->address = $row['address'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET name=:name, email=:email, phone=:phone, address=:address
                WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone = htmlspecialchars(strip_tags($this->phone));
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone", $this->phone);
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":id", $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
