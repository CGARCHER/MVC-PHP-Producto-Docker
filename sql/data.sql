CREATE TABLE `PRODUCTS` (
  `cod` INT AUTO_INCREMENT PRIMARY KEY,
  `short_name` varchar(20) NOT NULL,
  `pvp` decimal(5,2) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `PRODUCTS` (`cod`, `short_name`, `pvp`, `nombre`) VALUES
(1, 'SSD', '400.00', 'BENQ'),
(2, 'PIXEL 99999', '999.99', 'dispositivo google'),
(3, 'iPad Pro', '900.00', 'Apple iPad Pro 9');