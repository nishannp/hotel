<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');
require_once '../config.php';

// --- UTILITY FUNCTIONS ---
function send_success(array $data = []): void {
    echo json_encode(array_merge(['success' => true], $data));
    exit;
}

function send_error(string $message, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Ensure an admin is logged in for all actions
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    send_error('You must be logged in to perform this action.', 401);
}

// --- ROUTING ---
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    send_error('No action specified.');
}

try {
    switch ($action) {
        case 'getAdmins':
            $current_admin_id = $_SESSION['admin_id'] ?? 0;
            $result = $conn->query("SELECT AdminID, Username, PhoneNumber, CreatedAt FROM admins ORDER BY Username");
            $admins = $result->fetch_all(MYSQLI_ASSOC);
            send_success(['data' => $admins, 'current_admin_id' => $current_admin_id]);
            break;

        case 'addAdmin':
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $password_confirm = $_POST['password_confirm'] ?? '';
            $phone_number = trim($_POST['phone_number'] ?? '') ?: null;

            if (empty($username) || empty($password) || empty($password_confirm)) {
                send_error('Username and both password fields are required.');
            }
            if ($password !== $password_confirm) {
                send_error('Passwords do not match.');
            }
            if (strlen($password) < 8) {
                send_error('Password must be at least 8 characters long.');
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO admins (Username, PasswordHash, PhoneNumber) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $password_hash, $phone_number);

            if (!$stmt->execute()) {
                if ($conn->errno == 1062) {
                    send_error("An admin with that username or phone number already exists.");
                }
                send_error('Failed to create admin: ' . $stmt->error, 500);
            }
            
            send_success(['message' => "Admin '{$username}' created successfully."]);
            break;

        case 'updatePassword':
            $admin_id_to_change = filter_input(INPUT_POST, 'admin_id', FILTER_VALIDATE_INT);
            $new_password = $_POST['password'] ?? '';
            $new_password_confirm = $_POST['password_confirm'] ?? '';
            $current_password = $_POST['current_password'] ?? null;
            $current_session_id = $_SESSION['admin_id'] ?? 0;

            if (!$admin_id_to_change || empty($new_password) || empty($new_password_confirm)) {
                send_error('Admin ID and both new password fields are required.');
            }
            if ($new_password !== $new_password_confirm) {
                send_error('New passwords do not match.');
            }
            if (strlen($new_password) < 8) {
                send_error('New password must be at least 8 characters long.');
            }

            // If the user is changing their own password, verify their current one
            if ($admin_id_to_change === $current_session_id) {
                if (empty($current_password)) {
                    send_error('Current password is required to change your own password.');
                }
                
                $stmt = $conn->prepare("SELECT PasswordHash FROM admins WHERE AdminID = ?");
                $stmt->bind_param("i", $current_session_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $admin = $result->fetch_assoc();
                $stmt->close();

                if (!$admin || !password_verify($current_password, $admin['PasswordHash'])) {
                    send_error('Incorrect current password.');
                }
            }

            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE admins SET PasswordHash = ? WHERE AdminID = ?");
            $stmt->bind_param("si", $password_hash, $admin_id_to_change);

            if (!$stmt->execute()) {
                send_error('Failed to update password: ' . $stmt->error, 500);
            }

            send_success(['message' => 'Password updated successfully.']);
            break;

        case 'deleteAdmin':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) send_error('Invalid Admin ID.');

            if ($id === ($_SESSION['admin_id'] ?? 0)) {
                send_error('You cannot delete your own account.');
            }

            $count_stmt = $conn->query("SELECT COUNT(*) as admin_count FROM admins");
            $count = $count_stmt->fetch_assoc()['admin_count'];
            if ($count <= 1) {
                send_error('Cannot delete the last remaining administrator.');
            }

            $stmt = $conn->prepare("DELETE FROM admins WHERE AdminID = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) send_error('Failed to delete admin: ' . $stmt->error, 500);
            
            if ($stmt->affected_rows === 0) send_error('Admin not found.', 404);

            send_success(['message' => 'Admin deleted successfully.']);
            break;

        default:
            send_error('Invalid action specified.');
            break;
    }
} catch (Exception $e) {
    error_log("Admin Handler Error: " . $e->getMessage());
    send_error('A server error occurred. Please try again.', 500);
}

$conn->close();
