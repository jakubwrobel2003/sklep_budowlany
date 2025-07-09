<?php
session_start();
require 'db.php';

// 1. Autoryzacja
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['Rola'], ['pracownik','admin'])) {
    header('Location: login.php');
    exit;
}

// 2. Obsługa formularzy --------------------------------------------------

// 2a. Dodawanie produktu
if (isset($_POST['akcja']) && $_POST['akcja'] === 'dodaj_produkt') {
    $nazwa  = $_POST['nazwa'];
    $opis   = trim($_POST['opis']);
    $cena   = floatval($_POST['cena']);
    $ilosc  = intval($_POST['ilosc']);

    // Walidacja ceny i opisu
    if ($cena <= 0) {
        $_SESSION['error'] = "Cena musi być większa od zera.";
        header('Location: panel_pracownika.php');
        exit;
    }
    if ($opis === '') {
        $_SESSION['error'] = "Opis nie może być pusty.";
        header('Location: panel_pracownika.php');
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO produkty (Nazwa, Opis, Cena, Ilosc) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $nazwa, $opis, $cena, $ilosc);
    $stmt->execute();
    header('Location: panel_pracownika.php');
    exit;
}

// 2b. Edycja produktu
if (isset($_POST['akcja']) && $_POST['akcja'] === 'edytuj_produkt') {
    $id     = intval($_POST['produkt_id']);
    $nazwa  = $_POST['nazwa'];
    $opis   = trim($_POST['opis']);
    $cena   = floatval($_POST['cena']);
    $ilosc  = intval($_POST['ilosc']);

    // Walidacja ceny i opisu
    if ($cena <= 0) {
        $_SESSION['error'] = "Cena musi być większa od zera.";
        header('Location: panel_pracownika.php?edit=' . $id);
        exit;
    }
    if ($opis === '') {
        $_SESSION['error'] = "Opis nie może być pusty.";
        header('Location: panel_pracownika.php?edit=' . $id);
        exit;
    }

    $stmt = $conn->prepare("UPDATE produkty SET Nazwa=?, Opis=?, Cena=?, Ilosc=? WHERE Produkt_ID=?");
    $stmt->bind_param("ssdii", $nazwa, $opis, $cena, $ilosc, $id);
    $stmt->execute();
    header('Location: panel_pracownika.php');
    exit;
}

// 2c. Usuwanie produktu
if (isset($_GET['usun_produkt'])) {
    $id = intval($_GET['usun_produkt']);
    $conn->query("DELETE FROM produkty WHERE Produkt_ID = $id");
    header('Location: panel_pracownika.php');
    exit;
}

// 2d. Zmiana statusu zamówienia / ilości
if (isset($_POST['akcja']) && $_POST['akcja'] === 'edytuj_zamowienie') {
    $zamId  = intval($_POST['zamowienie_id']);
    $ilosc  = intval($_POST['ilosc']);
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE historia_zakupow SET Ilosc=?, Status=? WHERE Faktura_ID=?");
    $stmt->bind_param("isi", $ilosc, $status, $zamId);
    $stmt->execute();
    header('Location: panel_pracownika.php');
    exit;
}

// 3. Pobranie danych ------------------------------------------------------

// 3a. Produkty
$produkty = $conn->query("SELECT * FROM produkty ORDER BY Produkt_ID DESC");

