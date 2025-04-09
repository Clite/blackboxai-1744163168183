<?php
class Sale {
    private $conn;
    private $table_name = "sales";
    private $items_table = "sale_items";

    public $id;
    public $customer_id;
    public $total_amount;
    public $payment_method;
    public $sale_date;
    public $items = array();

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        // Start transaction
        $this->conn->beginTransaction();

        try {
            // Insert sale record
            $query = "INSERT INTO " . $this->table_name . "
                    SET customer_id=:customer_id, total_amount=:total_amount,
                    payment_method=:payment_method";
            
            $stmt = $this->conn->prepare($query);
            
            $this->customer_id = htmlspecialchars(strip_tags($this->customer_id));
            $this->total_amount = htmlspecialchars(strip_tags($this->total_amount));
            $this->payment_method = htmlspecialchars(strip_tags($this->payment_method));
            
            $stmt->bindParam(":customer_id", $this->customer_id);
            $stmt->bindParam(":total_amount", $this->total_amount);
            $stmt->bindParam(":payment_method", $this->payment_method);
            
            $stmt->execute();
            $sale_id = $this->conn->lastInsertId();

            // Insert sale items
            foreach ($this->items as $item) {
                $query = "INSERT INTO " . $this->items_table . "
                        SET sale_id=:sale_id, product_id=:product_id,
                        quantity=:quantity, unit_price=:unit_price,
                        total_price=:total_price";
                
                $stmt = $this->conn->prepare($query);
                
                $product_id = htmlspecialchars(strip_tags($item['product_id']));
                $quantity = htmlspecialchars(strip_tags($item['quantity']));
                $unit_price = htmlspecialchars(strip_tags($item['unit_price']));
                $total_price = htmlspecialchars(strip_tags($item['total_price']));
                
                $stmt->bindParam(":sale_id", $sale_id);
                $stmt->bindParam(":product_id", $product_id);
                $stmt->bindParam(":quantity", $quantity);
                $stmt->bindParam(":unit_price", $unit_price);
                $stmt->bindParam(":total_price", $total_price);
                
                $stmt->execute();

                // Update product inventory
                $query = "UPDATE products 
                         SET quantity = quantity - :quantity 
                         WHERE id = :product_id";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(":quantity", $quantity);
                $stmt->bindParam(":product_id", $product_id);
                $stmt->execute();
            }

            // Process points redemption if any
            if ($this->customer_id && isset($_POST['points_redeemed']) && $_POST['points_redeemed'] > 0) {
                require_once 'Customer.php';
                $points_redeemed = (int)$_POST['points_redeemed'];
                $customer = new Customer($this->conn);
                
                // Verify customer has enough points
                $current_points = $customer->getLoyaltyPoints($this->customer_id);
                if ($current_points >= $points_redeemed) {
                    $customer->redeemLoyaltyPoints($this->customer_id, $points_redeemed, $sale_id);
                    $discount = $points_redeemed / 100;
                    $this->total_amount = max(0, $this->total_amount - $discount);
                    
                    // Update sale total with discount
                    $query = "UPDATE " . $this->table_name . " 
                             SET total_amount = :total_amount 
                             WHERE id = :id";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(":total_amount", $this->total_amount);
                    $stmt->bindParam(":id", $sale_id);
                    $stmt->execute();
                }
            }

            // Award loyalty points (1 point per $1 spent)
            if ($this->customer_id) {
                require_once 'Customer.php';
                $points = floor($this->total_amount);
                $customer = new Customer($this->conn);
                $customer->addLoyaltyPoints($this->customer_id, $points, $sale_id);
            }

            // Commit transaction
            $this->conn->commit();
            return $sale_id;

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function read() {
        $query = "SELECT s.*, c.name as customer_name 
                 FROM " . $this->table_name . " s
                 LEFT JOIN customers c ON s.customer_id = c.id
                 ORDER BY s.sale_date DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT s.*, c.name as customer_name 
                 FROM " . $this->table_name . " s
                 LEFT JOIN customers c ON s.customer_id = c.id
                 WHERE s.id = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->customer_id = $row['customer_id'];
        $this->total_amount = $row['total_amount'];
        $this->payment_method = $row['payment_method'];
        $this->sale_date = $row['sale_date'];
        
        // Get sale items
        $query = "SELECT si.*, p.name as product_name 
                 FROM " . $this->items_table . " si
                 JOIN products p ON si.product_id = p.id
                 WHERE si.sale_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $this->items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
