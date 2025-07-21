<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Receber dados JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
    exit;
}

// Validar campos obrigatórios
$required_fields = ['fullname', 'email', 'message'];
foreach ($required_fields as $field) {
    if (empty($input[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Campo '$field' é obrigatório"]);
        exit;
    }
}

// Validar email
if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Sanitizar dados
$fullname = htmlspecialchars(trim($input['fullname']));
$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
$phone = isset($input['phone']) ? htmlspecialchars(trim($input['phone'])) : '';
$position = isset($input['position']) ? htmlspecialchars(trim($input['position'])) : '';
$message = htmlspecialchars(trim($input['message']));

// Configurar email
$to = 'contato@nywera.com.br';
$subject = 'Nova mensagem do formulário de contato - Nywera';

// Criar corpo do email
$email_body = "
Nova mensagem recebida através do formulário de contato do site.

DADOS DO CONTATO:
Nome: $fullname
Email: $email
Telefone: " . ($phone ?: 'Não informado') . "
Cargo: " . ($position ?: 'Não informado') . "

MENSAGEM:
$message

---
Enviado em: " . date('d/m/Y H:i:s') . "
IP: " . $_SERVER['REMOTE_ADDR'] . "
";

// Headers do email
$headers = array(
    'From: noreply@nywera.com.br',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion()
);

// Tentar enviar email
$mail_sent = mail($to, $subject, $email_body, implode("\r\n", $headers));

if ($mail_sent) {
    // Enviar email de confirmação para o usuário
    $user_subject = 'Recebemos sua mensagem - Nywera';
    $user_body = "
Olá $fullname,

Obrigado por entrar em contato conosco! Recebemos sua mensagem e responderemos em breve.

DADOS DO SEU CONTATO:
Nome: $fullname
Email: $email
Telefone: " . ($phone ?: 'Não informado') . "
Cargo: " . ($position ?: 'Não informado') . "

SUA MENSAGEM:
$message

---
Atenciosamente,
Equipe Nywera
contato@nywera.com.br
";

    $user_headers = array(
        'From: contato@nywera.com.br',
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    );

    mail($email, $user_subject, $user_body, implode("\r\n", $user_headers));

    echo json_encode([
        'success' => true, 
        'message' => 'Mensagem enviada com sucesso! Em breve entraremos em contato.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao enviar mensagem. Tente novamente ou entre em contato pelo WhatsApp.'
    ]);
}
?> 