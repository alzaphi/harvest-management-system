<?php
// Set the content type
header('Content-Type: text/plain');

// Set the limits
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '110M');
ini_set('memory_limit', '512M');

// Display current settings
echo "Current Settings:\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

// Simple file upload form
echo "\n\nTest File Upload:\n";
echo '<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="testfile">
    <input type="submit" value="Upload">
</form>';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['testfile'])) {
    if ($_FILES['testfile']['error'] === UPLOAD_ERR_OK) {
        echo "File uploaded successfully! Size: " . 
             round($_FILES['testfile']['size'] / 1024 / 1024, 2) . " MB";
    } else {
        switch ($_FILES['testfile']['error']) {
            case UPLOAD_ERR_INI_SIZE:
                echo "Error: The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                echo "Error: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                echo "Error: The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                echo "Error: No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                echo "Error: Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                echo "Error: Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                echo "Error: A PHP extension stopped the file upload";
                break;
            default:
                echo "Upload error: " . $_FILES['testfile']['error'];
        }
    }
}
