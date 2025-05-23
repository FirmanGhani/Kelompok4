<?php
function log_activity($user_id, $username, $activity_type, $description) {
    global $conn;
    
    // Pastikan koneksi database tersedia
    if (!isset($conn)) {
        require_once 'config.php';
    }
    
    // Cek apakah tabel activity_log ada, jika tidak buat tabel
    $table_check = $conn->query("SHOW TABLES LIKE 'activity_log'");
    if ($table_check->num_rows == 0) {
        $create_table = "
            CREATE TABLE activity_log (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                username VARCHAR(50) NOT NULL,
                activity_type VARCHAR(50) NOT NULL,
                description TEXT,
                timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ";
        $conn->query($create_table);
    }
    
    // Insert log activity
    $stmt = $conn->prepare("INSERT INTO activity_log (user_id, username, activity_type, description) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $username, $activity_type, $description);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
    return false;
}
?>