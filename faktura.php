<?php
session_start();
require 'db.php';

// Sprawdzenie czy zalogowany
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Sprawdzenie czy przekazano ID faktury
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Brak poprawnego ID faktury.");
}

$faktura_id = intval($_GET['id']);

// Pobranie danych faktury + użytkownika + firmy
$sql = "
    SELECT hz.*, u.Imie, u.Nazwisko, u.Email, u.Telefon, u.Typ_Klienta,
           f.Nazwa_Firmy, f.NIP
    FROM historia_zakupow hz
    JOIN uzytkownicy u ON hz.Uzytkownik_ID = u.Uzytkownik_ID
    LEFT JOIN firmy f ON u.Firmy_ID = f.Firmy_ID
    WHERE hz.Faktura_ID = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $faktura_id);
$stmt->execute();
$dane = $stmt->get_result()->fetch_assoc();

if (!$dane) {
    die("Nie znaleziono faktury.");
}

// Pobranie produktów z faktury
$sql_produkty = "
    SELECT p.Nazwa, p.Cena, z.Ilosc
    FROM zawartosc_faktury z
    JOIN produkty p ON z.Produkt_ID = p.Produkt_ID
    WHERE z.Faktura_ID = ?
";
$stmt_prod = $conn->prepare($sql_produkty);
$stmt_prod->bind_param("i", $faktura_id);
$stmt_prod->execute();
$produkty = $stmt_prod->get_result();

// Oblicz sumę
$suma = 0;
foreach ($produkty as $prod) {
    $suma += $prod['Cena'] * $prod['Ilosc'];
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Faktura #<?= $faktura_id ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container invoice-box">
    <h2>🧾 Faktura #<?= $faktura_id ?></h2>

    <p><strong>Data wystawienia:</strong> <?= $dane['Data'] ?></p>

    <h3>👤 Dane klienta</h3>
    <p><strong>Imię i nazwisko:</strong> <?= htmlspecialchars($dane['Imie']) ?> <?= htmlspecialchars($dane['Nazwisko']) ?></p>
    <p><strong>Email:</strong> <?= htmlspecialchars($dane['Email']) ?></p>
    <p><strong>Telefon:</strong> <?= htmlspecialchars($dane['Telefon']) ?></p>
    <p><strong>Typ klienta:</strong> <?= htmlspecialchars($dane['Typ_Klienta']) ?></p>

    <?php if ($dane['Typ_Klienta'] === 'firma'): ?>
        <p><strong>Nazwa firmy:</strong> <?= htmlspecialchars($dane['Nazwa_Firmy']) ?></p>
        <p><strong>NIP:</strong> <?= htmlspecialchars($dane['NIP']) ?></p>
    <?php endif; ?>

    <h3>📦 Zamówione produkty</h3>
    <table>
        <tr><th>Nazwa</th><th>Ilość</th><th>Cena jedn.</th><th>Wartość</th></tr>
        <?php
        mysqli_data_seek($produkty, 0); // reset wyniku
        while ($p = $produkty->fetch_assoc()):
            $wartosc = $p['Cena'] * $p['Ilosc'];
        ?>
            <tr>
                <td><?= htmlspecialchars($p['Nazwa']) ?></td>
                <td><?= $p['Ilosc'] ?></td>
                <td><?= number_format($p['Cena'], 2) ?> zł</td>
                <td><?= number_format($wartosc, 2) ?> zł</td>
            </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="3" style="text-align:right;"><strong>Razem do zapłaty:</strong></td>
            <td><strong><?= number_format($suma, 2) ?> zł</strong></td>
        </tr>
    </table>

    <a class="back-button" href="javascript:window.print()">🖨️ Drukuj fakturę</a>
</main>
</body>
</html>
