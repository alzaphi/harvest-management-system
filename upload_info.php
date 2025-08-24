<?php
// Show upload settings
echo '<h2>Upload Settings</h2>';
echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . '<br>';
echo 'post_max_size: ' . ini_get('post_max_size') . '<br>';
echo 'memory_limit: ' . ini_get('memory_limit') . '<br>';

// Show PHP info for detailed configuration
phpinfo();
