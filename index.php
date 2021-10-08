<?php

# 加密
function encrypt($key, $payload)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($payload, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

# 解密
function decode($key, $garble)
{
    list($encrypted_data, $iv) = explode('::', base64_decode($garble), 2);
    return openssl_decrypt($encrypted_data, 'aes-256-cbc', $key, 0, $iv);
}

# 寫入檔案
function setFile($msg, $dest)
{
    //取出目錄路徑中目錄(不包括後面的檔案)
    $dir_name = dirname($dest);

    //如果目錄不存在就建立
    if(!file_exists($dir_name)) {
        mkdir(iconv("UTF-8", "GBK", $dir_name), 0777, true);
    }

    //開啟檔案資源通道，不存在則自動建立
    $fp = fopen($dest, "w");

    //寫入檔案
    fwrite($fp, $msg);

    //關閉資源通道
    fclose($fp);
}

# 檔案上傳邏輯
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $file = $_FILES['file']['tmp_name'];

    if ($_POST['type']  == 'e') {
        $dest = 'encrypt/' . $_FILES['file']['name'];
        $e = encrypt('testKey', file_get_contents($file));
        setFile($e, $dest);
    }

    if ($_POST['type']  == 'd') {
        $dest = 'decode/' . $_FILES['file']['name'];
        $d = decode('testKey', file_get_contents($file));
        setFile($d, $dest);
    }

    echo '上傳成功' . '</br>';
}
?>


<form method="post" enctype="multipart/form-data">
    encrypt
    <input type="hidden" name="type" value="e" />
    <input type="file" id="file" name="file" />
    <button>Submit</button>
</form>

</br>
<form method="post" enctype="multipart/form-data">
    decode
    <input type="hidden" name="type" value="d" />
    <input type="file" id="file" name="file" />
    <button>Submit</button>
</form>
