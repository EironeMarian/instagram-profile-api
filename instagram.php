<?php
ini_set('display_errors', 0);
ini_set('error_reporting', 0);

$username = $_GET["username"];

if (!ctype_alnum($username)) {
        die(":D user_erro");
    }
$url = 'https://pixwox.net/profile/' . $username;
$html = file_get_contents($url);

if (!$html) {
    echo json_encode(["error" => "Veri alınamadı"]);
    exit;
}

$postCount = $followersCount = $followingsCount = 0;
preg_match('/var postCount = (\d+);/', $html, $postMatches);
preg_match('/var followersCount = (\d+);/', $html, $followersMatches);
preg_match('/var followingsCount = (\d+);/', $html, $followingsMatches);

// Eğer eşleşme yoksa, hata mesajı yazdır ve çık
if (empty($postMatches) || empty($followersMatches) || empty($followingsMatches)) {
    echo json_encode(["error" => "Veri bulunamadı"]);
    exit;
}

$postCount = $postMatches[1];
$followersCount = $followersMatches[1];
$followingsCount = $followingsMatches[1];

$dom = new DOMDocument();
@$dom->loadHTML($html); 

$xpath = new DOMXPath($dom);
$class = "col-lg-4 text-center";
$query = "//div[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]//img";
$images = $xpath->query($query);

$class = "card-title text-primary fw-medium";
$query = "//h5[contains(concat(' ', normalize-space(@class), ' '), ' $class ')]";
$texts = $xpath->query($query);

$checkText = "bi bi-patch-check-fill text-primary";

$htmlContainsCheckText = strpos($html, $checkText) !== false;

$jsonData = array();

foreach ($images as $image) {
    $src = $image->getAttribute('src');
    
    if (strpos($src, 'https://cdn2.pixwox.net/index.php/') !== false) {
        $newSrc = str_replace('https://cdn2.pixwox.net/index.php/', '', $src);
        
        $jsonData["profil_photo"] = $newSrc;
    }
}

foreach ($texts as $text) {
    $name = $text->nodeValue;

    $jsonData["name"] = $name;
}

$blueTick = $htmlContainsCheckText ? true : false;
$jsonData["blue_tick"] = $blueTick;

$jsonData["postCount"] = $postCount;
$jsonData["followersCount"] = $followersCount;
$jsonData["followingsCount"] = $followingsCount;

$json = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    $jsonData = str_replace('\/', '/', $json);
   
   echo $jsonData;
?>
