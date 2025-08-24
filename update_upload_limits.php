<?php
// Increase upload limits for this script
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '110M');
ini_set('memory_limit', '256M');

// Show the new settings
echo '<h2>Updated Upload Settings</h2>';
echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . '<br>';
echo 'post_max_size: ' . ini_get('post_max_size') . '<br>';
echo 'memory_limit: ' . ini_get('memory_limit') . '<br>';

// Show original php.ini location
echo '<h3>PHP Configuration File</h3>';
echo 'Loaded Configuration File: ' . php_ini_loaded_file() . '<br>';

// Show additional PHP info for debugging
phpinfo();
