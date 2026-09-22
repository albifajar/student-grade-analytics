<?php
/**
 * Database Connection & Raw Data Provider
 * ==========================================================
 * Project: Student Grade Analytics
 * Study Plan: Phase 1 (Database -> PHP Array)
 * 
 * This file handles the PDO connection to MySQL.
 * If your database is not yet set up, it automatically falls back
 * to realistic mock PHP arrays, allowing you to study and run all 
 * array manipulations immediately without setup errors!
 * ==========================================================
 */

declare(strict_types=1);

// Database configuration settings
// Adjust these to match your local MySQL configuration when ready:
const DB_HOST = 'db.fr-roub1.bengt.wasmernet.com';
const DB_PORT = '20184';
const DB_NAME = 'db_a53d78ff';
const DB_USER = 'user_04afd36a';
const DB_PASS = 'pw_F0JWEJBMjGoLwRR0DurNKcQkPzTa41pj';
const DB_CHARSET = 'utf8mb4';

/**
 * Attempt to establish a PDO connection to MySQL.
 * 
 * @return PDO|null Returns PDO instance or null on connection failure.
 */
function getDatabaseConnection(): ?PDO
{
    // If you want to force mock mode while practicing, set this to true:
    $forceMock = false;
    if ($forceMock) {
        return null;
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST,
        DB_PORT,
        DB_NAME,
        DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 2, // Quick timeout so page loads instantly if DB is down
    ];

    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Suppress and fallback gracefully to mock data
        return null;
    }
}

/**
 * Phase 1: Fetch raw students.
 * Queries MySQL if available; otherwise returns default mock array.
 * 
 * @param PDO|null $pdo
 * @return array<int, array{id: int, nis: string, name: string, class: string}>
 */
function getRawStudents(?PDO $pdo = null): array
{
    if ($pdo !== null) {
        try {
            /**
             * Study 1 Concept:
             * $students = $pdo->query("SELECT * FROM students")->fetchAll(PDO::FETCH_ASSOC);
             */
            $stmt = $pdo->query("SELECT id, nis, name, class FROM students ORDER BY id ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($data)) {
                return $data;
            }
        } catch (PDOException $e) {
            // Table might not exist yet, fallback to mock data
        }
    }

    // Default Mock Dataset (Phase 1 sample data)
    return [
        ['id' => 1,  'nis' => '001', 'name' => 'Citra Lestari', 'class' => 'X-A'],
        ['id' => 2,  'nis' => '002', 'name' => 'Andi Pratama',  'class' => 'X-B'],
        ['id' => 3,  'nis' => '003', 'name' => 'Budi Santoso',  'class' => 'X-A'],
        ['id' => 4,  'nis' => '004', 'name' => 'Dewi Anggraini','class' => 'X-A'],
        ['id' => 5,  'nis' => '005', 'name' => 'Eko Prasetyo',  'class' => 'X-B'],
        ['id' => 6,  'nis' => '006', 'name' => 'Fajar Ramadhan','class' => 'X-C'],
        ['id' => 7,  'nis' => '007', 'name' => 'Gita Gutawa',   'class' => 'X-B'],
        ['id' => 8,  'nis' => '008', 'name' => 'Hadi Wijaya',   'class' => 'X-C'],
        ['id' => 9,  'nis' => '009', 'name' => 'Indah Permata', 'class' => 'X-A'],
        ['id' => 10, 'nis' => '010', 'name' => 'Joko Susilo',   'class' => 'X-C'],
        ['id' => 11, 'nis' => '011', 'name' => 'Kartika Putri', 'class' => 'X-B'],
        ['id' => 12, 'nis' => '012', 'name' => 'Lukman Hakim',  'class' => 'X-A'],
        ['id' => 13, 'nis' => '013', 'name' => 'Maya Safitri',  'class' => 'X-C'],
        ['id' => 14, 'nis' => '014', 'name' => 'Nanda Kurnia',  'class' => 'X-B'],
        ['id' => 15, 'nis' => '015', 'name' => 'Oki Setiana',   'class' => 'X-A'],
        ['id' => 16, 'nis' => '016', 'name' => 'Putra Bagus',   'class' => 'X-C'],
        ['id' => 17, 'nis' => '017', 'name' => 'Qori Sandioriva','class' => 'X-B'],
        ['id' => 18, 'nis' => '018', 'name' => 'Rizky Febian',  'class' => 'X-A'],
        ['id' => 19, 'nis' => '019', 'name' => 'Siti Nurhaliza','class' => 'X-C'],
        ['id' => 20, 'nis' => '020', 'name' => 'Taufik Hidayat','class' => 'X-B'],
    ];
}

