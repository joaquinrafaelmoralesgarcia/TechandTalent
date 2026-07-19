<?php
// Script de diagnóstico SMTP — eliminar del servidor después de usar
header('Content-Type: text/plain; charset=utf-8');

$smtpHost = 'smtp.titan.email';
$smtpUser = 'info@techandtalentservices.com.mx';
$smtpPass = '1903Rafa$';

function read_smtp($sock) {
    $r = '';
    while ($l = fgets($sock, 512)) {
        $r .= $l;
        if (strlen($l) >= 4 && $l[3] === ' ') break;
    }
    return trim($r);
}
function cmd($sock, $c) {
    fwrite($sock, $c . "\r\n");
    return read_smtp($sock);
}
function ok($r, $code) {
    return substr(trim($r), 0, 3) === (string)$code;
}

function test_smtp($host, $port, $ssl, $user, $pass) {
    echo "\n=== Probando puerto $port" . ($ssl ? " (SSL directo)" : " (STARTTLS)") . " ===\n";

    $addr = ($ssl ? 'ssl://' : '') . $host;
    $errno = 0; $errstr = '';
    $sock = @fsockopen($addr, $port, $errno, $errstr, 10);

    if (!$sock) {
        echo "CONEXIÓN: FALLÓ — $errstr ($errno)\n";
        return false;
    }
    echo "CONEXIÓN: OK\n";
    stream_set_timeout($sock, 10);

    $r = read_smtp($sock);
    echo "SALUDO: $r\n";
    if (!ok($r, 220)) { fclose($sock); return false; }

    $r = cmd($sock, 'EHLO localhost');
    echo "EHLO: " . substr($r, 0, 60) . "\n";

    if (!$ssl) {
        $r = cmd($sock, 'STARTTLS');
        echo "STARTTLS: $r\n";
        if (!ok($r, 220)) { fclose($sock); return false; }
        stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $r = cmd($sock, 'EHLO localhost');
        echo "EHLO post-TLS: " . substr($r, 0, 60) . "\n";
    }

    $r = cmd($sock, 'AUTH LOGIN');
    echo "AUTH LOGIN: $r\n";
    if (!ok($r, 334)) { fclose($sock); return false; }

    $r = cmd($sock, base64_encode($user));
    echo "USUARIO: $r\n";
    if (!ok($r, 334)) { fclose($sock); return false; }

    $r = cmd($sock, base64_encode($pass));
    echo "CONTRASEÑA: $r\n";

    cmd($sock, 'QUIT');
    fclose($sock);

    if (ok($r, 235)) {
        echo "✓ AUTENTICACIÓN EXITOSA en puerto $port\n";
        return true;
    } else {
        echo "✗ AUTENTICACIÓN FALLIDA en puerto $port\n";
        return false;
    }
}

echo "=== DIAGNÓSTICO SMTP TITAN MAIL ===\n";
echo "Host: $smtpHost\nUsuario: $smtpUser\n";

$ok465 = test_smtp($smtpHost, 465, true,  $smtpUser, $smtpPass);
$ok587 = test_smtp($smtpHost, 587, false, $smtpUser, $smtpPass);

echo "\n=== RESULTADO ===\n";
echo "Puerto 465 (SSL):      " . ($ok465 ? "✓ FUNCIONA" : "✗ FALLA") . "\n";
echo "Puerto 587 (STARTTLS): " . ($ok587 ? "✓ FUNCIONA" : "✗ FALLA") . "\n";

if (!$ok465 && !$ok587) {
    echo "\nACCIÓN REQUERIDA: Activa 'Third-party access' en Titan Mail Settings → Security\n";
}
