-- Hotel Food and Beverage Management System Database Schema
-- This schema focuses on the restaurant/cafe aspect of a hotel,
-- dealing with dine-in customers, menu management, and orders.
-- VERSION 3.0: Simplified by removing inventory and ingredient management.

-- #####################################################################
-- # 1. Core User, Staff, and Admin Tables
-- #####################################################################

-- Table for system administrators
CREATE TABLE admins (
    AdminID INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL UNIQUE,
    PhoneNumber VARCHAR(20) UNIQUE,
    PasswordHash VARCHAR(255) NOT NULL, -- IMPORTANT: Store a hashed password, not plain text.
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table to store information about customers (walk-in or registered)
CREATE TABLE customers (
    CustomerID INT PRIMARY KEY AUTO_INCREMENT,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50),
    PhoneNumber VARCHAR(20) UNIQUE,
    Email VARCHAR(100) UNIQUE,
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for hotel staff/employees (waiters, chefs, managers)
CREATE TABLE staff (
    StaffID INT PRIMARY KEY AUTO_INCREMENT,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Role ENUM('Waiter', 'Chef', 'Manager', 'Cashier') NOT NULL,
    PhoneNumber VARCHAR(20) UNIQUE,
    HireDate DATE NOT NULL,
    ImageUrl VARCHAR(255), -- URL for the staff member's profile picture
    IsActive BOOLEAN DEFAULT TRUE
);

-- #####################################################################
-- # 2. Menu Management Tables
-- #####################################################################

-- Table for menu item categories (e.g., Appetizers, Main Course, Desserts, Beverages)
CREATE TABLE menu_categories (
    CategoryID INT PRIMARY KEY AUTO_INCREMENT,
    CategoryName VARCHAR(50) NOT NULL UNIQUE,
    Description TEXT
);

-- Table for individual menu items
CREATE TABLE menu_items (
    MenuItemID INT PRIMARY KEY AUTO_INCREMENT,
    CategoryID INT NOT NULL,
    Name VARCHAR(100) NOT NULL,
    Description TEXT,
    Price DECIMAL(10, 2) NOT NULL,
    ImageUrl VARCHAR(255), -- URL for the menu item's image
    IsAvailable BOOLEAN DEFAULT TRUE, -- To quickly mark items as "out of stock"
    FOREIGN KEY (CategoryID) REFERENCES menu_categories(CategoryID) ON DELETE CASCADE
);

-- #####################################################################
-- # 3. Order and Billing Tables
-- #####################################################################

-- Table for restaurant tables
CREATE TABLE restaurant_tables (
    TableID INT PRIMARY KEY AUTO_INCREMENT,
    TableNumber INT NOT NULL UNIQUE,
    Capacity INT NOT NULL,
    Status ENUM('Available', 'Occupied', 'Reserved') DEFAULT 'Available'
);

-- Master table for a customer's order
CREATE TABLE orders (
    OrderID INT PRIMARY KEY AUTO_INCREMENT,
    CustomerID INT,
    TableID INT NOT NULL,
    StaffID INT NOT NULL,
    OrderTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    OrderStatus ENUM('Pending', 'In-Progress', 'Completed', 'Cancelled') DEFAULT 'Pending',
    TotalAmount DECIMAL(10, 2) DEFAULT 0.00,
    FOREIGN KEY (CustomerID) REFERENCES customers(CustomerID) ON DELETE SET NULL,
    FOREIGN KEY (TableID) REFERENCES restaurant_tables(TableID),
    FOREIGN KEY (StaffID) REFERENCES staff(StaffID)
);

-- Detail table for items within an order
CREATE TABLE order_details (
    OrderDetailID INT PRIMARY KEY AUTO_INCREMENT,
    OrderID INT NOT NULL,
    MenuItemID INT NOT NULL,
    Quantity INT NOT NULL,
    Subtotal DECIMAL(10, 2) NOT NULL,
    SpecialInstructions TEXT,
    FOREIGN KEY (OrderID) REFERENCES orders(OrderID) ON DELETE CASCADE,
    FOREIGN KEY (MenuItemID) REFERENCES menu_items(MenuItemID)
);

-- Table to handle payments for orders
CREATE TABLE payments (
    PaymentID INT PRIMARY KEY AUTO_INCREMENT,
    OrderID INT NOT NULL,
    PaymentMethod ENUM('Cash', 'Credit Card', 'Debit Card', 'Online Payment') NOT NULL,
    AmountPaid DECIMAL(10, 2) NOT NULL,
    PaymentTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    TransactionID VARCHAR(100),
    ProcessedByStaffID INT,
    FOREIGN KEY (OrderID) REFERENCES orders(OrderID),
    FOREIGN KEY (ProcessedByStaffID) REFERENCES staff(StaffID)
);


-- #####################################################################
-- # 4. Autonomous Triggers for Order Management
-- #####################################################################

DELIMITER $$

-- TRIGGER 1: Manages totals and table status when a new item is added to an order.
CREATE TRIGGER after_order_detail_insert
AFTER INSERT ON order_details
FOR EACH ROW
BEGIN
    -- Update table status to Occupied
    UPDATE restaurant_tables SET Status = 'Occupied' WHERE TableID = (SELECT TableID FROM orders WHERE OrderID = NEW.OrderID);

    -- Update the total amount in the main Orders table
    UPDATE orders SET TotalAmount = TotalAmount + NEW.Subtotal WHERE OrderID = NEW.OrderID;
END$$


-- TRIGGER 2: Manages totals when an item is removed from an order.
CREATE TRIGGER after_order_detail_delete
AFTER DELETE ON order_details
FOR EACH ROW
BEGIN
    -- Update the total amount in the main Orders table
    UPDATE orders SET TotalAmount = TotalAmount - OLD.Subtotal WHERE OrderID = OLD.OrderID;
END$$


-- TRIGGER 3: Manages table status when an order is completed or cancelled.
CREATE TRIGGER after_orders_update
AFTER UPDATE ON orders
FOR EACH ROW
BEGIN
    DECLARE active_orders_count INT;
    IF NEW.OrderStatus IN ('Completed', 'Cancelled') THEN
        -- Check if there are any other active orders for this table
        SELECT COUNT(*) INTO active_orders_count
        FROM orders
        WHERE TableID = NEW.TableID AND OrderStatus NOT IN ('Completed', 'Cancelled');

        -- If no other active orders, set table to Available
        IF active_orders_count = 0 THEN
            UPDATE restaurant_tables SET Status = 'Available' WHERE TableID = NEW.TableID;
        END IF;
    END IF;
END$$

DELIMITER ;

-- #####################################################################
-- # End of Schema
-- #####################################################################

-- #####################################################################
-- # MODULE: SIMPLE STORE SALES
-- #####################################################################

CREATE TABLE store_item_categories (
    CategoryID INT PRIMARY KEY AUTO_INCREMENT,
    CategoryName VARCHAR(100) NOT NULL UNIQUE
);

-- Table for individual store items
CREATE TABLE store_items (
    StoreItemID INT PRIMARY KEY AUTO_INCREMENT,
    CategoryID INT NOT NULL,
    Name VARCHAR(100) NOT NULL,
    Description TEXT,
    Price DECIMAL(10, 2) NOT NULL,
    ImageUrl VARCHAR(255),
    IsAvailable BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (CategoryID) REFERENCES store_item_categories(CategoryID) ON DELETE CASCADE
);

--
-- Table 2: store_sales_log
-- Description: A log to record sales of individual store items, grouped by transaction.
--
CREATE TABLE store_sales_log (
    SaleID INT PRIMARY KEY AUTO_INCREMENT,
    TransactionID VARCHAR(50) NOT NULL COMMENT 'Groups multiple items into a single sale.',
    StoreItemID INT NOT NULL,
    Quantity INT NOT NULL DEFAULT 1,
    SalePrice DECIMAL(10, 2) NOT NULL COMMENT 'Price of the item at the time of sale.',
    TotalAmount DECIMAL(10, 2) GENERATED ALWAYS AS (SalePrice * Quantity) STORED,
    SaleTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (StoreItemID) REFERENCES store_items(StoreItemID) ON DELETE CASCADE,
    INDEX idx_transaction_id (TransactionID)
);





ALTER TABLE `staff`
     ADD `MonthlySalary` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER `Role`;
    
     -- Create a new table for staff financial transactions (ledger)
     CREATE TABLE `staff_ledger` (
       `LedgerID` INT PRIMARY KEY AUTO_INCREMENT,
       `StaffID` INT NOT NULL,
    `TransactionDate` DATE NOT NULL,
      `Description` VARCHAR(255) NOT NULL,
  `Credit` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Money owed to staff (e.g., monthly 
      salary)',
     `Debit` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 COMMENT 'Money paid to staff (e.g., payment, 
      advance)',
      `CreatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      FOREIGN KEY (`StaffID`) REFERENCES `staff`(`StaffID`) ON DELETE CASCADE
 );