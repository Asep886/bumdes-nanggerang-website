<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'bumdes_db';
    private $username = 'root';
    private $password = '';
    private $conn;

    // Get database connection
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }

        return $this->conn;
    }
}

// Product class for handling product-related operations
class Product {
    private $conn;
    private $table_name = "products";

    public $id;
    public $category_id;
    public $name;
    public $description;
    public $price;
    public $stock;
    public $image_url;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get all products
    public function getAll() {
        $query = "SELECT 
                    p.id, p.name, p.description, p.price, p.stock, p.image_url,
                    c.name as category_name 
                FROM 
                    " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                ORDER BY 
                    p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Get product by ID
    public function getById($id) {
        $query = "SELECT 
                    p.id, p.name, p.description, p.price, p.stock, p.image_url,
                    c.name as category_name 
                FROM 
                    " . $this->table_name . " p
                    LEFT JOIN categories c ON p.category_id = c.id
                WHERE 
                    p.id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();

        return $stmt;
    }

    // Get products by category
    public function getByCategory($category_id) {
        $query = "SELECT 
                    p.id, p.name, p.description, p.price, p.stock, p.image_url
                FROM 
                    " . $this->table_name . " p
                WHERE 
                    p.category_id = ?
                ORDER BY 
                    p.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $category_id);
        $stmt->execute();

        return $stmt;
    }

    // Create new product
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    name=:name, 
                    description=:description, 
                    price=:price, 
                    category_id=:category_id,
                    stock=:stock,
                    image_url=:image_url";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":image_url", $this->image_url);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Update product
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    name=:name,
                    description=:description,
                    price=:price,
                    category_id=:category_id,
                    stock=:stock,
                    image_url=:image_url
                WHERE
                    id=:id";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->category_id = htmlspecialchars(strip_tags($this->category_id));
        $this->stock = htmlspecialchars(strip_tags($this->stock));
        $this->image_url = htmlspecialchars(strip_tags($this->image_url));
        $this->id = htmlspecialchars(strip_tags($this->id));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":price", $this->price);
        $stmt->bindParam(":category_id", $this->category_id);
        $stmt->bindParam(":stock", $this->stock);
        $stmt->bindParam(":image_url", $this->image_url);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete product
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

// Cart class for handling cart operations
class Cart {
    private $conn;
    private $table_name = "cart";

    public function __construct($db) {
        $this->conn = $db;
    }

    // Add item to cart
    public function addItem($user_id, $product_id, $quantity) {
        // Check if item already exists in cart
        $query = "SELECT id, quantity FROM " . $this->table_name . "
                WHERE user_id = ? AND product_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $product_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            // Update quantity if item exists
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $new_quantity = $row['quantity'] + $quantity;
            
            $query = "UPDATE " . $this->table_name . "
                    SET quantity = :quantity
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":quantity", $new_quantity);
            $stmt->bindParam(":id", $row['id']);
        } else {
            // Insert new item if it doesn't exist
            $query = "INSERT INTO " . $this->table_name . "
                    SET
                        user_id=:user_id,
                        product_id=:product_id,
                        quantity=:quantity";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":product_id", $product_id);
            $stmt->bindParam(":quantity", $quantity);
        }

        return $stmt->execute();
    }

    // Get cart items for a user
    public function getItems($user_id) {
        $query = "SELECT 
                    c.id, c.quantity,
                    p.name, p.price, p.image_url
                FROM 
                    " . $this->table_name . " c
                    LEFT JOIN products p ON c.product_id = p.id
                WHERE 
                    c.user_id = ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        return $stmt;
    }

    // Remove item from cart
    public function removeItem($cart_id, $user_id) {
        $query = "DELETE FROM " . $this->table_name . "
                WHERE id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $cart_id);
        $stmt->bindParam(2, $user_id);

        return $stmt->execute();
    }
}
?>
