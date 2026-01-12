<?php
session_start();
require_once '../config/db.php';

if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit;
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$erro = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $senha = $_POST['senha'] ?? '';

    if (!empty($usuario) && !empty($senha)) {
        $stmt = $conn->prepare("SELECT id, nome, senha FROM profissionais WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $profissional = $stmt->fetch();

        if ($profissional && md5($senha) === $profissional['senha']) {
            $_SESSION['admin_id'] = $profissional['id'];
            $_SESSION['admin_nome'] = $profissional['nome'];
            $_SESSION['admin_logado'] = true;
            $_SESSION['last_activity'] = time();

            header("Location: dashboard.php");
            exit;
        } else {
            $erro = "Usuário ou senha incorretos.";
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Profissional</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app-container login-container">
        <header class="app-header">
            <div class="header-content">
                <div class="header-brand">
                    <div class="brand-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h1>Acesso Restrito</h1>
                </div>
                <a href="../index.php" class="admin-access-btn">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </header>

        <main class="login-content">
            <div class="login-form-container">
                <div class="login-header">
                    <div class="login-icon">
                        <i class="fas fa-user-md"></i>
                    </div>
                    <h2>Acesso Profissional</h2>
                    <p>Área restrita para profissionais da clínica</p>
                </div>

                <?php if (!empty($erro)): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $erro; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="login-form">
                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-user"></i>
                            <input type="text" id="usuario" name="usuario" class="input-field" required placeholder="Seu usuário">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-key"></i>
                            <input type="password" id="senha" name="senha" class="input-field" required placeholder="Senha">
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Entrar no Painel
                    </button>
                </form>

                <div class="login-footer">
                    <p class="login-note">Esta área é exclusiva para profissionais autorizados.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>