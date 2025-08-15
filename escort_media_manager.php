<?php
/**
 * Painel para Escorts Gerenciarem Mídia Privada
 * escort_media_manager.php
 */

include 'auth.php';

// Por enquanto, só admins podem acessar (depois podemos criar login para escorts)
requireAdmin();

$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");

$success = '';
$error = '';

// Pegar ID da escort da URL
$escort_id = isset($_GET['escort_id']) ? intval($_GET['escort_id']) : 0;

if ($escort_id <= 0) {
    header("Location: admin_escorts.php");
    exit;
}

// Buscar dados da escort
$stmt = $conn->prepare("SELECT * FROM escorts WHERE id = ?");
$stmt->bind_param("i", $escort_id);
$stmt->execute();
$escort = $stmt->get_result()->fetch_assoc();

if (!$escort) {
    header("Location: admin_escorts.php");
    exit;
}

// Processar upload de mídia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_media'])) {
    $media_type = $_POST['media_type'] ?? '';
    $media_url = trim($_POST['media_url'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $required_plan = $_POST['required_plan'] ?? 'silver_plus';
    
    if (!empty($media_url) && in_array($media_type, ['photo', 'video'])) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO escort_private_media (escort_id, media_type, media_url, title, description, required_plan)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isssss", $escort_id, $media_type, $media_url, $title, $description, $required_plan);
            
            if ($stmt->execute()) {
                $success = 'Mídia adicionada com sucesso!';
                logActivity('private_media_added', "Adicionou mídia privada para {$escort['name']}", $_SESSION['user_id']);
            } else {
                $error = 'Erro ao adicionar mídia.';
            }
        } catch (Exception $e) {
            $error = 'Erro: ' . $e->getMessage();
        }
    } else {
        $error = 'Dados inválidos.';
    }
}

// Processar exclusão de mídia
if (isset($_GET['delete_media'])) {
    $media_id = intval($_GET['delete_media']);
    
    try {
        $stmt = $conn->prepare("DELETE FROM escort_private_media WHERE id = ? AND escort_id = ?");
        $stmt->bind_param("ii", $media_id, $escort_id);
        
        if ($stmt->execute()) {
            $success = 'Mídia removida com sucesso!';
        } else {
            $error = 'Erro ao remover mídia.';
        }
    } catch (Exception $e) {
        $error = 'Erro: ' . $e->getMessage();
    }
}

