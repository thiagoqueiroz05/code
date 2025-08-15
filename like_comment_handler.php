<?php
/**
 * Sistema de Likes e Comentários CORRIGIDO
 * like_comment_handler.php
 */

include 'auth.php';

// Verificar se está logado
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Você precisa estar logado']);
    exit;
}

$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Erro de conexão com banco']);
    exit;
}

$response = ['success' => false, 'error' => ''];

// FUNÇÕES AUXILIARES PARA VERIFICAR PERMISSÕES
function getUserCurrentPlan($conn, $userId) {
    $stmt = $conn->prepare("
        SELECT 
            u.current_plan,
            u.plan_expires_at,
            sp.name as plan_name,
            sp.features,
            sp.color,
            sp.icon
        FROM users u
        LEFT JOIN subscription_plans sp ON u.current_plan = sp.slug
        WHERE u.id = ?
    ");
    
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if (!$result) {
        return [
            'current_plan' => 'free',
            'plan_name' => 'Gratuito',
            'features' => [],
            'is_active' => false,
            'color' => '#666',
            'icon' => 'fas fa-user'
        ];
    }
    
    $features = $result['features'] ? json_decode($result['features'], true) : [];
    
    return [
        'current_plan' => $result['current_plan'] ?: 'free',
        'plan_name' => $result['plan_name'] ?: 'Gratuito',
        'features' => $features,
        'color' => $result['color'] ?: '#666',
        'icon' => $result['icon'] ?: 'fas fa-user',
        'expires_at' => $result['plan_expires_at'],
        'is_active' => $result['current_plan'] !== 'free' && (!$result['plan_expires_at'] || strtotime($result['plan_expires_at']) > time())
    ];
}

function userHasPermission($conn, $userId, $permission) {
    $userPlan = getUserCurrentPlan($conn, $userId);
    
    if ($userPlan['current_plan'] === 'free') {
        return false;
    }
    
    if (!$userPlan['is_active']) {
        return false;
    }
    
    return isset($userPlan['features'][$permission]) && $userPlan['features'][$permission] === true;
}

// PROCESSAR LIKES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like') {
    $targetType = $_POST['target_type'] ?? '';
    $targetId = intval($_POST['target_id'] ?? 0);
    $userId = $_SESSION['user_id'];
    
    // Verificar permissão para curtir
    if (!userHasPermission($conn, $userId, 'likes')) {
        $response['error'] = 'Você precisa de um plano premium para curtir. Adquira o plano Bronze ou superior!';
        $response['upgrade_required'] = true;
        $response['required_plan'] = 'bronze';
        echo json_encode($response);
        exit;
    }
    
    if (!in_array($targetType, ['escort', 'user', 'comment']) || $targetId <= 0) {
        $response['error'] = 'Dados inválidos';
        echo json_encode($response);
        exit;
    }
    
    try {
        // Verificar se já curtiu
        $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND target_type = ? AND target_id = ?");
        $stmt->bind_param("isissss", 
            $userId, 
            $targetType, 
            $targetId, 
            $content, 
            $hasEmojiInt, 
            $isHighlightedInt, 
            $userPlan['current_plan']
        );
        
        if ($stmt->execute()) {
            $commentId = $conn->insert_id;
            
            // Buscar dados do usuário
            $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            
            $response['success'] = true;
            $response['message'] = 'Comentário adicionado com sucesso!';
            $response['comment'] = [
                'id' => $commentId,
                'content' => htmlspecialchars($content),
                'username' => $user['username'],
                'user_plan' => $userPlan['current_plan'],
                'plan_color' => $userPlan['color'],
                'plan_icon' => $userPlan['icon'],
                'plan_name' => $userPlan['plan_name'],
                'is_highlighted' => $isHighlighted,
                'has_emoji' => $hasEmoji,
                'created_at' => date('d/m/Y H:i'),
                'can_delete' => true
            ];
            
            // Log da atividade
            if (function_exists('logActivity')) {
                logActivity('comment_added', "Comentou no perfil ID: $targetId", $userId);
            }
            
        } else {
            throw new Exception($stmt->error);
        }
        
    } catch (Exception $e) {
        $response['error'] = 'Erro ao adicionar comentário: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// BUSCAR COMENTÁRIOS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_comments') {
    $targetType = $_GET['target_type'] ?? '';
    $targetId = intval($_GET['target_id'] ?? 0);
    
    if (!in_array($targetType, ['escort', 'user']) || $targetId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
        exit;
    }
    
    try {
        $stmt = $conn->prepare("
            SELECT 
                c.*,
                u.username,
                sp.name as plan_name,
                sp.color as plan_color,
                sp.icon as plan_icon
            FROM comments c
            JOIN users u ON c.user_id = u.id
            LEFT JOIN subscription_plans sp ON c.user_plan = sp.slug
            WHERE c.target_type = ? AND c.target_id = ? AND c.is_approved = 1
            ORDER BY c.is_highlighted DESC, c.created_at DESC
            LIMIT 50
        ");
        
        $stmt->bind_param("si", $targetType, $targetId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $comments = [];
        while ($comment = $result->fetch_assoc()) {
            $comments[] = [
                'id' => $comment['id'],
                'content' => htmlspecialchars($comment['content']),
                'username' => $comment['username'],
                'user_plan' => $comment['user_plan'],
                'plan_name' => $comment['plan_name'],
                'plan_color' => $comment['plan_color'],
                'plan_icon' => $comment['plan_icon'],
                'is_highlighted' => (bool)$comment['is_highlighted'],
                'has_emoji' => (bool)$comment['has_emoji'],
                'created_at' => date('d/m/Y H:i', strtotime($comment['created_at'])),
                'can_delete' => ($_SESSION['user_id'] == $comment['user_id'] || isAdmin())
            ];
        }
        
        echo json_encode(['success' => true, 'comments' => $comments]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Erro ao buscar comentários']);
    }
    
    exit;
}

// BUSCAR STATUS DE LIKES
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_like_status') {
    $targetType = $_GET['target_type'] ?? '';
    $targetId = intval($_GET['target_id'] ?? 0);
    $userId = $_SESSION['user_id'];
    
    if (!in_array($targetType, ['escort', 'user', 'comment']) || $targetId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
        exit;
    }
    
    try {
        // Verificar se usuário curtiu
        $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND target_type = ? AND target_id = ?");
        $stmt->bind_param("isi", $userId, $targetType, $targetId);
        $stmt->execute();
        $userLiked = $stmt->get_result()->num_rows > 0;
        
        // Buscar total de likes
        $stmt = $conn->prepare("SELECT total_likes FROM like_stats WHERE target_type = ? AND target_id = ?");
        $stmt->bind_param("si", $targetType, $targetId);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'user_liked' => $userLiked,
            'total_likes' => $stats['total_likes'] ?? 0,
            'can_like' => userHasPermission($conn, $userId, 'likes'),
            'can_comment' => userHasPermission($conn, $userId, 'comments'),
            'can_use_emoji' => userHasPermission($conn, $userId, 'emoji_comments')
        ]);
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Erro ao buscar status']);
    }
    
    exit;
}

// DELETAR COMENTÁRIO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_comment') {
    $commentId = intval($_POST['comment_id'] ?? 0);
    $userId = $_SESSION['user_id'];
    
    if ($commentId <= 0) {
        echo json_encode(['success' => false, 'error' => 'ID do comentário inválido']);
        exit;
    }
    
    try {
        // Verificar se é o dono do comentário ou admin
        $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
        $stmt->bind_param("i", $commentId);
        $stmt->execute();
        $comment = $stmt->get_result()->fetch_assoc();
        
        if (!$comment) {
            throw new Exception("Comentário não encontrado");
        }
        
        if ($comment['user_id'] != $userId && !isAdmin()) {
            throw new Exception("Você não tem permissão para deletar este comentário");
        }
        
        // Deletar comentário
        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $commentId);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = 'Comentário removido com sucesso!';
            
            if (function_exists('logActivity')) {
                logActivity('comment_deleted', "Deletou comentário ID: $commentId", $userId);
            }
        } else {
            throw new Exception($stmt->error);
        }
        
    } catch (Exception $e) {
        $response['error'] = 'Erro ao deletar comentário: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// Se chegou aqui, ação inválida
echo json_encode(['success' => false, 'error' => 'Ação inválida']);
?>("isi", $userId, $targetType, $targetId);
        $stmt->execute();
        $existingLike = $stmt->get_result()->fetch_assoc();
        
        if ($existingLike) {
            // Descurtir
            $stmt = $conn->prepare("DELETE FROM likes WHERE id = ?");
            $stmt->bind_param("i", $existingLike['id']);
            $stmt->execute();
            
            $response['success'] = true;
            $response['action'] = 'unliked';
            $response['message'] = 'Like removido';
        } else {
            // Curtir
            $stmt = $conn->prepare("INSERT INTO likes (user_id, target_type, target_id) VALUES (?, ?, ?)");
            $stmt->bind_param("isi", $userId, $targetType, $targetId);
            $stmt->execute();
            
            $response['success'] = true;
            $response['action'] = 'liked';
            $response['message'] = 'Curtido com sucesso!';
        }
        
        // Atualizar contador
        $stmt = $conn->prepare("
            INSERT INTO like_stats (target_type, target_id, total_likes) 
            VALUES (?, ?, 1) 
            ON DUPLICATE KEY UPDATE 
            total_likes = (SELECT COUNT(*) FROM likes WHERE target_type = ? AND target_id = ?)
        ");
        $stmt->bind_param("sisi", $targetType, $targetId, $targetType, $targetId);
        $stmt->execute();
        
        // Buscar novo total
        $stmt = $conn->prepare("SELECT total_likes FROM like_stats WHERE target_type = ? AND target_id = ?");
        $stmt->bind_param("si", $targetType, $targetId);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();
        
        $response['total_likes'] = $stats['total_likes'] ?? 0;
        
    } catch (Exception $e) {
        $response['error'] = 'Erro ao processar like: ' . $e->getMessage();
    }
    
    echo json_encode($response);
    exit;
}

