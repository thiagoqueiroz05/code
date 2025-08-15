<?php
// Configuração específica para Hostinger
// IMPORTANTE: Atualize estas credenciais com as informações do seu painel Hostinger

// === CONFIGURAÇÃO DO BANCO DE DADOS ===
// No painel da Hostinger, vá em: Websites > Manage > Databases
// Copie as informações exatas de lá

define('DB_HOST', 'localhost');  // Geralmente é 'localhost' na Hostinger
define('DB_USER', 'u333528817_escorts');  // Seu usuário do banco
define('DB_PASS', 'At081093@');  // Sua senha do banco
define('DB_NAME', 'u333528817_escorts');  // Nome do banco

// === FUNÇÃO DE CONEXÃO ===
function getConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Erro de conexão: " . $conn->connect_error);
        }
        
        // Configurar charset
        $conn->set_charset("utf8mb4");
        
        return $conn;
        
    } catch (Exception $e) {
        // Log do erro
        error_log("Erro de conexão DB: " . $e->getMessage());
        
        // Mostrar erro amigável
        die("
        <div style='
            background: #1a1a1a; 
            color: #fff; 
            padding: 40px; 
            font-family: Arial, sans-serif;
            text-align: center;
        '>
            <h2>🔧 Configuração Necessária</h2>
            <p style='color: #ff6b6b; margin: 20px 0;'>
                Erro de conexão com o banco de dados
            </p>
            <div style='
                background: #2a2a2a; 
                padding: 20px; 
                border-radius: 10px; 
                margin: 20px auto;
                max-width: 600px;
                text-align: left;
            '>
                <h3>📋 Para configurar no Hostinger:</h3>
                <ol style='line-height: 1.8;'>
                    <li>Acesse o <strong>painel da Hostinger</strong></li>
                    <li>Vá em <strong>Websites → Manage → Databases</strong></li>
                    <li>Copie as credenciais do seu banco</li>
                    <li>Atualize o arquivo <code>config.php</code></li>
                    <li>Se não tiver banco, <strong>crie um novo</strong></li>
                </ol>
                
                <h3 style='margin-top: 20px;'>⚙️ Credenciais típicas Hostinger:</h3>
                <pre style='
                    background: #1a1a1a; 
                    padding: 15px; 
                    border-radius: 5px;
                    color: #00ff88;
                '>
Host: localhost
Usuário: u123456_nomedousuario
Senha: SuaSenhaSegura123
Banco: u123456_nomedobanco
                </pre>
            </div>
            
            <p style='color: #feca57;'>
                💡 Depois de configurar, recarregue esta página
            </p>
        </div>
        ");
    }
}

// === TESTE DE CONEXÃO ===
function testConnection() {
    try {
        $conn = getConnection();
        
        // Testar consulta simples
        $result = $conn->query("SELECT 1");
        
        if (!$result) {
            throw new Exception("Erro na consulta de teste");
        }
        
        $conn->close();
        return true;
        
    } catch (Exception $e) {
        return false;
    }
}