// 3b. Zamówienia (historia)
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
    <title>Panel Pracownika</title>
    <link rel="stylesheet" href="style.css">
    <style>
      /* drobne wyróżnienie sekcji */
      .card { padding:1em; margin:1em 0; border:1px solid #ccc; border-radius:6px; }
      .flex { display:flex; gap:1em; align-items:flex-start; }
      .flex > * { flex:1; }
      .error { color: red; margin-bottom: 1em; }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<main class="container">
    <h1>🛠 Panel Pracownika / Admina</h1>

    <?php
    if (isset($_SESSION['error'])) {
        echo '<p class="error">'.htmlspecialchars($_SESSION['error']).'</p>';
        unset($_SESSION['error']);
    }
    ?>

    <!-- Sekcja 1: Dodaj produkt -->
    <div class="card">
      <h2>➕ Dodaj nowy produkt</h2>
      <form method="post" class="flex">
        <input type="hidden" name="akcja" value="dodaj_produkt">
        <input type="text"    name="nazwa"  placeholder="Nazwa"   required>
        <input type="number"  name="cena"   placeholder="Cena"    step="0.01" min="0.01" required>
        <input type="number"  name="ilosc"  placeholder="Ilość"   min="1" required>
        <input type="text"    name="opis"   placeholder="Opis" required>
        <button type="submit">Dodaj</button>
      </form>
    </div>

    <!-- Sekcja 2: Lista i edycja produktów -->
    <div class="card">
      <h2>📦 Produkty w sklepie</h2>
      <table>
        <tr><th>ID</th><th>Nazwa</th><th>Cena</th><th>Ilość</th><th>Akcje</th></tr>
        <?php while($p = $produkty->fetch_assoc()): ?>
          <tr>
            <td><?= $p['Produkt_ID'] ?></td>
            <td><?= htmlspecialchars($p['Nazwa']) ?></td>
            <td><?= number_format($p['Cena'],2) ?> zł</td>
            <td><?= $p['Ilosc'] ?></td>
            <td>
              <a href="panel_pracownika.php?edit=<?= $p['Produkt_ID'] ?>">✏️</a>
              <a href="panel_pracownika.php?usun_produkt=<?= $p['Produkt_ID'] ?>" 
                 onclick="return confirm('Usunąć produkt?')">🗑️</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>

      <?php if (isset($_GET['edit'])):
        $id = intval($_GET['edit']);
        $res = $conn->query("SELECT * FROM produkty WHERE Produkt_ID = $id");
        $prod = $res->fetch_assoc();
      ?>
        <h3>✏️ Edytuj produkt #<?= $id ?></h3>
        <form method="post" class="flex">
          <input type="hidden" name="akcja" value="edytuj_produkt">
          <input type="hidden" name="produkt_id" value="<?= $id ?>">
          <input type="text"    name="nazwa"  value="<?= htmlspecialchars($prod['Nazwa']) ?>" required>
          <input type="number"  name="cena"   value="<?= $prod['Cena'] ?>" step="0.01" min="0.01" required>
          <input type="number"  name="ilosc"  value="<?= $prod['Ilosc'] ?>" min="1" required>
          <input type="text"    name="opis"   value="<?= htmlspecialchars($prod['Opis']) ?>" required>
          <button type="submit">Zapisz zmiany</button>
        </form>
      <?php endif; ?>
    </div>

    <!-- Sekcja 3: Lista zamówień i ich edycja -->
    <div class="card">
      <h2>📝 Zamówienia</h2>
      <?php while($z = $zamowienia->fetch_assoc()): ?>
        <form method="post" class="card">
          <input type="hidden" name="akcja" value="edytuj_zamowienie">
          <input type="hidden" name="zamowienie_id" value="<?= $z['Faktura_ID'] ?>">
          <p>
            <strong>#<?= $z['Faktura_ID'] ?></strong> 
            klient: <?= htmlspecialchars($z['Login']) ?> —
            data: <?= $z['Data'] ?>
          </p>
          <label>Ilość: 
            <input type="number" name="ilosc" value="<?= $z['Ilosc'] ?>" min="1">
          </label>
          <label>Status: 
            <select name="status">
              <?php foreach(['Nowe','W realizacji','Wysłane','Zrealizowane','Anulowane'] as $s): ?>
                <option value="<?= $s ?>" <?= $s === $z['Status']?'selected':'' ?>>
                  <?= $s ?>
                </option>
              <?php endforeach; ?>
            </select>
          </label>
          <button type="submit">Zapisz</button>
          <a href="faktura.php?id=<?= $z['Faktura_ID'] ?>" target="_blank">Podgląd faktury</a>
        </form>
      <?php endwhile; ?>
    </div>
    <div class="card">
      <h2>📊 Raporty</h2>
      <a href="raport_produkty.php" target="_blank" class="back-button">🧾 Lista produktów (PDF/druk)</a>
      <a href="raport_zamowienia.php" target="_blank" class="back-button">📄 Lista zamówień (PDF/druk)</a>
    </div>           
</main>

<?php include 'footer.php'; ?>
</body>
</html>
