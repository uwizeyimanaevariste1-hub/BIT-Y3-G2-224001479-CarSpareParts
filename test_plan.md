# ONLINE CAR SPARE PARTS STORE
## SOFTWARE TEST PLAN

### 1. Project Information

**Project Title:** Online Car Spare Parts Store

**Student Name:** UWIZEYIMANA EVARISTE

**Registration Number:** 224001479

**Database:** car_spare_parts_db

### 2. Introduction

This document describes the testing procedures for the Online Car Spare Parts Store.

The purpose of testing is to verify that the application works correctly, handles invalid input, protects user information and maintains accurate product and order information.

### 3. Testing Objectives

The objectives are to:

- Verify customer registration and login.
- Verify administrator authentication.
- Test spare-parts management.
- Test shopping cart functionality.
- Test order placement and order history.
- Verify live search using Fetch API and JSON.
- Test form validation and security.
- Verify stock management during order cancellation.

### 4. Test Environment

| Component | Description |
|---|---|
| Operating System | Windows |
| Web Server | Apache (XAMPP) |
| Backend | PHP |
| Database | MySQL |
| Database Connection | PDO |
| Frontend | HTML, CSS, JavaScript |
| Browser | Google Chrome |
| Local Server | localhost |

### 5. Test Cases

#### A. Customer Registration and Authentication

| ID | Test Description | Test Input/Action | Expected Result |
|---|---|---|---|
| TC01 | Register a new customer | Enter valid username, email and password | Account should be created successfully. |
| TC02 | Register using an existing email | Enter an email already registered | System should reject duplicate email. |
| TC03 | Register with invalid email | Enter an incorrectly formatted email | System should display a validation error. |
| TC04 | Register with a short password | Enter a password shorter than 8 characters | System should reject the password. |
| TC05 | Login with correct credentials | Enter valid email and password | Customer should access the dashboard. |
| TC06 | Login with incorrect password | Enter a valid email and wrong password | System should reject the login. |

#### B. Spare Parts and Live Search

| ID | Test Description | Test Input/Action | Expected Result |
|---|---|---|---|
| TC07 | View spare parts | Open the spare parts page | Available products should be displayed. |
| TC08 | Search by brand | Search for Toyota | Matching Toyota products should appear. |
| TC09 | Search by category | Search for Electrical | Matching electrical products should appear. |
| TC10 | Search for unavailable product | Search for a product that does not exist | System should display no matching results. |

#### C. Shopping Cart and Orders

| ID | Test Description | Test Input/Action | Expected Result |
|---|---|---|---|
| TC11 | Add a product to cart | Select an available product and add it | Product should appear in the shopping cart. |
| TC12 | Add more than available stock | Request a quantity greater than stock | System should reject the request. |
| TC13 | Remove a cart item | Select Remove in the cart | Product should be removed from the cart. |
| TC14 | Place an order | Proceed to checkout with valid cart items | Order should be created successfully. |
| TC15 | View order history | Open customer order history | Customer should see their own orders. |

#### D. Administrator Functions

| ID | Test Description | Test Input/Action | Expected Result |
|---|---|---|---|
| TC16 | Add a spare part | Enter valid product information | New product should be saved. |
| TC17 | Edit a spare part | Change product price or stock | Updated information should be displayed. |
| TC18 | Delete a spare part | Delete a product that is not used in an order | Product should be removed successfully. |
| TC19 | Cancel an eligible order | Cancel a pending order | Order should be cancelled and stock restored. |
| TC20 | Access admin page as customer | Open an administrator URL as a customer | Access should be denied or redirected. |

### 6. Security Testing

The following security checks should also be performed:

1. Verify that passwords are stored as hashes rather than plain text.
2. Verify that prepared statements are used for database operations.
3. Submit a form without a valid CSRF token.
4. Try accessing administrator pages without administrator authentication.
5. Enter HTML or JavaScript characters in user input and verify safe output escaping.
6. Verify that customers cannot view another customer's private order history.

### 7. Test Results Record

For each test, record the actual result and mark its status.

| Test ID | Actual Result | Status | Remarks |
|---|---|---|---|
| TC01 | | Not Tested | |
| TC02 | | Not Tested | |
| TC03 | | Not Tested | |
| TC04 | | Not Tested | |
| TC05 | | Not Tested | |
| TC06 | | Not Tested | |
| TC07 | | Not Tested | |
| TC08 | | Not Tested | |
| TC09 | | Not Tested | |
| TC10 | | Not Tested | |
| TC11 | | Not Tested | |
| TC12 | | Not Tested | |
| TC13 | | Not Tested | |
| TC14 | | Not Tested | |
| TC15 | | Not Tested | |
| TC16 | | Not Tested | |
| TC17 | | Not Tested | |
| TC18 | | Not Tested | |
| TC19 | | Not Tested | |
| TC20 | | Not Tested | |

### 8. Conclusion

The test plan provides a structured approach to verifying the functionality, reliability and security of the Online Car Spare Parts Store.

All test cases should be executed before final project submission. Any failed test should be investigated and corrected.