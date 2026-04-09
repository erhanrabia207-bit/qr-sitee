<?php
// chatbot_api.php - HAZIR CEVAPLI ÇALIŞAN VERSİYON
session_start();
include "config/db.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$message = mb_strtolower($data['message'] ?? '', 'UTF-8');

// Veritabanından toplam ürün sayısını al
$sorgu = $baglanti->query("SELECT COUNT(*) as toplam FROM urunler");
$toplam = $sorgu->fetch()['toplam'];

// Son eklenen ürünü bul
$son_urun = $baglanti->query("SELECT urun_adi FROM urunler ORDER BY id DESC LIMIT 1")->fetch();
$son_urun_adi = $son_urun ? $son_urun['urun_adi'] : 'henüz ürün yok';

// Cevaplar
if (strpos($message, 'merhaba') !== false || strpos($message, 'selam') !== false || strpos($message, 'hello') !== false) {
    $cevap = "Merhaba! 👋\nToplam $toplam ürününüz var.\nSize nasıl yardımcı olabilirim?";
}
elseif (strpos($message, 'nasılsın') !== false) {
    $cevap = "Teşekkür ederim, ben bir yapay zeka asistanıyım, her zaman hazırım! Size nasıl yardımcı olabilirim?";
}
elseif (strpos($message, 'ürün ekle') !== false || strpos($message, 'nasıl eklerim') !== false) {
    $cevap = "📦 **Ürün ekleme:**\n\n1. Sağ üstteki 'Yeni Ürün' butonuna tıkla\n2. Barkod, ürün adı ve açıklama gir\n3. Kaydet'e bas\n\nSon eklenen: $son_urun_adi";
}
elseif (strpos($message, 'ürün sil') !== false || strpos($message, 'nasıl silerim') !== false) {
    $cevap = "🗑️ **Ürün silme:**\n\n1. Ürün listesinde 'Sil' butonuna tıkla\n2. Onayla\n\n⚠️ Dikkat: Geri alınamaz!";
}
elseif (strpos($message, 'düzenle') !== false || strpos($message, 'güncelle') !== false) {
    $cevap = "✏️ **Ürün düzenleme:**\n\n1. 'Düzenle' butonuna tıkla\n2. Bilgileri güncelle\n3. Kaydet'e bas";
}
elseif (strpos($message, 'ara') !== false || strpos($message, 'bul') !== false || strpos($message, 'search') !== false) {
    $cevap = "🔍 **Arama yapma:**\n\nÜstteki arama kutusuna ürün adı, barkod veya açıklama yaz.";
}
elseif (strpos($message, 'yazdır') !== false || strpos($message, 'print') !== false) {
    $cevap = "🖨️ **Yazdırma:**\n\nSayfanın altındaki 'Liste Yazdır' butonuna tıkla veya Ctrl+P (Cmd+P) yap.";
}
elseif (strpos($message, 'kaç ürün') !== false || strpos($message, 'toplam') !== false) {
    $cevap = "📊 Toplam **$toplam** ürün var.";
}
elseif (strpos($message, 'teşekkür') !== false || strpos($message, 'sağol') !== false) {
    $cevap = "Rica ederim! Başka bir şey sormak ister misin?";
}
elseif (strpos($message, 'yardım') !== false || strpos($message, 'help') !== false) {
    $cevap = "🤖 **Yardım Menüsü**\n\n• Ürün ekleme\n• Ürün silme\n• Ürün düzenleme\n• Arama yapma\n• Liste yazdırma\n\nNe yapmak istersin?";
}
else {
    $cevap = "Anlamadım. Lütfen şunlardan birini sor:\n\n• Ürün ekleme\n• Ürün silme\n• Ürün düzenleme\n• Arama yapma\n• Liste yazdırma\n\nYardım için 'yardım' yaz.";
}

echo json_encode([
    'choices' => [
        ['message' => ['content' => $cevap]]
    ]
]);
?>