// PROCESSAR COMENTÁRIOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'comment') {
    $targetType = $_POST['target_type'] ?? '';
    $targetId = intval($_POST['target_id'] ?? 0);
    $content = trim($_POST['content'] ?? '');
    $emoji = trim($_POST['emoji'] ?? '');
    $userId = $_SESSION['user_id'];
    
    // Verificar permissão para comentar
    if (!userHasPermission($conn, $userId, 'comments')) {
        $response['error'] = 'Você precisa do plano Silver ou superior para comentar!';
        $response['upgrade_required'] = true;
        $response['required_plan'] = 'silver';
        echo json_encode($response);
        exit;
    }
    
    if (!in_array($targetType, ['escort', 'user']) || $targetId <= 0 || empty($content)) {
        $response['error'] = 'Dados inválidos';
        echo json_encode($response);
        exit;
    }
    
    // Verificar se pode usar emoji
    $hasEmoji = false;
    if (!empty($emoji)) {
        if (!userHasPermission($conn, $userId, 'emoji_comments')) {
            $response['error'] = 'Você precisa do plano Platinum para usar emojis nos comentários!';
            $response['upgrade_required'] = true;
            $response['required_plan'] = 'platinum';
            echo json_encode($response);
            exit;
        }
        $hasEmoji = true;
        $content = $emoji . ' ' . $content;
    }
    
    try {
        // Buscar plano do usuário para destacar comentário
        $userPlan = getUserCurrentPlan($conn, $userId);
        $isHighlighted = userHasPermission($conn, $userId, 'highlighted_comments');
        
        // Inserir comentário
        $stmt = $conn->prepare("
            INSERT INTO comments (user_id, target_type, target_id, content, has_emoji, is_highlighted, user_plan) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $hasEmojiInt = $hasEmoji ? 1 : 0;
        $isHighlightedInt = $isHighlighted ? 1 : 0;
        
        $stmt->bind_param