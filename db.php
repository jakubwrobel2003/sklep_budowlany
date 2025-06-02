<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // domyślnie puste w XAMPP
$dbname = 'sklep_budowlany';

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Błąd połączenia: " . $conn->connect_error);
}
?>
