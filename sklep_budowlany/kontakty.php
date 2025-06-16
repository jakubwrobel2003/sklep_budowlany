<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Kontakt – Sklep Budowlany</title>
    <link rel="stylesheet" href="style.css">
  
</head>
<body>
<?php include 'header.php'; ?>

<main class="kontakt-container">
    <h1>Skontaktuj się z nami</h1>

    <p><strong>Adres:</strong> ul. Przykładowa 12, 00-000 Warszawa</p>
    <p><strong>Telefon:</strong> +48 123 456 789</p>
    <p><strong>Email:</strong> kontakt@sklepbudowlany.pl</p>

    <h2>Formularz kontaktowy</h2>
    <form method="post" action="kontakty.php">
        <input type="text" name="imie" placeholder="Twoje imię" required>
        <input type="email" name="email" placeholder="Twój e-mail" required>
        <textarea name="wiadomosc" placeholder="Wiadomość..." rows="5" required></textarea>
        <button type="submit">Wyślij</button>
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $imie = htmlspecialchars($_POST['imie']);
        $email = htmlspecialchars($_POST['email']);
        $wiadomosc = htmlspecialchars($_POST['wiadomosc']);

        echo "<p><strong>Dziękujemy za kontakt, $imie!</strong> Odpowiemy na adres $email.</p>";
    }
    ?>
</main>
<?php include 'footer.php'; ?>

</body>
</html>