// Buscar mídia existente
$stmt = $conn->prepare("SELECT * FROM escort_private_media WHERE escort_id = ? ORDER BY media_type, created_at DESC");
$stmt->bind_param("i", $escort_id);
$stmt->execute();
$media_items = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Mídia - <?= htmlspecialchars($escort['name']) ?> | Citas</title>
    
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
            font-size: 1.8rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .page-title {
            font-size: 2.5rem;
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

        .form-section {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

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
        .form-group select,
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
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #ff6b6b;
            background: rgba(255, 255, 255, 0.12);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .btn {
            padding: 0.8rem 2rem;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff4757, #c44569);
            color: white;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: #aaa;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .media-item {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .media-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.2);
        }

        .media-preview {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .media-preview img,
        .media-preview video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .media-type-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 0.3rem 0.8rem;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .media-type-badge.photo {
            background: linear-gradient(135deg, #48dbfb, #0abde3);
        }

        .media-type-badge.video {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
        }

        .media-info {
            padding: 1.5rem;
        }

        .media-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .media-description {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            line-height: 1.4;
        }

        .media-plan {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: #333;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .media-actions {
            display: flex;
            gap: 0.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
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
        }

        .back-btn:hover {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            transform: translateX(-5px);
        }

        .required-plan-info {
            background: rgba(255, 215, 0, 0.1);
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .required-plan-info h4 {
            color: #FFD700;
            margin-bottom: 0.5rem;
        }

        .plan-list {
            list-style: none;
            padding: 0;
        }

        .plan-list li {
            padding: 0.3rem 0;
            color: #ddd;
        }

        .plan-list li strong {
            color: #FFD700;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .media-grid {
                grid-template-columns: 1fr;
            }

            .media-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">🔥 Citas</a>
        </div>
    </header>

    <div class="container">
        <!-- Back Button -->
        <a href="admin_escorts.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Voltar para Escorts
        </a>

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

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-images"></i>
                Gerenciar Mídia Privada
            </h1>
            <p class="page-subtitle"><?= htmlspecialchars($escort['name']) ?></p>
        </div>

        <!-- Add Media Form -->
        <div class="form-section">
            <h2 class="section-title">
                <i class="fas fa-plus"></i>
                Adicionar Nova Mídia
            </h2>

            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="media_type">Tipo de Mídia</label>
                        <select id="media_type" name="media_type" required>
                            <option value="">Selecione...</option>
                            <option value="photo">📸 Foto</option>
                            <option value="video">🎥 Vídeo</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="required_plan">Plano Necessário</label>
                        <select id="required_plan" name="required_plan" required>
                            <option value="silver_plus">Silver+ (Fotos)</option>
                            <option value="gold">Gold (Vídeos)</option>
                            <option value="platinum">Platinum (Premium)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="media_url">URL da Mídia</label>
                    <input type="url" id="media_url" name="media_url" placeholder="https://exemplo.com/imagem.jpg" required>
                </div>

                <div class="form-group">
                    <label for="title">Título (Opcional)</label>
                    <input type="text" id="title" name="title" placeholder="Ex: Foto Exclusiva">
                </div>

                <div class="form-group">
                    <label for="description">Descrição (Opcional)</label>
                    <textarea id="description" name="description" placeholder="Descreva o conteúdo..."></textarea>
                </div>

                <button type="submit" name="add_media" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Adicionar Mídia
                </button>
            </form>

            <div class="required-plan-info">
                <h4><i class="fas fa-info-circle"></i> Informações sobre Planos:</h4>
                <ul class="plan-list">
                    <li><strong>Silver+:</strong> Necessário para ver fotos privadas</li>
                    <li><strong>Gold:</strong> Necessário para ver vídeos privados</li>
                    <li><strong>Platinum:</strong> Acesso total + funcionalidades premium</li>
                </ul>
            </div>
        </div>

        <!-- Existing Media -->
        <div class="form-section">
            <h2 class="section-title">
                <i class="fas fa-folder"></i>
                Mídia Existente
            </h2>

            <?php if ($media_items->num_rows > 0): ?>
            <div class="media-grid">
                <?php while ($media = $media_items->fetch_assoc()): ?>
                <div class="media-item">
                    <div class="media-preview">
                        <?php if ($media['media_type'] === 'photo'): ?>
                            <img src="<?= htmlspecialchars($media['media_url']) ?>" alt="<?= htmlspecialchars($media['title']) ?>">
                            <div class="media-type-badge photo">📸 FOTO</div>
                        <?php else: ?>
                            <video>
                                <source src="<?= htmlspecialchars($media['media_url']) ?>" type="video/mp4">
                            </video>
                            <div class="media-type-badge video">🎥 VÍDEO</div>
                        <?php endif; ?>
                    </div>

                    <div class="media-info">
                        <h3 class="media-title">
                            <?= htmlspecialchars($media['title'] ?: 'Sem título') ?>
                        </h3>
                        
                        <?php if ($media['description']): ?>
                        <p class="media-description">
                            <?= htmlspecialchars($media['description']) ?>
                        </p>
                        <?php endif; ?>

                        <div class="media-plan">
                            🔒 Plano: <?= strtoupper($media['required_plan']) ?>
                        </div>

                        <div class="media-actions">
                            <a href="<?= htmlspecialchars($media['media_url']) ?>" target="_blank" class="btn btn-secondary">
                                <i class="fas fa-eye"></i>
                                Ver
                            </a>
                            <a href="?escort_id=<?= $escort_id ?>&delete_media=<?= $media['id'] ?>" 
                               class="btn btn-danger"
                               onclick="return confirm('Tem certeza que deseja remover esta mídia?')">
                                <i class="fas fa-trash"></i>
                                Remover
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 3rem; color: #666;">
                <i class="fas fa-folder-open" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                <p style="font-size: 1.2rem;">Nenhuma mídia privada adicionada ainda.</p>
                <p>Use o formulário acima para adicionar fotos e vídeos exclusivos.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const mediaType = document.getElementById('media_type').value;
            const requiredPlan = document.getElementById('required_plan').value;
            
            // Validar combinação de tipo e plano
            if (mediaType === 'photo' && requiredPlan === 'gold') {
                if (!confirm('Você está definindo uma FOTO como Gold (normalmente para vídeos). Continuar?')) {
                    e.preventDefault();
                }
            }
            
            if (mediaType === 'video' && requiredPlan === 'silver_plus') {
                if (!confirm('Você está definindo um VÍDEO como Silver+ (normalmente para fotos). Continuar?')) {
                    e.preventDefault();
                }
            }
        });

        // Preview media URL
        document.getElementById('media_url').addEventListener('blur', function() {
            const url = this.value;
            if (url) {
                // Simple validation
                if (!url.match(/\.(jpg|jpeg|png|gif|mp4|webm|mov)$/i)) {
                    alert('⚠️ A URL deve terminar com uma extensão de imagem (.jpg, .png, .gif) ou vídeo (.mp4, .webm, .mov)');
                }
            }
        });
    </script>
</body>
</html>