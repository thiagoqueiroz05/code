<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$success = '';
$error = '';

// Processar logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Processar atualização de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $bio = trim($_POST['bio']);
    $phone = trim($_POST['phone']);
    $telegram = trim($_POST['telegram']);
    
    if (!empty($username) && !empty($email)) {
        // Verificar se email já existe (exceto o próprio usuário)
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_stmt->bind_param("si", $email, $_SESSION['user_id']);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows === 0) {
            $update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, bio = ?, phone = ?, telegram = ? WHERE id = ?");
            $update_stmt->bind_param("sssssi", $username, $email, $bio, $phone, $telegram, $_SESSION['user_id']);
            
            if ($update_stmt->execute()) {
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
                $success = 'Perfil atualizado com sucesso!';
                
                // Recarregar dados do usuário
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Erro ao atualizar perfil.';
            }
        } else {
            $error = 'Este email já está em uso.';
        }
    } else {
        $error = 'Nome de usuário e email são obrigatórios.';
    }
}

// Processar mudança de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (password_verify($current_password, $user['password'])) {
        if (strlen($new_password) >= 6) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $pwd_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $pwd_stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
                
                if ($pwd_stmt->execute()) {
                    $success = 'Senha alterada com sucesso!';
                } else {
                    $error = 'Erro ao alterar senha.';
                }
            } else {
                $error = 'As senhas não coincidem.';
            }
        } else {
            $error = 'A nova senha deve ter pelo menos 6 caracteres.';
        }
    } else {
        $error = 'Senha atual incorreta.';
    }
}

