<?php
require_once 'session_config.php';
session_start();
session_destroy();
header('Location: index.php');
exit();
?>
