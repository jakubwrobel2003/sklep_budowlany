<?php
session_start();
require_once 'db.php';

$blad = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $haslo = $_POST['haslo'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM uzytkownicy WHERE Login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($haslo, $user['Haslo'])) {
      $_SESSION['uzytkownik_id'] = $user['Uzytkownik_ID'];
 // <- to było brakujące!
        header("Location: index.php");
        exit;
    } else {
        $blad = "Nieprawidłowy login lub hasło.";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
</head>
<body>
    <h1>Logowanie</h1>
    <form method="post">
    <label>Login:</label>
    <input type="text" name="login" required><br>
    <label>Hasło:</label>
    <input type="password" name="haslo" required><br>
    <button type="submit">Zaloguj</button>
</form>

<p>Nie masz jeszcze konta? <a href="rejestracja.php">Zarejestruj się</a></p>

    <?php if ($blad): ?>
        <p style="color:red"><?= htmlspecialchars($blad) ?></p>
    <?php endif; ?>
</body>
</html>
