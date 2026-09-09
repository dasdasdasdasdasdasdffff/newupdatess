<?php
$host = 'smtp.gmail.com';
$user = 'capitalnestnepalpvtltd@gmail.com';
$pass = 'uujgchxumlqgsejb';
$port = 587;

$smtp = fsockopen($host, $port, $errno, $errstr, 20);
if (!$smtp) {
    echo "CONNECT_FAIL\n";
    echo $errstr . " (" . $errno . ")\n";
    exit(1);
}

stream_set_timeout($smtp, 20);
$banner = fgets($smtp, 515);
echo "BANNER: " . trim($banner) . PHP_EOL;

fputs($smtp, "EHLO smtp.gmail.com\r\n");
while (($line = fgets($smtp, 515)) !== false) {
    echo trim($line) . PHP_EOL;
    if (strpos($line, '250-') === false && strpos($line, '250 ') === 0) {
        break;
    }
}

fputs($smtp, "STARTTLS\r\n");
$resp = fgets($smtp, 515);
echo "STARTTLS: " . trim($resp) . PHP_EOL;

if (!stream_socket_enable_crypto($smtp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
    echo "TLS_FAIL\n";
    fclose($smtp);
    exit(1);
}

fputs($smtp, "EHLO smtp.gmail.com\r\n");
while (($line = fgets($smtp, 515)) !== false) {
    echo trim($line) . PHP_EOL;
    if (strpos($line, '250-') === false && strpos($line, '250 ') === 0) {
        break;
    }
}

fputs($smtp, "AUTH LOGIN\r\n");
$resp = fgets($smtp, 515);
echo "AUTH_LOGIN: " . trim($resp) . PHP_EOL;

fputs($smtp, base64_encode($user) . "\r\n");
$resp = fgets($smtp, 515);
echo "USER_RESPONSE: " . trim($resp) . PHP_EOL;

fputs($smtp, base64_encode($pass) . "\r\n");
$resp = fgets($smtp, 515);
echo "PASS_RESPONSE: " . trim($resp) . PHP_EOL;

if (strpos(trim($resp), '235') === 0) {
    echo "AUTH_OK\n";
} else {
    echo "AUTH_FAIL\n";
}

fputs($smtp, "QUIT\r\n");
while (!feof($smtp)) { fgets($smtp, 515); }
fclose($smtp);
