<?php
session_start();
include "config/db.php";

// Kullanıcı giriş yapmamışsa index.php'ye yönlendir
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: index.php");
    exit;
}

// Toplam ürün sayısı
$toplam_urun = $baglanti->query("SELECT COUNT(*) as sayi FROM urunler")->fetch(PDO::FETCH_ASSOC)['sayi'];

// Aktif depo sayısı
$aktif_depo = $baglanti->query("SELECT COUNT(*) as sayi FROM depolar")->fetch(PDO::FETCH_ASSOC)['sayi'];

// Kritik stok (stok miktarı 10'dan az olan ürünler)
$kritik_stok = $baglanti->query("SELECT COUNT(DISTINCT urun_id) as sayi FROM stoklar WHERE miktar < 10")->fetch(PDO::FETCH_ASSOC)['sayi'];

// Stok değeri (ortalama 100 TL varsayalım, gerçek fiyat sütunu yok)
$stok_miktar_toplam = $baglanti->query("SELECT SUM(miktar) as toplam FROM stoklar")->fetch(PDO::FETCH_ASSOC)['toplam'];
$stok_degeri = $stok_miktar_toplam * 100; // 100 TL ortalama fiyat

// Son 5 hareket
$son_hareketler = $baglanti->query("
    SELECT sh.*, u.urun_adi, d.depo_adi 
    FROM stok_hareketleri sh
    JOIN urunler u ON sh.urun_id = u.id
    JOIN depolar d ON sh.depo_id = d.id
    ORDER BY sh.tarih DESC 
    LIMIT 5
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard · Stok Takip Sistemi</title>
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
        .dashboard-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Header / Üst bar */
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

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: white;
            background: rgba(255, 255, 255, 0.05);
            padding: 10px 20px;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .user-info i {
            color: #ffd166;
        }

        .logout-btn {
            background: rgba(255, 107, 107, 0.2);
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

        /* Hoşgeldin kartı */
        .welcome-card {
            background: linear-gradient(135deg, #667eea20, #764ba220);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 40px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(102, 126, 234, 0.2) 0%, transparent 50%);
            animation: rotate 15s linear infinite;
        }

        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .welcome-content {
            position: relative;
            z-index: 1;
        }

        .welcome-content h2 {
            color: white;
            font-size: 36px;
            margin-bottom: 15px;
        }

        .welcome-content p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 18px;
            max-width: 600px;
        }

        /* İstatistik kartları */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: rgba(11, 15, 25, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }

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

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 20px;
        }

        .stat-icon.blue { background: rgba(62, 142, 208, 0.2); color: #3e8ed0; }
        .stat-icon.green { background: rgba(72, 199, 142, 0.2); color: #48c78e; }
        .stat-icon.orange { background: rgba(246, 153, 63, 0.2); color: #f6993f; }
        .stat-icon.purple { background: rgba(159, 122, 234, 0.2); color: #9f7aea; }

        .stat-value {
            color: white;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-label {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
        }

        /* Son Hareketler Tablosu */
        .recent-section {
            background: rgba(11, 15, 25, 0.7);
            backdrop-filter: blur(10px);
            border-radius: 30px;
            padding: 30px;
            margin-bottom: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .section-header h3 {
            color: white;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header h3 i {
            color: #ffd166;
        }

        .view-all {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            padding: 8px 16px;
            border: 1px solid #667eea;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .view-all:hover {
            background: #667eea;
            color: white;
        }

        .recent-table {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 15px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 600;
            font-size: 13px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        td {
            padding: 15px;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .badge {
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge.giris { background: rgba(72, 199, 142, 0.2); color: #48c78e; }
        .badge.cikis { background: rgba(255, 107, 107, 0.2); color: #ff6b6b; }
        .badge.transfer { background: rgba(246, 153, 63, 0.2); color: #f6993f; }

        /* Menü başlığı */
        .section-title {
            color: white;
            font-size: 28px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #ffd166;
        }

        /* Menü grid */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .menu-card {
            background: rgba(11, 15, 25, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 30px 25px;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 15px;
            animation: slideIn 0.5s ease forwards;
            opacity: 0;
            transform: translateX(-30px);
        }

        .menu-card:nth-child(1) { animation-delay: 0.1s; }
        .menu-card:nth-child(2) { animation-delay: 0.2s; }
        .menu-card:nth-child(3) { animation-delay: 0.3s; }
        .menu-card:nth-child(4) { animation-delay: 0.4s; }
        .menu-card:nth-child(5) { animation-delay: 0.5s; }
        .menu-card:nth-child(6) { animation-delay: 0.6s; }

        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .menu-card:hover {
            transform: translateY(-10px) scale(1.02);
            border-color: #667eea;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            background: rgba(11, 15, 25, 0.9);
        }

        .menu-icon {
            width: 80px;
            height: 80px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            transition: all 0.3s;
        }

        .menu-card:hover .menu-icon {
            transform: rotate(5deg) scale(1.1);
        }

        .menu-icon.urun-ekle { background: rgba(72, 199, 142, 0.2); color: #48c78e; }
        .menu-icon.stok-giris { background: rgba(62, 142, 208, 0.2); color: #3e8ed0; }
        .menu-icon.stok-transfer { background: rgba(108, 178, 235, 0.2); color: #6cb2eb; }
        .menu-icon.rapor { background: rgba(255, 179, 71, 0.2); color: #ffb347; }
        .menu-icon.urun-list { background: rgba(159, 122, 234, 0.2); color: #9f7aea; }
        .menu-icon.ayarlar { background: rgba(160, 174, 192, 0.2); color: #a0aec0; }

        .menu-title {
            color: white;
            font-size: 20px;
            font-weight: 600;
        }

        .menu-desc {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            line-height: 1.6;
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 50px;
            color: rgba(255, 255, 255, 0.3);
            font-size: 13px;
        }

        .footer i {
            color: #ff6b6b;
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                padding: 15px;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .welcome-content h2 {
                font-size: 28px;
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
            
            .recent-table {
                font-size: 14px;
            }
            
            td, th {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-area">
                <div class="logo-icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="logo-text">
                    <h1>StokMaster</h1>
                    <p>Stok Takip Sistemi</p>
                </div>
            </div>
            
            <div class="user-menu">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION["kullanici_adi"] ?? 'Kullanıcı'; ?></span>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Çıkış Yap
                </a>
            </div>
        </div>

        <!-- Hoşgeldin Kartı -->
        <div class="welcome-card">
            <div class="welcome-content">
                <h2>Hoş Geldin, <?php echo $_SESSION["kullanici_adi"] ?? 'Kullanıcı'; ?>! 👋</h2>
                <p>Stok takip sistemine başarıyla giriş yaptın. Aşağıdan yapmak istediğin işlemi seçebilirsin.</p>
            </div>
        </div>

        <!-- İstatistikler (DİNAMİK) -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-value"><?php echo number_format($toplam_urun); ?></div>
                <div class="stat-label">Toplam Ürün</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="stat-value"><?php echo number_format($aktif_depo); ?></div>
                <div class="stat-label">Aktif Depo</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value"><?php echo number_format($kritik_stok); ?></div>
                <div class="stat-label">Kritik Stok</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-value">₺<?php echo number_format($stok_degeri, 0); ?></div>
                <div class="stat-label">Stok Değeri</div>
            </div>
        </div>

        <!-- Son Hareketler -->
        <div class="recent-section">
            <div class="section-header">
                <h3>
                    <i class="fas fa-history"></i>
                    Son Stok Hareketleri
                </h3>
                <a href="rapor.php" class="view-all">
                    Tümünü Gör <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="recent-table">
                <table>
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Ürün</th>
                            <th>Depo</th>
                            <th>İşlem</th>
                            <th>Miktar</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($son_hareketler as $hareket): ?>
                        <tr>
                            <td><?php echo date('d.m.Y H:i', strtotime($hareket['tarih'])); ?></td>
                            <td><?php echo htmlspecialchars($hareket['urun_adi']); ?></td>
                            <td><?php echo htmlspecialchars($hareket['depo_adi']); ?></td>
                            <td>
                                <?php 
                                $badge_class = '';
                                if ($hareket['islem_turu'] == 'Stok Giriş') $badge_class = 'giris';
                                elseif ($hareket['islem_turu'] == 'Stok Çıkış') $badge_class = 'cikis';
                                else $badge_class = 'transfer';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>">
                                    <?php echo htmlspecialchars($hareket['islem_turu']); ?>
                                </span>
                            </td>
                            <td><?php echo $hareket['miktar']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Menü Başlığı -->
        <h2 class="section-title">
            <i class="fas fa-rocket"></i>
            Hızlı İşlemler
        </h2>

        <!-- Menü Grid -->
        <div class="menu-grid">
            <!-- Ürün Ekle -->
            <a href="urun_ekle.php" class="menu-card">
                <div class="menu-icon urun-ekle">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="menu-title">Ürün Ekle</div>
                <div class="menu-desc">Yeni ürünleri sisteme ekleyebilir, stoklara tanımlayabilirsin</div>
            </a>

            <!-- Stok Giriş -->
            <a href="stok_giris.php" class="menu-card">
                <div class="menu-icon stok-giris">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="menu-title">Stok Girişi</div>
                <div class="menu-desc">Tedarikçilerden gelen ürünleri stoğa ekleyebilirsin</div>
            </a>

            <!-- Stok Transfer -->
            <a href="stok_transfer.php" class="menu-card">
                <div class="menu-icon stok-transfer">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="menu-title">Stok Transfer</div>
                <div class="menu-desc">Depolar arası stok transferi yapabilir, hareketleri kaydedebilirsin</div>
            </a>

            <!-- Raporlar -->
            <a href="rapor.php" class="menu-card">
                <div class="menu-icon rapor">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="menu-title">Stok Raporları</div>
                <div class="menu-desc">Detaylı stok hareketlerini görüntüleyebilir, analiz yapabilirsin</div>
            </a>

            <!-- Ürün Listele -->
            <a href="urun_list.php" class="menu-card">
                <div class="menu-icon urun-list">
                    <i class="fas fa-list-ul"></i>
                </div>
                <div class="menu-title">Ürün Listele</div>
                <div class="menu-desc">Tüm ürünleri görüntüleyebilir, filtreleyebilir ve düzenleyebilirsin</div>
            </a>


    </div>
    
</body>
</html>