<?php
session_start();
include "config/db.php";
if (!isset($_SESSION["login"])) {
    header("Location: /stok_takipsonnnn/index.php");
    exit;
}

if ($_POST) {
    $barkod = $_POST["barkod"];
    $urun_adi = $_POST["urun_adi"];
    $aciklama = $_POST["aciklama"];

    $ekle = $baglanti->prepare(
        "INSERT INTO urunler (barkod, urun_adi, aciklama)
         VALUES (?, ?, ?)"
    );
    $ekle->execute([$barkod, $urun_adi, $aciklama]);

    $mesaj = "Ürün başarıyla eklendi ✅";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Barkodlu Ürün Ekle</title>

    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }

        form {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            width: 350px;
            text-align: center;
        }

        h2 {
            margin-bottom: 25px;
            color: #ff6347;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            box-shadow: inset 0 2px 5px rgba(0,0,0,0.1);
            transition: border 0.2s, box-shadow 0.2s;
        }

        textarea {
            resize: vertical;
            min-height: 80px;
        }

        input:focus, textarea:focus {
            border-color: #ff6347;
            box-shadow: 0 0 8px rgba(255,99,71,0.3);
            outline: none;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            border: none;
            border-radius: 8px;
            background-color: #ff7f50;
            color: #fff;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            background-color: #ff6347;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .mesaj {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 8px;
            font-weight: 500;
            background-color: #d1e7dd;
            color: #0f5132;
        }

        a {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #fff;
            background-color: #32cd32;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background 0.2s;
        }

        a:hover {
            background-color: #228b22;
        }
    </style>
</head>
<body>

<form method="post">
    <h2>Barkodlu Ürün Ekle</h2>

    <?php if (isset($mesaj)) { echo '<div class="mesaj">'.$mesaj.'</div>'; } ?>

    <input type="text" name="barkod" placeholder="Barkod" required>
    <input type="text" name="urun_adi" placeholder="Ürün Adı" required>
    <textarea name="aciklama" placeholder="Açıklama"></textarea>
    <button type="submit">Kaydet</button>

    <a href="urun_list.php">Ürünleri Listele</a>
</form>

</body>
</html>
