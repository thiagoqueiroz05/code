<?php
// Helper de autenticação
session_start();

// Função para verificar se está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Função para verificar se é admin
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Função para redirecionar se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

// Função para redirecionar se não for admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: dashboard.php");
        exit;
    }
}

// Função para obter dados do usuário
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Função para fazer logout
function logout() {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Função para registrar atividade
function logActivity($action, $description = null, $user_id = null) {
    if (!$user_id && isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
    }
    
    if (!$user_id) return;
    
    $conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $stmt = $conn->prepare("INSERT INTO user_activity_logs (user_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $action, $description, $ip, $user_agent);
    $stmt->execute();
}

// Função para gerar header dinâmico
function renderUserMenu() {
    if (isLoggedIn()) {
        $user = getCurrentUser();
        $avatar = strtoupper(substr($user['username'], 0, 1));
        
        echo '<div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">' . $avatar . '</div>
                    <span>' . htmlspecialchars($user['username']) . '</span>
                </div>
                <div class="user-dropdown">
                    <a href="dashboard.php" class="dropdown-item">
                        <i class="fas fa-user"></i> Dashboard
                    </a>';
        
        if (isAdmin()) {
            echo '<a href="admin_escorts.php" class="dropdown-item">
                    <i class="fas fa-cog"></i> Admin
                  </a>';
        }
        
        echo '<a href="?logout=1" class="dropdown-item logout" onclick="return confirm(\'Tem certeza que deseja sair?\')">
                <i class="fas fa-sign-out-alt"></i> Sair
              </a>
            </div>
        </div>';
    } else {
        echo '<div class="header-actions">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" placeholder="Search" class="search-input" />
                </div>
                <a href="login.php" class="btn btn-outline">Login</a>
                <a href="https://t.me/CitasNortesSupport" target="_blank" class="btn btn-outline">Únete</a>
              </div>';
    }
}

// CSS para o menu de usuário
function renderUserMenuCSS() {
    echo '<style>
        .user-menu {
            position: relative;
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
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .user-info:hover {
            background: rgba(255, 255, 255, 0.15);
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
            font-size: 0.9rem;
        }

        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 15px;
            padding: 0.5rem 0;
            min-width: 200px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .user-menu:hover .user-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .dropdown-item.logout {
            color: #ff6b6b;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .dropdown-item.logout:hover {
            background: rgba(255, 107, 107, 0.2);
        }

        @media (max-width: 768px) {
            .user-info span {
                display: none;
            }
            
            .user-dropdown {
                right: -50px;
            }
        }
    </style>';
}

// Processar logout se solicitado
if (isset($_GET['logout'])) {
    logActivity('logout', 'Usuário fez logout');
    logout();
}
?>