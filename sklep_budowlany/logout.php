<?php
session_start();

// Usuwamy wszystkie dane z sesji
session_unset();
session_destroy();

// Przekierowanie na stronę główną
header("Location: index.php");
exit;