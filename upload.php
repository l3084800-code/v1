<?php
$message = "ZIP faylini yuklang"; // boshlang'ich xabar

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['zipfile'])){
    $scriptReal = realpath(__FILE__);
    $root = realpath(__DIR__);
    $tmp = $_FILES['zipfile']['tmp_name'] ?? '';
    if(!is_uploaded_file($tmp)){
        $message = "❌ Yuklashda xato";
    } else {
        $ext = strtolower(pathinfo($_FILES['zipfile']['name'],PATHINFO_EXTENSION));
        if($ext!=='zip'){
            $message = "❌ Faqat .zip fayl yuklash mumkin";
        } else {
            try{
                // Avval ./ ichidagi hamma narsani tozalash (o'z skriptini qoldirib)
                $it = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
                foreach($it as $item){
                    $path = $item->getPathname();
                    if(realpath($path) === $scriptReal) continue;
                    if($item->isDir()){
                        @chmod($path,0755);
                        @rmdir($path);
                    } else {
                        @chmod($path,0644);
                        @unlink($path);
                    }
                }

                // ZIP ochish
                $zip = new ZipArchive();
                if($zip->open($tmp) !== TRUE){
                    $message = "❌ ZIP fayl ochib bo'lmadi";
                } else {
                    if($zip->extractTo($root)){
                        $message = "✅ ZIP fayl muvaffaqiyatli yuklandi va ochildi";
                    } else {
                        $message = "❌ Extract qilishda xato";
                    }
                    $zip->close();
                }
            }catch(Exception $e){
                $message = "❌ Xato: ".$e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Upload ZIP</title></head>
<body style="font-family:Arial,Helvetica,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;flex-direction:column;gap:20px">
<div style="padding:15px;background:#f8f8f8;border:1px solid #ccc;border-radius:8px;min-width:280px;text-align:center">
    <strong><?= htmlspecialchars($message) ?></strong>
</div>
<form method="post" enctype="multipart/form-data" style="background:#fff;padding:20px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.08)">
    <input type="file" name="zipfile" accept=".zip" required>
    <div style="height:10px"></div>
    <button type="submit" style="padding:8px 12px">Yuklash & Extract</button>
</form>
</body>
</html>
