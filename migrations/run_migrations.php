<?php
// Safe migration runner: checks for columns/indexes and alters table only when needed.
require_once __DIR__ . '/../config/db.php';

function columnExists($conn, $db, $table, $column) {
    $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $db, $table, $column);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)$res['cnt'] > 0;
}

function indexExists($conn, $db, $table, $index) {
    $sql = "SELECT COUNT(*) AS cnt FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $db, $table, $index);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    return (int)$res['cnt'] > 0;
}

$dbName = '';
$result = $conn->query('SELECT DATABASE() AS dbname');
if ($result) {
    $row = $result->fetch_assoc();
    $dbName = $row['dbname'];
}

if (!$dbName) {
    echo "Could not determine database name.\n";
    exit(1);
}

echo "Running safe migrations on database: $dbName\n";

$table = 'users';

// 1) Ensure password column is VARCHAR(255)
if (columnExists($conn, $dbName, $table, 'password')) {
    // modify to VARCHAR(255) if needed
    $sql = "ALTER TABLE `$table` MODIFY `password` VARCHAR(255) NOT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Ensured `password` is VARCHAR(255).\n";
    } else {
        echo "Note: could not modify `password` column: " . $conn->error . "\n";
    }
} else {
    $sql = "ALTER TABLE `$table` ADD COLUMN `password` VARCHAR(255) NOT NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Added `password` column.\n";
    } else {
        echo "Note: could not add `password` column: " . $conn->error . "\n";
    }
}

// 2) Add verification_code if missing
if (!columnExists($conn, $dbName, $table, 'verification_code')) {
    $sql = "ALTER TABLE `$table` ADD COLUMN `verification_code` VARCHAR(64) NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Added `verification_code` column.\n";
    } else {
        echo "Note: could not add `verification_code`: " . $conn->error . "\n";
    }
} else {
    echo "`verification_code` already exists.\n";
}

// 3) Add token_expiry if missing
if (!columnExists($conn, $dbName, $table, 'token_expiry')) {
    $sql = "ALTER TABLE `$table` ADD COLUMN `token_expiry` DATETIME NULL";
    if ($conn->query($sql) === TRUE) {
        echo "Added `token_expiry` column.\n";
    } else {
        echo "Note: could not add `token_expiry`: " . $conn->error . "\n";
    }
} else {
    echo "`token_expiry` already exists.\n";
}

// 4) Add is_verified if missing
if (!columnExists($conn, $dbName, $table, 'is_verified')) {
    $sql = "ALTER TABLE `$table` ADD COLUMN `is_verified` TINYINT(1) NOT NULL DEFAULT 0";
    if ($conn->query($sql) === TRUE) {
        echo "Added `is_verified` column.\n";
    } else {
        echo "Note: could not add `is_verified`: " . $conn->error . "\n";
    }
} else {
    echo "`is_verified` already exists.\n";
}

// 5) Add index for verification_code if missing
if (!indexExists($conn, $dbName, $table, 'idx_verification_code')) {
    $sql = "CREATE INDEX idx_verification_code ON `$table` (verification_code)";
    if ($conn->query($sql) === TRUE) {
        echo "Created index idx_verification_code.\n";
    } else {
        echo "Note: could not create index: " . $conn->error . "\n";
    }
} else {
    echo "Index idx_verification_code already exists.\n";
}

echo "Migrations complete.\n";

// close connection if needed
$conn->close();

?>