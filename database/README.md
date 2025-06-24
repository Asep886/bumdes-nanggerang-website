# BUMDes Database and API Documentation

## Database Setup

1. Create a MySQL database named `bumdes_db`
2. Import the schema using the following command:
   ```bash
   mysql -u root -p bumdes_db < schema.sql
   ```

## Database Structure

### Tables
1. `users` - Store user information
   - id (PK)
   - username
   - password (hashed)
   - email
   - full_name
   - role (admin/customer)

2. `categories` - Product categories
   - id (PK)
   - name
   - description

3. `products` - Store product information
   - id (PK)
   - category_id (FK)
   - name
   - description
   - price
   - stock
   - image_url

4. `orders` - Store order information
   - id (PK)
   - user_id (FK)
   - total_amount
   - status
   - shipping_address
   - payment_method

5. `order_items` - Store items in each order
   - id (PK)
   - order_id (FK)
   - product_id (FK)
   - quantity
   - price

6. `cart` - Store shopping cart items
   - id (PK)
   - user_id (FK)
   - product_id (FK)
   - quantity

7. `testimonials` - Store customer testimonials
   - id (PK)
   - user_id (FK)
   - content
   - rating
   - status

## API Endpoints

### Products API (`/api/products.php`)

1. GET Methods:
   - Get all products: `GET /api/products.php`
   - Get single product: `GET /api/products.php?id={id}`
   - Get products by category: `GET /api/products.php?category_id={category_id}`

2. POST Method:
   - Create new product: `POST /api/products.php`
   ```json
   {
     "name": "Product Name",
     "description": "Product Description",
     "price": 99.99,
     "category_id": 1,
     "stock": 100,
     "image_url": "/images/products/product.jpg"
   }
   ```

3. PUT Method:
   - Update product: `PUT /api/products.php`
   ```json
   {
     "id": 1,
     "name": "Updated Name",
     "description": "Updated Description",
     "price": 149.99,
     "category_id": 1,
     "stock": 150,
     "image_url": "/images/products/updated.jpg"
   }
   ```

4. DELETE Method:
   - Delete product: `DELETE /api/products.php?id={id}`

### Cart API (`/api/cart.php`)

1. GET Method:
   - Get cart items: `GET /api/cart.php?user_id={user_id}`

2. POST Method:
   - Add item to cart: `POST /api/cart.php`
   ```json
   {
     "user_id": 1,
     "product_id": 1,
     "quantity": 2
   }
   ```

3. DELETE Method:
   - Remove item from cart: `DELETE /api/cart.php?cart_id={cart_id}&user_id={user_id}`

## Security Considerations

1. Password Hashing
   - All user passwords are hashed using PHP's password_hash() function
   - Never store plain-text passwords

2. SQL Injection Prevention
   - All database queries use prepared statements
   - Input validation and sanitization implemented

3. CORS Headers
   - API endpoints include necessary CORS headers for cross-origin requests
   - Methods and headers are properly restricted

4. Input Validation
   - All input data is validated and sanitized before processing
   - Appropriate error messages are returned for invalid data

## Error Handling

All API endpoints return appropriate HTTP status codes:
- 200: Success
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 405: Method Not Allowed
- 503: Service Unavailable

## Database Configuration

The database connection settings can be modified in `config.php`:
```php
private $host = 'localhost';
private $db_name = 'bumdes_db';
private $username = 'root';
private $password = '';
```

## Future Improvements

1. Implement user authentication and JWT tokens
2. Add order processing API endpoints
3. Implement payment gateway integration
4. Add image upload functionality
5. Implement caching for frequently accessed data
6. Add rate limiting for API endpoints
