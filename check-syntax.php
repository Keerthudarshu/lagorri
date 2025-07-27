<?php
// Simple test for auth.php syntax
$content = file_get_contents('../api/auth.php');

// Check for basic PHP syntax issues
$tempFile = tempnam(sys_get_temp_dir(), 'auth_check');
file_put_contents($tempFile, $content);

// Test for basic syntax
$output = [];
$returnCode = 0;
exec("php -l $tempFile 2>&1", $output, $returnCode);

header('Content-Type: application/json');

if ($returnCode === 0) {
    echo json_encode([
        'success' => true,
        'message' => 'PHP syntax is valid',
        'file_size' => strlen($content),
        'lines' => substr_count($content, "\n") + 1
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'PHP syntax error',
        'output' => $output,
        'file_size' => strlen($content),
        'lines' => substr_count($content, "\n") + 1
    ]);
}

unlink($tempFile);
?>
