<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$uzytkownik_id = $_SESSION['user']['id'];

// Jeśli formularz został wysłany (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Odbierz dane z formularza
    $ulica = $_POST['ulica'] ?? '';
    $nr = $_POST['nr_budynku'] ?? '';
    $kod = $_POST['kod_pocztowy'] ?? '';
    $miasto = $_POST['miejscowosc'] ?? '';

    if (!$ulica || !$nr || !$kod || !$miasto) {
        echo "Wszystkie pola adresowe są wymagane.";
        exit;
    }

    if (empty($_SESSION['koszyk'])) {
        echo "Koszyk jest pusty.";
        exit;
    }

    // Zapisz adres do bazy
    $sql_adres = "INSERT INTO adresy (Ulica, Nr_budynku, Kod_pocztowy, Miejscowosc, Uzytkownik_ID)
                  VALUES (?, ?, ?, ?, ?)";
    $stmt_adres = $conn->prepare($sql_adres);
    $stmt_adres->bind_param("ssssi", $ulica, $nr, $kod, $miasto, $uzytkownik_id);
    $stmt_adres->execute();
    $adres_id = $conn->insert_id;

    // Dodaj fakturę
    $sql_faktura = "INSERT INTO historia_zakupow (Uzytkownik_ID, Adres_ID, Data, Status)
                    VALUES (?, ?, NOW(), 'w trakcie')";
    $stmt_faktura = $conn->prepare($sql_faktura);
    $stmt_faktura->bind_param("ii", $uzytkownik_id, $adres_id);
    $stmt_faktura->execute();
    $faktura_id = $conn->insert_id;

    // Dodaj produkty z koszyka
    $sql_produkt = "INSERT INTO zawartosc_faktury (Faktura_ID, Produkt_ID, Ilosc) VALUES (?, ?, ?)";
    $stmt_produkt = $conn->prepare($sql_produkt);

    foreach ($_SESSION['koszyk'] as $produkt_id => $ilosc) {
        $stmt_produkt->bind_param("iii", $faktura_id, $produkt_id, $ilosc);
        $stmt_produkt->execute();
    }

    unset($_SESSION['koszyk']);
    header("Location: profil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
        <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Podaj adres dostawy</title>
   
</head>
<body>
<?php include 'header.php'; ?>
<h2>📦 Adres dostawy</h2>
<form method="post" action="zamow.php">
    <label>Ulica:
        <input type="text" name="ulica" required>
    </label>
    <label>Nr budynku:
        <input type="text" name="nr_budynku" required>
    </label>
    <label>Kod pocztowy:
        <input type="text" name="kod_pocztowy" required>
    </label>
    <label>Miejscowość:
        <input type="text" name="miejscowosc" required>
    </label>
    <button type="submit">Zamów 🛒</button>
</form>
<?php include 'footer.php'; ?>

</body>
</html>
