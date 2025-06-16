<?php
require_once 'db.php';
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $login = $_POST['login'];
    $haslo = $_POST['haslo'];
    $rola = 'klient'; // rejestrujemy tylko klientów
    $typ_klienta = $_POST['typ_klienta'] ?? 'osoba';
    $nip = ($typ_klienta === 'firma') ? $_POST['nip'] : null;

    if ($typ_klienta === 'firma' && empty($nip)) {
        $error = "Podaj NIP dla firmy.";
    } else {
        // Sprawdzenie czy login już istnieje
        $stmt = $conn->prepare("SELECT * FROM Uzytkownicy WHERE Login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Taki login już istnieje.";
        } else {
            // Hashowanie hasła
            $hasloHash = password_hash($haslo, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Uzytkownicy (Imie, Nazwisko, Login, Haslo, Rola, Typ_Klienta, NIP) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $imie, $nazwisko, $login, $hasloHash, $rola, $typ_klienta, $nip);

            if ($stmt->execute()) {
                // Udana rejestracja – przekieruj do logowania
                header("Location: login.php");
                exit;
            } else {
                $error = "Błąd podczas rejestracji.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Rejestracja klienta</h2>
    <?php if ($error) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post" id="rejestracjaForm">
        <label>Imię: <input type="text" name="imie" required></label><br>
        <label>Nazwisko: <input type="text" name="nazwisko" required></label><br>
        <label>Login: <input type="text" name="login" required></label><br>
        <label>Hasło: <input type="password" name="haslo" required></label><br>

        <label>Typ klienta:
            <select name="typ_klienta" id="typ_klienta" required>
                <option value="osoba">Osoba indywidualna</option>
                <option value="firma">Firma</option>
            </select>
        </label><br>

        <div id="nipDiv" style="display:none;">
            <label>NIP: <input type="text" name="nip" id="nip"></label><br>
        </div>

        <button type="submit">Zarejestruj się</button>
    </form>

    <script>
    document.getElementById('typ_klienta').addEventListener('change', function() {
        if(this.value === 'firma') {
            document.getElementById('nipDiv').style.display = 'block';
            document.getElementById('nip').setAttribute('required', 'required');
        } else {
            document.getElementById('nipDiv').style.display = 'none';
            document.getElementById('nip').removeAttribute('required');
        }
    });
    </script>
</body>
<?php include 'footer.php'; ?>
</html>
