<?php
require 'db.php';
$zamowienia = $conn->query("
    SELECT hz.Faktura_ID, hz.Ilosc, hz.Status, hz.Data, u.Login
    FROM historia_zakupow hz
    JOIN uzytkownicy u ON hz.Uzytkownik_ID = u.Uzytkownik_ID
    ORDER BY hz.Data DESC
");
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <title>Raport zamówień</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
  <h1>📄 Raport – Lista zamówień</h1>
  <table>
    <tr><th>Faktura</th><th>Login</th><th>Data</th><th>Ilość</th><th>Status</th></tr>
    <?php while($z = $zamowienia->fetch_assoc()): ?>
      <tr>
        <td>#<?= $z['Faktura_ID'] ?></td>
        <td><?= htmlspecialchars($z['Login']) ?></td>
        <td><?= $z['Data'] ?></td>
        <td><?= $z['Ilosc'] ?></td>
        <td><?= $z['Status'] ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</main>
</body>
</html>
