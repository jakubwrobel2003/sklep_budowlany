<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "Nieprawidłowy identyfikator produktu.";
    exit;
}

$id = (int)$_GET['id'];

$sql = "SELECT p.*, k.Nazwa AS Kategoria 
        FROM produkty p 
        LEFT JOIN kategorie k ON p.Kategoria_ID = k.Kategoria_ID
        WHERE Produkt_ID = $id";

$result = $conn->query($sql);

if ($result->num_rows === 0) {
    echo "Produkt nie istnieje.";
    exit;
}

$produkt = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($produkt['Nazwa']) ?> - Szczegóły produktu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1><?= htmlspecialchars($produkt['Nazwa']) ?></h1>
        <p><strong>Kategoria:</strong> <?= htmlspecialchars($produkt['Kategoria']) ?></p>
        <p><strong>Opis:</strong> <?= nl2br(htmlspecialchars($produkt['Opis'])) ?></p>
        <p><strong>Cena:</strong> <?= number_format($produkt['Cena'], 2) ?> zł</p>

    <form method="post" action="index.php">
        <input type="hidden" name="produkt_id" value="<?= $produkt['Produkt_ID'] ?>">
        <label for="ilosc">Ilość:</label>
        <input type="number" name="ilosc" id="ilosc" value="1" min="1" required>
        <button type="submit">Dodaj do koszyka</button>
    </form>

    </main>
</body>
</html>
