<?php
session_start();
include "config/db.php";

if (!isset($_SESSION["login"])) {
    header("Location: /stok_takipsonnnn/index.php");
    exit;
}

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: urun_list.php");
    exit;
}

$id = $_GET['id'];

// Ürün bilgilerini getir
$sorgu = $baglanti->prepare("SELECT * FROM urunler WHERE id = ?");
$sorgu->execute([$id]);
$urun = $sorgu->fetch();

if (!$urun) {
    header("Location: urun_list.php");
    exit;
}

$message = '';
$message_type = '';

// Güncelleme işlemi
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $barkod = trim($_POST["barkod"]);
    $urun_adi = trim($_POST["urun_adi"]);
    $aciklama = trim($_POST["aciklama"]);
    
    if (empty($barkod) || empty($urun_adi)) {
        $message = "Barkod ve ürün adı zorunludur!";
        $message_type = "error";
    } else {
        // Barkodun başka üründe olup olmadığını kontrol et (kendi ID'si hariç)
        $kontrol = $baglanti->prepare("SELECT id FROM urunler WHERE barkod = ? AND id != ?");
        $kontrol->execute([$barkod, $id]);
        
        if ($kontrol->fetch()) {
            $message = "Bu barkod zaten başka bir üründe kullanılıyor!";
            $message_type = "error";
        } else {
            $guncelle = $baglanti->prepare("UPDATE urunler SET barkod = ?, urun_adi = ?, aciklama = ? WHERE id = ?");
            $guncelle->execute([$barkod, $urun_adi, $aciklama, $id]);
            
            $message = "Ürün başarıyla güncellendi!";
            $message_type = "success";
            
            // Güncel bilgileri tekrar getir
            $sorgu = $baglanti->prepare("SELECT * FROM urunler WHERE id = ?");
            $sorgu->execute([$id]);
            $urun = $sorgu->fetch();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Düzenle · Stok Takip Sistemi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 60%);
            animation: rotate 20s linear infinite;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(118, 75, 162, 0.1) 0%, transparent 60%);
            animation: rotate 15s linear infinite reverse;
            z-index: 0;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        .header {
            background: rgba(11, 15, 25, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 20px 30px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }

        .logo-text h1 {
            color: white;
            font-size: 24px;
            font-weight: 600;
        }

        .logo-text p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-5px);
        }

        .form-card {
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 40px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -30%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(72, 199, 142, 0.08) 0%, transparent 60%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.2); opacity: 0.6; }
        }

        .form-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .form-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #3e8ed0, #667eea);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            box-shadow: 0 10px 30px rgba(62, 142, 208, 0.3);
        }

        .form-header h2 {
            color: white;
            font-size: 28px;
            font-weight: 600;
        }

        .form-header p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            margin-top: 5px;
        }

        .message {
            padding: 16px 20px;
            border-radius: 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            position: relative;
            z-index: 1;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.success {
            background: rgba(72, 199, 142, 0.15);
            border: 1px solid #48c78e;
            color: #48c78e;
        }

        .message.error {
            background: rgba(255, 107, 107, 0.15);
            border: 1px solid #ff6b6b;
            color: #ff6b6b;
        }

        .message i {
            font-size: 18px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        .form-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-label i {
            color: #48c78e;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 15px 18px;
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.05);
            border-radius: 18px;
            font-size: 15px;
            color: white;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #48c78e;
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 0 4px rgba(72, 199, 142, 0.1);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }

        textarea.form-control {
            min-height: 120px;
            resize: vertical;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 35px;
            position: relative;
            z-index: 1;
        }

        .btn-primary {
            flex: 1;
            padding: 16px;
            background: linear-gradient(135deg, #48c78e, #3e8ed0);
            border: none;
            border-radius: 18px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 5px 20px rgba(72, 199, 142, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(72, 199, 142, 0.5);
        }

        .btn-secondary {
            padding: 16px 30px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateY(-3px);
        }

        .product-info {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 18px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            position: relative;
            z-index: 1;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
        }

        .info-item i {
            color: #48c78e;
        }

        .info-item strong {
            color: white;
            margin-right: 5px;
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-card {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="logo-area">
            <div class="logo-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="logo-text">
                <h1>StokMaster</h1>
                <p>Ürün Düzenle</p>
            </div>
        </div>
        
        <a href="urun_list.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Listeye Dön
        </a>
    </div>

    <!-- Form Kartı -->
    <div class="form-card">
        <!-- Başlık -->
        <div class="form-header">
            <div class="form-icon">
                <i class="fas fa-edit"></i>
            </div>
            <div>
                <h2>Ürün Düzenle</h2>
                <p>Ürün bilgilerini güncelleyin</p>
            </div>
        </div>

        <!-- Mesaj -->
        <?php if ($message != ''): ?>
            <div class="message <?= $message_type ?>">
                <i class="fas <?= $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                <?= $message ?>
            </div>
        <?php endif; ?>

        <!-- Ürün Bilgisi -->
        <div class="product-info">
            <div class="info-item">
                <i class="fas fa-hashtag"></i>
                <strong>ID:</strong> #<?= $urun['id'] ?>
            </div>
            <div class="info-item">
                <i class="fas fa-barcode"></i>
                <strong>Mevcut Barkod:</strong> <?= htmlspecialchars($urun['barkod']) ?>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar-alt"></i>
                <strong>Kayıt Tarihi:</strong> <?= date('d.m.Y H:i', strtotime($urun['created_at'] ?? 'now')) ?>
            </div>
        </div>

        <!-- Form -->
        <form method="post">
            <div class="form-group">
                <div class="form-label">
                    <i class="fas fa-barcode"></i>
                    <span>Barkod *</span>
                </div>
                <input type="text" name="barkod" class="form-control" value="<?= htmlspecialchars($urun['barkod']) ?>" placeholder="Ürün barkod numarası" required>
            </div>

            <div class="form-group">
                <div class="form-label">
                    <i class="fas fa-box"></i>
                    <span>Ürün Adı *</span>
                </div>
                <input type="text" name="urun_adi" class="form-control" value="<?= htmlspecialchars($urun['urun_adi']) ?>" placeholder="Ürün adını girin" required>
            </div>

            <div class="form-group">
                <div class="form-label">
                    <i class="fas fa-align-left"></i>
                    <span>Açıklama (İsteğe bağlı)</span>
                </div>
                <textarea name="aciklama" class="form-control" placeholder="Ürün hakkında açıklama..."><?= htmlspecialchars($urun['aciklama'] ?? '') ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i>
                    Değişiklikleri Kaydet
                </button>
                <a href="urun_list.php" class="btn-secondary">
                    <i class="fas fa-times"></i>
                    İptal
                </a>
            </div>
        </form>
    </div>
</div>


</body>
</html>