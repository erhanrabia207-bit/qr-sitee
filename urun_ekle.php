<?php
session_start();
include "config/db.php";

// Eğer kullanıcı giriş yapmamışsa login sayfasına yönlendir
if (!isset($_SESSION["login"])) {
    header("Location: index.php");
    exit;
}

// PDO hata modunu açıyoruz
$baglanti->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$mesaj = "";
$mesaj_tipi = "";

if ($_POST) {
    $barkod = trim($_POST["barkod"]);
    $urun_adi = trim($_POST["urun_adi"]);
    $aciklama = trim($_POST["aciklama"]);

    // Barkod kontrol
    $kontrol = $baglanti->prepare("SELECT id FROM urunler WHERE barkod = ?");
    $kontrol->execute([$barkod]);

    if ($kontrol->rowCount() > 0) {
        $mesaj = "❌ Bu barkod zaten kayıtlı!";
        $mesaj_tipi = "error";
    } else {
        // Ürünü ekle
        $ekle = $baglanti->prepare("INSERT INTO urunler (barkod, urun_adi, aciklama) VALUES (?, ?, ?)");
        $ekle->execute([$barkod, $urun_adi, $aciklama]);
        $mesaj = "✅ Ürün başarıyla eklendi!";
        $mesaj_tipi = "success";
        
        // Formu temizlemek için değişkenleri boşalt
        $barkod = $urun_adi = $aciklama = "";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Ekle · Stok Takip Sistemi</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Animasyonlu arkaplan */
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

        /* Ana container */
        .container {
            width: 100%;
            max-width: 550px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }

        /* Header */
        .header {
            background: rgba(11, 15, 25, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 15px 25px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .logo-text h1 {
            color: white;
            font-size: 20px;
            font-weight: 600;
        }

        .logo-text p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 11px;
        }

        .back-btn {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 8px 16px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(-5px);
        }

        /* Ana kart */
        .form-card {
            background: rgba(11, 15, 25, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 40px;
            padding: 35px 30px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
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

        /* Başlık */
        .form-header {
            text-align: center;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        .form-header .icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 32px;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .form-header h2 {
            color: white;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .form-header p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }

        /* Form grupları */
        .form-group {
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }

        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 16px;
            transition: all 0.3s;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 45px;
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

        .form-control:focus + i {
            color: #48c78e;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.2);
            font-size: 14px;
        }

        /* Textarea için özel */
        textarea.form-control {
            padding: 14px 16px 14px 45px;
            resize: vertical;
            min-height: 90px;
        }

        /* Mesaj kutusu */
        .alert {
            padding: 14px 18px;
            border-radius: 18px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease;
            position: relative;
            z-index: 1;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert.error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid #ff6b6b;
            color: #ff6b6b;
        }

        .alert.success {
            background: rgba(72, 199, 142, 0.1);
            border: 1px solid #48c78e;
            color: #48c78e;
        }

        .alert i {
            font-size: 18px;
        }

        /* Buton */
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
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
            gap: 8px;
            position: relative;
            z-index: 1;
            overflow: hidden;
            margin-top: 15px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
            z-index: -1;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        /* İşlem butonları */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }

        .action-btn {
            flex: 1;
            padding: 12px;
            border-radius: 16px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
        }

        .action-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .action-btn.stok-giris {
            background: linear-gradient(135deg, #48c78e20, #3e8ed020);
            border-color: #48c78e;
            color: #48c78e;
        }

        .action-btn.stok-giris:hover {
            background: linear-gradient(135deg, #48c78e40, #3e8ed040);
        }

        .action-btn.liste {
            background: linear-gradient(135deg, #9f7aea20, #f6993f20);
            border-color: #9f7aea;
            color: #9f7aea;
        }

        .action-btn.liste:hover {
            background: linear-gradient(135deg, #9f7aea40, #f6993f40);
        }

        /* Responsive */
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            
            .form-card {
                padding: 25px 20px;
            }
            
            .header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .form-header h2 {
                font-size: 24px;
            }
            
            .action-buttons {
                flex-direction: column;
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
                <p>Ürün Ekle</p>
            </div>
        </div>
        
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Dashboard
        </a>
    </div>

    <!-- Form Kartı -->
    <div class="form-card">
        <!-- Başlık -->
        <div class="form-header">
            <div class="icon">
                <i class="fas fa-plus-circle"></i>
            </div>
            <h2>Yeni Ürün Ekle</h2>
            <p>Sisteme yeni bir ürün ekleyin</p>
        </div>

        <!-- Mesaj -->
        <?php if ($mesaj != ""): ?>
            <div class="alert <?php echo $mesaj_tipi; ?>">
                <i class="fas <?php echo ($mesaj_tipi == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $mesaj; ?>
            </div>
        <?php endif; ?>

        <!-- Form -->
        <form method="post">
            <!-- Barkod -->
            <div class="form-group">
                <label>Barkod Numarası</label>
                <div class="input-wrapper">
                    <i class="fas fa-barcode"></i>
                    <input type="text" name="barkod" class="form-control" placeholder="Örn: 8691234567890" value="<?= isset($barkod) ? htmlspecialchars($barkod) : '' ?>" required>
                </div>
            </div>

            <!-- Ürün Adı -->
            <div class="form-group">
                <label>Ürün Adı</label>
                <div class="input-wrapper">
                    <i class="fas fa-box"></i>
                    <input type="text" name="urun_adi" class="form-control" placeholder="Örn: Laptop, Telefon, Kitap..." value="<?= isset($urun_adi) ? htmlspecialchars($urun_adi) : '' ?>" required>
                </div>
            </div>

            <!-- Açıklama -->
            <div class="form-group">
                <label>Açıklama (Opsiyonel)</label>
                <div class="input-wrapper">
                    <i class="fas fa-align-left" style="top: 25px; transform: none;"></i>
                    <textarea name="aciklama" class="form-control" placeholder="Ürün hakkında detaylı bilgi..."><?= isset($aciklama) ? htmlspecialchars($aciklama) : '' ?></textarea>
                </div>
            </div>

            <!-- Kaydet Butonu -->
            <button type="submit" class="btn">
                <i class="fas fa-save"></i>
                Ürünü Kaydet
            </button>
        </form>

        <!-- İşlem butonları -->
        <div class="action-buttons">
            <a href="stok_giris.php" class="action-btn stok-giris">
                <i class="fas fa-arrow-down"></i>
                Stok Girişi Yap
            </a>
            <a href="urun_list.php" class="action-btn liste">
                <i class="fas fa-list"></i>
                Ürün Listesi
            </a>
        </div>
    </div>
</div>

</body>
</html>