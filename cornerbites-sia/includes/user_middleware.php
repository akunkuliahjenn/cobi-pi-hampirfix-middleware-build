<?php
// includes/user_middleware.php
// Middleware untuk memastikan data isolation per user

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: /cornerbites-sia/auth/login.php");
    exit();
}

// Function helper untuk query yang aman dengan user_id
function executeUserQuery($conn, $query, $params = [], $includeUserId = true) {
    if ($includeUserId && !empty($_SESSION['user_id'])) {
        // Tambahkan user_id ke parameter jika belum ada
        if (strpos($query, 'user_id') === false) {
            // Jika WHERE sudah ada, tambahkan AND
            if (stripos($query, 'WHERE') !== false) {
                $query = str_ireplace('WHERE', 'WHERE user_id = ? AND', $query);
                array_unshift($params, $_SESSION['user_id']);
            } else {
                // Jika belum ada WHERE, tambahkan
                $insertPos = stripos($query, 'ORDER BY');
                if ($insertPos === false) {
                    $insertPos = stripos($query, 'LIMIT');
                }
                if ($insertPos === false) {
                    $insertPos = stripos($query, 'GROUP BY');
                }

                if ($insertPos !== false) {
                    $query = substr_replace($query, ' WHERE user_id = ? ', $insertPos, 0);
                } else {
                    $query .= ' WHERE user_id = ?';
                }
                array_push($params, $_SESSION['user_id']);
            }
        }
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

// Function untuk INSERT dengan user_id otomatis
function insertWithUserId($conn, $table, $data) {
    $data['user_id'] = $_SESSION['user_id'];
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));

    $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
    $stmt = $conn->prepare($query);

    foreach ($data as $key => $value) {
        $stmt->bindValue(":{$key}", $value);
    }

    return $stmt->execute();
}

// Function untuk UPDATE dengan user_id filter otomatis
function updateWithUserId($conn, $table, $data, $where, $whereParams = []) {
    // Hapus user_id dari data update jika ada
    unset($data['user_id']);

    $setParts = [];
    foreach ($data as $key => $value) {
        $setParts[] = "{$key} = :{$key}";
    }
    $setClause = implode(', ', $setParts);

    // Tambahkan user_id filter ke WHERE
    $where .= " AND user_id = :user_id";
    $whereParams['user_id'] = $_SESSION['user_id'];

    $query = "UPDATE {$table} SET {$setClause} WHERE {$where}";
    $stmt = $conn->prepare($query);

    // Bind data values
    foreach ($data as $key => $value) {
        $stmt->bindValue(":{$key}", $value);
    }

    // Bind where values
    foreach ($whereParams as $key => $value) {
        $stmt->bindValue(":{$key}", $value);
    }

    return $stmt->execute();
}

// Function untuk DELETE dengan user_id filter otomatis
function deleteWithUserId($conn, $table, $where, $whereParams = []) {
    // Tambahkan user_id filter ke WHERE
    $where .= " AND user_id = :user_id";
    $whereParams['user_id'] = $_SESSION['user_id'];

    $query = "DELETE FROM {$table} WHERE {$where}";
    $stmt = $conn->prepare($query);

    // Bind where values
    foreach ($whereParams as $key => $value) {
        $stmt->bindValue(":{$key}", $value);
    }

    return $stmt->execute();
}
?>