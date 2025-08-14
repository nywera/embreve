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

$message = '';
$error = '';

// Processar envio de newsletter
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_newsletter'])) {
    $subject = trim($_POST['subject']);
    $content = trim($_POST['content']);
    
    if (empty($subject) || empty($content)) {
        $error = 'Assunto e conteúdo são obrigatórios.';
    } else {
        $active_subscribers = [];
        if (isset($subscribers_data['subscribers'])) {
            foreach ($subscribers_data['subscribers'] as $subscriber) {
                if ($subscriber['active']) {
                    $active_subscribers[] = $subscriber;
                }
            }
        }
        
        if (empty($active_subscribers)) {
            $error = 'Não há inscritos ativos para enviar a newsletter.';
        } else {
            $sent_count = 0;
            $failed_count = 0;
            
            foreach ($active_subscribers as $subscriber) {
                $email_body = "
$content

---
Para se descadastrar, responda este email com 'DESCADASTRAR'.
Atenciosamente,
Equipe nywera
contato@nywera.com.br
";
                
                $headers = array(
                    'From: contato@nywera.com.br',
                    'Content-Type: text/plain; charset=UTF-8',
                    'X-Mailer: PHP/' . phpversion()
                );
                
                if (mail($subscriber['email'], $subject, $email_body, implode("\r\n", $headers))) {
                    $sent_count++;
                } else {
                    $failed_count++;
                }
            }
            
            $message = "Newsletter enviada! $sent_count emails enviados com sucesso.";
            if ($failed_count > 0) {
                $message .= " $failed_count emails falharam.";
            }
        }
    }
}

$total_active = 0;
if (isset($subscribers_data['subscribers'])) {
    foreach ($subscribers_data['subscribers'] as $subscriber) {
        if ($subscriber['active']) {
            $total_active++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Newsletter - nywera</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .header {
            background: #ff6b35;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            text-align: center;
            font-size: 2rem;
        }
        
        .newsletter-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }
        
        .btn {
            background: #ff6b35;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
        }
        
        .btn:hover {
            background: #e55a2b;
        }
        
        .btn-secondary {
            background: #6c757d;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .message {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .stats {
            background: #e9ecef;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #ff6b35;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Enviar Newsletter - nywera</h1>
    </div>
    
    <div class="container">
        <a href="newsletter-admin.php" class="back-link">← Voltar para Administração</a>
        
        <?php if ($message): ?>
            <div class="message success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="stats">
            <strong>Inscritos Ativos: <?php echo $total_active; ?></strong>
        </div>
        
        <div class="newsletter-form">
            <form method="POST">
                <div class="form-group">
                    <label for="subject">Assunto do Email *</label>
                    <input type="text" id="subject" name="subject" required 
                           placeholder="Ex: Novidades da nywera - Janeiro 2025">
                </div>
                
                <div class="form-group">
                    <label for="content">Conteúdo da Newsletter *</label>
                    <textarea id="content" name="content" required 
                              placeholder="Digite o conteúdo da newsletter aqui..."></textarea>
                </div>
                
                <div style="text-align: center;">
                    <button type="submit" name="send_newsletter" class="btn" 
                            onclick="return confirm('Tem certeza que deseja enviar a newsletter para <?php echo $total_active; ?> inscritos?')">
                        Enviar Newsletter
                    </button>
                </div>
            </form>
        </div>
        
        <div style="margin-top: 2rem; padding: 1rem; background: #f8f9fa; border-radius: 4px;">
            <h3>Dicas para uma boa newsletter:</h3>
            <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                <li>Use um assunto atrativo e claro</li>
                <li>Mantenha o conteúdo relevante e útil</li>
                <li>Inclua call-to-actions quando apropriado</li>
                <li>Teste o email antes de enviar em massa</li>
                <li>Respeite a opção de descadastro dos usuários</li>
            </ul>
        </div>
    </div>
</body>
</html> 