// === CRIAR TABELAS AUTOMATICAMENTE ===
function createTables() {
    $conn = getConnection();
    
    // Array com todas as queries de criação
    $tables = [
        'cities' => "
            CREATE TABLE IF NOT EXISTS `cities` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `title` varchar(100) NOT NULL,
              `slug` varchar(100) NOT NULL,
              `image_url` text DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ",
        
        'escorts' => "
            CREATE TABLE IF NOT EXISTS `escorts` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(100) NOT NULL,
              `age` int(11) DEFAULT NULL,
              `image_url` text DEFAULT NULL,
              `location` varchar(100) DEFAULT NULL,
              `zone` varchar(100) DEFAULT NULL,
              `description` text DEFAULT NULL,
              `phone` varchar(20) DEFAULT NULL,
              `nationality` varchar(10) DEFAULT NULL,
              `vip` tinyint(1) DEFAULT 0,
              `top` tinyint(1) DEFAULT 0,
              `super_vip` tinyint(1) DEFAULT 0,
              `verificado` tinyint(1) DEFAULT 0,
              `telegram` tinyint(1) DEFAULT 0,
              `city_id` int(11) DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              `type` enum('chica','trans') DEFAULT 'chica',
              `latitude` decimal(10,8) DEFAULT NULL,
              `longitude` decimal(11,8) DEFAULT NULL,
              `map_address` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `city_id` (`city_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ",
        
        'escort_gallery' => "
            CREATE TABLE IF NOT EXISTS `escort_gallery` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `escort_id` int(11) NOT NULL,
              `image_url` text NOT NULL,
              `image_title` varchar(255) DEFAULT NULL,
              `image_order` int(11) DEFAULT 0,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `escort_id` (`escort_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ",
        
        'users' => "
            CREATE TABLE IF NOT EXISTS `users` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `username` varchar(50) NOT NULL,
              `email` varchar(100) NOT NULL,
              `password` varchar(255) NOT NULL,
              `is_admin` tinyint(1) DEFAULT 0,
              `active` tinyint(1) DEFAULT 1,
              `avatar` text DEFAULT NULL,
              `bio` text DEFAULT NULL,
              `phone` varchar(20) DEFAULT NULL,
              `telegram` varchar(50) DEFAULT NULL,
              `reset_token` varchar(64) DEFAULT NULL,
              `reset_expires` datetime DEFAULT NULL,
              `email_verified` tinyint(1) DEFAULT 0,
              `verification_token` varchar(64) DEFAULT NULL,
              `last_login` datetime DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `email` (`email`),
              UNIQUE KEY `username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ",
        
        'likes' => "
            CREATE TABLE IF NOT EXISTS `likes` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `target_type` enum('escort','user') NOT NULL,
              `target_id` int(11) NOT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `unique_like` (`user_id`, `target_type`, `target_id`),
              KEY `user_id` (`user_id`),
              KEY `target_type_id` (`target_type`, `target_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ",
        
        'like_stats' => "
            CREATE TABLE IF NOT EXISTS `like_stats` (
              `target_type` enum('escort','user') NOT NULL,
              `target_id` int(11) NOT NULL,
              `total_likes` int(11) DEFAULT 0,
              `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`target_type`, `target_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        ",
        
        'user_activity_logs' => "
            CREATE TABLE IF NOT EXISTS `user_activity_logs` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `user_id` int(11) DEFAULT NULL,
              `action` varchar(50) NOT NULL,
              `description` text DEFAULT NULL,
              `ip_address` varchar(45) DEFAULT NULL,
              `user_agent` text DEFAULT NULL,
              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `user_id` (`user_id`),
              KEY `action` (`action`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        "
    ];
    
    $created = [];
    $errors = [];
    
    foreach ($tables as $table_name => $sql) {
        try {
            if ($conn->query($sql)) {
                $created[] = $table_name;
            } else {
                $errors[] = "$table_name: " . $conn->error;
            }
        } catch (Exception $e) {
            $errors[] = "$table_name: " . $e->getMessage();
        }
    }
    
    // Inserir dados de exemplo se tabelas foram criadas
    if (count($created) > 0) {
        insertSampleData($conn);
    }
    
    $conn->close();
    
    return [
        'created' => $created,
        'errors' => $errors
    ];
}

// === INSERIR DADOS DE EXEMPLO ===
function insertSampleData($conn) {
    // Verificar se já tem dados
    $result = $conn->query("SELECT COUNT(*) as count FROM cities");
    if ($result && $result->fetch_assoc()['count'] == 0) {
        
        // Inserir cidades de exemplo
        $cities = [
            ['Madrid', 'madrid', 'https://i.ibb.co/sample1.jpg'],
            ['Barcelona', 'barcelona', 'https://i.ibb.co/sample2.jpg'],
            ['Valencia', 'valencia', 'https://i.ibb.co/sample3.jpg']
        ];
        
        $stmt = $conn->prepare("INSERT INTO cities (title, slug, image_url) VALUES (?, ?, ?)");
        foreach ($cities as $city) {
            $stmt->bind_param("sss", $city[0], $city[1], $city[2]);
            $stmt->execute();
        }
        
        // Inserir escorts de exemplo
        $escorts = [
            ['Ana', 25, 'https://via.placeholder.com/300x400', 'Madrid', 'Centro', 'Linda acompanhante', '611111111', 'ES', 1],
            ['Sofia', 28, 'https://via.placeholder.com/300x400', 'Barcelona', 'Zona Norte', 'Escort VIP', '622222222', 'BR', 2],
        ];
        
        $stmt = $conn->prepare("INSERT INTO escorts (name, age, image_url, location, zone, description, phone, nationality, city_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        foreach ($escorts as $escort) {
            $stmt->bind_param("sissssssi", ...$escort);
            $stmt->execute();
        }
        
        // Criar usuário admin
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, is_admin, email_verified) VALUES (?, ?, ?, 1, 1)");
        $username = 'admin';
        $email = 'admin@citasnortes.com';
        $stmt->bind_param("sss", $username, $email, $admin_password);
        $stmt->execute();
    }
}

// === AUTO-CONFIGURAÇÃO ===
if (!testConnection()) {
    // Se chegou aqui, vai mostrar o erro de configuração
    getConnection();
} else {
    // Conexão OK - verificar se precisa criar tabelas
    $conn = getConnection();
    $tables_check = $conn->query("SHOW TABLES");
    
    if (!$tables_check || $tables_check->num_rows < 5) {
        // Criar tabelas automaticamente
        $result = createTables();
        
        if (count($result['created']) > 0) {
            echo "
            <div style='
                background: #1a1a1a; 
                color: #fff; 
                padding: 40px; 
                font-family: Arial;
                text-align: center;
            '>
                <h2 style='color: #00ff88;'>✅ Configuração Concluída!</h2>
                <p>Tabelas criadas: " . implode(', ', $result['created']) . "</p>
                <p style='color: #feca57;'>
                    🎉 Seu site CitasNortes está pronto!<br>
                    👤 Admin: admin@citasnortes.com | Senha: admin123
                </p>
                <a href='index.php' style='
                    background: linear-gradient(135deg, #ff6b6b, #feca57);
                    color: white;
                    padding: 15px 30px;
                    text-decoration: none;
                    border-radius: 25px;
                    font-weight: bold;
                    display: inline-block;
                    margin-top: 20px;
                '>🚀 Acessar Site</a>
            </div>
            ";
        }
    }
    
    $conn->close();
}
?>