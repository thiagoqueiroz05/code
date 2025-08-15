<?php
/**
 * Script de Correção do Sistema de Comentários
 * fix_comments.php
 * 
 * Execute este arquivo UMA VEZ para corrigir o banco de dados
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuração do banco
$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");

if ($conn->connect_error) {
    die("❌ Erro de conexão: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$success = [];
$errors = [];

echo "<h2>🔧 Correção do Sistema de Comentários - CitasNortes</h2>";
echo "<style>body{font-family: Arial; background: #1a1a1a; color: #fff; padding: 20px;} .success{color: #00ff88;} .error{color: #ff6b6b;} .info{color: #48dbfb;}</style>";

// 1. Verificar e adicionar colunas na tabela comments
echo "<h3 class='info'>1. Verificando tabela comments...</h3>";

$columns_to_add = [
    'has_emoji' => "ADD COLUMN `has_emoji` tinyint(1) DEFAULT 0 AFTER `content`",
    'is_highlighted' => "ADD COLUMN `is_highlighted` tinyint(1) DEFAULT 0 AFTER `has_emoji`", 
    'user_plan' => "ADD COLUMN `user_plan` varchar(50) DEFAULT 'free' AFTER `is_highlighted`"
];

// Verificar quais colunas já existem
$existing_columns = [];
$result = $conn->query("DESCRIBE comments");
while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
}

foreach ($columns_to_add as $column => $sql) {
    if (!in_array($column, $existing_columns)) {
        try {
            if ($conn->query("ALTER TABLE `comments` " . $sql)) {
                $success[] = "✅ Coluna '$column' adicionada à tabela comments";
            } else {
                $errors[] = "❌ Erro ao adicionar coluna '$column': " . $conn->error;
            }
        } catch (Exception $e) {
            $errors[] = "❌ Exceção ao adicionar coluna '$column': " . $e->getMessage();
        }
    } else {
        $success[] = "ℹ️ Coluna '$column' já existe na tabela comments";
    }
}

// 2. Criar tabela subscription_plans
echo "<h3 class='info'>2. Verificando tabela subscription_plans...</h3>";

$table_exists = $conn->query("SHOW TABLES LIKE 'subscription_plans'")->num_rows > 0;

if (!$table_exists) {
    $create_plans_table = "
    CREATE TABLE `subscription_plans` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($create_plans_table)) {
        $success[] = "✅ Tabela subscription_plans criada";
    } else {
        $errors[] = "❌ Erro ao criar tabela subscription_plans: " . $conn->error;
    }
} else {
    $success[] = "ℹ️ Tabela subscription_plans já existe";
}

// 3. Inserir planos padrão
echo "<h3 class='info'>3. Inserindo planos padrão...</h3>";

$plans_count = $conn->query("SELECT COUNT(*) as count FROM subscription_plans")->fetch_assoc()['count'] ?? 0;

if ($plans_count == 0) {
    $plans = [
        ['Bronze', 'bronze', 5.99, '{"likes": true, "comments": false, "private_photos": false, "private_videos": false, "emoji_comments": false, "highlighted_comments": false}', '#CD7F32', 'fas fa-medal', 1],
        ['Silver', 'silver', 9.99, '{"likes": true, "comments": true, "private_photos": false, "private_videos": false, "emoji_comments": false, "highlighted_comments": false}', '#C0C0C0', 'fas fa-gem', 2],
        ['Silver+', 'silver_plus', 14.99, '{"likes": true, "comments": true, "private_photos": true, "private_videos": false, "emoji_comments": false, "highlighted_comments": false}', '#E6E6FA', 'fas fa-star', 3],
        ['Gold', 'gold', 19.99, '{"likes": true, "comments": true, "private_photos": true, "private_videos": true, "emoji_comments": false, "highlighted_comments": false}', '#FFD700', 'fas fa-crown', 4],
        ['Platinum', 'platinum', 29.99, '{"likes": true, "comments": true, "private_photos": true, "private_videos": true, "emoji_comments": true, "highlighted_comments": true}', '#E5E4E2', 'fas fa-diamond', 5]
    ];
    
    $stmt = $conn->prepare("INSERT INTO subscription_plans (name, slug, price, features, color, icon, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($plans as $plan) {
        $stmt->bind_param("ssdssi", $plan[0], $plan[1], $plan[2], $plan[3], $plan[4], $plan[5], $plan[6]);
        
        if ($stmt->execute()) {
            $success[] = "✅ Plano '{$plan[0]}' inserido";
        } else {
            $errors[] = "❌ Erro ao inserir plano '{$plan[0]}': " . $stmt->error;
        }
    }
    $stmt->close();
} else {
    $success[] = "ℹ️ Planos já existem ($plans_count planos encontrados)";
}

// 4. Verificar e adicionar colunas na tabela users
echo "<h3 class='info'>4. Verificando colunas de plano na tabela users...</h3>";

$user_columns = [];
$result = $conn->query("DESCRIBE users");
while ($row = $result->fetch_assoc()) {
    $user_columns[] = $row['Field'];
}

if (!in_array('current_plan', $user_columns)) {
    if ($conn->query("ALTER TABLE `users` ADD COLUMN `current_plan` varchar(50) DEFAULT 'free' AFTER `is_admin`")) {
        $success[] = "✅ Coluna 'current_plan' adicionada à tabela users";
    } else {
        $errors[] = "❌ Erro ao adicionar coluna 'current_plan': " . $conn->error;
    }
} else {
    $success[] = "ℹ️ Coluna 'current_plan' já existe na tabela users";
}

if (!in_array('plan_expires_at', $user_columns)) {
    if ($conn->query("ALTER TABLE `users` ADD COLUMN `plan_expires_at` datetime DEFAULT NULL AFTER `current_plan`")) {
        $success[] = "✅ Coluna 'plan_expires_at' adicionada à tabela users";
    } else {
        $errors[] = "❌ Erro ao adicionar coluna 'plan_expires_at': " . $conn->error;
    }
} else {
    $success[] = "ℹ️ Coluna 'plan_expires_at' já existe na tabela users";
}

// 5. Atualizar usuários sem plano
echo "<h3 class='info'>5. Atualizando usuários sem plano...</h3>";

$updated = $conn->query("UPDATE users SET current_plan = 'free' WHERE current_plan IS NULL OR current_plan = ''");
if ($updated) {
    $affected = $conn->affected_rows;
    $success[] = "✅ $affected usuários atualizados para plano 'free'";
} else {
    $errors[] = "❌ Erro ao atualizar usuários: " . $conn->error;
}

// 6. Criar tabela de mídia privada
echo "<h3 class='info'>6. Verificando tabela escort_private_media...</h3>";

$media_table_exists = $conn->query("SHOW TABLES LIKE 'escort_private_media'")->num_rows > 0;

if (!$media_table_exists) {
    $create_media_table = "
    CREATE TABLE `escort_private_media` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($create_media_table)) {
        $success[] = "✅ Tabela escort_private_media criada";
    } else {
        $errors[] = "❌ Erro ao criar tabela escort_private_media: " . $conn->error;
    }
} else {
    $success[] = "ℹ️ Tabela escort_private_media já existe";
}

// 7. Testar sistema de comentários criando um comentário de teste
echo "<h3 class='info'>7. Testando sistema de comentários...</h3>";

// Verificar se há users e escorts para teste
$user_test = $conn->query("SELECT id FROM users LIMIT 1")->fetch_assoc();
$escort_test = $conn->query("SELECT id FROM escorts LIMIT 1")->fetch_assoc();

if ($user_test && $escort_test) {
    // Tentar inserir comentário de teste
    $test_comment = "Comentário de teste do sistema 😊";
    $stmt = $conn->prepare("INSERT INTO comments (user_id, target_type, target_id, content, has_emoji, is_highlighted, user_plan) VALUES (?, 'escort', ?, ?, 1, 0, 'free')");
    $stmt->bind_param("iis", $user_test['id'], $escort_test['id'], $test_comment);
    
    if ($stmt->execute()) {
        $test_comment_id = $conn->insert_id;
        $success[] = "✅ Comentário de teste inserido com sucesso (ID: $test_comment_id)";
        
        // Remover comentário de teste
        $conn->query("DELETE FROM comments WHERE id = $test_comment_id");
        $success[] = "✅ Comentário de teste removido";
    } else {
        $errors[] = "❌ Erro ao inserir comentário de teste: " . $stmt->error;
    }
    $stmt->close();
} else {
    $errors[] = "⚠️ Sem usuários ou escorts para teste";
}

// 8. Verificações finais
echo "<h3 class='info'>8. Verificações finais...</h3>";

// Verificar estrutura da tabela comments
$comment_structure = $conn->query("DESCRIBE comments");
$comment_columns = [];
while ($row = $comment_structure->fetch_assoc()) {
    $comment_columns[] = $row['Field'] . " (" . $row['Type'] . ")";
}
$success[] = "ℹ️ Estrutura da tabela comments: " . implode(", ", $comment_columns);

// Verificar total de planos
$total_plans = $conn->query("SELECT COUNT(*) as count FROM subscription_plans")->fetch_assoc()['count'];
$success[] = "ℹ️ Total de planos disponíveis: $total_plans";

// Exibir resultados
echo "<h3 class='success'>✅ Sucessos:</h3>";
foreach ($success as $msg) {
    echo "<p class='success'>$msg</p>";
}

if (!empty($errors)) {
    echo "<h3 class='error'>❌ Erros:</h3>";
    foreach ($errors as $msg) {
        echo "<p class='error'>$msg</p>";
    }
}

echo "<h3 class='info'>🏁 Processo concluído!</h3>";
echo "<p class='info'>Agora o sistema de comentários deve estar funcionando corretamente.</p>";
echo "<p class='info'><strong>Próximos passos:</strong></p>";
echo "<ul>";
echo "<li>Teste criando um comentário em um perfil de escort</li>";
echo "<li>Verifique se os planos estão funcionando em plans.php</li>";
echo "<li>Teste o sistema de likes e comentários</li>";
echo "</ul>";

$conn->close();
?>