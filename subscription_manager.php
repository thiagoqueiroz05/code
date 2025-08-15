<?php
/**
 * Gerenciador de Assinaturas - Sistema de Planos
 * subscription_manager.php
 */

class SubscriptionManager {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
        $this->initializeTables();
    }
    
    /**
     * Inicializar tabelas se não existirem
     */
    private function initializeTables() {
        // Criar tabela de planos se não existir
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS `subscription_plans` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(50) NOT NULL,
              `slug` varchar(50) NOT NULL,
              `price` decimal(10,2) NOT NULL,
              `duration_days` int(11) NOT NULL DEFAULT 30,
              `features` JSON NOT NULL,
              `color` varchar(20) DEFAULT NULL,
              `icon` varchar(50) DEFAULT NULL,
              `is_active` tinyint(1) DEFAULT 1,
              `sort_order` int(11) DEFAULT 0,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // Inserir planos padrão se não existirem
        $count = $this->conn->query("SELECT COUNT(*) as count FROM subscription_plans")->fetch_assoc()['count'];
        if ($count == 0) {
            $this->conn->query("
                INSERT INTO `subscription_plans` (`name`, `slug`, `price`, `duration_days`, `features`, `color`, `icon`, `sort_order`) VALUES
                ('Bronze', 'bronze', 5.99, 30, '{\"likes\": true, \"comments\": false, \"private_photos\": false, \"private_videos\": false, \"emoji_comments\": false, \"highlighted_comments\": false}', '#CD7F32', 'fas fa-medal', 1),
                ('Silver', 'silver', 9.99, 30, '{\"likes\": true, \"comments\": true, \"private_photos\": false, \"private_videos\": false, \"emoji_comments\": false, \"highlighted_comments\": false}', '#C0C0C0', 'fas fa-gem', 2),
                ('Silver+', 'silver_plus', 14.99, 30, '{\"likes\": true, \"comments\": true, \"private_photos\": true, \"private_videos\": false, \"emoji_comments\": false, \"highlighted_comments\": false}', '#E6E6FA', 'fas fa-star', 3),
                ('Gold', 'gold', 19.99, 30, '{\"likes\": true, \"comments\": true, \"private_photos\": true, \"private_videos\": true, \"emoji_comments\": false, \"highlighted_comments\": false}', '#FFD700', 'fas fa-crown', 4),
                ('Platinum', 'platinum', 29.99, 30, '{\"likes\": true, \"comments\": true, \"private_photos\": true, \"private_videos\": true, \"emoji_comments\": true, \"highlighted_comments\": true}', '#E5E4E2', 'fas fa-diamond', 5)
            ");
        }
        
        // Adicionar colunas de plano na tabela users se não existirem
        $columns = $this->conn->query("DESCRIBE users");
        $has_plan_columns = false;
        while ($column = $columns->fetch_assoc()) {
            if ($column['Field'] === 'current_plan') {
                $has_plan_columns = true;
                break;
            }
        }
        
        if (!$has_plan_columns) {
            $this->conn->query("ALTER TABLE `users` ADD COLUMN `current_plan` varchar(50) DEFAULT 'free' AFTER `is_admin`");
            $this->conn->query("ALTER TABLE `users` ADD COLUMN `plan_expires_at` datetime DEFAULT NULL AFTER `current_plan`");
        }
        
        // Criar tabela de mídia privada se não existir
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS `escort_private_media` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `escort_id` int(11) NOT NULL,
              `media_type` enum('photo','video') NOT NULL,
              `media_url` text NOT NULL,
              `thumbnail_url` text DEFAULT NULL,
              `title` varchar(255) DEFAULT NULL,
              `description` text DEFAULT NULL,
              `required_plan` varchar(50) NOT NULL DEFAULT 'silver_plus',
              `media_order` int(11) DEFAULT 0,
              `is_active` tinyint(1) DEFAULT 1,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `escort_id` (`escort_id`),
              KEY `media_type` (`media_type`),
              KEY `required_plan` (`required_plan`),
              FOREIGN KEY (`escort_id`) REFERENCES `escorts` (`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        
        // Atualizar tabela de comentários
        $comments_columns = $this->conn->query("DESCRIBE comments");
        $has_comment_features = false;
        while ($column = $comments_columns->fetch_assoc()) {
            if ($column['Field'] === 'has_emoji') {
                $has_comment_features = true;
                break;
            }
        }
        
        if (!$has_comment_features) {
            $this->conn->query("ALTER TABLE `comments` ADD COLUMN `has_emoji` tinyint(1) DEFAULT 0 AFTER `content`");
            $this->conn->query("ALTER TABLE `comments` ADD COLUMN `is_highlighted` tinyint(1) DEFAULT 0 AFTER `has_emoji`");
            $this->conn->query("ALTER TABLE `comments` ADD COLUMN `user_plan` varchar(50) DEFAULT 'free' AFTER `is_highlighted`");
        }
    }
    
    /**
     * Verificar permissões do usuário
     */
    public function userHasPermission($userId, $permission) {
        $stmt = $this->conn->prepare("
            SELECT u.current_plan, u.plan_expires_at, sp.features
            FROM users u
            LEFT JOIN subscription_plans sp ON u.current_plan = sp.slug
            WHERE u.id = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!$result || $result['current_plan'] === 'free') {
            return false;
        }
        
        // Verificar se o plano não expirou
        if ($result['plan_expires_at'] && strtotime($result['plan_expires_at']) < time()) {
            // Plano expirado - voltar para free
            $this->conn->prepare("UPDATE users SET current_plan = 'free', plan_expires_at = NULL WHERE id = ?")->execute([$userId]);
            return false;
        }
        
        if ($result['features']) {
            $features = json_decode($result['features'], true);
            return isset($features[$permission]) && $features[$permission] === true;
        }
        
        return false;
    }
    
    /**
     * Buscar plano atual do usuário
     */
    public function getUserCurrentPlan($userId) {
        $stmt = $this->conn->prepare("
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
    
    /**
     * Buscar todos os planos disponíveis
     */
    public function getAllPlans() {
        $stmt = $this->conn->prepare("
            SELECT 
                sp.*,
                COUNT(u.id) as subscriber_count
            FROM subscription_plans sp
            LEFT JOIN users u ON sp.slug = u.current_plan AND u.plan_expires_at > NOW()
            WHERE sp.is_active = 1
            GROUP BY sp.id
            ORDER BY sp.sort_order ASC
        ");
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $plans = [];
        while ($plan = $result->fetch_assoc()) {
            $plan['features'] = json_decode($plan['features'], true);
            $plans[] = $plan;
        }
        
        return $plans;
    }
    
    /**
     * Ativar plano para usuário (simulação - sem pagamento real)
     */
    public function activatePlan($userId, $planSlug) {
        try {
            $this->conn->begin_transaction();
            
            // Buscar informações do plano
            $stmt = $this->conn->prepare("SELECT * FROM subscription_plans WHERE slug = ? AND is_active = 1");
            $stmt->bind_param("s", $planSlug);
            $stmt->execute();
            $plan = $stmt->get_result()->fetch_assoc();
            
            if (!$plan) {
                throw new Exception("Plano não encontrado");
            }
            
            // Calcular data de expiração
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$plan['duration_days']} days"));
            
            // Atualizar plano do usuário
            $stmt = $this->conn->prepare("
                UPDATE users 
                SET current_plan = ?, plan_expires_at = ? 
                WHERE id = ?
            ");
            
            $stmt->bind_param("ssi", $plan['slug'], $expiresAt, $userId);
            $stmt->execute();
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'plan' => $plan,
                'expires_at' => $expiresAt
            ];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Verificar se pode curtir
     */
    public function canLike($userId) {
        return $this->userHasPermission($userId, 'likes');
    }
    
    /**
     * Verificar se pode comentar
     */
    public function canComment($userId) {
        return $this->userHasPermission($userId, 'comments');
    }
    
    /**
     * Verificar se pode ver fotos privadas
     */
    public function canViewPrivatePhotos($userId) {
        return $this->userHasPermission($userId, 'private_photos');
    }
    
    /**
     * Verificar se pode ver vídeos privados
     */
    public function canViewPrivateVideos($userId) {
        return $this->userHasPermission($userId, 'private_videos');
    }
    
    /**
     * Verificar se pode usar emojis em comentários
     */
    public function canUseEmojiComments($userId) {
        return $this->userHasPermission($userId, 'emoji_comments');
    }
    
    /**
     * Verificar se comentários são destacados
     */
    public function hasHighlightedComments($userId) {
        return $this->userHasPermission($userId, 'highlighted_comments');
    }
}
?>