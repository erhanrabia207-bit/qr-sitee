<?php
session_start();
include "config/db.php";

// Giriş kontrolü
if (!isset($_SESSION["login"]) || $_SESSION["login"] !== true) {
    header("Location: index.php");
    exit;
}

// Çıkış yapıldıysa yönlendir
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Silme işlemi
$mesaj = "";
$mesaj_tipi = "";

if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Önce silinecek kaydı bulalım (mesaj için)
    $kayit = $baglanti->prepare("SELECT * FROM stok_hareketleri WHERE id=?");
    $kayit->execute([$delete_id]);
    $silinecek = $kayit->fetch(PDO::FETCH_ASSOC);
    
    if ($silinecek) {
        $baglanti->prepare("DELETE FROM stok_hareketleri WHERE id=?")->execute([$delete_id]);
        $mesaj = "✅ Kayıt başarıyla silindi!";
        $mesaj_tipi = "success";
    } else {
        $mesaj = "❌ Kayıt bulunamadı!";
        $mesaj_tipi = "error";
    }
}

// Filtreleme için
$filtre_urun = isset($_GET['urun']) ? $_GET['urun'] : '';
$filtre_islem = isset($_GET['islem']) ? $_GET['islem'] : '';

$sql = "SELECT sh.*, u.urun_adi, d.depo_adi
        FROM stok_hareketleri sh
        JOIN urunler u ON sh.urun_id = u.id
        JOIN depolar d ON sh.depo_id = d.id";

$params = [];

if ($filtre_urun != '') {
    $sql .= " WHERE u.urun_adi LIKE ?";
    $params[] = "%$filtre_urun%";
}

if ($filtre_islem != '') {
    if ($filtre_urun != '') {
        $sql .= " AND sh.islem_turu = ?";
    } else {
        $sql .= " WHERE sh.islem_turu = ?";
    }
    $params[] = $filtre_islem;
}

$sql .= " ORDER BY sh.tarih DESC";

$sorgu = $baglanti->prepare($sql);
$sorgu->execute($params);
$rapor = $sorgu->fetchAll();

