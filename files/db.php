<?php
$host = 'localhost';
$dbname = 'quan_ly_sv';
$username = 'root';
$password = '';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="color:red;padding:20px;font-family:sans-serif;">
        <strong>Lỗi kết nối database:</strong> ' . htmlspecialchars($e->getMessage()) . '
    </div>');
}
