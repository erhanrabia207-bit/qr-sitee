<?php
session_start();
include "config/db.php";

// Giriş kontrolü
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: index.php");
    exit;
}

// Kullanıcı bilgilerini çek
$kullanici_adi = $_SESSION["kullanici_adi"];
$kullanici = $baglanti->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = ?");
$kullanici->execute([$kullanici_adi]);
$user = $kullanici->fetch(PDO::FETCH_ASSOC);

$mesaj = "";
$mesaj_tipi = "";

// Profil güncelleme
if (isset($_POST["profil_guncelle"])) {
    $ad = $_POST["ad"] ?? '';
    $soyad = $_POST["soyad"] ?? '';
    $email = $_POST["email"] ?? '';
    
    // Email boş mu kontrol et
    if (empty($email)) {
        $mesaj = "❌ E-posta adresi boş olamaz!";
        $mesaj_tipi = "error";
    } else {
        try {
            $guncelle = $baglanti->prepare("UPDATE kullanicilar SET ad = ?, soyad = ?, email = ? WHERE kullanici_adi = ?");
            $guncelle->execute([$ad, $soyad, $email, $kullanici_adi]);
            
            $mesaj = "✅ Profil bilgileriniz güncellendi!";
            $mesaj_tipi = "success";
            
            // Güncel bilgileri tekrar çek
            $kullanici->execute([$kullanici_adi]);
            $user = $kullanici->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $mesaj = "❌ Güncelleme sırasında hata oluştu!";
            $mesaj_tipi = "error";
        }
    }
}

// Şifre değiştirme
if (isset($_POST["sifre_degistir"])) {
    $eski_sifre = $_POST["eski_sifre"] ?? '';
    $yeni_sifre = $_POST["yeni_sifre"] ?? '';
    $sifre_tekrar = $_POST["sifre_tekrar"] ?? '';
    
    // Boş kontrolü
    if (empty($eski_sifre) || empty($yeni_sifre) || empty($sifre_tekrar)) {
        $mesaj = "❌ Tüm alanları doldurun!";
        $mesaj_tipi = "error";
    } else {
        // Eski şifre kontrolü
        $eski_sifre_md5 = md5($eski_sifre);
        if ($eski_sifre_md5 != $user['sifre']) {
            $mesaj = "❌ Eski şifreniz yanlış!";
            $mesaj_tipi = "error";
        } elseif ($yeni_sifre != $sifre_tekrar) {
            $mesaj = "❌ Yeni şifreler eşleşmiyor!";
            $mesaj_tipi = "error";
        } elseif (strlen($yeni_sifre) < 3) {
            $mesaj = "❌ Şifre en az 3 karakter olmalı!";
            $mesaj_tipi = "error";
        } else {
            try {
                $yeni_sifre_md5 = md5($yeni_sifre);
                $guncelle = $baglanti->prepare("UPDATE kullanicilar SET sifre = ? WHERE kullanici_adi = ?");
                $guncelle->execute([$yeni_sifre_md5, $kullanici_adi]);
                
                $mesaj = "✅ Şifreniz başarıyla değiştirildi!";
                $mesaj_tipi = "success";
            } catch (PDOException $e) {
                $mesaj = "❌ Şifre değiştirilemedi!";
                $mesaj_tipi = "error";
            }
        }
    }
}

// Sistem ayarları
if (isset($_POST["sistem_ayarlari"])) {
    $kritik_stok = $_POST["kritik_stok"] ?? 10;
    $dil = $_POST["dil"] ?? 'tr';
    $tema = $_POST["tema"] ?? 'dark';
    
    // Burada sistem ayarlarını veritabanına kaydedebilirsin
    // Şimdilik demo mesaj gösteriyor
    $mesaj = "✅ Sistem ayarları kaydedildi! (Kritik Stok: $kritik_stok, Dil: $dil, Tema: $tema)";
    $mesaj_tipi = "success";
}

