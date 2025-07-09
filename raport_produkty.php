<?php
require 'db.php';
$produkty = $conn->query("SELECT * FROM produkty ORDER BY Produkt_ID ASC");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Raport produktów</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
  <h1>🧾 Raport – Lista produktów</h1>
  <table>
    <tr><th>ID</th><th>Nazwa</th><th>Opis</th><th>Cena</th><th>Ilość</th></tr>
    <?php while($p = $produkty->fetch_assoc()): ?>
      <tr>
        <td><?= $p['Produkt_ID'] ?></td>
        <td><?= htmlspecialchars($p['Nazwa']) ?></td>
        <td><?= htmlspecialchars($p['Opis']) ?></td>
        <td><?= number_format($p['Cena'],2) ?> zł</td>
        <td><?= $p['Ilosc'] ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</main>
</body>
</html>
