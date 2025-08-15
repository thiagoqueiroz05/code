<?php
include 'auth.php';

$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");

// Capturar ID do usuário pela URL
$user_id = isset($_GET['id']) ? intval($_GET['id']) : ($_SESSION['user_id'] ?? 0);

if ($user_id <= 0) {
    header("HTTP/1.0 404 Not Found");
    echo "<h2>Usuário não encontrado.</h2>";
    exit;
}

// Buscar dados do usuário
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND active = 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("HTTP/1.0 404 Not Found");
    echo "<h2>Usuário não encontrado.</h2>";
    exit;
}

// Verificar se é o próprio perfil
$is_own_profile = isLoggedIn() && $_SESSION['user_id'] == $user_id;

// Buscar estatísticas do usuário
$stats = [
    'join_date' => date('M Y', strtotime($user['created_at'])),
    'last_seen' => $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca',
    'profile_views' => rand(150, 500), // Simulated for now
    'total_posts' => rand(5, 25) // Simulated for now
];

// Registrar visualização do perfil (se não for o próprio)
if (!$is_own_profile && isLoggedIn()) {
    logActivity('profile_view', "Visualizou perfil de {$user['username']}", $_SESSION['user_id']);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['username']) ?> - Perfil | Citas</title>
    <meta name="description" content="Perfil de <?= htmlspecialchars($user['username']) ?> em Citas">
    
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
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            font-size: 1.8rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        /* Profile Container */
        .profile-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        /* Profile Header */
        .profile-header {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(135deg, #ff6b6b, #feca57, #48dbfb);
            z-index: 1;
        }

        .profile-content {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 2rem;
            align-items: center;
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
            position: relative;
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4);
        }

        .profile-avatar::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b6b, #feca57, #48dbfb);
            z-index: -1;
            animation: rotate 3s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .profile-info {
            flex: 1;
        }

        .profile-username {
            font-size: 2.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .profile-email {
            color: #aaa;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .profile-bio {
            color: #ddd;
            line-height: 1.6;
            font-size: 1.1rem;
        }

        .profile-badges {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
            text-align: center;
            min-width: 120px;
        }

        .badge-admin {
            background: linear-gradient(135deg, #ff0844, #ff6b6b);
            color: white;
            animation: glow-admin 2s ease-in-out infinite alternate;
        }

        .badge-member {
            background: linear-gradient(135deg, #48dbfb, #0abde3);
            color: white;
        }

        .badge-verified {
            background: linear-gradient(135deg, #00ff88, #26de81);
            color: white;
        }

        @keyframes glow-admin {
            0% { box-shadow: 0 0 10px rgba(255, 8, 68, 0.5); }
            100% { box-shadow: 0 0 20px rgba(255, 8, 68, 0.8); }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(255, 107, 107, 0.2);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #aaa;
            font-size: 1rem;
        }

        /* Contact Section */
        .contact-section {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .contact-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .contact-phone .contact-icon {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }

        .contact-telegram .contact-icon {
            background: linear-gradient(135deg, #0088cc, #54a0ff);
        }

        .contact-email .contact-icon {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.4);
        }

        .btn-secondary {
            background: transparent;
            color: #aaa;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            border-color: #ff6b6b;
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
        }

        /* Back Button */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #aaa;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.05);
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            width: fit-content;
        }

        .back-btn:hover {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            transform: translateX(-5px);
        }

        /* Status Online */
        .online-status {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #00ff88;
            animation: pulse-status 2s infinite;
            box-shadow: 0 0 10px #00ff88;
        }

        @keyframes pulse-status {
            0% { 
                box-shadow: 0 0 10px #00ff88;
                transform: scale(1);
            }
            50% { 
                box-shadow: 0 0 20px #00ff88;
                transform: scale(1.2);
            }
            100% { 
                box-shadow: 0 0 10px #00ff88;
                transform: scale(1);
            }
        }

        .status-text {
            color: #00ff88;
            font-weight: 600;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .profile-content {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 1.5rem;
            }

            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 2.5rem;
                margin: 0 auto;
            }

            .profile-username {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .contact-info {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .profile-container {
                padding: 0 0.5rem;
            }

            .profile-header {
                padding: 2rem 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">🔥 Citas</a>
            
            <?php renderUserMenuCSS(); ?>
            <?php renderUserMenu(); ?>
        </div>
    </header>

    <div class="profile-container">
        <!-- Back Button -->
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-content">
                <div class="profile-avatar">
                    <?= strtoupper(substr($user['username'], 0, 2)) ?>
                </div>

                <div class="profile-info">
                    <h1 class="profile-username"><?= htmlspecialchars($user['username']) ?></h1>
                    <div class="profile-email"><?= htmlspecialchars($user['email']) ?></div>
                    
                    <?php if ($user['bio']): ?>
                        <div class="profile-bio"><?= nl2br(htmlspecialchars($user['bio'])) ?></div>
                    <?php else: ?>
                        <div class="profile-bio" style="color: #666; font-style: italic;">
                            Este usuário ainda não adicionou uma biografia.
                        </div>
                    <?php endif; ?>

                    <div class="online-status">
                        <div class="status-dot"></div>
                        <span class="status-text">Online</span>
                    </div>
                </div>

                <div class="profile-badges">
                    <?php if ($user['is_admin']): ?>
                        <div class="badge badge-admin">
                            <i class="fas fa-crown"></i> Administrador
                        </div>
                    <?php else: ?>
                        <div class="badge badge-member">
                            <i class="fas fa-user"></i> Membro
                        </div>
                    <?php endif; ?>

                    <?php if ($user['email_verified']): ?>
                        <div class="badge badge-verified">
                            <i class="fas fa-check-circle"></i> Verificado
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-value"><?= $stats['join_date'] ?></div>
                <div class="stat-label">Membro desde</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-value"><?= $stats['last_seen'] ?></div>
                <div class="stat-label">Última atividade</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-value"><?= $stats['profile_views'] ?></div>
                <div class="stat-label">Visualizações</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-value"><?= $stats['total_posts'] ?></div>
                <div class="stat-label">Contribuições</div>
            </div>
        </div>

        <!-- Contact Info -->
        <?php if ($user['phone'] || $user['telegram'] || $is_own_profile): ?>
        <div class="contact-section">
            <h2 class="section-title">
                <i class="fas fa-address-card"></i>
                Informações de Contato
            </h2>

            <div class="contact-info">
                <div class="contact-item contact-email">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600;">Email</div>
                        <div style="color: #aaa;"><?= htmlspecialchars($user['email']) ?></div>
                    </div>
                </div>

                <?php if ($user['phone']): ?>
                <div class="contact-item contact-phone">
                    <div class="contact-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600;">WhatsApp</div>
                        <div style="color: #aaa;"><?= htmlspecialchars($user['phone']) ?></div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($user['telegram']): ?>
                <div class="contact-item contact-telegram">
                    <div class="contact-icon">
                        <i class="fab fa-telegram"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600;">Telegram</div>
                        <div style="color: #aaa;"><?= htmlspecialchars($user['telegram']) ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?php if ($is_own_profile): ?>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-edit"></i>
                    Editar Perfil
                </a>
            <?php else: ?>
                <?php if ($user['telegram']): ?>
                    <a href="https://t.me/<?= htmlspecialchars($user['telegram']) ?>" target="_blank" class="btn btn-primary">
                        <i class="fab fa-telegram"></i>
                        Contatar via Telegram
                    </a>
                <?php endif; ?>
                
                <?php if ($user['phone']): ?>
                    <a href="https://wa.me/<?= htmlspecialchars($user['phone']) ?>" target="_blank" class="btn btn-secondary">
                        <i class="fab fa-whatsapp"></i>
                        WhatsApp
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i>
                Voltar ao Início
            </a>
        </div>
    </div>

    <script>
        // Animações ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card, .contact-item');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>