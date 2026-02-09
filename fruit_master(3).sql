-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 29, 2026 at 08:44 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fruit_master`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `Attendance_ID` int(11) NOT NULL,
  `Employee_ID` int(11) NOT NULL,
  `Shift_ID` int(11) NOT NULL,
  `Att_Date` date NOT NULL,
  `Clock_In` time DEFAULT NULL,
  `Clock_Out` time DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `attendance`
--
DELIMITER $$
CREATE TRIGGER `trg_attendance_status` BEFORE INSERT ON `attendance` FOR EACH ROW BEGIN
    DECLARE shift_start TIME;

    SELECT Start_Time
    INTO shift_start
    FROM shift
    WHERE Shift_ID = NEW.Shift_ID;

    IF NEW.Clock_In > shift_start THEN
        SET NEW.Status = 'Late';
    ELSE
        SET NEW.Status = 'On Time';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `Employee_ID` int(11) NOT NULL,
  `Employee_Name` varchar(100) NOT NULL,
  `Employee_Role` varchar(50) NOT NULL,
  `Employee_Phone` varchar(20) NOT NULL,
  `Employee_Email` varchar(100) NOT NULL,
  `Employee_Hourly_Rate` decimal(6,2) NOT NULL CHECK (`Employee_Hourly_Rate` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fruit`
--

