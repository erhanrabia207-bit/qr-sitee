<?php
session_start();
include "config/db.php";

$error="";

if(isset($_POST['kullanici_adi'])){

$kadi=$_POST['kullanici_adi'];
$sifre=md5($_POST['sifre']);

$sorgu=$baglanti->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi=? AND sifre=?");
$sorgu->execute([$kadi,$sifre]);

if($sorgu->rowCount()>0){

$_SESSION['login']=true;
header("Location: dashboard.php");
exit;

}else{
$error="Kullanıcı adı veya şifre yanlış";
}

}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stok Takip Sistemi - Giriş</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>

*{
box-sizing:border-box;
margin:0;
padding:0;
font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body{
height:100vh;
display:flex;
justify-content:center;
align-items:center;
background:linear-gradient(135deg,#0f2027,#203a43,#2c5364);
padding:20px;
}

.container{
width:1600px;
height:850px;
display:flex;
border-radius:50px;
overflow:hidden;
box-shadow:0 30px 80px rgba(0,0,0,0.8);
position:relative;
}

/* Sol taraf - Giriş formu */
.left{
width:35%;
background:rgba(11,15,25,0.95);
color:white;
padding:70px 50px;
display:flex;
flex-direction:column;
justify-content:center;
backdrop-filter:blur(10px);
position:relative;
z-index:2;
border-right:1px solid rgba(255,255,255,0.1);
}

.left h1{
font-size:52px;
margin-bottom:20px;
background:linear-gradient(135deg,#667eea,#764ba2);
-webkit-background-clip:text;
-webkit-text-fill-color:transparent;
font-weight:700;
}

.left p{
color:#a0aec0;
margin-bottom:40px;
font-size:16px;
line-height:1.6;
}

.input-group{
position:relative;
margin-bottom:20px;
}

.input-group i{
position:absolute;
left:20px;
top:50%;
transform:translateY(-50%);
color:#a0aec0;
font-size:18px;
transition:all 0.3s;
}

.input{
width:100%;
padding:16px 20px 16px 55px;
border-radius:50px;
border:2px solid #2d3748;
background:#1a202c;
color:white;
font-size:16px;
transition:all 0.3s;
}

.input:focus{
outline:none;
border-color:#667eea;
box-shadow:0 0 0 4px rgba(102,126,234,0.3);
}

.input:focus + i{
color:#667eea;
}

button{
width:100%;
padding:16px;
border-radius:50px;
border:none;
background:linear-gradient(135deg,#667eea,#764ba2);
color:white;
cursor:pointer;
font-size:16px;
font-weight:600;
transition:all 0.3s;
margin-top:20px;
display:flex;
align-items:center;
justify-content:center;
gap:10px;
}

button:hover{
transform:translateY(-2px);
box-shadow:0 15px 35px rgba(102,126,234,0.5);
}

.error{
background:rgba(255,107,107,0.2);
color:#ff6b6b;
padding:15px;
border-radius:50px;
margin-bottom:25px;
font-size:14px;
text-align:center;
border:1px solid #ff6b6b;
display:flex;
align-items:center;
justify-content:center;
gap:10px;
}

/* Sağ taraf - Yapabileceğin işlemler */
.right{
width:65%;
background:linear-gradient(135deg,#1a2639,#2c3e50);
padding:50px 45px;
display:flex;
flex-direction:column;
justify-content:center;
position:relative;
z-index:1;
overflow:hidden;
}

/* Animasyonlu arkaplan */
.right::before{
content:'';
position:absolute;
top:-30%;
right:-30%;
width:160%;
height:160%;
background:radial-gradient(circle,rgba(102,126,234,0.15) 0%,transparent 60%);
animation:pulse 4s ease-in-out infinite;
}

.right::after{
content:'';
position:absolute;
bottom:-30%;
left:-30%;
width:160%;
height:160%;
background:radial-gradient(circle,rgba(118,75,162,0.15) 0%,transparent 60%);
animation:pulse 4s ease-in-out infinite reverse;
}

@keyframes pulse{
0%,100%{transform:scale(1); opacity:0.5;}
50%{transform:scale(1.2); opacity:0.8;}
}

.right-content{
position:relative;
z-index:2;
height:100%;
display:flex;
flex-direction:column;
justify-content:center;
}

.right h2{
color:white;
font-size:36px;
margin-bottom:10px;
display:flex;
align-items:center;
gap:12px;
}

.right h2 i{
color:#ffd166;
font-size:32px;
}

.right > p{
color:rgba(255,255,255,0.7);
font-size:16px;
margin-bottom:30px;
line-height:1.5;
}

/* İşlemler grid - 2 sütun */
.operations-grid{
display:grid;
grid-template-columns:repeat(2,1fr);
gap:20px;
margin-bottom:25px;
}

/* Her bir işlem kartı - BÜYÜK */
.operation-card{
background:rgba(255,255,255,0.05);
backdrop-filter:blur(10px);
border:1px solid rgba(255,255,255,0.1);
border-radius:20px;
padding:20px 15px;
display:flex;
align-items:center;
gap:15px;
transition:all 0.3s;
animation:slideIn 0.5s ease forwards;
opacity:0;
transform:translateX(30px);
}

/* Animasyon gecikmeleri */
.operation-card:nth-child(1){animation-delay:0.1s;}
.operation-card:nth-child(2){animation-delay:0.15s;}
.operation-card:nth-child(3){animation-delay:0.2s;}
.operation-card:nth-child(4){animation-delay:0.25s;}
.operation-card:nth-child(5){animation-delay:0.3s;}
.operation-card:nth-child(6){animation-delay:0.35s;}
.operation-card:nth-child(7){animation-delay:0.4s;}
.operation-card:nth-child(8){animation-delay:0.45s;}

@keyframes slideIn{
to{
opacity:1;
transform:translateX(0);
}
}

.operation-card:hover{
background:rgba(255,255,255,0.1);
transform:translateX(5px) scale(1.02);
border-color:#667eea;
box-shadow:0 10px 25px rgba(0,0,0,0.3);
}

/* İkonlar - BÜYÜK */
.card-icon{
width:55px;
height:55px;
border-radius:16px;
display:flex;
align-items:center;
justify-content:center;
font-size:28px;
transition:all 0.3s;
flex-shrink:0;
}

.operation-card:hover .card-icon{
transform:rotate(5deg) scale(1.1);
}

.card-content{
flex:1;
min-width:0;
}

.card-content h3{
color:white;
font-size:18px;
font-weight:600;
margin-bottom:5px;
}

.card-content p{
color:rgba(255,255,255,0.6);
font-size:13px;
line-height:1.5;
}

/* İkon renkleri */
.icon-urun-ekle{background:rgba(72,199,142,0.2); color:#48c78e;}
.icon-urun-list{background:rgba(62,142,208,0.2); color:#3e8ed0;}
.icon-depo-ekle{background:rgba(246,153,63,0.2); color:#f6993f;}
.icon-depo-list{background:rgba(159,122,234,0.2); color:#9f7aea;}
.icon-stok-durum{background:rgba(246,109,155,0.2); color:#f66d9b;}
.icon-stok-transfer{background:rgba(108,178,235,0.2); color:#6cb2eb;}
.icon-rapor{background:rgba(255,179,71,0.2); color:#ffb347;}
.icon-ayarlar{background:rgba(160,174,192,0.2); color:#a0aec0;}

/* Alt not - BÜYÜK */
.note-box{
background:rgba(0,0,0,0.2);
border-radius:18px;
padding:18px 20px;
margin-top:10px;
border:1px solid rgba(255,255,255,0.1);
display:flex;
align-items:center;
gap:15px;
flex-shrink:0;
}

.note-box i{
font-size:24px;
color:#ffd166;
flex-shrink:0;
}

.note-box p{
color:rgba(255,255,255,0.8);
font-size:14px;
line-height:1.5;
}

.note-box strong{
color:#ffd166;
}

/* Dashboard bilgisi */
.dashboard-info{
margin-top:25px;
text-align:center;
color:#a0aec0;
font-size:13px;
}

/* Responsive */
@media(max-width:1500px){
.container{
width:95%;
height:95vh;
}
.left h1{font-size:44px;}
.left{padding:50px 40px;}
.right{padding:40px 35px;}
.operations-grid{gap:15px;}
.card-icon{width:48px;height:48px;font-size:24px;}
.card-content h3{font-size:16px;}
.card-content p{font-size:12px;}
}

@media(max-width:1200px){
.container{
flex-direction:column;
height:auto;
}
.left,.right{
width:100%;
}
.left{
padding:50px 40px;
}
.right{
padding:40px 35px;
}
.operations-grid{
grid-template-columns:repeat(2,1fr);
}
}

@media(max-width:700px){
.operations-grid{
grid-template-columns:1fr;
}
.right h2{font-size:28px;}
.card-icon{width:42px;height:42px;font-size:22px;}
.card-content h3{font-size:15px;}
}

</style>
</head>

<body>

<div class="container">

<!-- Sol taraf - Giriş formu -->
<div class="left">

<h1>StokMaster</h1>
<p>Stok takip sistemine hoş geldiniz. Hesabınıza giriş yaparak tüm özellikleri kullanmaya başlayın.</p>

<?php if($error!=""){ ?>
<div class="error">
<i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
</div>
<?php } ?>

<form method="post">

<div class="input-group">
<i class="fas fa-user"></i>
<input class="input" type="text" name="kullanici_adi" placeholder="Kullanıcı adı" required>
</div>

<div class="input-group">
<i class="fas fa-lock"></i>
<input class="input" type="password" name="sifre" placeholder="Şifre" required>
</div>

<button type="submit">
<i class="fas fa-sign-in-alt"></i> Giriş Yap
</button>

</form>

<div class="dashboard-info">
<i class="fas fa-arrow-right"></i> Giriş yapınca <strong>Dashboard</strong>'a yönlendirilirsiniz
</div>

</div>

<!-- Sağ taraf - Yapabileceğin işlemler -->
<div class="right">
<div class="right-content">

<h2>
<i class="fas fa-rocket"></i> Yapabileceklerin
</h2>

<!-- İşlemler grid -->
<div class="operations-grid">

<!-- Ürün Ekle -->
<div class="operation-card">
<div class="card-icon icon-urun-ekle">
<i class="fas fa-plus-circle"></i>
</div>
<div class="card-content">
<h3>Ürün Ekle</h3>
<p>Yeni ürün ekleyebilir, stoklara tanımlayabilirsin</p>
</div>
</div>

<!-- Ürün Listele -->
<div class="operation-card">
<div class="card-icon icon-urun-list">
<i class="fas fa-list-ul"></i>
</div>
<div class="card-content">
<h3>Ürün Listele</h3>
<p>Tüm ürünleri görüntüleyebilir, filtreleyebilirsin</p>
</div>
</div>

<!-- Depo Ekle -->
<div class="operation-card">
<div class="card-icon icon-depo-ekle">
<i class="fas fa-warehouse"></i>
</div>
<div class="card-content">
<h3>Depo Ekle</h3>
<p>Yeni depolar oluşturabilir, mevcut depoları yönetebilirsin</p>
</div>
</div>

<!-- Depo Listele -->
<div class="operation-card">
<div class="card-icon icon-depo-list">
<i class="fas fa-building"></i>
</div>
<div class="card-content">
<h3>Depo Yönet</h3>
<p>Tüm depoları listeleyebilir, kapasitelerini görüntüleyebilirsin</p>
</div>
</div>

<!-- Stok Durumu -->
<div class="operation-card">
<div class="card-icon icon-stok-durum">
<i class="fas fa-chart-pie"></i>
</div>
<div class="card-content">
<h3>Stok Durumu</h3>
<p>Anlık stok miktarlarını görebilir, kritik stok uyarılarını takip edebilirsin</p>
</div>
</div>

<!-- Stok Transfer -->
<div class="operation-card">
<div class="card-icon icon-stok-transfer">
<i class="fas fa-exchange-alt"></i>
</div>
<div class="card-content">
<h3>Stok Transfer</h3>
<p>Depolar arası stok transferi yapabilir, hareketleri kaydedebilirsin</p>
</div>
</div>

<!-- Raporlar -->
<div class="operation-card">
<div class="card-icon icon-rapor">
<i class="fas fa-chart-line"></i>
</div>
<div class="card-content">
<h3>Raporlar</h3>
<p>Detaylı stok raporları oluşturabilir, analizler yapabilirsin</p>
</div>
</div>


</div>

<!-- Not kutusu -->
<div class="note-box">
<i class="fas fa-lightbulb"></i>
<p>
<strong>Not:</strong> Tüm bu işlemleri gerçekleştirmek için öncelikle giriş yapmalısın. Hesabın yoksa yöneticinle iletişime geç.
</p>
</div>

</div>
</div>

</div>

</body>
</html>