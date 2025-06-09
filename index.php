<?php
session_start();
require_once 'db.php';

// Obsługa dodania do koszyka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produkt_id'])) {
    $id = (int)$_POST['produkt_id'];
    $ilosc = isset($_POST['ilosc']) ? max(1, (int)$_POST['ilosc']) : 1;

    if (!isset($_SESSION['koszyk'][$id])) {
        $_SESSION['koszyk'][$id] = $ilosc;
    } else {
        $_SESSION['koszyk'][$id] += $ilosc;
    }

    header("Location: index.php");
    exit;
}


$sql = "SELECT p.*, k.Nazwa AS Kategoria 
        FROM produkty p 
        LEFT JOIN kategorie k ON p.Kategoria_ID = k.Kategoria_ID";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Sklep budowlany</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php include 'header.php'; ?>


    <main>
        <h1>Nasze produkty</h1>
        <div class="produkty">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="produkt">
                    <img src="https://via.placeholder.com/200x250.png?text=Produkt" alt="<?= htmlspecialchars($row['Nazwa']) ?>">
                    <h2><?= htmlspecialchars($row['Nazwa']) ?></h2>
                    <p><strong>Firma:</strong> <?= htmlspecialchars($row['Producent']) ?></p>
                    <p><strong>Kategoria:</strong> <?= htmlspecialchars($row['Kategoria'] ?? 'Brak kategorii') ?></p>
                    <p><?= htmlspecialchars($row['Opis']) ?></p>
                    <p class="cena"><?= number_format($row['Cena'], 2) ?> zł</p>
                    <form method="post">
                        <input type="hidden" name="produkt_id" value="<?= $row['Produkt_ID'] ?>">
                        <button type="submit">Dodaj do koszyka</button>
                    </form>
                    <form action="produkt.php" method="get" style="margin-top: 10px;">
                        <input type="hidden" name="id" value="<?= $row['Produkt_ID'] ?>">
                        <button type="submit">Zobacz produkt</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <footer>
        <p>📞 655-777-543 &nbsp;&nbsp; 📧 sklepbudowlany@gmail.com &nbsp;&nbsp; 📍 Klimowo, ul. Diametralna 45</p>
    </footer>
</body>
</html>
