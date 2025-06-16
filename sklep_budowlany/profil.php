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

    <main>
        <h1>👤 Profil użytkownika</h1>
        <p><strong>Imię:</strong> <?= htmlspecialchars($user['Imie']) ?></p>
        <p><strong>Nazwisko:</strong> <?= htmlspecialchars($user['Nazwisko']) ?></p>
        <p><strong>Login:</strong> <?= htmlspecialchars($user['Login']) ?></p>
        <p><strong>Rola:</strong> <?= htmlspecialchars($user['Rola']) ?></p>

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