CREATE TABLE `fruit` (
  `Fruit_ID` int(11) NOT NULL,
  `Fruit_Name` varchar(100) NOT NULL,
  `Unit` varchar(20) NOT NULL,
  `Price` decimal(8,2) NOT NULL CHECK (`Price` > 0),
  `Stock_Quantity` int(11) NOT NULL CHECK (`Stock_Quantity` >= 0),
  `Minimum_Threshold` int(11) NOT NULL CHECK (`Minimum_Threshold` >= 0),
  `Expiration_Date` date NOT NULL,
  `Status` varchar(20) DEFAULT 'Available',
  `Category_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fruit_category`
--

CREATE TABLE `fruit_category` (
  `Category_ID` int(11) NOT NULL,
  `Category_Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_log`
--

CREATE TABLE `inventory_log` (
  `Log_ID` int(11) NOT NULL,
  `Fruit_ID` int(11) NOT NULL,
  `Employee_ID` int(11) NOT NULL,
  `Transaction_Type` varchar(20) NOT NULL,
  `Quantity` int(11) NOT NULL CHECK (`Quantity` > 0),
  `Transaction_Date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale`
--

CREATE TABLE `sale` (
  `Sale_ID` int(11) NOT NULL,
  `Sale_Date_Time` datetime DEFAULT current_timestamp(),
  `Payment_Status` varchar(20) DEFAULT 'Pending',
  `Employee_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_item`
--

CREATE TABLE `sale_item` (
  `Sale_Item_ID` int(11) NOT NULL,
  `Sale_ID` int(11) NOT NULL,
  `Fruit_ID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL CHECK (`Quantity` > 0),
  `Unit_Price` decimal(8,2) NOT NULL CHECK (`Unit_Price` > 0),
  `Sub_Total` decimal(10,2) GENERATED ALWAYS AS (`Quantity` * `Unit_Price`) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `sale_item`
--
DELIMITER $$
CREATE TRIGGER `trg_check_stock_before_sale_item` BEFORE INSERT ON `sale_item` FOR EACH ROW BEGIN
    DECLARE current_stock INT;
    DECLARE emp_id INT;

    -- Get current stock of the fruit
    SELECT Stock_Quantity
    INTO current_stock
    FROM fruit
    WHERE Fruit_ID = NEW.Fruit_ID;

    -- If stock is insufficient
    IF current_stock < NEW.Quantity THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'STOCK INSUFFICIENT';
    ELSE
        -- Reduce stock
        UPDATE fruit
        SET Stock_Quantity = Stock_Quantity - NEW.Quantity
        WHERE Fruit_ID = NEW.Fruit_ID;

        -- Get employee who made the sale
        SELECT Employee_ID
        INTO emp_id
        FROM sale
        WHERE Sale_ID = NEW.Sale_ID;

        -- Insert inventory log
        INSERT INTO inventory_log
        (Fruit_ID, Employee_ID, Transaction_Type, Quantity)
        VALUES
        (NEW.Fruit_ID, emp_id, 'REMOVE', NEW.Quantity);
    END IF;

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `shift`
--

CREATE TABLE `shift` (
  `Shift_ID` int(11) NOT NULL,
  `Shift_Name` varchar(50) NOT NULL,
  `Start_Time` time NOT NULL,
  `End_Time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `Supplier_ID` int(11) NOT NULL,
  `Supplier_Name` varchar(100) DEFAULT NULL,
  `Supplier_Phone` varchar(20) NOT NULL,
  `Supplier_Email` varchar(100) NOT NULL,
  `Supplier_Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_fruit`
--

CREATE TABLE `supplier_fruit` (
  `Supplier_Fruit_ID` int(11) NOT NULL,
  `Supplier_ID` int(11) NOT NULL,
  `Fruit_ID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`Attendance_ID`),
  ADD KEY `fk_att_employee` (`Employee_ID`),
  ADD KEY `fk_att_shift` (`Shift_ID`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`Employee_ID`);

--
-- Indexes for table `fruit`
--
ALTER TABLE `fruit`
  ADD PRIMARY KEY (`Fruit_ID`),
  ADD KEY `fk_fruit_category` (`Category_ID`);

--
-- Indexes for table `fruit_category`
--
ALTER TABLE `fruit_category`
  ADD PRIMARY KEY (`Category_ID`),
  ADD UNIQUE KEY `Category_Name` (`Category_Name`);

--
-- Indexes for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD PRIMARY KEY (`Log_ID`),
  ADD KEY `fk_log_fruit` (`Fruit_ID`),
  ADD KEY `fk_log_employee` (`Employee_ID`);

--
-- Indexes for table `sale`
--
ALTER TABLE `sale`
  ADD PRIMARY KEY (`Sale_ID`),
  ADD KEY `fk_sale_employee` (`Employee_ID`);

--
-- Indexes for table `sale_item`
--
ALTER TABLE `sale_item`
  ADD PRIMARY KEY (`Sale_Item_ID`),
  ADD KEY `fk_sale` (`Sale_ID`),
  ADD KEY `fk_sale_fruit` (`Fruit_ID`);

--
-- Indexes for table `shift`
--
ALTER TABLE `shift`
  ADD PRIMARY KEY (`Shift_ID`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`Supplier_ID`);

--
-- Indexes for table `supplier_fruit`
--
ALTER TABLE `supplier_fruit`
  ADD PRIMARY KEY (`Supplier_Fruit_ID`),
  ADD KEY `fk_supplier` (`Supplier_ID`),
  ADD KEY `fk_supplier_fruit` (`Fruit_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `Attendance_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `Employee_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fruit`
--
ALTER TABLE `fruit`
  MODIFY `Fruit_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fruit_category`
--
ALTER TABLE `fruit_category`
  MODIFY `Category_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_log`
--
ALTER TABLE `inventory_log`
  MODIFY `Log_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale`
--
ALTER TABLE `sale`
  MODIFY `Sale_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_item`
--
ALTER TABLE `sale_item`
  MODIFY `Sale_Item_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shift`
--
ALTER TABLE `shift`
  MODIFY `Shift_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `Supplier_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_fruit`
--
ALTER TABLE `supplier_fruit`
  MODIFY `Supplier_Fruit_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_att_employee` FOREIGN KEY (`Employee_ID`) REFERENCES `employee` (`Employee_ID`),
  ADD CONSTRAINT `fk_att_shift` FOREIGN KEY (`Shift_ID`) REFERENCES `shift` (`Shift_ID`);

--
-- Constraints for table `fruit`
--
ALTER TABLE `fruit`
  ADD CONSTRAINT `fk_fruit_category` FOREIGN KEY (`Category_ID`) REFERENCES `fruit_category` (`Category_ID`);

--
-- Constraints for table `inventory_log`
--
ALTER TABLE `inventory_log`
  ADD CONSTRAINT `fk_log_employee` FOREIGN KEY (`Employee_ID`) REFERENCES `employee` (`Employee_ID`),
  ADD CONSTRAINT `fk_log_fruit` FOREIGN KEY (`Fruit_ID`) REFERENCES `fruit` (`Fruit_ID`);

--
-- Constraints for table `sale`
--
ALTER TABLE `sale`
  ADD CONSTRAINT `fk_sale_employee` FOREIGN KEY (`Employee_ID`) REFERENCES `employee` (`Employee_ID`);

--
-- Constraints for table `sale_item`
--
ALTER TABLE `sale_item`
  ADD CONSTRAINT `fk_sale` FOREIGN KEY (`Sale_ID`) REFERENCES `sale` (`Sale_ID`),
  ADD CONSTRAINT `fk_sale_fruit` FOREIGN KEY (`Fruit_ID`) REFERENCES `fruit` (`Fruit_ID`);

--
-- Constraints for table `supplier_fruit`
--
ALTER TABLE `supplier_fruit`
  ADD CONSTRAINT `fk_supplier` FOREIGN KEY (`Supplier_ID`) REFERENCES `supplier` (`Supplier_ID`),
  ADD CONSTRAINT `fk_supplier_fruit` FOREIGN KEY (`Fruit_ID`) REFERENCES `fruit` (`Fruit_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
