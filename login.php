<?php
session_start();
require 'db.php';

$blad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $haslo = $_POST['haslo'] ?? '';

    $sql = "SELECT * FROM uzytkownicy WHERE Login = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $wynik = $stmt->get_result();
    $uzytkownik = $wynik->fetch_assoc();

    if ($uzytkownik && password_verify($haslo, $uzytkownik['Haslo'])) {
        $_SESSION['user'] = [
            'id' => $uzytkownik['Uzytkownik_ID'],
            'Login' => $uzytkownik['Login'],
            'Rola' => $uzytkownik['Rola']
        ];
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
    <h1>🔐 Logowanie</h1>
    <?php if ($blad): ?>
        <p style="color:red"><?= htmlspecialchars($blad) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Login: <input type="text" name="login" required></label><br>
        <label>Hasło: <input type="password" name="haslo" required></label><br>
        <button type="submit">Zaloguj</button>
    </form>
    <p><a href="rejestracja.php">Nie masz konta? Zarejestruj się</a></p>
    <?php include 'footer.php'; ?>

</body>
</html>
