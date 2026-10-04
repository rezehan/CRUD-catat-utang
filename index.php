<?php
session_start();

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: data_utang.php");
} else {
    header("Location: login.php");
}
exit;
