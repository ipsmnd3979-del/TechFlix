<?php
// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error_log.txt');

// Custom error handler
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $error_msg = "Error [$errno]: $errstr in $errfile on line $errline\n";
    error_log($error_msg);
    
    // Don't display errors in production
    if (ini_get('display_errors')) {
        echo "<div style='background: #ff4757; color: white; padding: 10px; margin: 10px; border-radius: 5px;'>";
        echo "<strong>Error:</strong> $errstr<br>";
        echo "<small>File: $errfile (Line: $errline)</small>";
        echo "</div>";
    }
    
    return true;
}

set_error_handler("customErrorHandler");

// Exception handler
function customExceptionHandler($exception) {
    error_log("Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    
    if (ini_get('display_errors')) {
        echo "<div style='background: #ff4757; color: white; padding: 10px; margin: 10px; border-radius: 5px;'>";
        echo "<strong>Exception:</strong> " . $exception->getMessage() . "<br>";
        echo "<small>File: " . $exception->getFile() . " (Line: " . $exception->getLine() . ")</small>";
        echo "</div>";
    }
}

set_exception_handler("customExceptionHandler");
?>