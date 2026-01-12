<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento de Consulta</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="header-content">
                <div class="header-brand">
                    <div class="brand-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h1>Agendar Consulta</h1>
                </div>
                <a href="telas/login.php" class="admin-access-btn" title="Acesso administrativo">
                    <i class="fas fa-lock"></i>
                    <span>Acesso</span>
                </a>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
        </header>

        <main class="chat-container" id="chat-container">
            <div class="welcome-message">
                <div class="welcome-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="welcome-text">
                    <h3>Assistente de Agendamento</h3>
                    <p>Estou aqui para ajudá-lo a agendar sua consulta de forma rápida e fácil.</p>
                </div>
            </div>
        </main>

        <footer class="input-area" id="input-area">
        </footer>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>