<?php
/**
 * Handler de formulario de contacto — Tech and Talent Services
 * Envía los datos del formulario a info@techandtalentservices.com.mx
 */

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit;
}

function clean($v) {
    return htmlspecialchars(trim($v ?? ''), ENT_QUOTES, 'UTF-8');
}

$nombre    = clean($_POST['nombre'] ?? '');
$empresa   = clean($_POST['empresa'] ?? '');
$email     = trim($_POST['email'] ?? '');
$telefono  = clean($_POST['telefono'] ?? '');
$sector    = clean($_POST['sector'] ?? '');
$mensaje   = clean($_POST['mensaje'] ?? '');

// Validación básica
if ($nombre === '' || $empresa === '' || $email === '' || $mensaje === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Faltan campos requeridos']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Email inválido']);
    exit;
}

$to      = 'info@techandtalentservices.com.mx';
$subject = "Nuevo contacto desde el sitio — $empresa";

$body  = "Nuevo mensaje recibido desde el formulario de contacto:\n\n";
$body .= "Nombre: $nombre\n";
$body .= "Empresa: $empresa\n";
$body .= "Email: $email\n";
$body .= "Teléfono: $telefono\n";
$body .= "Sector: $sector\n";
$body .= "Mensaje:\n$mensaje\n";

$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: Sitio Web <no-reply@techandtalentservices.com.mx>\r\n";
$headers .= "Reply-To: $nombre <$email>\r\n";

$sent = @mail($to, $subject, $body, $headers);

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'No se pudo enviar el correo']);
}
