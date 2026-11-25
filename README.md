# ONLINE CAR SPARE PARTS STORE

## 1. Project Information

**Project Title:** Online Car Spare Parts Store

**Student Name:** UWIZEYIMANA EVARISTE

**Registration Number:** 224001479

**Course:** Web Programming

**Project Description:**

The Online Car Spare Parts Store is a web-based application that allows customers to browse available car spare parts, search for products, add products to a shopping cart and place orders.

The system also provides an administrator dashboard for managing spare parts, monitoring customer orders and updating order statuses.

## 2. Technologies Used

The project was developed using the following technologies:

- HTML5 – Structure of web pages.
- CSS3 – Website styling and responsive design.
- JavaScript – Client-side functionality and DOM manipulation.
- PHP – Server-side programming.
- MySQL – Database management.
- PDO – Secure database connectivity.
- Fetch API – Asynchronous communication.
- JSON – Data exchange between JavaScript and PHP.

## 3. Main System Features

### Customer Features

- Customer registration.
- Secure login and logout.
- Password hashing and verification.
- Browse available spare parts.
- Search spare parts by name, category or brand.
- Add products to a shopping cart.
- Remove products from the shopping cart.
- Place orders.
- View personal order history.

### Administrator Features

- Secure administrator login.
- View dashboard statistics.
- Add new spare parts.
- View all spare parts.
- Edit spare-part information.
- Delete spare parts.
- View customer orders.
- Update order statuses.
- Restore product stock when eligible orders are cancelled.

## 4. Database Design

Database Name:

`car_spare_parts_db`

The database contains four main tables:

### Users

Stores customer and administrator information.

- user_id – Primary Key.
- username.
- email.
- password_hash.
- role.
- created_at.

### Parts

Stores car spare-part information.

- part_id – Primary Key.
- name.
- category.
- price.
- stock.
- brand.

### Orders

Stores customer orders.

- order_id – Primary Key.
- customer_id – Foreign Key.
- order_date.
- total.
- status.

### Order Items

Stores the individual products included in each order.

- item_id – Primary Key.
- order_id – Foreign Key.
- part_id – Foreign Key.
- quantity.
- unit_price.

## 5. Installation Requirements

Before running the application, install:

1. XAMPP.
2. PHP 8.0 or a compatible version.
3. MySQL.
4. MySQL Workbench or phpMyAdmin.
5. A web browser.

## 6. Installation Instructions

### Step 1: Copy the Project

Copy the project folder into:

`C:\xampp\htdocs\`

The final project location should be:

`C:\xampp\htdocs\car_spare_parts_store`

### Step 2: Start XAMPP

Open XAMPP Control Panel.

Start:

- Apache.
- MySQL.

### Step 3: Create the Database

Open MySQL Workbench.

Execute the SQL file:

`database/schema.sql`

This creates the database and its four tables.

### Step 4: Insert Sample Data

Execute:

`database/seed.sql`

This inserts sample spare parts into the database.

Run the seed file only once on a fresh database.

### Step 5: Configure Database Connection

Open:

`config/database.php`

Configure the database connection using your local MySQL settings:

- Host: 127.0.0.1
- Port: 3306
- Database: car_spare_parts_db
- Username: root
- Password: Your local MySQL password.

Make sure the database credentials match your MySQL configuration.

### Step 6: Open the Application

Open your browser and visit:

http://localhost/car_spare_parts_store/public/

The homepage should appear.

## 7. Important Application Routes

| Page | URL |
|---|---|
| Homepage | `/public/` |
| Spare Parts | `/public/parts.php` |
| Registration | `/public/register.php` |
| Login | `/public/login.php` |
| Customer Dashboard | `/public/dashboard.php` |
| Shopping Cart | `/public/cart.php` |
| Checkout | `/public/checkout.php` |
| Order History | `/public/order_history.php` |
| Admin Dashboard | `/public/admin/dashboard.php` |
| Admin Parts Management | `/public/admin/parts.php` |
| Admin Orders Management | `/public/admin/orders.php` |
| Live Search API | `/public/api/search_parts.php` |

## 8. Security Implementation

The application includes several security mechanisms:

- Passwords are protected using PHP password_hash().
- Password verification uses password_verify().
- PDO prepared statements are used for database queries.
- PHP sessions manage user authentication.
- Role-based access control protects administrator pages.
- CSRF tokens protect important form submissions.
- Server-side validation checks user input.
- htmlspecialchars() helps prevent HTML injection and cross-site scripting when displaying user-provided data.
- Database transactions help maintain order and stock consistency.

## 9. Testing

The application should be tested using both successful and unsuccessful scenarios.

Examples include:

- Registering a new customer.
- Rejecting duplicate email addresses.
- Rejecting invalid login credentials.
- Searching for available spare parts.
- Adding products to the cart.
- Rejecting quantities greater than available stock.
- Successfully placing an order.
- Displaying customer order history.
- Adding a spare part as an administrator.
- Editing spare-part information.
- Deleting a spare part.
- Updating an order status.
- Restoring stock after an eligible order is cancelled.

Detailed test cases are provided in the project test plan.

## 10. Project Structure

The application uses an organized folder structure:

- app/ – Application components.
- config/ – Database configuration.
- database/ – SQL database scripts.
- public/ – Public pages and application entry points.
- public/admin/ – Administrator pages.
- public/api/ – JSON API endpoints.
- public/assets/ – CSS, JavaScript and images.
- tests/ – Project testing documentation and test files.

## 11. Conclusion

The Online Car Spare Parts Store demonstrates the practical application of web programming concepts, including frontend development, server-side programming, database management, asynchronous communication and web security.

The system provides an organized way for customers to purchase car spare parts online while allowing administrators to manage products, stock and customer orders.