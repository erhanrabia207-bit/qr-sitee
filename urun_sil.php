<?php
session_start();
include "config/db.php";

if (!isset($_SESSION["login"])) {
    header("Location: /stok_takipsonnnn/index.php");
    exit;
}

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "Geçersiz ürün ID!";
    $_SESSION['message_type'] = "error";
    header("Location: urun_list.php");
    exit;
}

$id = $_GET['id'];

// Ürün bilgilerini getir
$sorgu = $baglanti->prepare("SELECT * FROM urunler WHERE id = ?");
$sorgu->execute([$id]);
$urun = $sorgu->fetch();

if (!$urun) {
    $_SESSION['message'] = "Ürün bulunamadı!";
    $_SESSION['message_type'] = "error";
    header("Location: urun_list.php");
    exit;
}

// Önce bu ürüne ait stok kaydı var mı kontrol et
$stok_kontrol = $baglanti->prepare("SELECT COUNT(*) FROM stoklar WHERE urun_id = ?");
$stok_kontrol->execute([$id]);
$stok_sayisi = $stok_kontrol->fetchColumn();

$message = '';
$message_type = '';

// Silme işlemi (POST ile onaylı silme)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
        
        try {
            // Önce bu ürüne ait stok kayıtlarını sil
            if ($stok_sayisi > 0) {
                $sil_stok = $baglanti->prepare("DELETE FROM stoklar WHERE urun_id = ?");
                $sil_stok->execute([$id]);
            }
            
            // Sonra ürünü sil
            $sil_urun = $baglanti->prepare("DELETE FROM urunler WHERE id = ?");
            $sil_urun->execute([$id]);
            
            $_SESSION['message'] = "Ürün ve bağlı stok kayıtları başarıyla silindi!";
            $_SESSION['message_type'] = "success";
            header("Location: urun_list.php");
            exit;
            
        } catch (PDOException $e) {
            $_SESSION['message'] = "Silme hatası: " . $e->getMessage();
            $_SESSION['message_type'] = "error";
            header("Location: urun_list.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Sil · Stok Takip Sistemi</title>
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
            max-width: 700px;
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

        .delete-card {
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

        .delete-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -30%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 107, 107, 0.08) 0%, transparent 60%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.2); opacity: 0.6; }
        }

        .delete-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .delete-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
        }

        .delete-header h2 {
            color: white;
            font-size: 28px;
            font-weight: 600;
        }

        .delete-header p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            margin-top: 5px;
        }

        .warning-message {
            background: rgba(255, 107, 107, 0.15);
            border: 1px solid #ff6b6b;
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #ff6b6b;
            position: relative;
            z-index: 1;
        }

        .warning-message i {
            font-size: 28px;
        }

        .warning-message p {
            font-size: 15px;
            line-height: 1.5;
        }

        .stok-warning {
            background: rgba(255, 193, 7, 0.15);
            border: 1px solid #ffc107;
            border-radius: 20px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffc107;
            position: relative;
            z-index: 1;
        }

        .stok-warning i {
            font-size: 24px;
        }

        .stok-warning span {
            font-weight: bold;
            font-size: 18px;
        }

        .product-details {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 25px;
            padding: 25px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .detail-row {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            width: 120px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .detail-label i {
            color: #48c78e;
            width: 20px;
        }

        .detail-value {
            flex: 1;
            color: white;
            font-size: 16px;
            font-weight: 500;
        }

        .detail-value.barcode {
            font-family: monospace;
            background: rgba(255, 255, 255, 0.1);
            padding: 5px 12px;
            border-radius: 30px;
            display: inline-block;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 35px;
            position: relative;
            z-index: 1;
        }

        .btn-delete {
            flex: 1;
            padding: 16px;
            background: linear-gradient(135deg, #ff6b6b, #ff4757);
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
            box-shadow: 0 5px 20px rgba(255, 107, 107, 0.3);
        }

        .btn-delete:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.5);
        }

        .btn-cancel {
            flex: 1;
            padding: 16px;
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
            justify-content: center;
            gap: 8px;
        }

        .btn-cancel:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateY(-3px);
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
            
            .delete-card {
                padding: 25px 20px;
            }
            
            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .detail-label {
                width: 100%;
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
                <p>Ürün Sil</p>
            </div>
        </div>
        
        <a href="urun_list.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Listeye Dön
        </a>
    </div>

    <!-- Silme Kartı -->
    <div class="delete-card">
        <!-- Başlık -->
        <div class="delete-header">
            <div class="delete-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h2>Ürün Sil</h2>
                <p>Bu işlem geri alınamaz</p>
            </div>
        </div>

        <!-- Uyarı -->
        <div class="warning-message">
            <i class="fas fa-exclamation-circle"></i>
            <p>Bu ürünü silmek üzeresiniz. Bu işlem kalıcıdır ve geri alınamaz. Lütfen silmek istediğinizden emin olun.</p>
        </div>

        <!-- Stok Uyarısı (varsa) -->
        <?php if ($stok_sayisi > 0): ?>
        <div class="stok-warning">
            <i class="fas fa-boxes"></i>
            <div>
                <strong>⚠️ Dikkat!</strong><br>
                Bu ürüne ait <span><?= $stok_sayisi ?></span> adet stok kaydı bulunuyor.<br>
                Ürün silindiğinde bu stok kayıtları da otomatik olarak silinecektir.
            </div>
        </div>
        <?php endif; ?>

        <!-- Ürün Detayları -->
        <div class="product-details">
            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-hashtag"></i>
                    <span>ID</span>
                </div>
                <div class="detail-value">
                    <span style="background: rgba(72, 199, 142, 0.2); border: 1px solid #48c78e; color: #48c78e; padding: 4px 12px; border-radius: 30px; font-weight: 600;">#<?= $urun['id'] ?></span>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-barcode"></i>
                    <span>Barkod</span>
                </div>
                <div class="detail-value">
                    <span class="detail-value barcode">
                        <i class="fas fa-barcode" style="margin-right: 8px; color: #48c78e;"></i>
                        <?= htmlspecialchars($urun['barkod']) ?>
                    </span>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-box"></i>
                    <span>Ürün Adı</span>
                </div>
                <div class="detail-value">
                    <?= htmlspecialchars($urun['urun_adi']) ?>
                </div>
            </div>
            <?php if (!empty($urun['aciklama'])): ?>
            <div class="detail-row">
                <div class="detail-label">
                    <i class="fas fa-align-left"></i>
                    <span>Açıklama</span>
                </div>
                <div class="detail-value">
                    <?= htmlspecialchars($urun['aciklama']) ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Form -->
        <form method="post" class="form-actions" onsubmit="return confirmDelete(<?= $stok_sayisi ?>)">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit" class="btn-delete" id="deleteBtn">
                <i class="fas fa-trash-alt"></i>
                <?php if ($stok_sayisi > 0): ?>
                    Ürün ve <?= $stok_sayisi ?> Stok Kaydını Sil
                <?php else: ?>
                    Ürünü Kalıcı Olarak Sil
                <?php endif; ?>
            </button>
            <a href="urun_list.php" class="btn-cancel">
                <i class="fas fa-times"></i>
                Vazgeç, Listeye Dön
            </a>
        </form>
    </div>
</div>

<script>
    function confirmDelete(stokSayisi) {
        let message = "Bu işlem geri alınamaz! ";
        
        if (stokSayisi > 0) {
            message += `Bu ürüne ait ${stokSayisi} stok kaydı da silinecek. Devam etmek istediğinize emin misiniz?`;
        } else {
            message += "Devam etmek istediğinize emin misiniz?";
        }
        
        return confirm(message);
    }
</script>

</body>
</html>