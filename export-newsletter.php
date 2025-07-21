<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['newsletter_admin'])) {
    header('Location: newsletter-admin.php');
    exit;
}

$newsletter_file = 'newsletter_subscribers.json';
$subscribers_data = [];

if (file_exists($newsletter_file)) {
    $content = file_get_contents($newsletter_file);
    $subscribers_data = json_decode($content, true);
}

// Configurar headers para download CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="newsletter_subscribers_' . date('Y-m-d_H-i-s') . '.csv"');

// Criar arquivo CSV
$output = fopen('php://output', 'w');

// BOM para UTF-8 (importante para Excel)
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeçalhos do CSV
fputcsv($output, [
    'Email',
    'Data de Inscrição',
    'IP',
    'User Agent',
    'Status',
    'Data de Exportação'
]);

// Dados dos inscritos
if (isset($subscribers_data['subscribers']) && !empty($subscribers_data['subscribers'])) {
    foreach ($subscribers_data['subscribers'] as $subscriber) {
        fputcsv($output, [
            $subscriber['email'],
            $subscriber['subscribed_at'],
            $subscriber['ip'],
            isset($subscriber['user_agent']) ? $subscriber['user_agent'] : '',
            $subscriber['active'] ? 'Ativo' : 'Inativo',
            date('Y-m-d H:i:s')
        ]);
    }
}

fclose($output);
?> 