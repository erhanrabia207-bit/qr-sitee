<?php
session_start();
include "config/db.php";

// Sadece POST isteğiyle çalışsın
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Sadece POST isteği kabul edilir"]);
    exit;
}

$barkod = trim($_POST['barkod'] ?? '');

if ($barkod == '') {
    echo json_encode(["status" => "error", "message" => "Barkod boş olamaz"]);
    exit;
}

try {
    $sorgu = $baglanti->prepare("SELECT id, urun_adi, aciklama FROM urunler WHERE barkod = ?");
    $sorgu->execute([$barkod]);

    if ($sorgu->rowCount() > 0) {
        $urun = $sorgu->fetch(PDO::FETCH_ASSOC);
        echo json_encode([
            "status" => "var",
            "id" => $urun["id"],
            "urun_adi" => $urun["urun_adi"],
            "aciklama" => $urun["aciklama"]
        ]);
    } else {
        echo json_encode(["status" => "yok"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Veritabanı hatası"]);
}
?>