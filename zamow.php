<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$uzytkownik_id = $_SESSION['user']['id'];
$redirectUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ulica = $_POST['ulica'] ?? '';
    $nr = $_POST['nr_budynku'] ?? '';
    $kod = $_POST['kod_pocztowy'] ?? '';
    $miasto = $_POST['miejscowosc'] ?? '';

    // Walidacja pól
    if (!$ulica || !$nr || !$kod || !$miasto) {
        $error = "Wszystkie pola adresowe są wymagane.";
    }
    // ✅ Walidacja numeru budynku
    elseif (!preg_match('/^[0-9]+[a-zA-Z]?$/', $nr)) {
        $error = "Numer budynku musi zaczynać się od cyfry i może zawierać jedną literę.";
    }
    elseif (empty($_SESSION['koszyk'])) {
        $error = "Koszyk jest pusty.";
    } else {
        // Wstawianie adresu
        $sql_adres = "INSERT INTO adresy (Ulica, Nr_budynku, Kod_pocztowy, Miejscowosc, Uzytkownik_ID)
                      VALUES (?, ?, ?, ?, ?)";
        $stmt_adres = $conn->prepare($sql_adres);
        $stmt_adres->bind_param("ssssi", $ulica, $nr, $kod, $miasto, $uzytkownik_id);
        if ($stmt_adres->execute()) {
            $adres_id = $conn->insert_id;

            // Wstawianie faktury
            $sql_faktura = "INSERT INTO historia_zakupow (Uzytkownik_ID, Adres_ID, Data, Status)
                            VALUES (?, ?, NOW(), 'w trakcie')";
            $stmt_faktura = $conn->prepare($sql_faktura);
            $stmt_faktura->bind_param("ii", $uzytkownik_id, $adres_id);
            if ($stmt_faktura->execute()) {
                $faktura_id = $conn->insert_id;

                // Dodawanie produktów
                $sql_produkt = "INSERT INTO zawartosc_faktury (Faktura_ID, Produkt_ID, Ilosc) VALUES (?, ?, ?)";
                $stmt_produkt = $conn->prepare($sql_produkt);

                foreach ($_SESSION['koszyk'] as $produkt_id => $ilosc) {
                    $stmt_produkt->bind_param("iii", $faktura_id, $produkt_id, $ilosc);
                    $stmt_produkt->execute();
                }

                unset($_SESSION['koszyk']);

                // Ustaw link do przekierowania w JS
                $redirectUrl = "https://twoj-bank.pl/blik/platnosc?faktura=$faktura_id";
            } else {
                $error = "Błąd przy zapisie faktury.";
            }
        } else {
            $error = "Błąd przy zapisie adresu.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Podaj adres dostawy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>

<h2>📦 Adres dostawy</h2>

<?php if (!empty($error)): ?>
    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post" action="zamow.php" id="adresForm">
    <label>Ulica:
        <input type="text" name="ulica" required>
    </label><br>

    <label>Nr budynku:
        <input type="text" name="nr_budynku" required
               pattern="^[0-9]+[a-zA-Z]?$"
               title="Numer budynku musi zaczynać się od cyfry i może zawierać jedną literę"
               placeholder="np. 12 lub 12A">
    </label><br>

    <label>Kod pocztowy:
        <input type="text" name="kod_pocztowy" required 
               pattern="\d{2}-\d{3}" 
               title="Kod pocztowy w formacie 00-000"
               placeholder="np. 34-120">
    </label><br>

    <label>Miejscowość:
        <input type="text" name="miejscowosc" required>
    </label><br>

    <button type="submit">Zamów 🛒</button>
</form>

<?php include 'footer.php'; ?>

<?php if ($redirectUrl): ?>
<script>
    setTimeout(() => {
        window.location.href = <?= json_encode($redirectUrl) ?>;
    }, 1000);
</script>
<?php endif; ?>

</body>
</html>
