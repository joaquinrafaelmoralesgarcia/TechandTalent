<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

function clean($v) {
    return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

$nombre   = clean($_POST['nombre']   ?? '');
$empresa  = clean($_POST['empresa']  ?? '');
$email    = trim($_POST['email']     ?? '');
$telefono = clean($_POST['telefono'] ?? '');
$sector   = clean($_POST['sector']   ?? '');
$mensaje  = clean($_POST['mensaje']  ?? '');

if ($nombre === '' || $empresa === '' || $email === '' || $mensaje === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos: nombre, empresa, email y mensaje']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email inválido']);
    exit;
}

// ── Configuración SMTP Titan Mail ─────────────────────────────────────────
$smtpHost  = 'smtp.titan.email';
$smtpPort  = 587;
$smtpUser  = 'info@techandtalentservices.com.mx';
$smtpPass  = '1903Rafa$';
$fromEmail = 'info@techandtalentservices.com.mx';
$fromName  = 'Tech and Talent Services';
$toEmail   = 'info@techandtalentservices.com.mx';

$subject = "Nuevo contacto: {$nombre} - {$empresa}";

$body  = "Nuevo mensaje desde el formulario de contacto del sitio web.\n\n";
$body .= "Nombre:   {$nombre}\n";
$body .= "Empresa:  {$empresa}\n";
$body .= "Email:    {$email}\n";
$body .= "Teléfono: {$telefono}\n";
$body .= "Sector:   {$sector}\n\n";
$body .= "Mensaje:\n{$mensaje}\n";

// ── Cliente SMTP nativo (sin dependencias) ─────────────────────────────────
function smtp_read($sock) {
    $response = '';
    while ($line = fgets($sock, 512)) {
        $response .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') break;
    }
    return $response;
}

function smtp_cmd($sock, $cmd) {
    fwrite($sock, $cmd . "\r\n");
    return smtp_read($sock);
}

function smtp_ok($response, $code) {
    return substr(trim($response), 0, 3) === (string)$code;
}

$error = null;
$sock  = @fsockopen($smtpHost, $smtpPort, $errno, $errstr, 15);

if (!$sock) {
    $error = "No se pudo conectar a {$smtpHost}:{$smtpPort} — {$errstr} ({$errno})";
} else {
    stream_set_timeout($sock, 15);

    $r = smtp_read($sock);
    if (!smtp_ok($r, 220)) $error = "Saludo SMTP inesperado: {$r}";

    if (!$error) {
        $r = smtp_cmd($sock, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        if (!smtp_ok($r, 250)) $error = "EHLO falló: {$r}";
    }

    if (!$error) {
        $r = smtp_cmd($sock, 'STARTTLS');
        if (!smtp_ok($r, 220)) $error = "STARTTLS falló: {$r}";
    }

    if (!$error) {
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            $error = "No se pudo activar TLS";
        }
    }

    if (!$error) {
        $r = smtp_cmd($sock, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'));
        if (!smtp_ok($r, 250)) $error = "EHLO post-TLS falló: {$r}";
    }

    if (!$error) {
        $r = smtp_cmd($sock, 'AUTH LOGIN');
        if (!smtp_ok($r, 334)) $error = "AUTH LOGIN falló: {$r}";
    }

    if (!$error) {
        $r = smtp_cmd($sock, base64_encode($smtpUser));
        if (!smtp_ok($r, 334)) $error = "Usuario SMTP rechazado: {$r}";
    }

    if (!$error) {
        $r = smtp_cmd($sock, base64_encode($smtpPass));
        if (!smtp_ok($r, 235)) $error = "Contraseña SMTP incorrecta";
    }

    if (!$error) {
        $r = smtp_cmd($sock, "MAIL FROM:<{$fromEmail}>");
        if (!smtp_ok($r, 250)) $error = "MAIL FROM rechazado: {$r}";
    }

    if (!$error) {
        $r = smtp_cmd($sock, "RCPT TO:<{$toEmail}>");
        if (!smtp_ok($r, 250)) $error = "RCPT TO rechazado: {$r}";
    }

    if (!$error) {
        $r = smtp_cmd($sock, 'DATA');
        if (!smtp_ok($r, 354)) $error = "DATA rechazado: {$r}";
    }

    if (!$error) {
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $encodedFrom    = '=?UTF-8?B?' . base64_encode($fromName) . '?=';
        $encodedReplyTo = '=?UTF-8?B?' . base64_encode($nombre) . '?=';

        $msg  = 'Date: ' . date('r') . "\r\n";
        $msg .= "From: {$encodedFrom} <{$fromEmail}>\r\n";
        $msg .= "To: {$toEmail}\r\n";
        $msg .= "Reply-To: {$encodedReplyTo} <{$email}>\r\n";
        $msg .= "Subject: {$encodedSubject}\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n";
        $msg .= "\r\n";
        $msg .= chunk_split(base64_encode($body));
        $msg .= "\r\n.\r\n";

        fwrite($sock, $msg);
        $r = smtp_read($sock);
        if (!smtp_ok($r, 250)) $error = "Error al enviar: {$r}";
    }

    smtp_cmd($sock, 'QUIT');
    fclose($sock);
}

if ($error) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $error]);
} else {
    echo json_encode(['ok' => true]);
}
