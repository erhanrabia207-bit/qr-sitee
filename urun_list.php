<?php
session_start();
include "config/db.php";
if (!isset($_SESSION["login"])) {
    header("Location: /stok_takipsonnnn/index.php");
    exit;
}

// Arama filtresi
$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($search != '') {
    $sorgu = $baglanti->prepare("SELECT * FROM urunler WHERE urun_adi LIKE ? OR barkod LIKE ? OR aciklama LIKE ? ORDER BY id DESC");
    $sorgu->execute(["%$search%", "%$search%", "%$search%"]);
    $urunler = $sorgu->fetchAll();
} else {
    $urunler = $baglanti->query("SELECT * FROM urunler ORDER BY id DESC")->fetchAll();
}

// Toplam ürün sayısı
$toplam_urun = count($urunler);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Listesi · Stok Takip Sistemi</title>
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
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
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

        .add-btn {
            background: linear-gradient(135deg, #48c78e, #3e8ed0);
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 50px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 5px 15px rgba(72, 199, 142, 0.3);
        }

        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(72, 199, 142, 0.5);
        }

        /* Ana kart */
        .list-card {
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

        .list-card::before {
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
        .list-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .list-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .list-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #48c78e, #3e8ed0);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            box-shadow: 0 10px 30px rgba(72, 199, 142, 0.3);
        }

        .list-title h2 {
            color: white;
            font-size: 28px;
            font-weight: 600;
        }

        .list-title p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            margin-top: 5px;
        }

        /* Arama ve filtre */
        .search-section {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .search-wrapper {
            flex: 1;
            position: relative;
        }

        .search-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 16px;
        }

        .search-input {
            width: 100%;
            padding: 14px 16px 14px 45px;
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.05);
            border-radius: 18px;
            font-size: 15px;
            color: white;
            transition: all 0.3s;
        }

        .search-input:focus {
            outline: none;
            border-color: #48c78e;
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 0 4px rgba(72, 199, 142, 0.1);
        }

        .search-input:focus + i {
            color: #48c78e;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.2);
        }

        .search-btn {
            padding: 14px 28px;
            background: rgba(72, 199, 142, 0.2);
            border: 1px solid #48c78e;
            border-radius: 18px;
            color: #48c78e;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .search-btn:hover {
            background: #48c78e;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(72, 199, 142, 0.3);
        }

        .reset-btn {
            padding: 14px 28px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 18px;
            color: rgba(255, 255, 255, 0.5);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .reset-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        /* İstatistik kartı */
        .stats-mini {
            display: flex;
            gap: 20px;
            margin-bottom: 25px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 20px;
            padding: 15px 20px;
            position: relative;
            z-index: 1;
        }

        .stat-mini-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: rgba(255, 255, 255, 0.7);
        }

        .stat-mini-item i {
            color: #48c78e;
            font-size: 18px;
        }

        .stat-mini-item span {
            font-weight: 600;
            color: white;
            margin-right: 5px;
        }

        /* Tablo */
        .table-wrapper {
            overflow-x: auto;
            position: relative;
            z-index: 1;
            border-radius: 20px;
            background: rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th {
            background: rgba(0, 0, 0, 0.4);
            color: rgba(255, 255, 255, 0.7);
            font-weight: 600;
            font-size: 14px;
            padding: 18px 16px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        td {
            padding: 16px;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        /* ID badge */
        .id-badge {
            background: rgba(72, 199, 142, 0.2);
            border: 1px solid #48c78e;
            color: #48c78e;
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        /* Barkod */
        .barcode {
            font-family: monospace;
            background: rgba(255, 255, 255, 0.1);
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 13px;
            letter-spacing: 1px;
        }

        /* Açıklama */
        .description {
            max-width: 250px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: rgba(255, 255, 255, 0.7);
            font-size: 13px;
        }

        /* İşlem butonları */
        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .edit-btn {
            padding: 6px 12px;
            background: rgba(62, 142, 208, 0.1);
            border: 1px solid #3e8ed0;
            border-radius: 10px;
            color: #3e8ed0;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .edit-btn:hover {
            background: #3e8ed0;
            color: white;
            transform: translateY(-2px);
        }

        .delete-btn {
            padding: 6px 12px;
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid #ff6b6b;
            border-radius: 10px;
            color: #ff6b6b;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .delete-btn:hover {
            background: #ff6b6b;
            color: white;
            transform: translateY(-2px);
        }

        /* Sayfa bilgisi */
        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 13px;
        }

        .export-btn {
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: white;
            text-decoration: none;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .export-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Boş veri */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: rgba(255, 255, 255, 0.3);
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .empty-state a {
            color: #48c78e;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
            display: inline-block;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .list-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .search-section {
                flex-direction: column;
            }
            
            .list-card {
                padding: 25px 20px;
            }
            
            .stats-mini {
                flex-direction: column;
                gap: 10px;
            }
        }

        /* Yazdırma stilleri */
        @media print {
            body {
                background: white;
                padding: 20px;
            }
            
            body::before,
            body::after,
            .header,
            .search-section,
            .stats-mini,
            .action-buttons,
            .table-footer .export-btn,
            .add-btn,
            .back-btn,
            .list-card::before,
            .list-icon {
                display: none !important;
            }
            
            .container {
                max-width: 100%;
                margin: 0;
            }
            
            .list-card {
                background: white;
                padding: 20px;
                border-radius: 0;
                box-shadow: none;
                border: 1px solid #ddd;
            }
            
            .list-title h2 {
                color: #333;
                font-size: 24px;
                margin-bottom: 5px;
            }
            
            .list-title p {
                color: #666;
            }
            
            table {
                border-collapse: collapse;
                width: 100%;
                margin-top: 20px;
            }
            
            th {
                background: #f5f5f5;
                color: #333;
                border-bottom: 2px solid #ddd;
                padding: 12px;
            }
            
            td {
                color: #333;
                border-bottom: 1px solid #eee;
                padding: 10px 12px;
            }
            
            .id-badge {
                background: #f0f0f0;
                border: 1px solid #999;
                color: #333;
            }
            
            .barcode {
                background: #f5f5f5;
                color: #333;
            }
            
            .description {
                color: #666;
            }
            
            @page {
                size: A4;
                margin: 2cm;
            }
        }
    </style>
</head>
<body>

<!-- Session mesajı varsa göster -->
<?php if (isset($_SESSION['message'])): ?>
    <div style="max-width: 1400px; margin: 0 auto 20px; position: relative; z-index: 1;">
        <div style="background: <?= $_SESSION['message_type'] == 'success' ? 'rgba(72, 199, 142, 0.15)' : 'rgba(255, 107, 107, 0.15)'; ?>; border: 1px solid <?= $_SESSION['message_type'] == 'success' ? '#48c78e' : '#ff6b6b'; ?>; border-radius: 20px; padding: 16px 25px; display: flex; align-items: center; gap: 12px; backdrop-filter: blur(10px);">
            <i class="fas <?= $_SESSION['message_type'] == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>" style="color: <?= $_SESSION['message_type'] == 'success' ? '#48c78e' : '#ff6b6b'; ?>; font-size: 20px;"></i>
            <span style="color: white; font-size: 14px;"><?= $_SESSION['message'] ?></span>
        </div>
    </div>
    <?php 
    // Mesajı gösterdikten sonra session'dan temizle
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    ?>
<?php endif; ?>

<div class="container">
    <!-- Header -->
    <div class="header">
        <div class="logo-area">
            <div class="logo-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <div class="logo-text">
                <h1>StokMaster</h1>
                <p>Ürün Listesi</p>
            </div>
        </div>
        
        <div class="header-actions">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Dashboard
            </a>
            <a href="urun_ekle.php" class="add-btn">
                <i class="fas fa-plus-circle"></i>
                Yeni Ürün
            </a>
        </div>
    </div>

    <!-- Liste Kartı -->
    <div class="list-card">
        <!-- Başlık -->
        <div class="list-header">
            <div class="list-title">
                <div class="list-icon">
                    <i class="fas fa-boxes"></i>
                </div>
                <div>
                    <h2>Ürün Listesi</h2>
                    <p>Tüm ürünleri görüntüleyin ve yönetin</p>
                </div>
            </div>
        </div>

        <!-- Arama -->
        <form method="get" class="search-section">
            <div class="search-wrapper">
                <i class="fas fa-search"></i>
                <input type="text" name="search" class="search-input" placeholder="Ürün adı, barkod veya açıklama ile ara..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <button type="submit" class="search-btn">
                <i class="fas fa-search"></i>
                Ara
            </button>
            <?php if ($search != ''): ?>
                <a href="urun_list.php" class="reset-btn">
                    <i class="fas fa-times"></i>
                    Temizle
                </a>
            <?php endif; ?>
        </form>

        <!-- Mini istatistik -->
        <div class="stats-mini">
            <div class="stat-mini-item">
                <i class="fas fa-box"></i>
                <span><?= $toplam_urun ?></span> Toplam Ürün
            </div>
            <?php if ($search != ''): ?>
                <div class="stat-mini-item">
                    <i class="fas fa-search"></i>
                    <span><?= count($urunler) ?></span> Sonuç Bulundu
                </div>
            <?php endif; ?>
        </div>

        <!-- Tablo -->
        <?php if (count($urunler) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Barkod</th>
                            <th>Ürün Adı</th>
                            <th>Açıklama</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($urunler as $urun): ?>
                            <tr>
                                <td>
                                    <span class="id-badge">#<?= $urun["id"] ?></span>
                                </td>
                                <td>
                                    <span class="barcode">
                                        <i class="fas fa-barcode" style="margin-right: 5px; color: #48c78e;"></i>
                                        <?= htmlspecialchars($urun["barkod"]) ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: white; font-size: 15px;"><?= htmlspecialchars($urun["urun_adi"]) ?></strong>
                                </td>
                                <td>
                                    <div class="description" title="<?= htmlspecialchars($urun["aciklama"]) ?>">
                                        <?= htmlspecialchars($urun["aciklama"] ?: '-') ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="urun_duzenle.php?id=<?= $urun['id'] ?>" class="edit-btn">
                                            <i class="fas fa-edit"></i>
                                            Düzenle
                                        </a>
                                        <a href="urun_sil.php?id=<?= $urun['id'] ?>" class="delete-btn" onclick="return confirm('Bu ürünü silmek istediğinize emin misiniz?\n\nÜrün: <?= htmlspecialchars($urun['urun_adi']) ?>\nBarkod: <?= $urun['barkod'] ?>')">
                                            <i class="fas fa-trash"></i>
                                            Sil
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tablo footer -->
            <div class="table-footer">
                <div>
                    <i class="fas fa-database"></i>
                    Toplam <?= $toplam_urun ?> üründen <?= count($urunler) ?> gösteriliyor
                </div>
                <button onclick="window.print()" class="export-btn">
                    <i class="fas fa-print"></i>
                    Liste Yazdır
                </button>
            </div>

        <?php else: ?>
            <!-- Boş durum -->
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <p>Henüz ürün bulunmuyor</p>
                <a href="urun_ekle.php">
                    <i class="fas fa-plus-circle"></i>
                    İlk ürünü ekle
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Yazdırma butonuna tıklandığında
document.addEventListener('DOMContentLoaded', function() {
    const printBtn = document.querySelector('.export-btn');
    if (printBtn) {
        printBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    }
});

// Klavye kısayolu (Ctrl+P)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        window.print();
    }
});

</script>
<!-- CHATBOT - TEST EDİLMİŞ ÇALIŞAN VERSİYON -->
<button id="chatbot-toggle" style="position: fixed; bottom: 30px; right: 30px; background: linear-gradient(135deg, #48c78e, #3e8ed0); color: white; border: none; padding: 15px 25px; border-radius: 50px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 16px; font-weight: 600; box-shadow: 0 5px 20px rgba(0,0,0,0.3); z-index: 9999;">
    <i class="fas fa-robot"></i>
    <span>Yapay Zeka Asistan</span>
</button>

<div id="chatbot-window" style="position: fixed; bottom: 100px; right: 30px; width: 350px; height: 500px; background: #1a1e2b; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.5); display: none; flex-direction: column; overflow: hidden; z-index: 10000; border: 1px solid #333;">
    
    <!-- Header -->
    <div style="background: linear-gradient(135deg, #48c78e, #3e8ed0); padding: 15px 20px; display: flex; align-items: center; color: white;">
        <i class="fas fa-robot" style="margin-right: 10px;"></i>
        <h3 style="flex: 1; margin: 0; font-size: 16px;">Stok Asistanı</h3>
        <button onclick="document.getElementById('chatbot-window').style.display='none'" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer;">
            <i class="fas fa-times"></i>
        </button>
    </div>
    
    <!-- Messages -->
    <div id="chatbot-messages" style="flex: 1; padding: 20px; overflow-y: auto; background: #1a1e2b; display: flex; flex-direction: column; gap: 10px;">
        <div style="display: flex; margin-bottom: 10px;">
            <div style="background: #2a2f3c; color: white; padding: 10px 15px; border-radius: 15px; border-bottom-left-radius: 5px; max-width: 80%;">
                Merhaba! Size nasıl yardımcı olabilirim?
                <br><small style="color: #48c78e; display: block; margin-top: 5px;">Toplam <?= $toplam_urun ?> ürün var</small>
            </div>
        </div>
    </div>
    
    <!-- Input -->
    <div style="padding: 15px; background: #151a24; display: flex; gap: 10px; border-top: 1px solid #333;">
        <input type="text" id="chatbot-input" placeholder="Sorunuzu yazın..." style="flex: 1; padding: 12px 15px; background: #2a2f3c; border: 1px solid #444; border-radius: 25px; color: white; outline: none;">
        <button id="chatbot-send" style="width: 45px; height: 45px; background: linear-gradient(135deg, #48c78e, #3e8ed0); border: none; border-radius: 50%; color: white; cursor: pointer;">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<script>
// CHATBOT JAVASCRIPT
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('chatbot-toggle');
    const chatbotWindow = document.getElementById('chatbot-window');
    const sendBtn = document.getElementById('chatbot-send');
    const input = document.getElementById('chatbot-input');
    const messagesContainer = document.getElementById('chatbot-messages');

    // Aç/kapat
    toggleBtn.onclick = function() {
        if (chatbotWindow.style.display === 'none' || chatbotWindow.style.display === '') {
            chatbotWindow.style.display = 'flex';
            input.focus();
        } else {
            chatbotWindow.style.display = 'none';
        }
    };

    // Mesaj gönderme
    sendBtn.onclick = function() {
        sendMessage();
    };

    input.onkeypress = function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    };

    function sendMessage() {
        const message = input.value.trim();
        if (message === '') return;

        // Kullanıcı mesajını ekle
        addMessage(message, 'user');
        input.value = '';

        // Yükleniyor mesajı
        const loadingMsg = addLoadingMessage();

        // API'ye istek gönder
        fetch('chatbot_api.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message })
        })
        .then(response => response.json())
        .then(data => {
            // Yükleniyor mesajını kaldır
            if (loadingMsg && loadingMsg.remove) loadingMsg.remove();
            
            // Cevabı ekle
            if (data.choices && data.choices[0]) {
                addMessage(data.choices[0].message.content, 'bot');
            } else {
                addMessage('Üzgünüm, bir hata oluştu.', 'bot');
            }
        })
        .catch(error => {
            console.error('Hata:', error);
            if (loadingMsg && loadingMsg.remove) loadingMsg.remove();
            addMessage('Bağlantı hatası!', 'bot');
        });
    }

    function addMessage(text, sender) {
        const msgDiv = document.createElement('div');
        msgDiv.style.display = 'flex';
        msgDiv.style.marginBottom = '10px';
        msgDiv.style.justifyContent = sender === 'user' ? 'flex-end' : 'flex-start';
        
        const bubble = document.createElement('div');
        bubble.style.background = sender === 'user' ? 'linear-gradient(135deg, #48c78e, #3e8ed0)' : '#2a2f3c';
        bubble.style.color = 'white';
        bubble.style.padding = '10px 15px';
        bubble.style.borderRadius = '15px';
        bubble.style.borderBottomRightRadius = sender === 'user' ? '5px' : '15px';
        bubble.style.borderBottomLeftRadius = sender === 'user' ? '15px' : '5px';
        bubble.style.maxWidth = '80%';
        bubble.style.wordWrap = 'break-word';
        bubble.innerHTML = text.replace(/\n/g, '<br>');
        
        msgDiv.appendChild(bubble);
        messagesContainer.appendChild(msgDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        return msgDiv;
    }

    function addLoadingMessage() {
        const msgDiv = document.createElement('div');
        msgDiv.style.display = 'flex';
        msgDiv.style.marginBottom = '10px';
        msgDiv.style.justifyContent = 'flex-start';
        
        const bubble = document.createElement('div');
        bubble.style.background = '#2a2f3c';
        bubble.style.color = 'white';
        bubble.style.padding = '10px 15px';
        bubble.style.borderRadius = '15px';
        bubble.style.borderBottomLeftRadius = '5px';
        bubble.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Yazıyor...';
        
        msgDiv.appendChild(bubble);
        messagesContainer.appendChild(msgDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        return msgDiv;
    }
});
</script>

</body>
</html>