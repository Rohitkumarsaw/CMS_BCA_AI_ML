<?php
require_once 'includes/functions.php';
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
} else {
    header("Location: login.php");
}
exit();
