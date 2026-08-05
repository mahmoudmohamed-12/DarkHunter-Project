
<?php
// database.php - Mock database functions for IDOR Labs
// This simulates a database using arrays to avoid needing actual DB setup


// Mock Data Storage
$GLOBALS['orders_db'] = [
    // User 1 orders
    ['id' => 1, 'user_id' => 1, 'product' => 'Laptop', 'amount' => 999.99, 'status' => 'shipped', 'address' => '123 Main St, City', 'payment_method' => '****1234'],
    ['id' => 2, 'user_id' => 1, 'product' => 'Mouse', 'amount' => 29.99, 'status' => 'delivered', 'address' => '123 Main St, City', 'payment_method' => '****1234'],
    // User 2 orders (100, 101, 102)
    ['id' => 100, 'user_id' => 2, 'product' => 'Phone', 'amount' => 699.99, 'status' => 'processing', 'address' => '456 Oak Ave, Town', 'payment_method' => '****5678'],
    ['id' => 101, 'user_id' => 2, 'product' => 'Tablet', 'amount' => 499.99, 'status' => 'shipped', 'address' => '456 Oak Ave, Town', 'payment_method' => '****5678'],
    ['id' => 102, 'user_id' => 2, 'product' => 'Headphones', 'amount' => 199.99, 'status' => 'delivered', 'address' => '456 Oak Ave, Town', 'payment_method' => '****5678'],
    // Admin order (999) - SECRET
    ['id' => 999, 'user_id' => 99, 'product' => 'FLAG{IDOR_ORDER_MASTER}', 'amount' => 0.01, 'status' => 'secret', 'address' => 'Admin HQ, Secret Location', 'payment_method' => 'FLAG{IDOR_ORDER_MASTER}'],
];

$GLOBALS['documents_db'] = [
    // User 1 documents
    ['id' => 1, 'uuid' => '550e8400-e29b-41d4-a716-446655440001', 'user_id' => 1, 'title' => 'Project Proposal', 'classification' => 'normal', 'content' => 'This is a standard project proposal document.', 'secret_content' => ''],
    ['id' => 2, 'uuid' => '550e8400-e29b-41d4-a716-446655440002', 'user_id' => 1, 'title' => 'Research Notes', 'classification' => 'normal', 'content' => 'Research findings from Q1 2024.', 'secret_content' => ''],
    // User 2 documents
    ['id' => 3, 'uuid' => '550e8400-e29b-41d4-a716-446655440003', 'user_id' => 2, 'title' => 'Financial Report', 'classification' => 'confidential', 'content' => 'Confidential financial data for 2024.', 'secret_content' => ''],
    // Admin document (999) - TOP SECRET
    ['id' => 99, 'uuid' => '550e8400-e29b-41d4-a716-446655440999', 'user_id' => 99, 'title' => 'Master Key Document', 'classification' => 'top-secret', 'content' => 'TOP SECRET - AUTHORIZED EYES ONLY', 'secret_content' => 'FLAG{UUID_IDOR_VULNERABILITY_FOUND}'],
];

$GLOBALS['users_db'] = [
    ['id' => 1, 'username' => 'customer1', 'email' => 'customer1@example.com', 'role' => 'customer', 'api_key' => 'key_abc123'],
    ['id' => 2, 'username' => 'customer2', 'email' => 'customer2@example.com', 'role' => 'customer', 'api_key' => 'key_def456'],
    ['id' => 3, 'username' => 'user3', 'email' => 'user3@example.com', 'role' => 'user', 'api_key' => 'key_ghi789'],
    ['id' => 99, 'username' => 'admin', 'email' => 'admin@system.com', 'role' => 'administrator', 'api_key' => 'FLAG{API_IDOR_ADMIN_ACCESS}', 'secret_data' => 'FLAG{API_IDOR_ADMIN_ACCESS}'],
];

$GLOBALS['messages_db'] = [
    ['id' => 1, 'user_id' => 1, 'from' => 'support', 'content' => 'Welcome to our platform!'],
    ['id' => 2, 'user_id' => 1, 'from' => 'system', 'content' => 'Your order #1 has been shipped.'],
    ['id' => 3, 'user_id' => 2, 'from' => 'support', 'content' => 'Account verification required.'],
    ['id' => 4, 'user_id' => 2, 'from' => 'admin', 'content' => 'FLAG{MESSAGE_IDOR_ACCESS} - Admin secret message'],
    ['id' => 5, 'user_id' => 99, 'from' => 'system', 'content' => 'Admin panel access granted. FLAG{API_IDOR_ADMIN_DATA}'],
];

$GLOBALS['settings_db'] = [
    1 => ['email' => 'customer1@example.com', 'notifications' => 'on', 'theme' => 'dark'],
    2 => ['email' => 'customer2@example.com', 'notifications' => 'off', 'theme' => 'light'],
    99 => ['email' => 'admin@system.com', 'notifications' => 'on', 'theme' => 'dark', 'flag' => 'FLAG{SETTINGS_IDOR_MODIFIED}'],
];

