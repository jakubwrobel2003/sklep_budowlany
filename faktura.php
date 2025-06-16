<?php
session_start();
require 'db.php';

if (!isset($_GET['id'])) {
    echo "Brak ID faktury.";
    exit;
}

$faktura_id = intval($_GET['id']);

// Pobierz dane faktury + adres
$sql = "SELECT hz.*, a.Ulica, a.Nr_budynku, a.Miejscowosc, a.Kod_pocztowy, u.NIP
        FROM historia_zakupow hz
        LEFT JOIN adresy a ON hz.Adres_ID = a.Adres_ID
        LEFT JOIN uzytkownicy u ON hz.Uzytkownik_ID = u.Uzytkownik_ID
        WHERE hz.Faktura_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $faktura_id);
$stmt->execute();
$faktura = $stmt->get_result()->fetch_assoc();

if (!$faktura) {
    echo "Nie znaleziono faktury.";
    exit;
}

// Pobierz produkty z faktury
$sql2 = "SELECT zf.Ilosc, p.Nazwa, p.Cena
         FROM zawartosc_faktury zf
         JOIN produkty p ON zf.Produkt_ID = p.Produkt_ID
         WHERE zf.Faktura_ID = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $faktura_id);
$stmt2->execute();
$produkty = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Faktura #<?= $faktura_id ?></title>
        <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>🧾 Faktura #<?= $faktura_id ?></h2>
<p><strong>Data:</strong> <?= htmlspecialchars($faktura['Data']) ?></p>
<p><strong>Status:</strong> <?= htmlspecialchars($faktura['Status']) ?></p>

<?php
$kwota = 0;
foreach ($produkty as $p) {
    $kwota += $p['Ilosc'] * $p['Cena'];
}
$produkty->data_seek(0); // Reset wskaźnika do początku
?>

<p><strong>Kwota:</strong> <?= number_format($kwota, 2) ?> zł</p>

<h3>📍 Adres dostawy</h3>
<p>
    <?= htmlspecialchars($faktura['Ulica'] ?? '') ?> <?= htmlspecialchars($faktura['Nr_budynku'] ?? '') ?><br>
    <?= htmlspecialchars($faktura['Kod_pocztowy'] ?? '') ?> <?= htmlspecialchars($faktura['Miejscowosc'] ?? '') ?>
</p>
<?php if (!empty($faktura['NIP'])): ?>
    <p><strong>NIP:</strong> <?= htmlspecialchars($faktura['NIP']) ?></p>
<?php endif; ?>


<h3>📦 Produkty</h3>
<?php if ($produkty->num_rows > 0): ?>
    <table>
        <tr>
            <th>Produkt</th>
            <th>Ilość</th>
            <th>Cena jednostkowa</th>
            <th>Wartość</th>
        </tr>
        <?php while ($p = $produkty->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($p['Nazwa']) ?></td>
                <td><?= $p['Ilosc'] ?></td>
                <td><?= number_format($p['Cena'], 2) ?> zł</td>
                <td><?= number_format($p['Ilosc'] * $p['Cena'], 2) ?> zł</td>
            </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>Brak produktów w tej fakturze.</p>
<?php endif; ?>

</body>
</html>
