<?php
// Headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and object files
include_once '../database/config.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();
$product = new Product($db);

// Get HTTP method
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['id'])) {
            // Get single product
            $stmt = $product->getById($_GET['id']);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($row) {
                http_response_code(200);
                echo json_encode($row);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Product not found."));
            }
        } else if(isset($_GET['category_id'])) {
            // Get products by category
            $stmt = $product->getByCategory($_GET['category_id']);
            $products_arr = array();
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($products_arr, $row);
            }
            
            http_response_code(200);
            echo json_encode($products_arr);
        } else {
            // Get all products
            $stmt = $product->getAll();
            $products_arr = array();
            
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                array_push($products_arr, $row);
            }
            
            http_response_code(200);
            echo json_encode($products_arr);
        }
        break;

    case 'POST':
        // Create product
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->name) &&
            !empty($data->price) &&
            !empty($data->category_id)
        ) {
            $product->name = $data->name;
            $product->description = $data->description;
            $product->price = $data->price;
            $product->category_id = $data->category_id;
            $product->stock = $data->stock;
            $product->image_url = $data->image_url;
            
            if($product->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Product was created."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to create product."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to create product. Data is incomplete."));
        }
        break;

    case 'PUT':
        // Update product
        $data = json_decode(file_get_contents("php://input"));
        
        if(
            !empty($data->id) &&
            !empty($data->name) &&
            !empty($data->price) &&
            !empty($data->category_id)
        ) {
            $product->id = $data->id;
            $product->name = $data->name;
            $product->description = $data->description;
            $product->price = $data->price;
            $product->category_id = $data->category_id;
            $product->stock = $data->stock;
            $product->image_url = $data->image_url;
            
            if($product->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Product was updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update product."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update product. Data is incomplete."));
        }
        break;

    case 'DELETE':
        // Delete product
        if(isset($_GET['id'])) {
            if($product->delete($_GET['id'])) {
                http_response_code(200);
                echo json_encode(array("message" => "Product was deleted."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to delete product."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to delete product. No ID provided."));
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(array("message" => "Method not allowed"));
        break;
}
?>
