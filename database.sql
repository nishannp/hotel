-- Hotel Food and Beverage Management System Database Schema
-- This schema focuses on the restaurant/cafe aspect of a hotel,
-- dealing with dine-in customers, menu management, orders, and inventory.
-- VERSION 2.5: Added an 'admins' table for system administration.

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
    IsActive BOOLEAN DEFAULT TRUE
);

-- #####################################################################
-- # 2. Menu and Inventory Management Tables
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

-- Table for raw ingredients used in menu items
CREATE TABLE ingredients (
    IngredientID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(100) NOT NULL UNIQUE,
    UnitOfMeasure VARCHAR(20) NOT NULL, -- e.g., kg, liter, piece
    ImageUrl VARCHAR(255) -- URL for the ingredient's image
);

-- Table to manage stock of ingredients
CREATE TABLE inventory (
    IngredientID INT PRIMARY KEY,
    QuantityInStock DECIMAL(10, 3) NOT NULL DEFAULT 0.000,
    LastRestockDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    SupplierInfo TEXT,
    FOREIGN KEY (IngredientID) REFERENCES ingredients(IngredientID) ON DELETE CASCADE
);

-- Junction table to link MenuItems with Ingredients (many-to-many)
CREATE TABLE menu_item_ingredients (
    MenuItemID INT NOT NULL,
    IngredientID INT NOT NULL,
    QuantityRequired DECIMAL(10, 3) NOT NULL,
    PRIMARY KEY (MenuItemID, IngredientID),
    FOREIGN KEY (MenuItemID) REFERENCES menu_items(MenuItemID) ON DELETE CASCADE,
    FOREIGN KEY (IngredientID) REFERENCES ingredients(IngredientID) ON DELETE CASCADE
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
-- # 4. Autonomous Triggers for Inventory and Order Management
-- #####################################################################

DELIMITER $$

-- TRIGGER 1: Manages inventory and totals when a new item is added to an order.
CREATE TRIGGER after_order_detail_insert
AFTER INSERT ON order_details
FOR EACH ROW
BEGIN
    -- Loop through ingredients for the ordered item and check/decrement stock
    DECLARE done INT DEFAULT FALSE;
    DECLARE ing_id INT;
    DECLARE qty_needed DECIMAL(10, 3);
    DECLARE current_stock DECIMAL(10, 3);
    DECLARE item_name VARCHAR(100);
    DECLARE ing_name VARCHAR(100);
    DECLARE error_message VARCHAR(255);
    DECLARE cur_ingredients CURSOR FOR
        SELECT mi.IngredientID, i.Name, mi.QuantityRequired
        FROM menu_item_ingredients mi
        JOIN ingredients i ON mi.IngredientID = i.IngredientID
        WHERE mi.MenuItemID = NEW.MenuItemID;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    -- Update table status to Occupied
    UPDATE restaurant_tables SET Status = 'Occupied' WHERE TableID = (SELECT TableID FROM orders WHERE OrderID = NEW.OrderID);

    OPEN cur_ingredients;
    read_loop: LOOP
        FETCH cur_ingredients INTO ing_id, ing_name, qty_needed;
        IF done THEN
            LEAVE read_loop;
        END IF;

        SELECT QuantityInStock INTO current_stock FROM inventory WHERE IngredientID = ing_id;

        -- Check if there is enough stock
        IF current_stock < (qty_needed * NEW.Quantity) THEN
            SELECT Name INTO item_name FROM menu_items WHERE MenuItemID = NEW.MenuItemID;
            SET error_message = CONCAT('Not enough stock for ingredient: ', ing_name, ' to make item: ', item_name);
            SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = error_message;
        END IF;

        -- Decrement inventory
        UPDATE inventory SET QuantityInStock = QuantityInStock - (qty_needed * NEW.Quantity) WHERE IngredientID = ing_id;
    END LOOP;
    CLOSE cur_ingredients;

    -- Update the total amount in the main Orders table
    UPDATE orders SET TotalAmount = TotalAmount + NEW.Subtotal WHERE OrderID = NEW.OrderID;
END$$


-- TRIGGER 2: Manages inventory and totals when an item is removed from an order.
CREATE TRIGGER after_order_detail_delete
AFTER DELETE ON order_details
FOR EACH ROW
BEGIN
    -- Loop through ingredients and restock inventory
    DECLARE done INT DEFAULT FALSE;
    DECLARE ing_id INT;
    DECLARE qty_needed DECIMAL(10, 3);
    DECLARE cur_ingredients CURSOR FOR
        SELECT IngredientID, QuantityRequired FROM menu_item_ingredients WHERE MenuItemID = OLD.MenuItemID;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur_ingredients;
    read_loop: LOOP
        FETCH cur_ingredients INTO ing_id, qty_needed;
        IF done THEN
            LEAVE read_loop;
        END IF;
        -- Increment inventory (restock)
        UPDATE inventory SET QuantityInStock = QuantityInStock + (qty_needed * OLD.Quantity) WHERE IngredientID = ing_id;
    END LOOP;
    CLOSE cur_ingredients;

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
