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

if (!$input || !isset($input['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email é obrigatório']);
    exit;
}

// Validar email
$email = filter_var($input['email'], FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

// Arquivo para armazenar os emails
$newsletter_file = 'newsletter_subscribers.json';

// Função para carregar subscribers existentes
function loadSubscribers($filename) {
    if (file_exists($filename)) {
        $content = file_get_contents($filename);
        $data = json_decode($content, true);
        return $data ? $data : ['subscribers' => [], 'last_updated' => ''];
    }
    return ['subscribers' => [], 'last_updated' => ''];
}

// Função para salvar subscribers
function saveSubscribers($filename, $data) {
    $data['last_updated'] = date('Y-m-d H:i:s');
    return file_put_contents($filename, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Carregar subscribers existentes
$subscribers_data = loadSubscribers($newsletter_file);

// Verificar se o email já está inscrito
$email_exists = false;
foreach ($subscribers_data['subscribers'] as $subscriber) {
    if (strtolower($subscriber['email']) === strtolower($email)) {
        $email_exists = true;
        break;
    }
}

if ($email_exists) {
    echo json_encode([
        'success' => false, 
        'message' => 'Este email já está inscrito na nossa newsletter!'
    ]);
    exit;
}

// Adicionar novo subscriber
$new_subscriber = [
    'email' => $email,
    'subscribed_at' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
    'active' => true
];

$subscribers_data['subscribers'][] = $new_subscriber;

// Salvar no arquivo
if (saveSubscribers($newsletter_file, $subscribers_data)) {
    // Enviar email de confirmação para o usuário
    $user_subject = 'Inscrição na Newsletter - Nywera';
    $user_body = "
Olá!

Obrigado por se inscrever na nossa newsletter! Você receberá nossas novidades, dicas de design e insights sobre UX/UI diretamente no seu email.

O que você pode esperar:
• Dicas exclusivas de design
• Novidades sobre nossos projetos
• Insights sobre UX/UI
• Conteúdo relevante sobre branding

Se você não solicitou esta inscrição, pode se descadastrar respondendo este email.

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

    // Enviar notificação para a Nywera
    $nywera_subject = 'Nova inscrição na Newsletter - Nywera';
    $nywera_body = "
Nova inscrição na newsletter recebida.

DADOS DO INSCRITO:
Email: $email
Data: " . date('d/m/Y H:i:s') . "
IP: " . $_SERVER['REMOTE_ADDR'] . "

TOTAL DE INSCRITOS: " . count($subscribers_data['subscribers']) . "

---
Sistema de Newsletter - Nywera
";

    $nywera_headers = array(
        'From: noreply@nywera.com.br',
        'Content-Type: text/plain; charset=UTF-8',
        'X-Mailer: PHP/' . phpversion()
    );

    mail('contato@nywera.com.br', $nywera_subject, $nywera_body, implode("\r\n", $nywera_headers));

    echo json_encode([
        'success' => true, 
        'message' => 'Inscrição realizada com sucesso! Você receberá nossas novidades em breve.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Erro ao processar inscrição. Tente novamente.'
    ]);
}
?> 