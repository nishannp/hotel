<?php
// ajax/ajax_handler_staff.php
require_once '../config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => 'Invalid action.'];

// --- Image Crop and Resize Function (copied from other handlers) ---
function cropAndResizeImage($source_path, $destination_path, $size = 300) {
    list($original_width, $original_height, $type) = getimagesize($source_path);
    $image_resource = null;
    switch ($type) {
        case IMAGETYPE_JPEG: $image_resource = imagecreatefromjpeg($source_path); break;
        case IMAGETYPE_PNG: $image_resource = imagecreatefrompng($source_path); break;
        case IMAGETYPE_GIF: $image_resource = imagecreatefromgif($source_path); break;
        default: return false;
    }
    $new_image = imagecreatetruecolor($size, $size);
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }
    $source_x = 0; $source_y = 0;
    $source_width = $original_width; $source_height = $original_height;
    if ($original_width > $original_height) {
        $source_width = $original_height;
        $source_x = ($original_width - $original_height) / 2;
    } elseif ($original_height > $original_width) {
        $source_height = $original_width;
        $source_y = ($original_height - $original_width) / 2;
    }
    imagecopyresampled($new_image, $image_resource, 0, 0, $source_x, $source_y, $size, $size, $source_width, $source_height);
    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG: $success = imagejpeg($new_image, $destination_path, 90); break;
        case IMAGETYPE_PNG: $success = imagepng($new_image, $destination_path, 9); break;
        case IMAGETYPE_GIF: $success = imagegif($new_image, $destination_path); break;
    }
    imagedestroy($image_resource);
    imagedestroy($new_image);
    return $success;
}

switch ($action) {
    case 'fetchAll':
        $sql = "SELECT StaffID, FirstName, LastName, Role, PhoneNumber, HireDate, ImageUrl, IsActive FROM staff ORDER BY FirstName, LastName";
        $result = $conn->query($sql);
        $staff = [];
        while ($row = $result->fetch_assoc()) {
            $row['RelativeImagePath'] = $row['ImageUrl'];
            if (!empty($row['ImageUrl'])) {
                $row['ImageUrl'] = UPLOADS_URL . $row['ImageUrl'];
            }
            $staff[] = $row;
        }
        $response = ['success' => true, 'data' => $staff];
        break;

    case 'fetchSingle':
        $id = $_POST['id'] ?? 0;
        $stmt = $conn->prepare("SELECT * FROM staff WHERE StaffID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staffMember = $result->fetch_assoc();
        if ($staffMember && !empty($staffMember['ImageUrl'])) {
            $staffMember['FullImageUrl'] = UPLOADS_URL . $staffMember['ImageUrl'];
        }
        $response = ['success' => true, 'data' => $staffMember];
        $stmt->close();
        break;

    case 'save':
        $id = $_POST['staff_id'] ?? null;
        $fname = trim($_POST['first_name']);
        $lname = trim($_POST['last_name']);
        $role = $_POST['role'];
        $phone = trim($_POST['phone_number']);
        $hire_date = $_POST['hire_date'];
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $image_path = $_POST['existing_image_path'] ?? '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_dir = UPLOADS_DIR . 'staff/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

            if (!empty($id) && !empty($image_path)) {
                 $old_image_full_path = UPLOADS_DIR . $image_path;
                 if (file_exists($old_image_full_path)) unlink($old_image_full_path);
            }

            $file_name = uniqid() . '-' . basename($_FILES["image"]["name"]);
            $target_file = $upload_dir . $file_name;
            $image_path = 'staff/' . $file_name;

            if (!cropAndResizeImage($_FILES['image']['tmp_name'], $target_file)) {
                 $response['message'] = 'Failed to upload and resize image.';
                 echo json_encode($response);
                 exit;
            }
        }

        if (empty($id)) {
            $sql = "INSERT INTO staff (FirstName, LastName, Role, PhoneNumber, HireDate, ImageUrl, IsActive) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssi", $fname, $lname, $role, $phone, $hire_date, $image_path, $is_active);
            $message = 'Staff member added successfully.';
        } else {
            $sql = "UPDATE staff SET FirstName = ?, LastName = ?, Role = ?, PhoneNumber = ?, HireDate = ?, ImageUrl = ?, IsActive = ? WHERE StaffID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssii", $fname, $lname, $role, $phone, $hire_date, $image_path, $is_active, $id);
            $message = 'Staff member updated successfully.';
        }
        
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => $message];
        } else {
            $response['message'] = 'Error: ' . $stmt->error;
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'];
        
        // First, get the image path to delete the file
        $stmt_img = $conn->prepare("SELECT ImageUrl FROM staff WHERE StaffID = ?");
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        $result = $stmt_img->get_result();
        if ($item = $result->fetch_assoc()) {
            if (!empty($item['ImageUrl'])) {
                $image_full_path = UPLOADS_DIR . $item['ImageUrl'];
                if (file_exists($image_full_path)) unlink($image_full_path);
            }
        }
        $stmt_img->close();

        // Then, delete the database record
        $stmt = $conn->prepare("DELETE FROM staff WHERE StaffID = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $response = ['success' => true, 'message' => 'Staff member deleted successfully.'];
        } else {
            if($conn->errno == 1451) {
                 $response['message'] = 'Cannot delete. This staff member is assigned to existing orders or payments.';
            } else {
                 $response['message'] = 'Error: ' . $stmt->error;
            }
        }
        $stmt->close();
        break;
}

$conn->close();
echo json_encode($response);