// Buscar atividade recente
$activity_stmt = $conn->prepare("SELECT * FROM user_activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$activity_stmt->bind_param("i", $_SESSION['user_id']);
$activity_stmt->execute();
$activities = $activity_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Citas</title>
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
        }

        /* Header */
        .header {
            background: rgba(20, 20, 20, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .logout-btn {
            color: #ff6b6b;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255, 107, 107, 0.2);
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        /* Cards */
        .card {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Profile Card */
        .profile-card {
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 1rem;
            position: relative;
            overflow: hidden;
        }

        .profile-info h2 {
            margin-bottom: 0.5rem;
            color: white;
        }

        .profile-info p {
            color: #aaa;
            margin-bottom: 0.5rem;
        }

        .profile-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: linear-gradient(135deg, #48dbfb, #0abde3);
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-top: 1rem;
        }

        /* Forms */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #ddd;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: white;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #ff6b6b;
            background: rgba(255, 255, 255, 0.12);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        /* Buttons */
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Tabs */
        .tabs {
            display: flex;
            margin-bottom: 2rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 0.3rem;
        }

        .tab {
            flex: 1;
            padding: 0.8rem 1rem;
            text-align: center;
            cursor: pointer;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .tab.active {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
        }

        .tab:not(.active) {
            color: #888;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Stats */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(254, 202, 87, 0.1));
            border: 1px solid rgba(255, 107, 107, 0.2);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            color: #aaa;
            margin-top: 0.5rem;
        }

        /* Activity */
        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #48dbfb, #0abde3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .activity-content {
            flex: 1;
        }

        .activity-time {
            font-size: 0.8rem;
            color: #888;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.2);
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: #00ff88;
        }

        .alert-error {
            background: rgba(255, 71, 87, 0.2);
            border: 1px solid rgba(255, 71, 87, 0.3);
            color: #ff6b6b;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 1rem;
            }

            .header-content {
                padding: 0 1rem;
            }

            .user-info span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">🔥 Citas</a>
            
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    </div>
                    <span><?= htmlspecialchars($user['username']) ?></span>
                </div>
                <a href="?logout=1" class="logout-btn" onclick="return confirm('Tem certeza que deseja sair?')">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </header>

    <div class="container">
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

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $user['is_admin'] ? 'Admin' : 'User' ?></div>
                <div class="stat-label">Tipo de Conta</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
                <div class="stat-label">Membro desde</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $user['last_login'] ? date('d/m/Y', strtotime($user['last_login'])) : 'Nunca' ?></div>
                <div class="stat-label">Último Login</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $user['email_verified'] ? 'Sim' : 'Não' ?></div>
                <div class="stat-label">Email Verificado</div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Profile Card -->
            <div class="card profile-card">
                <div class="card-header">
                    <i class="fas fa-user"></i>
                    Meu Perfil
                </div>
                
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['username'], 0, 2)) ?>
                </div>
                
                <div class="profile-info">
                    <h2><?= htmlspecialchars($user['username']) ?></h2>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                    <?php if ($user['bio']): ?>
                        <p style="color: #ccc; font-style: italic;"><?= htmlspecialchars($user['bio']) ?></p>
                    <?php endif; ?>
                    <?php if ($user['is_admin']): ?>
                        <div class="profile-badge">Administrador</div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 1rem;">
                        <a href="user_profile.php?id=<?= $user['id'] ?>" class="btn btn-secondary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                            <i class="fas fa-eye"></i> Ver Perfil Público
                        </a>
                    </div>
                </div>
            </div>

            <!-- Settings Card -->
            <div class="card">
                <div class="tabs">
                    <div class="tab active" onclick="switchTab('profile')">
                        <i class="fas fa-user-edit"></i> Editar Perfil
                    </div>
                    <div class="tab" onclick="switchTab('password')">
                        <i class="fas fa-lock"></i> Senha
                    </div>
                </div>

                <!-- Edit Profile Tab -->
                <div class="tab-content active" id="profileTab">
                    <form method="POST">
                        <div class="form-group">
                            <label for="username">Nome de usuário</label>
                            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio</label>
                            <textarea id="bio" name="bio" placeholder="Conte um pouco sobre você..."><?= htmlspecialchars($user['bio']) ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="phone">Telefone</label>
                            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="+55 11 99999-9999">
                        </div>

                        <div class="form-group">
                            <label for="telegram">Telegram</label>
                            <input type="text" id="telegram" name="telegram" value="<?= htmlspecialchars($user['telegram']) ?>" placeholder="@seu_usuario">
                        </div>

                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                    </form>
                </div>

                <!-- Change Password Tab -->
                <div class="tab-content" id="passwordTab">
                    <form method="POST">
                        <div class="form-group">
                            <label for="current_password">Senha Atual</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Nova Senha</label>
                            <input type="password" id="new_password" name="new_password" minlength="6" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirmar Nova Senha</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>

                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="fas fa-key"></i> Alterar Senha
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-history"></i>
                Atividade Recente
            </div>

            <?php if ($activities->num_rows > 0): ?>
                <?php while ($activity = $activities->fetch_assoc()): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-<?= match($activity['action']) {
                            'login' => 'sign-in-alt',
                            'logout' => 'sign-out-alt',
                            'profile_update' => 'user-edit',
                            'password_change' => 'key',
                            default => 'circle'
                        } ?>"></i>
                    </div>
                    <div class="activity-content">
                        <div><?= htmlspecialchars($activity['description'] ?: $activity['action']) ?></div>
                        <div class="activity-time"><?= date('d/m/Y H:i', strtotime($activity['created_at'])) ?></div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p style="color: #888; text-align: center; padding: 2rem;">Nenhuma atividade registrada.</p>
            <?php endif; ?>
        </div>

        <?php if ($user['is_admin']): ?>
        <!-- Admin Panel -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-cog"></i>
                Painel Administrativo
            </div>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="admin_escorts.php" class="btn btn-secondary">
                    <i class="fas fa-users"></i> Gerenciar Escorts
                </a>
                <a href="admin_cidades.php" class="btn btn-secondary">
                    <i class="fas fa-map"></i> Gerenciar Cidades
                </a>
                <a href="#" class="btn btn-secondary">
                    <i class="fas fa-chart-bar"></i> Estatísticas
                </a>
                <a href="#" class="btn btn-secondary">
                    <i class="fas fa-users-cog"></i> Gerenciar Usuários
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Alternar entre tabs
        function switchTab(tab) {
            // Remover active de todas as tabs
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Ativar tab clicada
            event.target.classList.add('active');
            document.getElementById(tab + 'Tab').classList.add('active');
        }

        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Validação de senha
        document.getElementById('confirm_password')?.addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('As senhas não coincidem');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>