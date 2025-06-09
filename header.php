<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <div class="logo">🏠 Sklep Budowlany</div>
    <nav>
        <a href="index.php">Produkty</a>
        <a href="kontakty.php">Kontakt</a>
        <?php if (isset($_SESSION['user'])): ?>
            <span>👤 Zalogowany jako: 
                <a href="profil.php">
                    <strong><?= htmlspecialchars($_SESSION['user']['Login']) ?></strong>
                </a>
            </span> 
            <a href="logout.php">🚪 Wyloguj</a>
        <?php else: ?>
            <a href="login.php">🔐 Zaloguj</a>
        <?php endif; ?>
        <a href="koszyk.php">🛒 Koszyk (<?= array_sum($_SESSION['koszyk'] ?? []) ?>)</a>
    </nav>
</header>
