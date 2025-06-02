<?php
session_start();
require_once 'db.php';

// Obsługa dodania do koszyka
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['produkt_id'])) {
    $id = $_POST['produkt_id'];
    if (!isset($_SESSION['koszyk'][$id])) {
        $_SESSION['koszyk'][$id] = 1;
    } else {
        $_SESSION['koszyk'][$id]++;
    }
    header("Location: index.php");
    exit;
}

$sql = "SELECT * FROM Produkty";
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
    <header>
        <div class="logo">🏠 Sklep Budowlany</div>
        <nav>
            <a href="#">Produkty</a>
            <a href="#">Kontakt</a>
            <a href="koszyk.php">🛒 Koszyk (<?= array_sum($_SESSION['koszyk'] ?? []) ?>)</a>
        </nav>
    </header>

    <main>
        <h1>Nasze produkty</h1>
        <div class="produkty">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="produkt">
                    <img src="https://via.placeholder.com/200x250.png?text=Produkt" alt="<?= htmlspecialchars($row['Nazwa']) ?>">
                    <h2><?= htmlspecialchars($row['Nazwa']) ?></h2>
                    <p><strong>Firma:</strong> <?= htmlspecialchars($row['Producent']) ?></p>
                    <p><?= htmlspecialchars($row['Opis']) ?></p>
                    <p class="cena"><?= number_format($row['Cena'], 2) ?> zł</p>
                    <form method="post">
                        <input type="hidden" name="produkt_id" value="<?= $row['Produkt_ID'] ?>">
                        <button type="submit">Dodaj do koszyka</button>
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
