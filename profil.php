<?php
session_start();
require 'db.php';

// Sprawdzenie logowania
if (!isset($_SESSION['uzytkownik_id'])) {
    header('Location: login.php');
    exit;
}

$uzytkownik_id = $_SESSION['uzytkownik_id'];

// Pobranie danych użytkownika
$sql = "SELECT Imie, Nazwisko, Login, Rola FROM uzytkownicy WHERE Uzytkownik_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uzytkownik_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Pobranie historii zamówień
$sql_historia = "SELECT * FROM historia_zakupow WHERE Uzytkownik_ID = ? ORDER BY Data DESC";
$stmt_historia = $conn->prepare($sql_historia);
$stmt_historia->bind_param("i", $uzytkownik_id);
$stmt_historia->execute();
$historia = $stmt_historia->get_result();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Profil użytkownika</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="logo">🏠 Sklep Budowlany</div>
        <nav>
            <a href="index.php">Produkty</a>
            <a href="kontakt.php">Kontakt</a>
            <a href="logout.php">🚪 Wyloguj</a>
        </nav>
    </header>

    <main>
        <h1>👤 Profil użytkownika</h1>
        <p><strong>Imię:</strong> <?= htmlspecialchars($user['Imie']) ?></p>
        <p><strong>Nazwisko:</strong> <?= htmlspecialchars($user['Nazwisko']) ?></p>
        <p><strong>Login:</strong> <?= htmlspecialchars($user['Login']) ?></p>
        <p><strong>Rola:</strong> <?= htmlspecialchars($user['Rola']) ?></p>

        <h2>🧾 Historia zamówień</h2>
<?php if ($historia->num_rows > 0): ?>
    <ul>
        <?php while ($zamowienie = $historia->fetch_assoc()): ?>
            <?php
            // Oblicz łączną kwotę na podstawie zawartości faktury
            $faktura_id = $zamowienie['Faktura_ID'];
            $kwota_sql = "SELECT SUM(p.Cena * z.Ilosc) AS Kwota 
                          FROM zawartosc_faktury z 
                          JOIN produkty p ON z.Produkt_ID = p.Produkt_ID 
                          WHERE z.Faktura_ID = ?";
            $stmt_kwota = $conn->prepare($kwota_sql);
            $stmt_kwota->bind_param("i", $faktura_id);
            $stmt_kwota->execute();
            $kwota_result = $stmt_kwota->get_result()->fetch_assoc();
            $kwota = $kwota_result['Kwota'] ?? 0;
            ?>
            <li>
                Zamówienie nr <?= htmlspecialchars($faktura_id) ?> z dnia <?= htmlspecialchars($zamowienie['Data']) ?> –
                <?= number_format($kwota, 2) ?> zł
                <a href="faktura.php?id=<?= $faktura_id ?>" target="_blank">📄 Szczegóły</a>
            </li>
        <?php endwhile; ?>
    </ul>
<?php else: ?>
    <p>Brak zamówień.</p>
<?php endif; ?>
    </main>
</body>
</html>
