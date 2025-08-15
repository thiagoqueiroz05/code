<?php
include 'auth.php';
include 'subscription_manager.php';

// Redirecionar se não estiver logado
if (!isLoggedIn()) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}

$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");
$subscriptionManager = new SubscriptionManager($conn);

$success = '';
$error = '';

// Processar compra de plano
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_plan'])) {
    $planId = intval($_POST['plan_id']);
    $result = $subscriptionManager->createSubscription($_SESSION['user_id'], $planId, 'manual');
    
    if ($result['success']) {
        $success = 'Plano ativado com sucesso! Agora você tem acesso a todas as funcionalidades.';
        logActivity('plan_purchased', "Comprou plano: {$result['plan']['name']}", $_SESSION['user_id']);
    } else {
        $error = 'Erro ao ativar plano: ' . $result['error'];
    }
}

// Processar cancelamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_subscription'])) {
    $result = $subscriptionManager->cancelSubscription($_SESSION['user_id'], 'user_request');
    
    if ($result['success']) {
        $success = 'Assinatura cancelada. Você manterá o acesso até o fim do período pago.';
    } else {
        $error = 'Erro ao cancelar assinatura: ' . $result['error'];
    }
}

// Buscar dados
$userPlan = $subscriptionManager->getUserCurrentPlan($_SESSION['user_id']);
$allPlans = $subscriptionManager->getAllPlans();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos Premium - Citas</title>
    <meta name="description" content="Escolha seu plano premium e tenha acesso a funcionalidades exclusivas no Citas">
    
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

        .back-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #aaa;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            transform: translateX(-5px);
        }

        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Hero Section */
        .hero-section {
            text-align: center;
            margin-bottom: 4rem;
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            padding: 4rem 2rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(135deg, #ff6b6b, #feca57, #48dbfb, #ff9ff3);
            animation: rainbow 3s linear infinite;
        }

        @keyframes rainbow {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: #ddd;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .hero-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Current Plan */
        .current-plan {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 3rem;
            text-align: center;
            border: 2px solid rgba(255, 215, 0, 0.3);
            position: relative;
        }

        .current-plan::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
        }

        .current-plan-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #FFD700;
        }

        .current-plan-info {
            display: flex;
            justify-content: space-around;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }

        /* Plans Grid */
        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .plan-card {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            padding: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .plan-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.4);
        }

        .plan-card.featured {
            border-color: #FFD700;
            box-shadow: 0 0 30px rgba(255, 215, 0, 0.3);
        }

        .plan-card.current {
            border-color: #00ff88;
            box-shadow: 0 0 30px rgba(0, 255, 136, 0.3);
        }

        .plan-header {
            position: relative;
            margin-bottom: 2rem;
        }

        .plan-badge {
            position: absolute;
            top: -1rem;
            right: -1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
        }

        .plan-badge.popular {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
        }

        .plan-badge.recommended {
            background: linear-gradient(135deg, #00ff88, #26de81);
        }

        .plan-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }

        .plan-name {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .plan-price {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .plan-period {
            color: #aaa;
            font-size: 1rem;
            margin-bottom: 2rem;
        }

        /* Features List */
        .features-list {
            list-style: none;
            margin-bottom: 2rem;
            text-align: left;
        }

        .features-list li {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            padding: 0.8rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .features-list li:last-child {
            border-bottom: none;
        }

        .feature-icon {
            width: 20px;
            text-align: center;
        }

        .feature-icon.allowed {
            color: #00ff88;
        }

        .feature-icon.denied {
            color: #ff6b6b;
        }

        /* Buttons */
        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 25px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 1rem;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.4);
        }

        .btn-current {
            background: linear-gradient(135deg, #00ff88, #26de81);
            color: white;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #aaa;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .btn-cancel {
            background: linear-gradient(135deg, #ff4757, #c44569);
            color: white;
            margin-top: 1rem;
        }

        /* Alerts */
        .alert {
            padding: 1rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
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

        /* Features Comparison */
        .features-comparison {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 3rem;
        }

        .comparison-title {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .comparison-table {
            overflow-x: auto;
        }

        .comparison-table table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            overflow: hidden;
        }

        .comparison-table th,
        .comparison-table td {
            padding: 1rem;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .comparison-table th {
            background: rgba(255, 255, 255, 0.1);
            font-weight: bold;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .plans-grid {
                grid-template-columns: 1fr;
            }

            .current-plan-info {
                flex-direction: column;
                gap: 1rem;
            }

            .comparison-table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">🔥 Citas</a>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
        </div>
    </header>

    <div class="container">
        <!-- Alerts -->
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

        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-icon">
                <i class="fas fa-crown"></i>
            </div>
            <h1 class="hero-title">Planos Premium</h1>
            <p class="hero-subtitle">
                Desbloqueie funcionalidades exclusivas e tenha a melhor experiência no Citas
            </p>
        </div>

        <!-- Current Plan -->
        <?php if ($userPlan['current_plan'] !== 'free'): ?>
        <div class="current-plan">
            <h2 class="current-plan-title">
                <i class="<?= $userPlan['icon'] ?>"></i>
                Seu Plano: <?= $userPlan['plan_name'] ?>
            </h2>
            <div class="current-plan-info">
                <div>
                    <strong>Status:</strong> 
                    <?= $userPlan['is_active'] ? '<span style="color: #00ff88;">Ativo</span>' : '<span style="color: #ff6b6b;">Inativo</span>' ?>
                </div>
                <?php if ($userPlan['expires_at']): ?>
                <div>
                    <strong>Expira em:</strong> 
                    <?= date('d/m/Y H:i', strtotime($userPlan['expires_at'])) ?>
                </div>
                <?php endif; ?>
                <form method="POST" style="display: inline;" onsubmit="return confirm('Tem certeza que deseja cancelar sua assinatura?')">
                    <button type="submit" name="cancel_subscription" class="btn btn-cancel" style="padding: 0.5rem 1rem; width: auto;">
                        <i class="fas fa-times"></i> Cancelar Assinatura
                    </button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- Plans Grid -->
        <div class="plans-grid">
            <?php foreach ($allPlans as $index => $plan): 
                $features = $plan['features'];
                $isCurrent = $userPlan['current_plan'] === $plan['slug'];
                $cardClass = '';
                
                if ($plan['slug'] === 'gold') $cardClass = 'featured';
                if ($isCurrent) $cardClass .= ' current';
            ?>
            <div class="plan-card <?= $cardClass ?>">
                <?php if ($plan['slug'] === 'gold'): ?>
                    <div class="plan-badge popular">Mais Popular</div>
                <?php elseif ($plan['slug'] === 'silver_plus'): ?>
                    <div class="plan-badge recommended">Recomendado</div>
                <?php endif; ?>

                <div class="plan-header">
                    <div class="plan-icon" style="color: <?= $plan['color'] ?>">
                        <i class="<?= $plan['icon'] ?>"></i>
                    </div>
                    <h3 class="plan-name" style="color: <?= $plan['color'] ?>"><?= $plan['name'] ?></h3>
                    <div class="plan-price" style="color: <?= $plan['color'] ?>">
                        €<?= number_format($plan['price'], 2) ?>
                    </div>
                    <div class="plan-period">por mês</div>
                </div>

                <ul class="features-list">
                    <li>
                        <i class="fas fa-heart feature-icon <?= $features['likes'] ? 'allowed' : 'denied' ?>"></i>
                        <span>Curtir perfis</span>
                    </li>
                    <li>
                        <i class="fas fa-comment feature-icon <?= $features['comments'] ? 'allowed' : 'denied' ?>"></i>
                        <span>Comentar em perfis</span>
                    </li>
                    <li>
                        <i class="fas fa-images feature-icon <?= $features['private_photos'] ? 'allowed' : 'denied' ?>"></i>
                        <span>Ver fotos privadas</span>
                    </li>
                    <li>
                        <i class="fas fa-video feature-icon <?= $features['private_videos'] ? 'allowed' : 'denied' ?>"></i>
                        <span>Ver vídeos privados</span>
                    </li>
                    <li>
                        <i class="fas fa-smile feature-icon <?= $features['emoji_comments'] ? 'allowed' : 'denied' ?>"></i>
                        <span>Emojis nos comentários</span>
                    </li>
                    <li>
                        <i class="fas fa-star feature-icon <?= $features['highlighted_comments'] ? 'allowed' : 'denied' ?>"></i>
                        <span>Comentários destacados</span>
                    </li>
                </ul>

                <?php if ($isCurrent): ?>
                    <button class="btn btn-current" disabled>
                        <i class="fas fa-check"></i> Plano Atual
                    </button>
                <?php else: ?>
                    <form method="POST" onsubmit="return confirm('Confirma a ativação do plano <?= $plan['name'] ?>?')">
                        <input type="hidden" name="plan_id" value="<?= $plan['id'] ?>">
                        <button type="submit" name="buy_plan" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> Escolher Plano
                        </button>
                    </form>
                <?php endif; ?>

                <div style="margin-top: 1rem; color: #aaa; font-size: 0.9rem;">
                    <?= $plan['subscriber_count'] ?> usuários ativos
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Features Comparison -->
        <div class="features-comparison">
            <h2 class="comparison-title">Comparação de Funcionalidades</h2>
            
            <div class="comparison-table">
                <table>
                    <thead>
                        <tr>
                            <th>Funcionalidade</th>
                            <th style="color: #CD7F32;">Bronze</th>
                            <th style="color: #C0C0C0;">Silver</th>
                            <th style="color: #E6E6FA;">Silver+</th>
                            <th style="color: #FFD700;">Gold</th>
                            <th style="color: #E5E4E2;">Platinum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Curtir perfis</strong></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Comentar</strong></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Fotos privadas</strong></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Vídeos privados</strong></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Emojis em comentários</strong></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Comentários destacados</strong></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-times" style="color: #ff6b6b;"></i></td>
                            <td><i class="fas fa-check" style="color: #00ff88;"></i></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Info -->
        <div style="text-align: center; padding: 2rem; background: rgba(255, 255, 255, 0.05); border-radius: 15px; margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem; color: #ff6b6b;">
                <i class="fas fa-info-circle"></i>
                Informações Importantes
            </h3>
            <ul style="list-style: none; color: #ccc; line-height: 2;">
                <li>✓ Todos os planos têm duração de 30 dias</li>
                <li>✓ Você pode cancelar a qualquer momento</li>
                <li>✓ Acesso imediato após a ativação</li>
                <li>✓ Suporte premium 24/7</li>
                <li>✓ Funcionalidades exclusivas desbloqueadas</li>
            </ul>
        </div>
    </div>

    <script>
        // Animações ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.plan-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Auto-hide alerts
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);
        });
    </script>
</body>
</html>