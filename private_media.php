<?php
/**
 * Sistema de Mídia Privada (Fotos e Vídeos)
 * private_media.php
 */

include 'auth.php';
include 'subscription_manager.php';

// Verificar se está logado
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");
$subscriptionManager = new SubscriptionManager($conn);

$escortId = intval($_GET['escort_id'] ?? 0);
$mediaType = $_GET['type'] ?? 'photos'; // photos ou videos

if ($escortId <= 0) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Buscar dados da escort
$stmt = $conn->prepare("SELECT name, image_url FROM escorts WHERE id = ?");
$stmt->bind_param("i", $escortId);
$stmt->execute();
$escort = $stmt->get_result()->fetch_assoc();

if (!$escort) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

$userId = $_SESSION['user_id'];
$userPlan = $subscriptionManager->getUserCurrentPlan($userId);

// Verificar permissões
$canViewPhotos = $subscriptionManager->canViewPrivatePhotos($userId);
$canViewVideos = $subscriptionManager->canViewPrivateVideos($userId);

// Buscar mídia baseada no tipo e permissões
$mediaItems = [];
$requiredPlan = '';
$hasAccess = false;

if ($mediaType === 'photos') {
    $hasAccess = $canViewPhotos;
    $requiredPlan = 'Silver+';
    
    if ($hasAccess) {
        $stmt = $conn->prepare("
            SELECT * FROM escort_private_media 
            WHERE escort_id = ? AND media_type = 'photo' AND is_active = 1 
            ORDER BY media_order ASC, id ASC
        ");
        $stmt->bind_param("i", $escortId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($item = $result->fetch_assoc()) {
            $mediaItems[] = $item;
        }
    }
} else {
    $hasAccess = $canViewVideos;
    $requiredPlan = 'Gold';
    
    if ($hasAccess) {
        $stmt = $conn->prepare("
            SELECT * FROM escort_private_media 
            WHERE escort_id = ? AND media_type = 'video' AND is_active = 1 
            ORDER BY media_order ASC, id ASC
        ");
        $stmt->bind_param("i", $escortId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($item = $result->fetch_assoc()) {
            $mediaItems[] = $item;
        }
    }
}

// Log de acesso (se tiver permissão)
if ($hasAccess && !empty($mediaItems)) {
    logActivity('private_media_view', "Visualizou {$mediaType} privadas de {$escort['name']}", $userId);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucfirst($mediaType) ?> Privadas - <?= htmlspecialchars($escort['name']) ?> | Citas</title>
    
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

        /* Page Header */
        .page-header {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: #aaa;
            font-size: 1.2rem;
        }

        /* Type Toggle */
        .type-toggle {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .toggle-btn {
            padding: 0.8rem 2rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: transparent;
            color: white;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toggle-btn.active {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            border-color: transparent;
        }

        .toggle-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
        }

        /* Access Denied */
        .access-denied {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            padding: 4rem 2rem;
            text-align: center;
            border: 2px solid rgba(255, 71, 87, 0.3);
        }

        .access-denied-icon {
            font-size: 5rem;
            color: #ff4757;
            margin-bottom: 2rem;
        }

        .access-denied-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #ff4757;
        }

        .access-denied-text {
            font-size: 1.2rem;
            color: #aaa;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .upgrade-btn {
            padding: 1rem 2rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
        }

        .upgrade-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.4);
        }

        /* Media Grid */
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .media-item {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            position: relative;
            cursor: pointer;
        }

        .media-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(255, 107, 107, 0.2);
        }

        .media-preview {
            position: relative;
            height: 300px;
            overflow: hidden;
        }

        .media-preview img,
        .media-preview video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .media-item:hover .media-preview img,
        .media-item:hover .media-preview video {
            transform: scale(1.05);
        }

        .media-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.8));
            display: flex;
            align-items: flex-end;
            padding: 1rem;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .media-item:hover .media-overlay {
            opacity: 1;
        }

        .media-info {
            padding: 1.5rem;
        }

        .media-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: white;
        }

        .media-description {
            color: #aaa;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .video-indicator {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            font-size: 1.2rem;
        }

        /* Lightbox */
        .lightbox {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.95);
            z-index: 10000;
            align-items: center;
            justify-content: center;
        }

        .lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .lightbox-media {
            max-width: 100%;
            max-height: 100%;
            border-radius: 10px;
        }

        .lightbox-close {
            position: absolute;
            top: -50px;
            right: 0;
            background: rgba(0, 0, 0, 0.8);
            border: none;
            color: white;
            font-size: 2rem;
            padding: 10px 15px;
            border-radius: 50%;
            cursor: pointer;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.8);
            border: none;
            color: white;
            font-size: 2rem;
            padding: 15px 20px;
            border-radius: 50%;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .lightbox-prev {
            left: -80px;
        }

        .lightbox-next {
            right: -80px;
        }

        .lightbox-nav:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: translateY(-50%) scale(1.1);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
        }

        .empty-icon {
            font-size: 4rem;
            color: #666;
            margin-bottom: 2rem;
        }

        .empty-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #aaa;
        }

        .empty-text {
            color: #666;
            font-size: 1rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .media-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .type-toggle {
                flex-direction: column;
                align-items: center;
            }

            .lightbox-nav {
                position: fixed;
                top: 20px;
            }

            .lightbox-prev {
                left: 20px;
            }

            .lightbox-next {
                right: 80px;
            }
        }

        @media (max-width: 480px) {
            .media-grid {
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
            <a href="perfil.php?id=<?= $escortId ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Voltar ao Perfil
            </a>
        </div>
    </header>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-<?= $mediaType === 'photos' ? 'images' : 'video' ?>"></i>
                <?= ucfirst($mediaType) ?> Privadas
            </h1>
            <p class="page-subtitle"><?= htmlspecialchars($escort['name']) ?></p>
        </div>

        <!-- Type Toggle -->
        <div class="type-toggle">
            <a href="?escort_id=<?= $escortId ?>&type=photos" class="toggle-btn <?= $mediaType === 'photos' ? 'active' : '' ?>">
                <i class="fas fa-images"></i>
                Fotos Privadas
            </a>
            <a href="?escort_id=<?= $escortId ?>&type=videos" class="toggle-btn <?= $mediaType === 'videos' ? 'active' : '' ?>">
                <i class="fas fa-video"></i>
                Vídeos Privados
            </a>
        </div>

        <?php if (!$hasAccess): ?>
        <!-- Access Denied -->
        <div class="access-denied">
            <div class="access-denied-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2 class="access-denied-title">Conteúdo Premium</h2>
            <p class="access-denied-text">
                Para acessar <?= $mediaType === 'photos' ? 'fotos privadas' : 'vídeos privados' ?>, você precisa do plano 
                <strong><?= $requiredPlan ?></strong> ou superior.
                <br><br>
                Seu plano atual: <strong><?= $userPlan['plan_name'] ?></strong>
            </p>
            <a href="plans.php" class="upgrade-btn">
                <i class="fas fa-crown"></i>
                Fazer Upgrade
            </a>
        </div>

        <?php elseif (empty($mediaItems)): ?>
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-<?= $mediaType === 'photos' ? 'images' : 'video' ?>"></i>
            </div>
            <h3 class="empty-title">Nenhum conteúdo disponível</h3>
            <p class="empty-text">
                Esta escort ainda não adicionou <?= $mediaType === 'photos' ? 'fotos privadas' : 'vídeos privados' ?>.
            </p>
        </div>

        <?php else: ?>
        <!-- Media Grid -->
        <div class="media-grid">
            <?php foreach ($mediaItems as $index => $item): ?>
            <div class="media-item" onclick="openLightbox(<?= $index ?>)">
                <div class="media-preview">
                    <?php if ($item['media_type'] === 'photo'): ?>
                        <img src="<?= htmlspecialchars($item['media_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                    <?php else: ?>
                        <video preload="metadata">
                            <source src="<?= htmlspecialchars($item['media_url']) ?>" type="video/mp4">
                        </video>
                        <div class="video-indicator">
                            <i class="fas fa-play"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="media-overlay">
                        <div>
                            <div style="color: white; font-weight: bold;">
                                <?= htmlspecialchars($item['title'] ?: 'Conteúdo Premium') ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if ($item['title'] || $item['description']): ?>
                <div class="media-info">
                    <?php if ($item['title']): ?>
                        <h3 class="media-title"><?= htmlspecialchars($item['title']) ?></h3>
                    <?php endif; ?>
                    <?php if ($item['description']): ?>
                        <p class="media-description"><?= htmlspecialchars($item['description']) ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Lightbox -->
    <div id="lightbox" class="lightbox">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="closeLightbox()">×</button>
            <div id="lightbox-media"></div>
            <button class="lightbox-nav lightbox-prev" onclick="prevMedia()">❮</button>
            <button class="lightbox-nav lightbox-next" onclick="nextMedia()">❯</button>
        </div>
    </div>

    <script>
        // Dados da mídia
        const mediaItems = <?= json_encode($mediaItems) ?>;
        let currentIndex = 0;

        function openLightbox(index) {
            if (!mediaItems[index]) return;
            
            currentIndex = index;
            const item = mediaItems[index];
            const lightboxMedia = document.getElementById('lightbox-media');
            
            if (item.media_type === 'photo') {
                lightboxMedia.innerHTML = `<img src="${item.media_url}" class="lightbox-media" alt="${item.title || ''}">`;
            } else {
                lightboxMedia.innerHTML = `
                    <video class="lightbox-media" controls autoplay>
                        <source src="${item.media_url}" type="video/mp4">
                    </video>
                `;
            }
            
            document.getElementById('lightbox').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
            document.body.style.overflow = 'auto';
            
            // Parar vídeo se estiver tocando
            const video = document.querySelector('.lightbox-media video');
            if (video) {
                video.pause();
            }
        }

        function nextMedia() {
            currentIndex = (currentIndex + 1) % mediaItems.length;
            openLightbox(currentIndex);
        }

        function prevMedia() {
            currentIndex = (currentIndex - 1 + mediaItems.length) % mediaItems.length;
            openLightbox(currentIndex);
        }

        // Controles do teclado
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('lightbox').style.display === 'flex') {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowRight') nextMedia();
                if (e.key === 'ArrowLeft') prevMedia();
            }
        });

        // Fechar lightbox clicando fora
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLightbox();
            }
        });

        // Animações ao carregar
        document.addEventListener('DOMContentLoaded', function() {
            const items = document.querySelectorAll('.media-item');
            items.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateY(30px)';
                setTimeout(() => {
                    item.style.transition = 'all 0.6s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>