<?php
// Cek apakah ekstensi GD terload
if (extension_loaded('gd') && function_exists('gd_info')) {
    echo "GD library is installed on your web server";
    $gd_info = gd_info();
    echo "<pre>";
    print_r($gd_info);
    echo "</pre>";
} else {
    echo "GD library is NOT installed on your web server";
}
?>
