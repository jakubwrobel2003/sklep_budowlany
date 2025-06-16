<?php
session_start();
require 'db.php';

$blad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $haslo = $_POST['haslo'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM uzytkownicy WHERE Login = ?");
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

        // Przekierowanie zależne od roli
        switch ($_SESSION['user']['Rola']) {
            case 'pracownik':
            case 'admin':
                header("Location: panel_pracownika.php");
                break;
            case 'klient':
            default:
                header("Location: index.php");
                break;
        }
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
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header><h1>🔐 Logowanie</h1></header>
    <main>
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
    </main>
</body>
</html>
