-- SQL to create category table and add restriction checks

-- 1. Create category table (if not exists)
CREATE TABLE IF NOT EXISTS `category` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL UNIQUE,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 2. Insert default categories (run this once)
INSERT IGNORE INTO `category` (`category_name`) VALUES 
('General'),
('Technology'),
('Web Dev'),
('Education'),
('Lifestyle'),
('News'),
('Entertainment'),
('Other');