/**
 * Phase 1 & Phase 3: Fetch raw grades.
 * Deliberately queries raw grades without pre-aggregating or joining in SQL.
 * 
 * @param PDO|null $pdo
 * @return array<int, array{student_id: int, subject: string, score: float}>
 */
function getRawGrades(?PDO $pdo = null): array
{
    if ($pdo !== null) {
        try {
            $stmt = $pdo->query("SELECT student_id, subject, score FROM grades ORDER BY student_id ASC, subject ASC");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($data)) {
                // Cast scores to float
                return array_map(function ($row) {
                    return [
                        'student_id' => (int) $row['student_id'],
                        'subject'    => (string) $row['subject'],
                        'score'      => (float) $row['score'],
                    ];
                }, $data);
            }
        } catch (PDOException $e) {
            // Table might not exist yet, fallback to mock data
        }
    }

    // Default Mock Dataset (Phase 3 sample data)
    return [
        // 1. Citra Lestari (X-A)
        ['student_id' => 1, 'subject' => 'Math', 'score' => 96.0],
        ['student_id' => 1, 'subject' => 'English', 'score' => 95.0],
        ['student_id' => 1, 'subject' => 'Science', 'score' => 98.0],
        ['student_id' => 1, 'subject' => 'Computer Science', 'score' => 92.0],

        // 2. Andi Pratama (X-B)
        ['student_id' => 2, 'subject' => 'Math', 'score' => 90.0],
        ['student_id' => 2, 'subject' => 'English', 'score' => 92.0],
        ['student_id' => 2, 'subject' => 'Science', 'score' => 88.0],
        ['student_id' => 2, 'subject' => 'Computer Science', 'score' => 95.0],

        // 3. Budi Santoso (X-A)
        ['student_id' => 3, 'subject' => 'Math', 'score' => 85.0],
        ['student_id' => 3, 'subject' => 'English', 'score' => 78.0],
        ['student_id' => 3, 'subject' => 'Science', 'score' => 84.0],
        ['student_id' => 3, 'subject' => 'Computer Science', 'score' => 80.0],

        // 4. Dewi Anggraini (X-A)
        ['student_id' => 4, 'subject' => 'Math', 'score' => 88.0],
        ['student_id' => 4, 'subject' => 'English', 'score' => 91.0],
        ['student_id' => 4, 'subject' => 'Science', 'score' => 89.0],
        ['student_id' => 4, 'subject' => 'Computer Science', 'score' => 86.0],

        // 5. Eko Prasetyo (X-B)
        ['student_id' => 5, 'subject' => 'Math', 'score' => 72.0],
        ['student_id' => 5, 'subject' => 'English', 'score' => 68.0],
        ['student_id' => 5, 'subject' => 'Science', 'score' => 75.0],
        ['student_id' => 5, 'subject' => 'Computer Science', 'score' => 70.0],

        // 6. Fajar Ramadhan (X-C)
        ['student_id' => 6, 'subject' => 'Math', 'score' => 94.0],
        ['student_id' => 6, 'subject' => 'English', 'score' => 89.0],
        ['student_id' => 6, 'subject' => 'Science', 'score' => 91.0],
        ['student_id' => 6, 'subject' => 'Computer Science', 'score' => 96.0],

        // 7. Gita Gutawa (X-B)
        ['student_id' => 7, 'subject' => 'Math', 'score' => 86.0],
        ['student_id' => 7, 'subject' => 'English', 'score' => 94.0],
        ['student_id' => 7, 'subject' => 'Science', 'score' => 82.0],
        ['student_id' => 7, 'subject' => 'Computer Science', 'score' => 88.0],

        // 8. Hadi Wijaya (X-C)
        ['student_id' => 8, 'subject' => 'Math', 'score' => 65.0],
        ['student_id' => 8, 'subject' => 'English', 'score' => 70.0],
        ['student_id' => 8, 'subject' => 'Science', 'score' => 68.0],
        ['student_id' => 8, 'subject' => 'Computer Science', 'score' => 60.0],

        // 9. Indah Permata (X-A)
        ['student_id' => 9, 'subject' => 'Math', 'score' => 92.0],
        ['student_id' => 9, 'subject' => 'English', 'score' => 87.0],
        ['student_id' => 9, 'subject' => 'Science', 'score' => 90.0],
        ['student_id' => 9, 'subject' => 'Computer Science', 'score' => 93.0],

        // 10. Joko Susilo (X-C)
        ['student_id' => 10, 'subject' => 'Math', 'score' => 58.0],
        ['student_id' => 10, 'subject' => 'English', 'score' => 64.0],
        ['student_id' => 10, 'subject' => 'Science', 'score' => 54.0],
        ['student_id' => 10, 'subject' => 'Computer Science', 'score' => 62.0],

        // 11. Kartika Putri (X-B)
        ['student_id' => 11, 'subject' => 'Math', 'score' => 82.0],
        ['student_id' => 11, 'subject' => 'English', 'score' => 85.0],
        ['student_id' => 11, 'subject' => 'Science', 'score' => 80.0],
        ['student_id' => 11, 'subject' => 'Computer Science', 'score' => 84.0],

        // 12. Lukman Hakim (X-A)
        ['student_id' => 12, 'subject' => 'Math', 'score' => 78.0],
        ['student_id' => 12, 'subject' => 'English', 'score' => 82.0],
        ['student_id' => 12, 'subject' => 'Science', 'score' => 79.0],
        ['student_id' => 12, 'subject' => 'Computer Science', 'score' => 81.0],

        // 13. Maya Safitri (X-C)
        ['student_id' => 13, 'subject' => 'Math', 'score' => 89.0],
        ['student_id' => 13, 'subject' => 'English', 'score' => 92.0],
        ['student_id' => 13, 'subject' => 'Science', 'score' => 87.0],
        ['student_id' => 13, 'subject' => 'Computer Science', 'score' => 90.0],

        // 14. Nanda Kurnia (X-B)
        ['student_id' => 14, 'subject' => 'Math', 'score' => 60.0],
        ['student_id' => 14, 'subject' => 'English', 'score' => 65.0],
        ['student_id' => 14, 'subject' => 'Science', 'score' => 58.0],
        ['student_id' => 14, 'subject' => 'Computer Science', 'score' => 62.0],

        // 15. Oki Setiana (X-A)
        ['student_id' => 15, 'subject' => 'Math', 'score' => 84.0],
        ['student_id' => 15, 'subject' => 'English', 'score' => 88.0],
        ['student_id' => 15, 'subject' => 'Science', 'score' => 86.0],
        ['student_id' => 15, 'subject' => 'Computer Science', 'score' => 85.0],

        // 16. Putra Bagus (X-C)
        ['student_id' => 16, 'subject' => 'Math', 'score' => 76.0],
        ['student_id' => 16, 'subject' => 'English', 'score' => 72.0],
        ['student_id' => 16, 'subject' => 'Science', 'score' => 75.0],
        ['student_id' => 16, 'subject' => 'Computer Science', 'score' => 78.0],

        // 17. Qori Sandioriva (X-B)
        ['student_id' => 17, 'subject' => 'Math', 'score' => 91.0],
        ['student_id' => 17, 'subject' => 'English', 'score' => 93.0],
        ['student_id' => 17, 'subject' => 'Science', 'score' => 89.0],
        ['student_id' => 17, 'subject' => 'Computer Science', 'score' => 94.0],

        // 18. Rizky Febian (X-A)
        ['student_id' => 18, 'subject' => 'Math', 'score' => 79.0],
        ['student_id' => 18, 'subject' => 'English', 'score' => 84.0],
        ['student_id' => 18, 'subject' => 'Science', 'score' => 81.0],
        ['student_id' => 18, 'subject' => 'Computer Science', 'score' => 83.0],

        // 19. Siti Nurhaliza (X-C)
        ['student_id' => 19, 'subject' => 'Math', 'score' => 88.0],
        ['student_id' => 19, 'subject' => 'English', 'score' => 90.0],
        ['student_id' => 19, 'subject' => 'Science', 'score' => 85.0],
        ['student_id' => 19, 'subject' => 'Computer Science', 'score' => 89.0],

        // 20. Taufik Hidayat (X-B)
        ['student_id' => 20, 'subject' => 'Math', 'score' => 74.0],
        ['student_id' => 20, 'subject' => 'English', 'score' => 76.0],
        ['student_id' => 20, 'subject' => 'Science', 'score' => 72.0],
        ['student_id' => 20, 'subject' => 'Computer Science', 'score' => 75.0],
    ];
}

/**
 * Returns data source metadata for UI feedback.
 */
function getDataSourceInfo(?PDO $pdo): array
{
    if ($pdo !== null) {
        return [
            'type' => 'mysql',
            'label' => 'Live MySQL Database',
            'status' => 'connected',
            'detail' => sprintf('Connected to %s@%s:%s', DB_NAME, DB_HOST, DB_PORT),
        ];
    }

    return [
        'type' => 'mock',
        'label' => 'Built-in Mock Arrays',
        'status' => 'ready',
        'detail' => 'Database connection optional. Ready to integrate with MySQL anytime via db.php and database.sql.',
    ];
}
