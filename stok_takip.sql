-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 09 Mar 2026, 10:04:28
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `stok_takip`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `depolar`
--

CREATE TABLE `depolar` (
  `id` int(11) NOT NULL,
  `depo_adi` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `depolar`
--

INSERT INTO `depolar` (`id`, `depo_adi`) VALUES
(1, 'Ana Depo'),
(2, 'Şube Depo');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `kullanicilar`
--

CREATE TABLE `kullanicilar` (
  `id` int(11) NOT NULL,
  `ad` varchar(50) DEFAULT NULL,
  `soyad` varchar(50) DEFAULT NULL,
  `kullanici_adi` varchar(50) DEFAULT NULL,
  `sifre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `kullanicilar`
--

INSERT INTO `kullanicilar` (`id`, `ad`, `soyad`, `kullanici_adi`, `sifre`) VALUES
(1, 'Admin', 'User', 'admin', '81dc9bdb52d04dc20036dbd8313ed055');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `stoklar`
--

CREATE TABLE `stoklar` (
  `id` int(11) NOT NULL,
  `urun_id` int(11) DEFAULT NULL,
  `depo_id` int(11) DEFAULT NULL,
  `miktar` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `stoklar`
--

INSERT INTO `stoklar` (`id`, `urun_id`, `depo_id`, `miktar`) VALUES
(1, 1, 2, 15),
(2, 1, 1, 5501),
(3, 3, 2, 11),
(4, 4, 2, 2),
(5, 5, 2, 7),
(6, 4, 1, 4),
(7, 19, 1, 9),
(8, 19, 2, 90),
(9, 22, 1, 56),
(11, 17, 1, 78),
(12, 20, 1, 156),
(14, 22, 2, 4);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `stok_hareketleri`
--

CREATE TABLE `stok_hareketleri` (
  `id` int(11) NOT NULL,
  `urun_id` int(11) DEFAULT NULL,
  `depo_id` int(11) DEFAULT NULL,
  `islem_turu` varchar(50) DEFAULT NULL,
  `miktar` int(11) DEFAULT NULL,
  `tarih` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `stok_hareketleri`
--

INSERT INTO `stok_hareketleri` (`id`, `urun_id`, `depo_id`, `islem_turu`, `miktar`, `tarih`) VALUES
(4, 1, 2, 'Stok Giriş', 3, '2026-02-09 10:24:51'),
(5, 1, 2, 'Depo Transfer', 2, '2026-02-09 10:25:07'),
(6, 1, 2, 'Stok Giriş', 15, '2026-02-09 10:28:25'),
(7, 1, 2, 'Stok Giriş', -7, '2026-02-09 10:50:09'),
(9, 1, 2, 'Depo Transfer', 0, '2026-02-16 14:45:26'),
(10, 3, 2, 'Stok Giriş', 2, '2026-02-16 15:33:18'),
(14, 5, 2, 'Stok Giriş', 2, '2026-02-16 15:46:54'),
(21, 9, 2, 'Depo Transfer', 4, '2026-02-16 15:52:02'),
(31, 1, 1, 'Stok Giriş', 8, '2026-02-16 16:01:07'),
(33, 1, 1, 'Stok Giriş', 5475, '2026-03-09 10:33:02'),
(34, 1, 1, 'Depo Transfer', 26, '2026-03-09 10:33:07'),
(37, 22, 1, 'Stok Giriş', 60, '2026-03-09 11:10:49'),
(38, 17, 1, 'Stok Giriş', 78, '2026-03-09 11:24:51'),
(39, 20, 1, 'Stok Giriş', 78, '2026-03-09 11:27:35'),
(40, 20, 1, 'Stok Giriş', 78, '2026-03-09 11:27:42'),
(41, 22, 2, 'Transfer', 2, '2026-03-09 11:30:59'),
(42, 22, 2, 'Transfer', 2, '2026-03-09 11:31:14');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `urunler`
--

CREATE TABLE `urunler` (
  `id` int(11) NOT NULL,
  `barkod` varchar(50) DEFAULT NULL,
  `urun_adi` varchar(100) DEFAULT NULL,
  `aciklama` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Tablo döküm verisi `urunler`
--

INSERT INTO `urunler` (`id`, `barkod`, `urun_adi`, `aciklama`) VALUES
(1, '1236514751514', 'televizyon', 'rgert'),
(3, '15581', 'televizyon', ''),
(4, '18214847', 'televizyon', 'tykjtyk'),
(5, '1821484', 'televizyon', 'wesyhg'),
(6, '182125765', 'televizyon', 'ykıuy'),
(7, '1821484258', 'televizy', 'ıup9'),
(8, '1821447', 'yatak', 'ewgterg'),
(9, '1821487', 'yatak', 'hgukyugj'),
(10, '876875', 'yatak', 'ghjg'),
(11, '14562', 'yatak', 'wesyhg'),
(12, '18214175', 'yatak', 'tykjtyk'),
(13, '1821', 'televizy', 'ghjg'),
(14, '182', 'koltuk', 'tykjtyk'),
(15, '125876', 'televizyon', 'ghjg'),
(16, '18587', 'televizyon', 'ghjg'),
(17, '47578', 'yatak', 'ghjg'),
(18, '87451', 'xdfgh', 'srdftgh'),
(19, '874511574', 'laptop', 'dfh'),
(20, '87451789', 'laptopsd', 'tyuı'),
(21, '8745178988', 'laptop', 'hggggc'),
(22, '87451236', 'laptop', 'poıuyasdfgh'),
(23, '874517897863', 'telefon', 'awerthyjuı');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `depolar`
--
ALTER TABLE `depolar`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `kullanicilar`
--
ALTER TABLE `kullanicilar`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kullanici_adi` (`kullanici_adi`);

--
-- Tablo için indeksler `stoklar`
--
ALTER TABLE `stoklar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `urun_id` (`urun_id`),
  ADD KEY `depo_id` (`depo_id`);

--
-- Tablo için indeksler `stok_hareketleri`
--
ALTER TABLE `stok_hareketleri`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `urunler`
--
ALTER TABLE `urunler`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barkod` (`barkod`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `depolar`
--
ALTER TABLE `depolar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Tablo için AUTO_INCREMENT değeri `kullanicilar`
--
ALTER TABLE `kullanicilar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `stoklar`
--
ALTER TABLE `stoklar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Tablo için AUTO_INCREMENT değeri `stok_hareketleri`
--
ALTER TABLE `stok_hareketleri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Tablo için AUTO_INCREMENT değeri `urunler`
--
ALTER TABLE `urunler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `stoklar`
--
ALTER TABLE `stoklar`
  ADD CONSTRAINT `stoklar_ibfk_1` FOREIGN KEY (`urun_id`) REFERENCES `urunler` (`id`),
  ADD CONSTRAINT `stoklar_ibfk_2` FOREIGN KEY (`depo_id`) REFERENCES `depolar` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
