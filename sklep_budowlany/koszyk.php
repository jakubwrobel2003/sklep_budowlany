<?php
session_start();
require_once 'db.php';

$koszyk = $_SESSION['koszyk'] ?? [];

$produkty = [];

if (!empty($koszyk)) {
    $ids = implode(',', array_keys($koszyk));
    $sql = "SELECT * FROM Produkty WHERE Produkt_ID IN ($ids)";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $produkty[$row['Produkt_ID']] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Koszyk</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Twój koszyk</h1>
        <?php if (empty($koszyk)): ?>
            <p>Koszyk jest pusty.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Produkt</th>
                    <th>Ilość</th>
                    <th>Cena</th>
                    <th>Suma</th>
                </tr>
                <?php
                $suma = 0;
                foreach ($koszyk as $id => $ilosc):
                    $produkt = $produkty[$id];
                    $cena = $produkt['Cena'];
                    $suma += $cena * $ilosc;
                ?>
                    <tr>
                        <td><?= htmlspecialchars($produkt['Nazwa']) ?></td>
                        <td><?= $ilosc ?></td>
                        <td><?= number_format($cena, 2) ?> zł</td>
                        <td><?= number_format($cena * $ilosc, 2) ?> zł</td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <p><strong>Razem: <?= number_format($suma, 2) ?> zł</strong></p>

            <form method="post">
                <button name="clear">🗑️ Wyczyść koszyk</button>
            </form>
        <?php endif; ?>

        <?php
        if (isset($_POST['clear'])) {
            unset($_SESSION['koszyk']);
            header("Location: koszyk.php");
            exit;
        }
        ?>
        <?php if (!empty($_SESSION['koszyk'])): ?>
    <a href="zamow.php"><button>Zamów 🛒</button></a>
<?php endif; ?>

    </main>
    <?php include 'footer.php'; ?>

</body>
</html>
