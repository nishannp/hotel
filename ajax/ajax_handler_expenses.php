<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// --- ROUTING ---
$action = $_POST['action'] ?? $_GET['action'] ?? null;

if (!$action) {
    send_error('No action specified.');
}

try {
    switch ($action) {
        // --- FETCH INITIAL DATA ---
        case 'getInitialData':
            $data = [];
            $business_units_result = $conn->query("SELECT id, name FROM business_units ORDER BY name");
            $data['business_units'] = $business_units_result->fetch_all(MYSQLI_ASSOC);
            
            $categories_result = $conn->query("SELECT id, name FROM expense_categories ORDER BY name");
            $data['categories'] = $categories_result->fetch_all(MYSQLI_ASSOC);
            
            send_success(['data' => $data]);
            break;

        // --- EXPENSE CATEGORY ACTIONS ---
        case 'addCategory':
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) send_error('Category name is required.');

            $stmt = $conn->prepare("INSERT INTO expense_categories (name) VALUES (?)");
            $stmt->bind_param("s", $name);
            if (!$stmt->execute()) {
                if ($conn->errno == 1062) { // Duplicate entry
                    send_error("Category '{$name}' already exists.");
                }
                send_error('Failed to add category: ' . $stmt->error, 500);
            }
            $new_id = $stmt->insert_id;
            $stmt->close();
            send_success(['id' => $new_id, 'name' => $name, 'message' => 'Category added successfully.']);
            break;

        case 'updateCategory':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $name = trim($_POST['name'] ?? '');
            if (!$id || empty($name)) send_error('Invalid data provided.');

            $stmt = $conn->prepare("UPDATE expense_categories SET name = ? WHERE id = ?");
            $stmt->bind_param("si", $name, $id);
            if (!$stmt->execute()) send_error('Failed to update category: ' . $stmt->error, 500);
            
            send_success(['message' => 'Category updated successfully.']);
            break;

        case 'deleteCategory':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) send_error('Invalid Category ID.');

            $stmt = $conn->prepare("DELETE FROM expense_categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            if (!$stmt->execute()) {
                 if ($conn->errno == 1451) { // Foreign key constraint
                    send_error("Cannot delete category as it's linked to existing expenses.");
                }
                send_error('Failed to delete category: ' . $stmt->error, 500);
            }
            if ($stmt->affected_rows === 0) send_error('Category not found.', 404);
            
            send_success(['message' => 'Category deleted successfully.']);
            break;

        // --- EXPENSE ACTIONS ---
        case 'addExpense':
            $business_unit_id = filter_input(INPUT_POST, 'business_unit_id', FILTER_VALIDATE_INT);
            $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
            $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
            $description = trim($_POST['description'] ?? '');
            $quantity = !empty($_POST['quantity']) ? filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_FLOAT) : null;

            // Automatically set the expense date to the current date
            $expense_date = date('Y-m-d');

            if (!$business_unit_id || !$category_id || !$amount || empty($description)) {
                send_error('Please fill all required fields.');
            }

            $stmt = $conn->prepare("INSERT INTO expenses (business_unit_id, category_id, description, amount, quantity, expense_date) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisdds", $business_unit_id, $category_id, $description, $amount, $quantity, $expense_date);
            
            if (!$stmt->execute()) send_error('Failed to save expense: ' . $stmt->error, 500);
            
            send_success(['message' => 'Expense recorded successfully.']);
            break;

        case 'getExpenses':
            $month = $_GET['month'] ?? date('Y-m'); // e.g., '2025-07'
            $start_date = $month . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));

            $stmt = $conn->prepare("
                SELECT e.id, e.expense_date, bu.name as business_unit, ec.name as category, e.description, e.amount, e.quantity
                FROM expenses e
                JOIN business_units bu ON e.business_unit_id = bu.id
                JOIN expense_categories ec ON e.category_id = ec.id
                WHERE e.expense_date BETWEEN ? AND ?
                ORDER BY e.expense_date DESC, e.created_at DESC
            ");
            $stmt->bind_param("ss", $start_date, $end_date);
            $stmt->execute();
            $result = $stmt->get_result();
            $expenses = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            send_success(['data' => $expenses]);
            break;

        default:
            send_error('Invalid action specified.');
            break;
    }
} catch (Exception $e) {
    error_log("Expense Handler Error: " . $e->getMessage());
    send_error('A server error occurred. Please try again.', 500);
}

$conn->close();