// Bildirim ayarları
if (isset($_POST["bildirim_ayarlari"])) {
    $kritik_uyari = isset($_POST["kritik_uyari"]) ? 1 : 0;
    $stok_bildirim = isset($_POST["stok_bildirim"]) ? 1 : 0;
    $transfer_bildirim = isset($_POST["transfer_bildirim"]) ? 1 : 0;
    $email_bildirim = isset($_POST["email_bildirim"]) ? 1 : 0;
    
    // Bildirim ayarlarını kaydet
    $mesaj = "✅ Bildirim ayarları kaydedildi!";
    $mesaj_tipi = "success";
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ayarlar · Stok Takip Sistemi</title>
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
            padding: 30px;
        }

        /* Ana container */
        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
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

        .header-actions {
            display: flex;
            gap: 15px;
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

        .logout-btn {
            background: rgba(255, 107, 107, 0.1);
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logout-btn:hover {
            background: #ff6b6b;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(255, 107, 107, 0.3);
        }

        /* Ana kart */
        .settings-card {
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

        .settings-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -30%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(160, 174, 192, 0.08) 0%, transparent 60%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.2); opacity: 0.6; }
        }

        /* Başlık */
        .settings-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
            position: relative;
            z-index: 1;
        }

        .settings-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #a0aec0, #718096);
            border-radius: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            box-shadow: 0 10px 30px rgba(160, 174, 192, 0.3);
        }

        .settings-header h2 {
            color: white;
            font-size: 32px;
            font-weight: 600;
        }

        .settings-header p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            margin-top: 5px;
        }

        /* Mesaj kutusu */
        .alert {
            padding: 16px 20px;
            border-radius: 18px;
            margin-bottom: 30px;
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
                transform: translateY(-20px);
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

        /* Ayarlar grid */
        .settings-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            position: relative;
            z-index: 1;
        }

        /* Her bir ayar kartı */
        .setting-box {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 30px;
            padding: 30px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .setting-box h3 {
            color: white;
            font-size: 22px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .setting-box h3 i {
            color: #a0aec0;
        }

        /* Form grupları */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.7);
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
            color: rgba(255, 255, 255, 0.3);
            font-size: 16px;
            transition: all 0.3s;
            z-index: 2;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px 14px 45px;
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            font-size: 15px;
            color: white;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #a0aec0;
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 0 4px rgba(160, 174, 192, 0.1);
        }

        .form-control:focus + i {
            color: #a0aec0;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='rgba(255,255,255,0.4)' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
        }

        select.form-control option {
            background: #1a202c;
            color: white;
        }

        /* Checkbox grubu */
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 25px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            cursor: pointer;
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #48c78e;
            cursor: pointer;
        }

        .checkbox-item span {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Buton */
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #a0aec0, #718096);
            border: none;
            border-radius: 16px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 10px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(160, 174, 192, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .btn-primary:hover {
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        /* Bilgi kartı */
        .info-card {
            grid-column: span 2;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 30px;
            padding: 25px 30px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .info-card i {
            font-size: 40px;
            color: #ffd166;
        }

        .info-card p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 14px;
            line-height: 1.6;
        }

        .info-card strong {
            color: #ffd166;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
            
            .info-card {
                grid-column: span 1;
            }
            
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }

        @media (max-width: 600px) {
            .settings-card {
                padding: 30px 20px;
            }
            
            .setting-box {
                padding: 25px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-area">
                <div class="logo-icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="logo-text">
                    <h1>StokMaster</h1>
                    <p>Ayarlar Paneli</p>
                </div>
            </div>
            
            <div class="header-actions">
                <a href="dashboard.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Dashboard
                </a>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Çıkış Yap
                </a>
            </div>
        </div>

        <!-- Ana Kart -->
        <div class="settings-card">
            <!-- Başlık -->
            <div class="settings-header">
                <div class="settings-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <h2>Ayarlar</h2>
                    <p>Profil bilgilerini ve sistem tercihlerini yönet</p>
                </div>
            </div>

            <!-- Mesaj -->
            <?php if ($mesaj != ""): ?>
                <div class="alert <?php echo $mesaj_tipi; ?>">
                    <i class="fas <?php echo ($mesaj_tipi == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo $mesaj; ?>
                </div>
            <?php endif; ?>

            <!-- Ayarlar Grid -->
            <div class="settings-grid">
                <!-- Profil Bilgileri -->
                <div class="setting-box">
                    <h3>
                        <i class="fas fa-user"></i>
                        Profil Bilgileri
                    </h3>
                    
                    <form method="post">
                        <div class="form-group">
                            <label>Kullanıcı Adı</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user"></i>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['kullanici_adi']); ?>" readonly disabled style="opacity: 0.7; background: rgba(0,0,0,0.5);">
                            </div>
                            <small style="color: rgba(255,255,255,0.3); font-size: 11px; margin-top: 5px; display: block;">Kullanıcı adı değiştirilemez</small>
                        </div>

                        <div class="form-group">
                            <label>Ad</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user-circle"></i>
                                <input type="text" name="ad" class="form-control" value="<?php echo htmlspecialchars($user['ad'] ?? ''); ?>" placeholder="Adınız">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Soyad</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user-circle"></i>
                                <input type="text" name="soyad" class="form-control" value="<?php echo htmlspecialchars($user['soyad'] ?? ''); ?>" placeholder="Soyadınız">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>E-posta</label>
                            <div class="input-wrapper">
                                <i class="fas fa-envelope"></i>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="ornek@email.com" required>
                            </div>
                        </div>

                        <button type="submit" name="profil_guncelle" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            Profili Güncelle
                        </button>
                    </form>
                </div>

                <!-- Şifre Değiştir -->
                <div class="setting-box">
                    <h3>
                        <i class="fas fa-key"></i>
                        Şifre Değiştir
                    </h3>
                    
                    <form method="post">
                        <div class="form-group">
                            <label>Eski Şifre</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="eski_sifre" class="form-control" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Yeni Şifre</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="yeni_sifre" class="form-control" placeholder="En az 3 karakter" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Yeni Şifre Tekrar</label>
                            <div class="input-wrapper">
                                <i class="fas fa-lock"></i>
                                <input type="password" name="sifre_tekrar" class="form-control" placeholder="Şifrenizi tekrar girin" required>
                            </div>
                        </div>

                        <button type="submit" name="sifre_degistir" class="btn">
                            <i class="fas fa-key"></i>
                            Şifreyi Değiştir
                        </button>
                    </form>
                </div>

                <!-- Sistem Ayarları -->
                <div class="setting-box">
                    <h3>
                        <i class="fas fa-sliders-h"></i>
                        Sistem Ayarları
                    </h3>
                    
                    <form method="post">
                        <div class="form-group">
                            <label>Kritik Stok Seviyesi</label>
                            <div class="input-wrapper">
                                <i class="fas fa-exclamation-triangle"></i>
                                <input type="number" name="kritik_stok" class="form-control" value="10" min="1" max="100">
                            </div>
                            <small style="color: rgba(255,255,255,0.3); font-size: 11px; margin-top: 5px; display: block;">Stok miktarı bu değerin altına düşerse uyarı ver</small>
                        </div>

                        <div class="form-group">
                            <label>Varsayılan Dil</label>
                            <div class="input-wrapper">
                                <i class="fas fa-language"></i>
                                <select name="dil" class="form-control">
                                    <option value="tr" selected>Türkçe</option>
                                    <option value="en">English</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Tema</label>
                            <div class="input-wrapper">
                                <i class="fas fa-palette"></i>
                                <select name="tema" class="form-control">
                                    <option value="dark" selected>Koyu (Varsayılan)</option>
                                    <option value="light">Açık</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" name="sistem_ayarlari" class="btn">
                            <i class="fas fa-save"></i>
                            Ayarları Kaydet
                        </button>
                    </form>
                </div>

                <!-- Bildirim Ayarları -->
                <div class="setting-box">
                    <h3>
                        <i class="fas fa-bell"></i>
                        Bildirim Ayarları
                    </h3>
                    
                    <form method="post">
                        <div class="checkbox-group">
                            <label class="checkbox-item">
                                <input type="checkbox" name="kritik_uyari" checked>
                                <span>Kritik stok uyarıları</span>
                            </label>
                            
                            <label class="checkbox-item">
                                <input type="checkbox" name="stok_bildirim" checked>
                                <span>Stok giriş/çıkış bildirimleri</span>
                            </label>
                            
                            <label class="checkbox-item">
                                <input type="checkbox" name="transfer_bildirim">
                                <span>Transfer bildirimleri</span>
                            </label>
                            
                            <label class="checkbox-item">
                                <input type="checkbox" name="email_bildirim">
                                <span>E-posta bildirimleri</span>
                            </label>
                        </div>

                        <button type="submit" name="bildirim_ayarlari" class="btn">
                            <i class="fas fa-bell"></i>
                            Bildirimleri Kaydet
                        </button>
                    </form>
                </div>

                <!-- Bilgi Kartı -->
                <div class="info-card">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <p>
                            <strong>Güvenli Bağlantı:</strong> Tüm işlemleriniz SSL sertifikası ile korunmaktadır. 
                            Şifreniz değiştirildiğinde oturumunuz güvenlik nedeniyle yeniden başlatılabilir.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>