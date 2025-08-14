<?php
// Verificação básica de segurança (você pode melhorar isso)
session_start();

// Senha simples para acesso (recomendo usar autenticação mais robusta)
$admin_password = 'nywera2025';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['newsletter_admin'] = true;
    } else {
        $error = 'Senha incorreta';
    }
}

if (isset($_GET['logout'])) {
    unset($_SESSION['newsletter_admin']);
    header('Location: newsletter-admin.php');
    exit;
}

$newsletter_file = 'newsletter_subscribers.json';
$subscribers_data = [];

if (file_exists($newsletter_file)) {
    $content = file_get_contents($newsletter_file);
    $subscribers_data = json_decode($content, true);
}

$total_subscribers = isset($subscribers_data['subscribers']) ? count($subscribers_data['subscribers']) : 0;
$active_subscribers = 0;

if (isset($subscribers_data['subscribers'])) {
    foreach ($subscribers_data['subscribers'] as $subscriber) {
        if ($subscriber['active']) {
            $active_subscribers++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração Newsletter - nywera</title>
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
            max-width: 1200px;
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
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #ff6b35;
        }
        
        .login-form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 400px;
            margin: 2rem auto;
        }
        
        .login-form input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .login-form button {
            width: 100%;
            padding: 0.75rem;
            background: #ff6b35;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .subscribers-table {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .subscribers-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .subscribers-table th,
        .subscribers-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .subscribers-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
        
        .subscribers-table tr:hover {
            background: #f8f9fa;
        }
        
        .export-btn {
            background: #28a745;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .error {
            color: #dc3545;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .subscribers-table {
                overflow-x: auto;
            }
            
            .subscribers-table table {
                min-width: 600px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Administração Newsletter - nywera</h1>
    </div>
    
    <div class="container">
        <?php if (!isset($_SESSION['newsletter_admin'])): ?>
            <!-- Formulário de Login -->
            <div class="login-form">
                <h2>Login Administrativo</h2>
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="password" name="password" placeholder="Digite a senha" required>
                    <button type="submit">Entrar</button>
                </form>
            </div>
        <?php else: ?>
            <!-- Dashboard Administrativo -->
            <div style="text-align: right;">
                <a href="?logout=1" class="logout-btn">Sair</a>
            </div>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_subscribers; ?></div>
                    <div>Total de Inscritos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $active_subscribers; ?></div>
                    <div>Inscritos Ativos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo isset($subscribers_data['last_updated']) ? date('d/m/Y', strtotime($subscribers_data['last_updated'])) : 'N/A'; ?></div>
                    <div>Última Atualização</div>
                </div>
            </div>
            
            <a href="export-newsletter.php" class="export-btn">Exportar Lista (CSV)</a>
            <a href="send-newsletter.php" class="export-btn" style="background: #17a2b8; margin-left: 1rem;">Enviar Newsletter</a>
            
            <div class="subscribers-table">
                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Data de Inscrição</th>
                            <th>IP</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($subscribers_data['subscribers']) && !empty($subscribers_data['subscribers'])): ?>
                            <?php foreach ($subscribers_data['subscribers'] as $subscriber): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($subscriber['subscribed_at'])); ?></td>
                                    <td><?php echo htmlspecialchars($subscriber['ip']); ?></td>
                                    <td>
                                        <span style="color: <?php echo $subscriber['active'] ? '#28a745' : '#dc3545'; ?>">
                                            <?php echo $subscriber['active'] ? 'Ativo' : 'Inativo'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 2rem;">
                                    Nenhum inscrito encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 