$GLOBALS['files_db'] = [
    // User 1 files
    ['id' => 1, 'user_id' => 1, 'name' => 'report.pdf', 'size' => '2.5 MB', 'date' => '2024-01-15', 'content' => 'This is a sample PDF content for user 1.', 'is_sensitive' => false, 'classification' => 'normal'],
    ['id' => 2, 'user_id' => 1, 'name' => 'notes.txt', 'size' => '15 KB', 'date' => '2024-01-10', 'content' => 'Meeting notes from January.', 'is_sensitive' => false, 'classification' => 'normal'],
    // User 2 files
    ['id' => 5, 'user_id' => 2, 'name' => 'salary.pdf', 'size' => '1.8 MB', 'date' => '2024-01-20', 'content' => 'CONFIDENTIAL: Salary information for User 2. Annual: $75,000', 'is_sensitive' => true, 'classification' => 'confidential'],
    // User 3 files
    ['id' => 10, 'user_id' => 3, 'name' => 'passwords.txt', 'size' => '5 KB', 'date' => '2024-01-25', 'content' => 'User 3 Passwords:\\nEmail: pass123\\nBank: secret456', 'is_sensitive' => true, 'classification' => 'secret'],
    // Admin files (Blind IDOR target)
    ['id' => 99, 'user_id' => 99, 'name' => 'admin_secrets.pdf', 'size' => '3.2 MB', 'date' => '2024-01-01', 'content' => 'FLAG{BLIND_IDOR_TIMING_ATTACK_SUCCESS}', 'is_sensitive' => true, 'classification' => 'top-secret'],
    // Admin file (File Download)
    ['id' => 20, 'user_id' => 99, 'name' => 'flag.txt', 'size' => '1 KB', 'date' => '2024-01-30', 'content' => 'FLAG{FILE_DOWNLOAD_IDOR_MASTER}', 'is_sensitive' => true, 'classification' => 'top-secret'],
];

// Initialize database (mock function)
function initDB() {
    // In real scenario, this would connect to MySQL/PostgreSQL
    // Here we just ensure session is started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// ========== LAB 2 FUNCTIONS ==========
function getOrderById($order_id) {
    foreach ($GLOBALS['orders_db'] as $order) {
        if ($order['id'] == $order_id) {
            return $order;
        }
    }
    return null;
}

function getOrdersByUserId($user_id) {
    $orders = [];
    foreach ($GLOBALS['orders_db'] as $order) {
        if ($order['user_id'] == $user_id) {
            $orders[] = $order;
        }
    }
    return $orders;
}

function cancelOrder($order_id) {
    foreach ($GLOBALS['orders_db'] as &$order) {
        if ($order['id'] == $order_id) {
            $order['status'] = 'cancelled';
            return $order;
        }
    }
    return null;
}

function updateOrderAddress($order_id, $new_address) {
    foreach ($GLOBALS['orders_db'] as &$order) {
        if ($order['id'] == $order_id) {
            $order['address'] = $new_address;
            return true;
        }
    }
    return false;
}

// ========== LAB 3 FUNCTIONS ==========
function getDocumentByUUID($uuid) {
    foreach ($GLOBALS['documents_db'] as $doc) {
        if ($doc['uuid'] === $uuid) {
            return $doc;
        }
    }
    return null;
}

function getDocumentsByUserId($user_id) {
    $docs = [];
    foreach ($GLOBALS['documents_db'] as $doc) {
        if ($doc['user_id'] == $user_id) {
            $docs[] = $doc;
        }
    }
    return $docs;
}

// ========== LAB 4 FUNCTIONS ==========
function getUserAPI($user_id) {
    foreach ($GLOBALS['users_db'] as $user) {
        if ($user['id'] == $user_id) {
            // Remove sensitive fields for normal users
            if ($user_id != 99) {
                unset($user['secret_data']);
            }
            return $user;
        }
    }
    return ['error' => 'User not found'];
}

function getMessages($user_id) {
    $messages = [];
    foreach ($GLOBALS['messages_db'] as $msg) {
        if ($msg['user_id'] == $user_id) {
            $messages[] = $msg;
        }
    }
    return $messages;
}

function getSettings($user_id) {
    if (isset($GLOBALS['settings_db'][$user_id])) {
        return $GLOBALS['settings_db'][$user_id];
    }
    return ['error' => 'Settings not found'];
}

function updateSettings($user_id, $setting, $value) {
    if (!isset($GLOBALS['settings_db'][$user_id])) {
        $GLOBALS['settings_db'][$user_id] = [];
    }
    $GLOBALS['settings_db'][$user_id][$setting] = $value;
    return ['success' => true, 'user_id' => $user_id, 'updated' => [$setting => $value]];
}

function deleteUser($user_id) {
    // Simulate deletion (in real app, this would delete from DB)
    return ['success' => true, 'message' => "User $user_id deleted (simulated)", 'flag' => 'FLAG{USER_DELETED_IDOR}'];
}

// ========== LAB 5 FUNCTIONS ==========
function getUserFiles($user_id) {
    $files = [];
    foreach ($GLOBALS['files_db'] as $file) {
        if ($file['user_id'] == $user_id) {
            $files[] = $file;
        }
    }
    return $files;
}

function accessFile($file_id, $current_user_id) {
    // Simulate timing differences for blind IDOR
    // Fast (10ms): File doesn't exist
    // Medium (50ms): Your file
    // Slow (100ms): File exists but not yours
    
    $file = null;
    foreach ($GLOBALS['files_db'] as $f) {
        if ($f['id'] == $file_id) {
            $file = $f;
            break;
        }
    }
    
    if (!$file) {
        // File doesn't exist - fast response
        usleep(10000); // 10ms
        return 'not_found';
    }
    
    if ($file['user_id'] == $current_user_id) {
        // Your file - medium response
        usleep(50000); // 50ms
        return $file;
    } else {
        // File exists but not yours - slow response (authorization check takes time)
        usleep(100000); // 100ms
        return 'unauthorized';
    }
}

// ========== LAB 6 FUNCTIONS ==========
function getFileById($file_id) {
    foreach ($GLOBALS['files_db'] as $file) {
        if ($file['id'] == $file_id) {
            return $file;
        }
    }
    return null;
}

?>
