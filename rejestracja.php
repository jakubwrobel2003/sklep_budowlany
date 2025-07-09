<?php
require_once 'db.php';
require_once 'header.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $imie = $_POST['imie'];
    $nazwisko = $_POST['nazwisko'];
    $login = $_POST['login'];
    $haslo = $_POST['haslo'];
    $telefon = $_POST['telefon'];
    $email = $_POST['email'];
    $rola = 'klient';
    $typ_klienta = $_POST['typ_klienta'] ?? 'osoba';
    $nip = ($typ_klienta === 'firma') ? $_POST['nip'] : null;
    $nazwa_firmy = ($typ_klienta === 'firma') ? $_POST['nazwa_firmy'] : null;
    $firmy_id = null;

    if ($typ_klienta === 'firma') {
        if (empty($nip) || empty($nazwa_firmy)) {
            $error = "Dla firmy podaj NIP i nazwę firmy.";
        } else {
            $stmt = $conn->prepare("SELECT Firmy_ID FROM firmy WHERE NIP = ?");
            $stmt->bind_param("s", $nip);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $firmy_id = $result->fetch_assoc()['Firmy_ID'];
            } else {
                $stmt = $conn->prepare("INSERT INTO firmy (Nazwa_Firmy, NIP) VALUES (?, ?)");
                $stmt->bind_param("ss", $nazwa_firmy, $nip);
                if ($stmt->execute()) {
                    $firmy_id = $stmt->insert_id;
                } else {
                    $error = "Błąd przy dodawaniu firmy.";
                }
            }
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("SELECT * FROM Uzytkownicy WHERE Login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Taki login już istnieje.";
        } else {
            $hasloHash = password_hash($haslo, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO Uzytkownicy (Imie, Nazwisko, Login, Haslo, Rola, Email, Telefon, Typ_Klienta, Firmy_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssi", $imie, $nazwisko, $login, $hasloHash, $rola, $email, $telefon, $typ_klienta, $firmy_id);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Błąd podczas rejestracji użytkownika.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja klienta</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<main>
    <div class="container">
        <h2>Rejestracja klienta</h2>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

      <form method="post" action="rejestracja.php" onsubmit="return walidujFormularz();">
    <label>Imię:
        <input type="text" name="imie" required>
    </label>

    <label>Nazwisko:
        <input type="text" name="nazwisko" required>
    </label>

    <label>Login:
        <input type="text" name="login" required>
    </label>

    <label>Hasło:
        <input type="password" name="haslo" id="haslo" required
               pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&]).{8,}"
               title="Min. 8 znaków, 1 wielka i mała litera, 1 cyfra, 1 znak specjalny">
    </label>

    <label>Telefon:
    <input type="tel" name="telefon" id="telefon" required 
           pattern="\d{9}" title="Wpisz 9-cyfrowy numer telefonu, np. 501234567">
    </label>

    <label>Email:
        <input type="email" name="email" required>
    </label>

    <label>Typ klienta:
        <select name="typ_klienta" id="typ_klienta" required onchange="pokazPolaFirmy()">
            <option value="osoba">Osoba indywidualna</option>
            <option value="firma">Firma</option>
        </select>
    </label>

    <div id="firma_fields" class="firma-fields" style="display: none;">
        <label>Nazwa firmy:
            <input type="text" name="nazwa_firmy" id="nazwa_firmy">
        </label>

        <label>NIP:
            <input type="text" name="nip" id="nip" pattern="\d{10}" 
                   title="NIP musi zawierać dokładnie 10 cyfr">
        </label>
    </div>

    <button type="submit">Zarejestruj się</button>
</form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typKlientaSelect = document.getElementById('typ_klienta');
    const firmaFields = document.getElementById('firma_fields');
    const nipInput = document.getElementById('nip');
    const nazwaFirmyInput = document.getElementById('nazwa_firmy');

    function toggleFirmaFields() {
        const isFirma = typKlientaSelect.value === 'firma';
        firmaFields.style.display = isFirma ? 'block' : 'none';
        nipInput.required = isFirma;
        nazwaFirmyInput.required = isFirma;
    }

    typKlientaSelect.addEventListener('change', toggleFirmaFields);
    toggleFirmaFields(); // od razu przy starcie
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
