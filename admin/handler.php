<?php
session_start();
require_once __DIR__ . '/../db/config.php';
require_once __DIR__ . '/auth_check.php';

$action = $_REQUEST['action'] ?? '';
$pdo = db();

// Default redirect location
$redirect_to = 'index.php';

switch ($action) {
    case 'add':
        $redirect_to = 'add_product.php'; // Redirect back to form on error
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
            $colors = trim($_POST['colors'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            $errors = [];

            // Validation
            if (empty($name)) $errors[] = "Product name is required.";
            if (empty($description)) $errors[] = "Description is required.";
            if ($price === false) $errors[] = "Price is invalid or missing.";

            $image_path = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../assets/images/products/';

                if (!is_dir($upload_dir)) {
                    if (!mkdir($upload_dir, 0777, true)) {
                        $errors[] = "Image directory does not exist and could not be created.";
                    }
                }

                if (!is_writable($upload_dir)) {
                    $errors[] = "Image directory is not writable. Please check server permissions.";
                } else {
                    $filename = uniqid('product_', true) . '_' . basename($_FILES['image']['name']);
                    $target_file = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_path = 'assets/images/products/' . $filename;
                    } else {
                        $errors[] = "Failed to move uploaded file.";
                    }
                }
            } else {
                $file_error = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
                $upload_errors = [
                    UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the server's maximum upload size (upload_max_filesize).",
                    UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the maximum size specified in the form.",
                    UPLOAD_ERR_PARTIAL => "The file was only partially uploaded.",
                    UPLOAD_ERR_NO_FILE => "No file was selected for upload.",
                    UPLOAD_ERR_NO_TMP_DIR => "Server configuration is missing a temporary folder for uploads.",
                    UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk. Check permissions.",
                    UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload.",
                ];
                if ($file_error !== UPLOAD_ERR_NO_FILE) {
                    $errors[] = $upload_errors[$file_error] ?? "An unknown error occurred during file upload.";
                }
            }

            if (empty($errors)) {
                $sql = "INSERT INTO products (name, description, price, image_url, colors, is_featured) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $price, $image_path, $colors, $is_featured]);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'محصول با موفقیت اضافه شد!'];
                $redirect_to = 'products.php';
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => implode("<br>", $errors)];
            }
        }
        break;

    case 'edit':
        $redirect_to = 'products.php'; // Default redirect on success or if ID is missing
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
            $colors = trim($_POST['colors'] ?? '');
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;

            $errors = [];

            if (!$id) {
                $errors[] = "Invalid product ID.";
            } else {
                $redirect_to = "edit_product.php?id=$id"; // Redirect back to the edit form on error
            }

            if (empty($name)) $errors[] = "Product name is required.";
            if (empty($description)) $errors[] = "Description is required.";
            if ($price === false) $errors[] = "Price is invalid or missing.";


            $current_image_path = $_POST['current_image'] ?? '';
            $image_path = $current_image_path;

            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../assets/images/products/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                if (is_writable($upload_dir)) {
                    $filename = uniqid('product_', true) . '_' . basename($_FILES['image']['name']);
                    $target_file = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_path = 'assets/images/products/' . $filename;
                        // Optionally, delete the old image if it's different
                        if ($current_image_path && file_exists(__DIR__ . '/../' . $current_image_path)) {
                            // unlink(__DIR__ . '/../' . $current_image_path);
                        }
                    } else {
                        $errors[] = "Failed to move uploaded file.";
                    }
                } else {
                    $errors[] = "Image directory is not writable.";
                }
            }

            if (empty($errors)) {
                $sql = "UPDATE products SET name = ?, description = ?, price = ?, image_url = ?, colors = ?, is_featured = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $description, $price, $image_path, $colors, $is_featured, $id]);
                $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'محصول با موفقیت به‌روزرسانی شد!'];
                $redirect_to = 'products.php';
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => implode("<br>", $errors)];
            }
        }
        break;

    case 'delete':
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            // First, get the image path to delete the file
            $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($product && !empty($product['image_url'])) {
                $image_file = __DIR__ . '/../' . $product['image_url'];
                if (file_exists($image_file)) {
                    // unlink($image_file);
                }
            }

            // Then, delete the record from the database
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'محصول با موفقیت حذف شد!'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'شناسه محصول برای حذف نامعتبر است.'];
        }
        $redirect_to = 'products.php';
        break;

    default:
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'عملیات نامعتبر است.'];
        break;
}

header("Location: " . $redirect_to);
exit;
