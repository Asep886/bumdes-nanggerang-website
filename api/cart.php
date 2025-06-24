<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
include_once '../database/config.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$cart = new Cart($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Get cart items for a user
        if(isset($_GET['user_id'])) {
            $stmt = $cart->getItems($_GET['user_id']);
            $cart_items = array();
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $item = array(
                    "id" => $row['id'],
                    "product_name" => $row['name'],
                    "price" => $row['price'],
                    "quantity" => $row['quantity'],
                    "image_url" => $row['image_url'],
                    "subtotal" => $row['price'] * $row['quantity']
                );
                array_push($cart_items, $item);
            }
            
            $response = array(
                "items" => $cart_items,
                "total" => array_sum(array_column($cart_items, 'subtotal'))
            );
            
            http_response_code(200);
            echo json_encode($response);
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "User ID is required."));
        }
        break;

    case 'POST':
        // Add item to cart
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->user_id) &&
            !empty($data->product_id) &&
            !empty($data->quantity)
        ) {
            if($cart->addItem($data->user_id, $data->product_id, $data->quantity)) {
                http_response_code(201);
                echo json_encode(array("message" => "Item was added to cart."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to add item to cart."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to add item to cart. Data is incomplete."));
        }
        break;

    case 'DELETE':
        // Remove item from cart
        if(
            isset($_GET['cart_id']) &&
            isset($_GET['user_id'])
        ) {
            if($cart->removeItem($_GET['cart_id'], $_GET['user_id'])) {
                http_response_code(200);
                echo json_encode(array("message" => "Item was removed from cart."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to remove item from cart."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to remove item from cart. Cart ID and User ID are required."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}
?>
