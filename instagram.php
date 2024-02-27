<?php
ini_set('display_errors', 0);
ini_set('error_reporting', 0);

$username = $_GET["username"];

if (!ctype_alnum($username)) {
        die(":D user_erro");
    }
$url = 'https://pixwox.net/profile/' . $username;
$html = file_get_contents($url);

// Veri var mı kontrol et
if (!$html) {
    echo json_encode(["error" => "Veri alınamadı"]);
    exit;
}

// Düzenli ifade kullanarak postCount, followersCount ve followingsCount verilerini bul
$postCount = $followersCount = $followingsCount = 0;
preg_match('/var postCount = (\d+);/', $html, $postMatches);
preg_match('/var followersCount = (\d+);/', $html, $followersMatches);
preg_match('/var followingsCount = (\d+);/', $html, $followingsMatches);

// Eğer eşleşme yoksa, hata mesajı yazdır ve çık
if (empty($postMatches) || empty($followersMatches) || empty($followingsMatches)) {
    echo json_encode(["error" => "Veri bulunamadı"]);
    exit;
}

// Eşleşen değerleri al
$postCount = $postMatches[1];
$followersCount = $followersMatches[1];
$followingsCount = $followingsMatches[1];

// HTML içeriğini DOMDocument'e yükle
$dom = new DOMDocument();
@$dom->loadHTML($html); // Uyarıları bastırmak için @ kullanıldı, pratikte daha iyi hata yönetimi sağlamak için kaldırılabilir

// Belirli sınıf içindeki img etiketlerini seç
$xpath = new DOMXPath($dom);
$class = "col-lg-4 text-center"; // İşlem yapmak istediğiniz sınıf
$query = "//div[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]//img";
$images = $xpath->query($query);

// Belirli sınıf içindeki text içeriklerini seç
$class = "card-title text-primary fw-medium"; // İşlem yapmak istediğiniz sınıf
$query = "//h5[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
$texts = $xpath->query($query);

// Kontrol edilecek metin
$checkText = "bi bi-patch-check-fill text-primary";

// HTML içeriğinde kontrol metnini ara
$htmlContainsCheckText = strpos($html, $checkText) !== false;

// JSON verisi için bir dizi oluştur
$jsonData = array();

// Her bir img etiketi için işlem yap
foreach ($images as $image) {
    // img etiketinin src özniteliğini al
    $src = $image->getAttribute('src');
    
    // Eğer src özniteliği belirtilen önek içeriyorsa
    if (strpos($src, 'https://cdn2.pixwox.net/index.php/') !== false) {
        // Öneki kaldır ve geri kalan kısmı $newSrc değişkenine ata
        $newSrc = str_replace('https://cdn2.pixwox.net/index.php/', '', $src);
        
        // JSON verisine ekle
        $jsonData["profil_photo"] = $newSrc;
    }
}

// Her bir text içeriği için işlem yap
foreach ($texts as $text) {
    // text içeriğini al
    $name = $text->nodeValue;

    // JSON verisine ekle
    $jsonData["name"] = $name;
}

// blue_tick değerini belirle
$blueTick = $htmlContainsCheckText ? true : false;
$jsonData["blue_tick"] = $blueTick;

// JSON verisini yazdır
$jsonData["postCount"] = $postCount;
$jsonData["followersCount"] = $followersCount;
$jsonData["followingsCount"] = $followingsCount;

// JSON verisini yazdırırken hata ayıklama yap
$json = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    $jsonData = str_replace('\/', '/', $json);
   
   echo $jsonData;
?>
