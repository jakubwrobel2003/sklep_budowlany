<?php
require_once 'db.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $login = $_POST['login'];
    $haslo = $_POST['haslo'];
    $rola = 'klient'; // rejestrujemy tylko klientów

    // Sprawdzenie czy login już istnieje
    $stmt = $conn->prepare("SELECT * FROM Uzytkownicy WHERE Login = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Taki login już istnieje.";
    } else {
        // Hashowanie hasła
        $hasloHash = password_hash($haslo, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO Uzytkownicy (Imie, Nazwisko, Login, Haslo, Rola) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $imie, $nazwisko, $login, $hasloHash, $rola);

        if ($stmt->execute()) {
            // ✅ Udana rejestracja – przekieruj do logowania
            header("Location: login.php");
            exit;
        } else {
            $error = "Błąd podczas rejestracji.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><title>Rejestracja</title></head>
<body>
    <h2>Rejestracja klienta</h2>
    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <label>Imię: <input type="text" name="imie" required></label><br>
        <label>Nazwisko: <input type="text" name="nazwisko" required></label><br>
        <label>Login: <input type="text" name="login" required></label><br>
        <label>Hasło: <input type="password" name="haslo" required></label><br>
        <button type="submit">Zarejestruj się</button>
    </form>
</body>
</html>
