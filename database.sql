-- ==========================================================
-- Student Grade Analytics Database Schema & Seed Data
-- ==========================================================
-- You can run this file in your MySQL database to set up
-- the tables and populate them with sample data.
-- 
-- Command: mysql -u <username> -p <database_name> < database.sql
-- ==========================================================

-- 1. Create Students Table
CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nis` VARCHAR(10) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `class` VARCHAR(10) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Create Grades Table
-- Notice: We deliberately store raw scores per subject without pre-calculating averages,
-- so that PHP arrays can perform all the aggregation, grouping, and ranking operations!
CREATE TABLE IF NOT EXISTS `grades` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` INT NOT NULL,
    `subject` VARCHAR(50) NOT NULL,
    `score` DECIMAL(5, 2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_grades_student` 
        FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) 
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Seed Students
INSERT INTO `students` (`id`, `nis`, `name`, `class`) VALUES
(1, '001', 'Citra Lestari', 'X-A'),
(2, '002', 'Andi Pratama', 'X-B'),
(3, '003', 'Budi Santoso', 'X-A'),
(4, '004', 'Dewi Anggraini', 'X-A'),
(5, '005', 'Eko Prasetyo', 'X-B'),
(6, '006', 'Fajar Ramadhan', 'X-C'),
(7, '007', 'Gita Gutawa', 'X-B'),
(8, '008', 'Hadi Wijaya', 'X-C'),
(9, '009', 'Indah Permata', 'X-A'),
(10, '010', 'Joko Susilo', 'X-C'),
(11, '011', 'Kartika Putri', 'X-B'),
(12, '012', 'Lukman Hakim', 'X-A'),
(13, '013', 'Maya Safitri', 'X-C'),
(14, '014', 'Nanda Kurnia', 'X-B'),
(15, '015', 'Oki Setiana', 'X-A'),
(16, '016', 'Putra Bagus', 'X-C'),
(17, '017', 'Qori Sandioriva', 'X-B'),
(18, '018', 'Rizky Febian', 'X-A'),
(19, '019', 'Siti Nurhaliza', 'X-C'),
(20, '020', 'Taufik Hidayat', 'X-B')
ON DUPLICATE KEY UPDATE `name`=VALUES(`name`), `class`=VALUES(`class`);

-- 4. Seed Grades (Math, English, Science, Computer Science)
INSERT INTO `grades` (`student_id`, `subject`, `score`) VALUES
-- 1. Citra Lestari (X-A) - Top Performer
(1, 'Math', 96.00),
(1, 'English', 95.00),
(1, 'Science', 98.00),
(1, 'Computer Science', 92.00),

-- 2. Andi Pratama (X-B) - High Performer
(2, 'Math', 90.00),
(2, 'English', 92.00),
(2, 'Science', 88.00),
(2, 'Computer Science', 95.00),

-- 3. Budi Santoso (X-A)
(3, 'Math', 85.00),
(3, 'English', 78.00),
(3, 'Science', 84.00),
(3, 'Computer Science', 80.00),

-- 4. Dewi Anggraini (X-A)
(4, 'Math', 88.00),
(4, 'English', 91.00),
(4, 'Science', 89.00),
(4, 'Computer Science', 86.00),

-- 5. Eko Prasetyo (X-B)
(5, 'Math', 72.00),
(5, 'English', 68.00),
(5, 'Science', 75.00),
(5, 'Computer Science', 70.00),

-- 6. Fajar Ramadhan (X-C)
(6, 'Math', 94.00),
(6, 'English', 89.00),
(6, 'Science', 91.00),
(6, 'Computer Science', 96.00),

-- 7. Gita Gutawa (X-B)
(7, 'Math', 86.00),
(7, 'English', 94.00),
(7, 'Science', 82.00),
(7, 'Computer Science', 88.00),

-- 8. Hadi Wijaya (X-C)
(8, 'Math', 65.00),
(8, 'English', 70.00),
(8, 'Science', 68.00),
(8, 'Computer Science', 60.00),

-- 9. Indah Permata (X-A)
(9, 'Math', 92.00),
(9, 'English', 87.00),
(9, 'Science', 90.00),
(9, 'Computer Science', 93.00),

-- 10. Joko Susilo (X-C)
(10, 'Math', 58.00),
(10, 'English', 64.00),
(10, 'Science', 54.00),
(10, 'Computer Science', 62.00),

-- 11. Kartika Putri (X-B)
(11, 'Math', 82.00),
(11, 'English', 85.00),
(11, 'Science', 80.00),
(11, 'Computer Science', 84.00),

-- 12. Lukman Hakim (X-A)
(12, 'Math', 78.00),
(12, 'English', 82.00),
(12, 'Science', 79.00),
(12, 'Computer Science', 81.00),

-- 13. Maya Safitri (X-C)
(13, 'Math', 89.00),
(13, 'English', 92.00),
(13, 'Science', 87.00),
(13, 'Computer Science', 90.00),

-- 14. Nanda Kurnia (X-B)
(14, 'Math', 60.00),
(14, 'English', 65.00),
(14, 'Science', 58.00),
(14, 'Computer Science', 62.00),

-- 15. Oki Setiana (X-A)
(15, 'Math', 84.00),
(15, 'English', 88.00),
(15, 'Science', 86.00),
(15, 'Computer Science', 85.00),

-- 16. Putra Bagus (X-C)
(16, 'Math', 76.00),
(16, 'English', 72.00),
(16, 'Science', 75.00),
(16, 'Computer Science', 78.00),

-- 17. Qori Sandioriva (X-B)
(17, 'Math', 91.00),
(17, 'English', 93.00),
(17, 'Science', 89.00),
(17, 'Computer Science', 94.00),

-- 18. Rizky Febian (X-A)
(18, 'Math', 79.00),
(18, 'English', 84.00),
(18, 'Science', 81.00),
(18, 'Computer Science', 83.00),

-- 19. Siti Nurhaliza (X-C)
(19, 'Math', 88.00),
(19, 'English', 90.00),
(19, 'Science', 85.00),
(19, 'Computer Science', 89.00),

-- 20. Taufik Hidayat (X-B)
(20, 'Math', 74.00),
(20, 'English', 76.00),
(20, 'Science', 72.00),
(20, 'Computer Science', 75.00);
