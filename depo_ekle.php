<link rel="stylesheet" href="style.css">
<?php
session_start();
include "../config/db.php";

if ($_POST) {
    $depo_adi = $_POST["depo_adi"];

    $ekle = $baglanti->prepare(
        "INSERT INTO depolar (depo_adi) VALUES (?)"
    );
    $ekle->execute([$depo_adi]);

    echo "Depo eklendi ✅";
}
?>

<h2>Depo Ekle</h2>

<form method="post">
    <input type="text" name="depo_adi" placeholder="Depo Adı" required>
    <button>Ekle</button>
</form>