// Tüm ürünleri çek (filtre için)
$tum_urunler = $baglanti->query("SELECT DISTINCT urun_adi FROM urunler ORDER BY urun_adi")->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Hareketleri Raporu · Stok Takip Sistemi</title>
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
        .report-card {
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

        .report-card::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -30%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(159, 122, 234, 0.08) 0%, transparent 60%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.2); opacity: 0.6; }
        }

        /* Başlık */
        .report-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .report-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .report-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #9f7aea, #3e8ed0);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            box-shadow: 0 10px 30px rgba(159, 122, 234, 0.3);
        }

        .report-title h2 {
            color: white;
            font-size: 28px;
            font-weight: 600;
        }

        .report-title p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 14px;
            margin-top: 5px;
        }

        /* Filtreler */
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .filter-group {
            flex: 1;
        }

        .filter-input {
            width: 100%;
            padding: 12px 16px;
            background: rgba(0, 0, 0, 0.3);
            border: 2px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            font-size: 14px;
            color: white;
            transition: all 0.3s;
        }

        .filter-input:focus {
            outline: none;
            border-color: #9f7aea;
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 0 4px rgba(159, 122, 234, 0.1);
        }

        .filter-input option {
            background: #1a202c;
            color: white;
        }

        .filter-btn {
            padding: 12px 24px;
            background: rgba(159, 122, 234, 0.2);
            border: 1px solid #9f7aea;
            border-radius: 16px;
            color: #9f7aea;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn:hover {
            background: #9f7aea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(159, 122, 234, 0.3);
        }

        /* Mesaj kutusu */
        .alert {
            padding: 16px 20px;
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

        /* İşlem tipi badge */
        .badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge.giris {
            background: rgba(72, 199, 142, 0.2);
            color: #48c78e;
            border: 1px solid #48c78e;
        }

        .badge.cikis {
            background: rgba(255, 107, 107, 0.2);
            color: #ff6b6b;
            border: 1px solid #ff6b6b;
        }

        .badge.transfer {
            background: rgba(246, 153, 63, 0.2);
            color: #f6993f;
            border: 1px solid #f6993f;
        }

        /* Sil butonu */
        .delete-btn {
            padding: 8px 14px;
            background: rgba(255, 107, 107, 0.1);
            border: 1px solid #ff6b6b;
            border-radius: 12px;
            color: #ff6b6b;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .delete-btn:hover {
            background: #ff6b6b;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
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

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .report-header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .filters {
                flex-direction: column;
            }
            
            .report-card {
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
                <p>Raporlama Paneli</p>
            </div>
        </div>
        
        <div class="header-actions">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Dashboard
            </a>
            <a href="?logout=true" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                Çıkış Yap
            </a>
        </div>
    </div>

    <!-- Rapor Kartı -->
    <div class="report-card">
        <!-- Başlık -->
        <div class="report-header">
            <div class="report-title">
                <div class="report-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div>
                    <h2>Stok Hareketleri Raporu</h2>
                    <p>Tüm stok hareketlerini görüntüleyin ve yönetin</p>
                </div>
            </div>
            
            <!-- Toplam kayıt -->
            <div style="color: rgba(255,255,255,0.5);">
                <i class="fas fa-database"></i> Toplam <?= count($rapor) ?> kayıt
            </div>
        </div>

        <!-- Mesaj -->
        <?php if ($mesaj != ""): ?>
            <div class="alert <?php echo $mesaj_tipi; ?>">
                <i class="fas <?php echo ($mesaj_tipi == 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $mesaj; ?>
            </div>
        <?php endif; ?>

        <!-- Filtreler -->
        <form method="get" class="filters">
            <div class="filter-group">
                <input type="text" name="urun" class="filter-input" placeholder="Ürün ara..." value="<?= htmlspecialchars($filtre_urun) ?>">
            </div>
            <div class="filter-group">
                <select name="islem" class="filter-input">
                    <option value="">Tüm işlemler</option>
                    <option value="Stok Giriş" <?= $filtre_islem == 'Stok Giriş' ? 'selected' : '' ?>>Stok Giriş</option>
                    <option value="Stok Çıkış" <?= $filtre_islem == 'Stok Çıkış' ? 'selected' : '' ?>>Stok Çıkış</option>
                    <option value="Transfer" <?= $filtre_islem == 'Transfer' ? 'selected' : '' ?>>Transfer</option>
                </select>
            </div>
            <button type="submit" class="filter-btn">
                <i class="fas fa-search"></i>
                Filtrele
            </button>
        </form>

        <!-- Tablo -->
        <?php if (count($rapor) > 0): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Ürün</th>
                            <th>Depo</th>
                            <th>İşlem Türü</th>
                            <th>Miktar</th>
                            <th>Tarih</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rapor as $r): ?>
                            <tr>
                                <td>
                                    <strong style="color: white;"><?= htmlspecialchars($r["urun_adi"]) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($r["depo_adi"]) ?></td>
                                <td>
                                    <?php 
                                    $badge_class = "";
                                    if ($r["islem_turu"] == "Stok Giriş") $badge_class = "giris";
                                    elseif ($r["islem_turu"] == "Stok Çıkış") $badge_class = "cikis";
                                    else $badge_class = "transfer";
                                    ?>
                                    <span class="badge <?= $badge_class ?>">
                                        <?= htmlspecialchars($r["islem_turu"]) ?>
                                    </span>
                                </td>
                                <td><strong><?= $r["miktar"] ?></strong></td>
                                <td><?= date('d.m.Y H:i', strtotime($r["tarih"])) ?></td>
                                <td>
                                    <a href="?delete_id=<?= $r['id'] ?><?= $filtre_urun ? '&urun='.urlencode($filtre_urun) : '' ?><?= $filtre_islem ? '&islem='.urlencode($filtre_islem) : '' ?>" 
                                       class="delete-btn" 
                                       onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz?\n\nÜrün: <?= htmlspecialchars($r['urun_adi']) ?>\nİşlem: <?= $r['islem_turu'] ?>\nMiktar: <?= $r['miktar'] ?>\nTarih: <?= date('d.m.Y H:i', strtotime($r['tarih'])) ?>');">
                                        <i class="fas fa-trash"></i>
                                        Sil
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Tablo footer -->
            <div class="table-footer">
                <div>
                    <i class="fas fa-info-circle"></i>
                    Sayfa başına <?= count($rapor) ?> kayıt
                </div>
                <a href="#" onclick="window.print()" class="export-btn">
                    <i class="fas fa-print"></i>
                    Yazdır
                </a>
            </div>

        <?php else: ?>
            <!-- Boş durum -->
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p>Henüz stok hareketi bulunmuyor</p>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>