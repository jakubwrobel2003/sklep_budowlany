<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$id = $_SESSION['user']['id'];

// Pobierz dane użytkownika
$sql = "SELECT * FROM uzytkownicy WHERE Uzytkownik_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Jeśli użytkownik to firma, pobierz dane firmy
$firma = null;
if ($user['Typ_Klienta'] === 'firma' && $user['Firmy_ID']) {
    $stmtFirma = $conn->prepare("SELECT * FROM firmy WHERE Firmy_ID = ?");
    $stmtFirma->bind_param("i", $user['Firmy_ID']);
    $stmtFirma->execute();
    $firma = $stmtFirma->get_result()->fetch_assoc();
}

// Pobierz historię zamówień
$sql_historia = "SELECT * FROM historia_zakupow WHERE Uzytkownik_ID = ? ORDER BY Data DESC";
$stmt_historia = $conn->prepare($sql_historia);
$stmt_historia->bind_param("i", $id);
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
<?php include 'header.php'; ?>

<main class="container">
    <h1>👤 Profil użytkownika</h1>

    <div class="profile-info">
        <p><strong>Imię:</strong> <?= htmlspecialchars($user['Imie']) ?></p>
        <p><strong>Nazwisko:</strong> <?= htmlspecialchars($user['Nazwisko']) ?></p>
        <p><strong>Login:</strong> <?= htmlspecialchars($user['Login']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['Email']) ?></p>
        <p><strong>Telefon:</strong> <?= htmlspecialchars($user['Telefon']) ?></p>
        <p><strong>Rola:</strong> <?= htmlspecialchars($user['Rola']) ?></p>
        <p><strong>Typ klienta:</strong> <?= htmlspecialchars($user['Typ_Klienta']) ?></p>

        <?php if ($firma): ?>
            <h3>🏢 Dane firmy</h3>
            <p><strong>Nazwa firmy:</strong> <?= htmlspecialchars($firma['Nazwa_Firmy']) ?></p>
            <p><strong>NIP:</strong> <?= htmlspecialchars($firma['NIP']) ?></p>
        <?php endif; ?>
    </div>

    <h2>🧾 Historia zamówień</h2>
    <?php if ($historia && $historia->num_rows > 0): ?>
        <ul>
            <?php while ($zamowienie = $historia->fetch_assoc()): ?>
                <?php
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

<?php include 'footer.php'; ?>
</body>
</html>
