<?php
    header('Access-Control-Allow-Origin: *');

    // Verificar que la solicitud sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit;
    }

    // Leer y validar el cuerpo de la solicitud
    $input = json_decode(file_get_contents('php://input'), true);
    if (!isset($input['fromEmail']) || !filter_var($input['fromEmail'], FILTER_VALIDATE_EMAIL) ||
        !isset($input['toEmail']) || !is_array($input['toEmail']) ||
        !isset($input['subject']) || !is_string($input['subject']) ||
        !isset($input['messageHtml']) || !is_string($input['messageHtml']) ||
        !isset($input['bccEmails']) || !is_array($input['bccEmails'])) {
        http_response_code(400);
        exit;
    }

    // Validar cada correo electrónico en los arrays 'toEmail' y 'bccEmails'
    foreach (array_merge($input['toEmail'], $input['bccEmails']) as $email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            exit;
        }
    }

    // Incluir autoload de Composer
    require_once 'vendor/autoload.php';

    // Inicializar cliente Resend
    $resend = Resend::client('re_123456789');

    // Intentar enviar el correo
    try {
        $resend->emails->send([
            'from' => $input['fromEmail'],
            'to' => $input['toEmail'],
            'subject' => $input['subject'],
            'html' => $input['messageHtml'],
            'bcc' => $input['bccEmails'],
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        exit;
    }

    // Respuesta exitosa
    echo "Email enviado con éxito.";
?>