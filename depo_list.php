<link rel="stylesheet" href="style.css">
<?php
include "../config/db.php";
$depolar = $baglanti->query("SELECT * FROM depolar")->fetchAll();
?>

<h2>Depo Listesi</h2>

<table border="1">
<tr>
    <th>ID</th>
    <th>Depo Adı</th>
</tr>

<?php foreach ($depolar as $d): ?>
<tr>
    <td><?= $d["id"] ?></td>
    <td><?= $d["depo_adi"] ?></td>
</tr>
<?php endforeach; ?>
</table>

