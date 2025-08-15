<?php
session_start();

$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");

// Verificar se já está logado
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';
$mode = $_GET['mode'] ?? 'login'; // login ou register

// Processar LOGIN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, email, password, is_admin, created_at FROM users WHERE email = ? AND active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Login bem-sucedido
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Atualizar último login
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                header("Location: " . ($_GET['redirect'] ?? 'index.php'));
                exit;
            } else {
                $error = 'Email ou senha incorretos.';
            }
        } else {
            $error = 'Email ou senha incorretos.';
        }
    } else {
        $error = 'Por favor, preencha todos os campos.';
    }
}

// Processar REGISTRO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validações
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Por favor, preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido.';
    } elseif (strlen($password) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($password !== $confirm_password) {
        $error = 'As senhas não coincidem.';
    } else {
        // Verificar se email já existe
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $error = 'Este email já está cadastrado.';
        } else {
            // Criar usuário
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($insert_stmt->execute()) {
                $success = 'Conta criada com sucesso! Você pode fazer login agora.';
                $mode = 'login';
            } else {
                $error = 'Erro ao criar conta. Tente novamente.';
            }
        }
    }
}

// Processar ESQUECI SENHA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forgot_password'])) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!empty($email)) {
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            // Gerar token de reset
            $reset_token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $update_stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $update_stmt->bind_param("sss", $reset_token, $expires, $email);
            $update_stmt->execute();
            
            // Aqui você enviaria o email (simulado)
            $success = 'Se este email estiver cadastrado, você receberá instruções para redefinir sua senha.';
        } else {
            $success = 'Se este email estiver cadastrado, você receberá instruções para redefinir sua senha.';
        }
    } else {
        $error = 'Por favor, digite seu email.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mode === 'register' ? 'Registrarse' : 'Login' ?> - Citas</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #0f0f0f 100%);
            color: white;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Background animado */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(255, 107, 107, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(254, 202, 87, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(72, 219, 251, 0.1) 0%, transparent 50%);
            animation: backgroundMove 20s ease-in-out infinite;
            z-index: -1;
        }

        @keyframes backgroundMove {
            0%, 100% { transform: translateX(0) translateY(0); }
            33% { transform: translateX(-20px) translateY(-20px); }
            66% { transform: translateX(20px) translateY(-10px); }
        }

        /* Container principal */
        .login-container {
            background: linear-gradient(145deg, rgba(42, 42, 42, 0.95), rgba(30, 30, 30, 0.95));
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 2rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 
                0 20px 40px rgba(0, 0, 0, 0.4),
                0 10px 25px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #ff6b6b, #feca57, #48dbfb);
            z-index: 1;
        }

        /* Logo/Header */
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            z-index: 2;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .login-subtitle {
            color: #aaa;
            font-size: 1rem;
        }

        /* Tabs */
        .tabs {
            display: flex;
            margin-bottom: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 0.3rem;
            position: relative;
        }

        .tab {
            flex: 1;
            padding: 0.8rem 1rem;
            text-align: center;
            cursor: pointer;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 600;
            position: relative;
            z-index: 2;
        }

        .tab.active {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        .tab:not(.active) {
            color: #888;
        }

        .tab:not(.active):hover {
            color: #ccc;
            background: rgba(255, 255, 255, 0.05);
        }

        /* Forms */
        .form-container {
            position: relative;
        }

        .form {
            display: none;
            animation: fadeInUp 0.5s ease;
        }

        .form.active {
            display: block;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #ddd;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 1rem 1rem 1rem 2.8rem;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input::placeholder {
            color: #666;
        }

        .form-group input:focus {
            border-color: #ff6b6b;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
            transform: translateY(-1px);
        }

        .form-group .icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 1.1rem;
            margin-top: 0.75rem;
        }

        .form-group input:focus + .icon {
            color: #ff6b6b;
        }

        /* Botões */
        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 15px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
            margin-bottom: 1rem;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            color: #aaa;
            border: 2px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 1rem;
        }

        .btn-secondary:hover {
            border-color: #ff6b6b;
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
        }

        /* Links */
        .form-links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .form-links a {
            color: #ff6b6b;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-links a:hover {
            color: #feca57;
            text-decoration: underline;
        }

        /* Alertas */
        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .alert-error {
            background: rgba(255, 71, 87, 0.2);
            border: 1px solid rgba(255, 71, 87, 0.3);
            color: #ff6b6b;
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.2);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: #00ff88;
        }

        /* Back to home */
        .back-home {
            position: absolute;
            top: 2rem;
            left: 2rem;
            z-index: 1000;
        }

        .back-home a {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #aaa;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .back-home a:hover {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            transform: translateX(-5px);
        }

        /* Loading */
        .loading {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 107, 107, 0.3);
            border-top: 4px solid #ff6b6b;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .login-container {
                margin: 1rem;
                padding: 1.5rem;
                max-width: none;
            }

            .logo {
                font-size: 2rem;
            }

            .back-home {
                top: 1rem;
                left: 1rem;
            }
        }

        /* Password strength */
        .password-strength {
            margin-top: 0.5rem;
            font-size: 0.8rem;
        }

        .strength-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            margin-top: 0.3rem;
            overflow: hidden;
        }

        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }

        .strength-weak { width: 25%; background: #ff4757; }
        .strength-fair { width: 50%; background: #ffa502; }
        .strength-good { width: 75%; background: #26de81; }
        .strength-strong { width: 100%; background: #00ff88; }

        /* Social Login (para futuro) */
        .social-login {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .social-login p {
            color: #888;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .social-buttons {
            display: flex;
            gap: 1rem;
        }

        .social-btn {
            flex: 1;
            padding: 0.8rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: transparent;
            color: #aaa;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .social-btn:hover {
            border-color: #ff6b6b;
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
        }
    </style>
</head>
<body>
    <!-- Voltar para home -->
    <div class="back-home">
        <a href="index.php">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>
    </div>

    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="logo">
                🔥 Citas
            </div>
            <p class="login-subtitle">Acesse sua conta ou crie uma nova</p>
        </div>

        <!-- Tabs -->
        <div class="tabs">
            <div class="tab <?= $mode === 'login' ? 'active' : '' ?>" onclick="switchTab('login')">
                <i class="fas fa-sign-in-alt"></i> Login
            </div>
            <div class="tab <?= $mode === 'register' ? 'active' : '' ?>" onclick="switchTab('register')">
                <i class="fas fa-user-plus"></i> Registrar
            </div>
        </div>

        <!-- Alertas -->
        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>

        <!-- Forms -->
        <div class="form-container">
            <!-- LOGIN FORM -->
            <form class="form <?= $mode === 'login' ? 'active' : '' ?>" id="loginForm" method="POST">
                <div class="form-group">
                    <label for="login_email">Email</label>
                    <input type="email" id="login_email" name="email" placeholder="seu@email.com" required>
                    <i class="fas fa-envelope icon"></i>
                </div>

                <div class="form-group">
                    <label for="login_password">Senha</label>
                    <input type="password" id="login_password" name="password" placeholder="Sua senha" required>
                    <i class="fas fa-lock icon"></i>
                </div>

                <button type="submit" name="login" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Entrar
                </button>

                <div class="form-links">
                    <a href="javascript:void(0)" onclick="switchTab('forgot')">Esqueci minha senha</a>
                </div>
            </form>

            <!-- REGISTER FORM -->
            <form class="form <?= $mode === 'register' ? 'active' : '' ?>" id="registerForm" method="POST">
                <div class="form-group">
                    <label for="register_username">Nome de usuário</label>
                    <input type="text" id="register_username" name="username" placeholder="Seu nome de usuário" required>
                    <i class="fas fa-user icon"></i>
                </div>

                <div class="form-group">
                    <label for="register_email">Email</label>
                    <input type="email" id="register_email" name="email" placeholder="seu@email.com" required>
                    <i class="fas fa-envelope icon"></i>
                </div>

                <div class="form-group">
                    <label for="register_password">Senha</label>
                    <input type="password" id="register_password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6" onkeyup="checkPasswordStrength()">
                    <i class="fas fa-lock icon"></i>
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span id="strengthText"></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirmar Senha</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Digite a senha novamente" required>
                    <i class="fas fa-lock icon"></i>
                </div>

                <button type="submit" name="register" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i>
                    Criar Conta
                </button>
            </form>

            <!-- FORGOT PASSWORD FORM -->
            <form class="form" id="forgotForm" method="POST">
                <div class="form-group">
                    <label for="forgot_email">Email</label>
                    <input type="email" id="forgot_email" name="email" placeholder="Digite seu email" required>
                    <i class="fas fa-envelope icon"></i>
                </div>

                <button type="submit" name="forgot_password" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i>
                    Enviar Instruções
                </button>

                <button type="button" class="btn btn-secondary" onclick="switchTab('login')">
                    <i class="fas fa-arrow-left"></i>
                    Voltar ao Login
                </button>
            </form>
        </div>

        <!-- Loading -->
        <div class="loading" id="loading">
            <div class="spinner"></div>
        </div>

        <!-- Social Login (para futuro) -->
        <!--
        <div class="social-login">
            <p>Ou entre com:</p>
            <div class="social-buttons">
                <button class="social-btn">
                    <i class="fab fa-google"></i> Google
                </button>
                <button class="social-btn">
                    <i class="fab fa-facebook"></i> Facebook
                </button>
            </div>
        </div>
        -->
    </div>

    <script>
        // Alternar entre tabs
        function switchTab(tab) {
            // Remover active de todas as tabs e forms
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.form').forEach(f => f.classList.remove('active'));
            
            // Ativar tab e form corretos
            document.querySelector(`.tab:nth-child(${tab === 'register' ? '2' : '1'})`).classList.add('active');
            document.getElementById(tab + 'Form').classList.add('active');
            
            // Atualizar URL
            const url = new URL(window.location);
            url.searchParams.set('mode', tab);
            window.history.pushState({}, '', url);
        }

        // Verificar força da senha
        function checkPasswordStrength() {
            const password = document.getElementById('register_password').value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let text = '';
            let className = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            
            switch(strength) {
                case 0:
                case 1:
                    text = 'Muito fraca';
                    className = 'strength-weak';
                    break;
                case 2:
                    text = 'Fraca';
                    className = 'strength-fair';
                    break;
                case 3:
                    text = 'Boa';
                    className = 'strength-good';
                    break;
                case 4:
                    text = 'Forte';
                    className = 'strength-strong';
                    break;
            }
            
            strengthFill.className = 'strength-fill ' + className;
            strengthText.textContent = text;
            strengthText.style.color = getComputedStyle(strengthFill).backgroundColor;
        }

        // Loading nos forms
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                document.getElementById('loading').style.display = 'block';
            });
        });

        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateX(-20px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Configurar tab inicial baseado na URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const mode = urlParams.get('mode') || 'login';
            if (mode === 'register') {
                switchTab('register');
            }
        });
    </script>
</body>
</html>