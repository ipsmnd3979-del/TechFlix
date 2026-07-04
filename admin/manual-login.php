<?php
session_start();
$_SESSION['loggedin'] = true;
$_SESSION['role'] = 'admin'; 
$_SESSION['username'] = 'Admin';

echo "<h1>✅ Admin Session Set Manually</h1>";
echo "<p>Logged in: " . ($_SESSION['loggedin'] ? 'true' : 'false') . "</p>";
echo "<p>Role: " . $_SESSION['role'] . "</p>";
echo "<p><a href='dashboard.php'>Now try the dashboard</a></p>";
echo "<p><a href='simple.php'>Or use simple admin</a></p>";
?>