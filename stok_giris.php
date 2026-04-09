<?php
session_start();
include "config/db.php";

// Eğer kullanıcı giriş yapmamışsa login sayfasına yönlendir
if (!isset($_SESSION["login"])) {
    header("Location: index.php");
    exit;
}

$urunler = $baglanti->query("SELECT * FROM urunler")->fetchAll();
$depolar = $baglanti->query("SELECT * FROM depolar")->fetchAll();

$mesaj = "";
$mesaj_tipi = "";

if ($_POST) {
    $urun_id = $_POST["urun_id"];
    $depo_id = $_POST["depo_id"];
    $miktar  = $_POST["miktar"];

    // stok var mı?
    $kontrol = $baglanti->prepare(
        "SELECT * FROM stoklar WHERE urun_id=? AND depo_id=?"
    );
    $kontrol->execute([$urun_id, $depo_id]);

    if ($kontrol->rowCount()) {
        $baglanti->prepare(
            "UPDATE stoklar SET miktar = miktar + ? WHERE urun_id=? AND depo_id=?"
        )->execute([$miktar, $urun_id, $depo_id]);
    } else {
        $baglanti->prepare(
            "INSERT INTO stoklar (urun_id, depo_id, miktar) VALUES (?, ?, ?)"
        )->execute([$urun_id, $depo_id, $miktar]);
    }

    // log
    $baglanti->prepare(
        "INSERT INTO stok_hareketleri (urun_id, depo_id, islem_turu, miktar) VALUES (?, ?, 'Stok Giriş', ?)"
    )->execute([$urun_id, $depo_id, $miktar]);

    // Başarılı mesaj
    $mesaj = "✅ Stok girişi başarıyla yapıldı!";
    $mesaj_tipi = "success";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Giriş · Stok Takip Sistemi</title>
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
            max-width: 500px;
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
            padding: 40px 35px;
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
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .form-header .icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #48c78e, #3e8ed0);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 32px;
            color: white;
            box-shadow: 0 10px 30px rgba(72, 199, 142, 0.3);
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
            appearance: none;
            -webkit-appearance: none;
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

        /* Select için özel stil */
        select.form-control {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
            padding-right: 45px;
        }

        select.form-control option {
            background: #1a202c;
            color: white;
            padding: 10px;
        }

        /* Sayı input için okları kaldır */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }

        /* Mesaj kutusu */
        .alert {
            padding: 14px 18px;
            border-radius: 18px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
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

        .alert.success {
            background: rgba(72, 199, 142, 0.1);
            border: 1px solid #48c78e;
            color: #48c78e;
        }

        .alert.error {
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid #ff6b6b;
            color: #ff6b6b;
        }

        .alert i {
            font-size: 18px;
        }

        /* Buton */
        .btn {
            width: 100%;
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
            box-shadow: 0 15px 35px rgba(72, 199, 142, 0.4);
        }

        /* Transfer butonu - DÜZELTİLDİ */
        .transfer-btn {
            display: block;
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #f6993f, #f66d9b);
            border: none;
            border-radius: 18px;
            color: white;
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            border: 2px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 30px rgba(246, 153, 63, 0.3);
            animation: pulseButton 2s infinite;
            margin-top: 25px;
            cursor: pointer;
        }

        @keyframes pulseButton {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        .transfer-btn:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 20px 40px rgba(246, 153, 63, 0.5);
            border-color: rgba(255, 255, 255, 0.4);
            background: linear-gradient(135deg, #f66d9b, #f6993f);
        }

        .transfer-btn i {
            font-size: 22px;
            margin-right: 8px;
        }

        /* İşlem butonları */
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
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

        .action-btn.urun-ekle {
            background: linear-gradient(135deg, #48c78e20, #3e8ed020);
            border-color: #48c78e;
            color: #48c78e;
        }

        .action-btn.urun-ekle:hover {
            background: linear-gradient(135deg, #48c78e40, #3e8ed040);
        }

        .action-btn.transfer {
            background: linear-gradient(135deg, #f6993f20, #f66d9b20);
            border-color: #f6993f;
            color: #f6993f;
        }

        .action-btn.transfer:hover {
            background: linear-gradient(135deg, #f6993f40, #f66d9b40);
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
                <p>Stok Giriş Paneli</p>
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
                <i class="fas fa-arrow-down"></i>
            </div>
            <h2>Stok Girişi</h2>
            <p>Depoya yeni ürün girişi yapın</p>
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
            <!-- Ürün Seç -->
            <div class="form-group">
                <label>Ürün Seçin</label>
                <div class="input-wrapper">
                    <i class="fas fa-box"></i>
                    <select name="urun_id" class="form-control" required>
                        <option value="" disabled selected>Ürün seçin</option>
                        <?php foreach ($urunler as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['urun_adi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Depo Seç -->
            <div class="form-group">
                <label>Depo Seçin</label>
                <div class="input-wrapper">
                    <i class="fas fa-warehouse"></i>
                    <select name="depo_id" class="form-control" required>
                        <option value="" disabled selected>Depo seçin</option>
                        <?php foreach ($depolar as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['depo_adi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Miktar -->
            <div class="form-group">
                <label>Miktar</label>
                <div class="input-wrapper">
                    <i class="fas fa-cubes"></i>
                    <input type="number" name="miktar" class="form-control" placeholder="Miktar girin" min="1" step="1" required>
                </div>
            </div>

            <!-- Buton -->
            <button type="submit" class="btn">
                <i class="fas fa-save"></i>
                Stok Girişi Yap
            </button>
        </form>

        <!-- ALT KISIM - TÜM BUTONLAR ÇALIŞIYOR -->
        <div style="margin-top: 25px;">
            <?php if ($mesaj != "" && $mesaj_tipi == "success"): ?>
                <!-- Başarılı işlem sonrası BÜYÜK TRANSFER BUTONU -->
                <a href="stok_transfer.php" class="transfer-btn" onclick="console.log('Transfer butonuna tıklandı');">
                    <i class="fas fa-exchange-alt"></i>
                    Stok Transfer Sayfasına Git →
                </a>
            <?php endif; ?>

            <!-- NORMAL BUTONLAR (HER ZAMAN GÖRÜNÜR) -->
            <div class="action-buttons" style="margin-top: 15px;">
                <a href="urun_ekle.php" class="action-btn urun-ekle">
                    <i class="fas fa-plus-circle"></i>
                    Yeni Ürün Ekle
                </a>
                <a href="stok_transfer.php" class="action-btn transfer">
                    <i class="fas fa-exchange-alt"></i>
                    Stok Transfer
                </a>
            </div>

            <!-- TEST LİNKİ (GEÇİCİ) -->
            <div style="margin-top: 10px; text-align: center; font-size: 12px; color: rgba(255,255,255,0.3);">
                <a href="stok_transfer.php" style="color: #48c78e; text-decoration: none;">➡️ Doğrudan transfer linki</a>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript ile kontrol -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tüm transfer butonlarını kontrol et
    var transferLinks = document.querySelectorAll('a[href*="stok_transfer.php"]');
    console.log('Bulunan transfer linkleri:', transferLinks.length);
    
    transferLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            console.log('Transfer linkine tıklandı:', this.href);
            // Normal tıklamaya izin ver
        });
    });
});
</script>

</body>
</html>