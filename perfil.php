<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'auth.php';
include 'subscription_manager.php';

$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");
$subscriptionManager = new SubscriptionManager($conn);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Buscar dados da escort
$stmt = $conn->prepare("SELECT e.*, c.title as city_name FROM escorts e LEFT JOIN cities c ON e.city_id = c.id WHERE e.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$escort = $result->fetch_assoc();

if (!$escort) {
    header("HTTP/1.0 404 Not Found");
    exit;
}

// Definir valores padrão
$age = (!empty($escort['age']) && $escort['age'] > 0) ? $escort['age'] : rand(22, 28);
$location = !empty($escort['location']) ? $escort['location'] : 'Disponível';
$zone = !empty($escort['zone']) ? $escort['zone'] : '';
$description = !empty($escort['description']) ? $escort['description'] : 'Linda acompanhante disponível para encontros discretos.';
$phone = !empty($escort['phone']) ? $escort['phone'] : '';

// Verificar permissões do usuário
$userPermissions = [
    'can_like' => false,
    'can_comment' => false,
    'can_view_private_photos' => false,
    'can_view_private_videos' => false,
    'current_plan' => 'free'
];

if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $userPermissions = [
        'can_like' => $subscriptionManager->canLike($userId),
        'can_comment' => $subscriptionManager->canComment($userId),
        'can_view_private_photos' => $subscriptionManager->canViewPrivatePhotos($userId),
        'can_view_private_videos' => $subscriptionManager->canViewPrivateVideos($userId),
        'current_plan' => $subscriptionManager->getUserCurrentPlan($userId)['current_plan']
    ];
}

// Buscar galeria
$stmt_gallery = $conn->prepare("SELECT * FROM escort_gallery WHERE escort_id = ? ORDER BY image_order ASC, id ASC");
$stmt_gallery->bind_param("i", $id);
$stmt_gallery->execute();
$gallery_result = $stmt_gallery->get_result();
$gallery_photos = [];
while ($photo = $gallery_result->fetch_assoc()) {
    $gallery_photos[] = $photo;
}

if (empty($gallery_photos)) {
    $gallery_photos[] = [
        'image_url' => $escort['image_url'],
        'image_title' => 'Foto de Perfil'
    ];
}

// Buscar mídia privada
$private_media_count = ['photos' => 0, 'videos' => 0];
$stmt = $conn->prepare("SELECT media_type, COUNT(*) as count FROM escort_private_media WHERE escort_id = ? AND is_active = 1 GROUP BY media_type");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $private_media_count[$row['media_type'] . 's'] = $row['count'];
}

// Status de likes
$like_status = ['user_liked' => false, 'total_likes' => 0];

if (isLoggedIn()) {
    $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND target_type = 'escort' AND target_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $id);
    $stmt->execute();
    $like_status['user_liked'] = $stmt->get_result()->num_rows > 0;
}

$stmt = $conn->prepare("SELECT total_likes FROM like_stats WHERE target_type = 'escort' AND target_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$like_status['total_likes'] = $stats['total_likes'] ?? 0;

// Comentários
$comments = [];
if (isLoggedIn()) {
    $stmt = $conn->prepare("
        SELECT c.*, u.username, sp.name as plan_name, sp.color as plan_color, sp.icon as plan_icon
        FROM comments c
        JOIN users u ON c.user_id = u.id
        LEFT JOIN subscription_plans sp ON c.user_plan = sp.slug
        WHERE c.target_type = 'escort' AND c.target_id = ? AND c.is_approved = 1
        ORDER BY c.is_highlighted DESC, c.created_at DESC LIMIT 20
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($comment = $result->fetch_assoc()) {
        $comments[] = $comment;
    }
}

// Buscar escorts aleatórias
$random_escorts = [];
$stmt = $conn->prepare("
    SELECT e.*, c.title as city_name 
    FROM escorts e 
    LEFT JOIN cities c ON e.city_id = c.id 
    WHERE e.id != ? 
    ORDER BY RAND() 
    LIMIT 4
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($random_escort = $result->fetch_assoc()) {
    $random_escorts[] = $random_escort;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Citas - Escorts en España</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
/* Reset básico */
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
  overflow-x: hidden;
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

.container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
}

.header .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
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
}

.logo-icon {
  margin-right: 0.5rem;
  font-size: 2rem;
  background: linear-gradient(135deg, #ff6b6b, #feca57);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.nav {
  display: flex;
  gap: 2rem;
  align-items: center;
}

/* CORRIGIDO: Nav item com dropdown */
.nav-item {
  position: relative;
  display: flex;
  align-items: center;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 30px;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  cursor: pointer;
}

.nav-link:hover {
  background: linear-gradient(135deg, #ff6b6b, #feca57);
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 107, 107, 0.3);
}

.nav-link.telegram {
  background: linear-gradient(135deg, rgba(0, 136, 204, 0.2), rgba(84, 160, 255, 0.2));
  border-color: rgba(0, 136, 204, 0.3);
}

.nav-link.telegram:hover {
  background: linear-gradient(135deg, #0088cc, #54a0ff);
  border-color: transparent;
}

/* CORRIGIDO: Dropdown Menu Styles */
.nav-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
  border-radius: 15px;
  padding: 1rem 0;
  min-width: 280px;
  max-width: 350px;
  box-shadow: 0 15px 35px rgba(0,0,0,0.6);
  border: 1px solid rgba(255, 255, 255, 0.1);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.3s ease;
  z-index: 2000;
  max-height: 400px;
  overflow-y: auto;
}

.nav-item:hover .nav-dropdown {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-header {
  padding: 0.5rem 1.5rem 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  margin-bottom: 0.5rem;
}

.dropdown-title {
  font-size: 1.1rem;
  font-weight: bold;
  background: linear-gradient(135deg, #ff6b6b, #feca57);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 0.3rem;
}

.dropdown-subtitle {
  font-size: 0.85rem;
  color: #aaa;
}

.cities-dropdown-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 0.3rem;
  padding: 0 1rem;
}

.city-dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding: 0.8rem;
  color: white;
  text-decoration: none;
  transition: all 0.3s ease;
  border-radius: 10px;
  font-size: 0.9rem;
}

.city-dropdown-item:hover {
  background: rgba(255, 107, 107, 0.15);
  transform: translateX(5px);
}

.city-dropdown-icon {
  width: 8px;
  height: 8px;
  background: #00ff00;
  border-radius: 50%;
  flex-shrink: 0;
  box-shadow: 0 0 8px #00ff00;
  animation: pulse 2s infinite;
}

.view-all-cities {
  margin: 1rem 1rem 0.5rem;
  padding: 0.8rem 1rem;
  background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(254, 202, 87, 0.1));
  border: 1px solid rgba(255, 107, 107, 0.3);
  border-radius: 10px;
  color: white;
  text-decoration: none;
  text-align: center;
  font-weight: 500;
  transition: all 0.3s ease;
  display: block;
}

.view-all-cities:hover {
  background: linear-gradient(135deg, rgba(255, 107, 107, 0.2), rgba(254, 202, 87, 0.2));
  border-color: rgba(255, 107, 107, 0.5);
  transform: translateY(-2px);
}

/* CORRIGIDO: Dropdown arrow - agora clicável separadamente */
.dropdown-arrow {
  margin-left: 0.3rem;
  transition: transform 0.3s ease;
  font-size: 0.8rem;
  cursor: pointer;
  padding: 0.2rem;
  border-radius: 50%;
}

.dropdown-arrow:hover {
  background: rgba(255, 255, 255, 0.1);
}

.nav-item:hover .dropdown-arrow {
  transform: rotate(180deg);
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.search-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.5rem 2.5rem 0.5rem 1rem;
  border-radius: 25px;
  outline: none;
  transition: all 0.3s ease;
  min-width: 200px;
}

.search-input:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: #ff6b6b;
  box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
}

.search-icon {
  position: absolute;
  right: 1rem;
  color: #888;
}

.btn {
  padding: 0.5rem 1.5rem;
  border: none;
  border-radius: 25px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  text-decoration: none;
  display: inline-block;
}

.btn-outline {
  background: transparent;
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-outline:hover {
  border-color: #ff6b6b;
  background: linear-gradient(135deg, #ff6b6b, #feca57);
  transform: translateY(-2px);
  box-shadow: 0 3px 10px rgba(255, 107, 107, 0.3);
}

/* Mobile Menu Button */
.mobile-menu-btn {
  display: none;
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
  padding: 0.5rem;
}

/* Mobile Menu */
.mobile-menu {
  position: fixed;
  top: 0;
  right: -100%;
  width: 300px;
  height: 100vh;
  background: linear-gradient(135deg, rgba(20, 20, 20, 0.98), rgba(40, 40, 40, 0.98));
  backdrop-filter: blur(20px);
  display: flex;
  flex-direction: column;
  padding: 2rem;
  transition: right 0.3s ease;
  z-index: 9999;
  box-shadow: -4px 0 20px rgba(0, 0, 0, 0.5);
}

.mobile-menu.active {
  right: 0;
}

.mobile-menu-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.mobile-menu-close {
  background: none;
  border: none;
  color: white;
  font-size: 1.5rem;
  cursor: pointer;
}

.mobile-menu a {
  color: white;
  text-decoration: none;
  padding: 1rem;
  margin-bottom: 0.5rem;
  border-radius: 15px;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
}

.mobile-menu a:hover {
  background: linear-gradient(135deg, #ff6b6b, #feca57);
}

/* CORRIGIDO: Mobile dropdown styles mais limpo */
.mobile-nav-item {
  margin-bottom: 0.5rem;
}

.mobile-nav-header {
  display: flex;
  align-items: center;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 15px;
  overflow: hidden;
}

.mobile-nav-main-link {
  flex: 1;
  color: white;
  text-decoration: none;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
  background: none;
}

.mobile-nav-main-link:hover {
  background: linear-gradient(135deg, #ff6b6b, #feca57);
}

.mobile-nav-dropdown-btn {
  background: none;
  border: none;
  color: white;
  padding: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border-left: 1px solid rgba(255, 255, 255, 0.1);
}

.mobile-nav-dropdown-btn:hover {
  background: rgba(255, 255, 255, 0.1);
}

.mobile-nav-toggle {
  background: none;
  border: none;
  color: white;
  padding: 1rem;
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  transition: all 0.3s ease;
  width: 100%;
  text-align: left;
  cursor: pointer;
}

.mobile-nav-toggle:hover {
  background: linear-gradient(135deg, #ff6b6b, #feca57);
}

/* CORRIGIDO: Adicionado link mobile direto */
.mobile-nav-link {
  background: none;
  border: none;
  color: white;
  padding: 1rem;
  border-radius: 15px;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: all 0.3s ease;
  width: 100%;
  text-align: left;
  cursor: pointer;
  text-decoration: none;
  margin-bottom: 0.5rem;
}

.mobile-nav-link:hover {
  background: linear-gradient(135deg, #ff6b6b, #feca57);
}

.mobile-cities-dropdown {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.3s ease;
  background: rgba(0, 0, 0, 0.3);
  border-radius: 10px;
  margin-top: 0.5rem;
}

.mobile-cities-dropdown.active {
  max-height: 300px;
  overflow-y: auto;
}

.mobile-cities-dropdown a {
  padding: 0.7rem 1rem;
  margin-bottom: 0.2rem;
  font-size: 0.9rem;
  border-radius: 8px;
}

/* Overlay */
.mobile-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9998;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
}

.mobile-overlay.active {
  opacity: 1;
  visibility: visible;
}

/* User Menu CSS */
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

/* Main Content */
.main {
  padding: 2rem 0;
}

/* Mobile Search */
.mobile-search {
  display: none;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  margin-bottom: 1rem;
  border-radius: 15px;
}

.mobile-search-input {
  width: 100%;
  background: rgba(255, 255, 255, 0.1);
  border: 2px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 0.8rem 1rem;
  border-radius: 10px;
  outline: none;
  transition: all 0.3s ease;
  font-size: 1rem;
}

.mobile-search-input:focus {
  border-color: #ff6b6b;
  background: rgba(255, 255, 255, 0.15);
}

.mobile-search-input::placeholder {
  color: #aaa;
}

/* Cities Grid */
.cities-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  margin-bottom: 4rem;
}

.city-card {
  position: relative;
  border-radius: 20px;
  overflow: hidden;
  transition: all 0.4s ease;
  text-decoration: none;
  color: white;
  background: linear-gradient(135deg, #2a2a2a, #1e1e1e);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.city-card:hover {
  transform: translateY(-10px) scale(1.02);
  box-shadow: 0 15px 40px rgba(255, 107, 107, 0.2);
}

.city-image {
  position: relative;
  height: 200px;
  overflow: hidden;
}

.city-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.4s ease;
}

.city-card:hover .city-image img {
  transform: scale(1.1);
}

.city-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(255, 107, 107, 0.8), rgba(254, 202, 87, 0.8));
  opacity: 0;
  transition: opacity 0.3s ease;
}

.city-card:hover .city-overlay {
  opacity: 1;
}

.city-indicator {
  position: absolute;
  top: 15px;
  right: 15px;
  width: 12px;
  height: 12px;
  background: #00ff00;
  border-radius: 50%;
  box-shadow: 0 0 10px #00ff00;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { box-shadow: 0 0 10px #00ff00; }
  50% { box-shadow: 0 0 20px #00ff00, 0 0 30px #00ff00; }
  100% { box-shadow: 0 0 10px #00ff00; }
}

.city-icon {
  position: absolute;
  top: 50%;
  right: 20px;
  transform: translateY(-50%);
  font-size: 2rem;
  color: white;
  opacity: 0.8;
  transition: all 0.3s ease;
}

.city-card:hover .city-icon {
  transform: translateY(-50%) translateX(-5px);
  opacity: 1;
}

.city-info {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 1.5rem;
  background: linear-gradient(to top, rgba(0, 0, 0, 0.9), transparent);
}

.city-info h3 {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0;
}

/* Show More Button */
.show-more-cities {
  display: none;
  width: 100%;
  text-align: center;
  margin: 1rem 0 2rem;
}

.show-more-btn {
  padding: 0.8rem 2rem;
  background: linear-gradient(135deg, #ff6b6b, #feca57);
  color: white;
  border: none;
  border-radius: 25px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.show-more-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
}

/* Sections */
section {
  margin-bottom: 4rem;
}

section h2 {
  font-size: 2.5rem;
  text-align: center;
  margin-bottom: 2rem;
  background: linear-gradient(135deg, #ff6b6b, #feca57);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  position: relative;
}

section h2::after {
  content: '';
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 100px;
  height: 3px;
  background: linear-gradient(135deg, #ff6b6b, #feca57);
  border-radius: 2px;
}

/* Footer */
.footer {
  background: linear-gradient(135deg, #1a1a1a, #0f0f0f);
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding: 3rem 0 2rem;
  margin-top: 4rem;
}

.footer-links {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
  margin-bottom: 2rem;
}

.footer-column a {
  display: block;
  color: #888;
  text-decoration: none;
  padding: 0.5rem 0;
  transition: color 0.3s ease;
}

.footer-column a:hover {
  color: #ff6b6b;
}

.legal-notice {
  text-align: center;
  color: #666;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  padding-top: 2rem;
  font-size: 0.9rem;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .header .container {
    padding: 1rem;
  }

  .nav, .header-actions {
    display: none;
  }

  .mobile-menu-btn {
    display: block;
  }

  .container {
    padding: 0 1rem;
  }

  .mobile-search {
    display: block;
  }

  /* CORRIGIDO: Mobile Cities Grid - REMOVIDO o CSS que força display:none */
  .cities-grid {
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 0.8rem !important;
    margin-bottom: 1rem !important;
  }

  .city-card {
    height: 120px !important;
    border-radius: 15px !important;
    /* REMOVIDO: display: none forçado */
  }

  .city-image {
    height: 85px !important;
    border-radius: 15px 15px 0 0 !important;
  }

  .city-info {
    padding: 0.6rem !important;
    position: relative !important;
    background: rgba(0, 0, 0, 0.8) !important;
    border-radius: 0 0 15px 15px !important;
  }

  .city-info h3 {
    font-size: 0.85rem !important;
    line-height: 1.1 !important;
    margin: 0 !important;
    text-align: center !important;
  }

  .city-icon {
    font-size: 1rem !important;
    right: 6px !important;
    top: 6px !important;
  }

  .city-indicator {
    top: 8px !important;
    right: 8px !important;
    width: 8px !important;
    height: 8px !important;
  }

  .show-more-cities {
    display: block;
  }

  /* NOVO: Classes para controle JavaScript das cidades mobile */
  .city-card.js-hidden {
    display: none !important;
  }

  .city-card.js-visible {
    display: block !important;
  }

  section h2 {
    font-size: 2rem;
  }

  .user-info span {
    display: none;
  }
}

@media (max-width: 480px) {
  .logo {
    font-size: 1.5rem;
  }

  section h2 {
    font-size: 1.8rem;
  }

  /* MOBILE PEQUENO: Ajustar cidades para telas menores */
  .cities-grid {
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 0.6rem !important;
  }

  .city-card {
    height: 100px !important;
  }

  .city-image {
    height: 70px !important;
  }

  .city-info {
    padding: 0.4rem !important;
  }

  .city-info h3 {
    font-size: 0.75rem !important;
  }

  .city-icon {
    font-size: 0.9rem !important;
    right: 4px !important;
    top: 4px !important;
  }

  .mobile-search-input {
    font-size: 0.9rem;
    padding: 0.7rem;
  }
}

/* Loading Animation */
.loading {
  opacity: 0;
  animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Scroll Animations */
.animate-on-scroll {
  opacity: 0;
  transform: translateY(30px);
  transition: all 0.6s ease;
}

.animate-on-scroll.visible {
  opacity: 1;
  transform: translateY(0);
}

/* NOVO: Scrollbar personalizada para dropdown */
.nav-dropdown::-webkit-scrollbar,
.mobile-cities-dropdown::-webkit-scrollbar {
  width: 6px;
}

.nav-dropdown::-webkit-scrollbar-track,
.mobile-cities-dropdown::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

.nav-dropdown::-webkit-scrollbar-thumb,
.mobile-cities-dropdown::-webkit-scrollbar-thumb {
  background: linear-gradient(135deg, #ff6b6b, #feca57);
  border-radius: 3px;
}
  </style>
</head>
<body>
  <!-- Mobile Overlay -->
  <div class="mobile-overlay" id="mobileOverlay"></div>

  <!-- Mobile Menu -->
  <div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header">
      <div style="font-size: 1.2rem; font-weight: bold; background: linear-gradient(135deg, #ff6b6b, #feca57); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">🔥 Citas</div>
      <button class="mobile-menu-close" id="mobileMenuClose">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- CORRIGIDO: Mobile nav items mais limpo -->
    <div class="mobile-nav-item">
      <div class="mobile-nav-header">
        <a href="index.php?type=chica" class="mobile-nav-main-link">
          <i class="fas fa-venus"></i> Chicas
        </a>
        <button class="mobile-nav-dropdown-btn" onclick="toggleMobileCitiesDropdown('chica')">
          <i class="fas fa-chevron-down dropdown-arrow" id="arrow-chica"></i>
        </button>
      </div>
      <div class="mobile-cities-dropdown" id="mobile-dropdown-chica">
        <!-- Cidades serão carregadas aqui via JavaScript -->
      </div>
    </div>

    <div class="mobile-nav-item">
      <div class="mobile-nav-header">
        <a href="index.php?type=chico" class="mobile-nav-main-link">
          <i class="fas fa-mars"></i> Chicos
        </a>
        <button class="mobile-nav-dropdown-btn" onclick="toggleMobileCitiesDropdown('chico')">
          <i class="fas fa-chevron-down dropdown-arrow" id="arrow-chico"></i>
        </button>
      </div>
      <div class="mobile-cities-dropdown" id="mobile-dropdown-chico">
        <!-- Cidades serão carregadas aqui via JavaScript -->
      </div>
    </div>

    <div class="mobile-nav-item">
      <div class="mobile-nav-header">
        <a href="index.php?type=trans" class="mobile-nav-main-link">
          <i class="fas fa-transgender"></i> Trans
        </a>
        <button class="mobile-nav-dropdown-btn" onclick="toggleMobileCitiesDropdown('trans')">
          <i class="fas fa-chevron-down dropdown-arrow" id="arrow-trans"></i>
        </button>
      </div>
      <div class="mobile-cities-dropdown" id="mobile-dropdown-trans">
        <!-- Cidades serão carregadas aqui via JavaScript -->
      </div>
    </div>

    <a href="https://t.me/CitasNortes" target="_blank"><i class="fab fa-telegram"></i> Telegram</a>
    
    <?php if (isLoggedIn()): ?>
      <?php $current_user = getCurrentUser(); ?>
      <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 1rem 0; padding-top: 1rem;">
        <div style="padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 10px; margin-bottom: 1rem;">
          <strong><?= htmlspecialchars($current_user['username']) ?></strong>
        </div>
        <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <?php if (isAdmin()): ?>
        <a href="admin_escorts.php"><i class="fas fa-cog"></i> Admin</a>
        <?php endif; ?>
        <a href="?logout=1" onclick="return confirm('Sair?')"><i class="fas fa-sign-out-alt"></i> Sair</a>
      </div>
    <?php else: ?>
      <div style="border-top: 1px solid rgba(255,255,255,0.1); margin: 1rem 0; padding-top: 1rem;">
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="login.php?mode=register"><i class="fas fa-user-plus"></i> Registrar</a>
        <a href="unete.php"><i class="fas fa-star"></i> Únete</a>
      </div>
    <?php endif; ?>
  </div>

  <!-- Header -->
  <header class="header">
    <div class="container">
      <div class="logo">
        <span class="logo-icon">🔥</span>
        <span>Citas</span>
      </div>
      
      <button class="mobile-menu-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
      </button>
      
      <nav class="nav">
        <!-- CORRIGIDO: Nav items com dropdown mas mantendo link clicável -->
        <div class="nav-item">
          <a href="index.php?type=chica" class="nav-link">
            <i class="fas fa-venus"></i>Chicas
            <i class="fas fa-chevron-down dropdown-arrow" onclick="event.preventDefault(); event.stopPropagation();"></i>
          </a>
          <div class="nav-dropdown">
            <div class="dropdown-header">
              <div class="dropdown-title">🔥 Chicas Disponibles</div>
              <div class="dropdown-subtitle">Selecciona tu ciudad</div>
            </div>
            <div class="cities-dropdown-grid" id="desktop-dropdown-chica">
              <!-- Cidades serão carregadas aqui via JavaScript -->
            </div>
            <a href="index.php?type=chica" class="view-all-cities">
              <i class="fas fa-map-marker-alt"></i> Ver todas las ciudades
            </a>
          </div>
        </div>

        <div class="nav-item">
          <a href="index.php?type=chico" class="nav-link">
            <i class="fas fa-mars"></i>Chicos
            <i class="fas fa-chevron-down dropdown-arrow" onclick="event.preventDefault(); event.stopPropagation();"></i>
          </a>
          <div class="nav-dropdown">
            <div class="dropdown-header">
              <div class="dropdown-title">💪 Chicos Disponibles</div>
              <div class="dropdown-subtitle">Selecciona tu ciudad</div>
            </div>
            <div class="cities-dropdown-grid" id="desktop-dropdown-chico">
              <!-- Cidades serão carregadas aqui via JavaScript -->
            </div>
            <a href="index.php?type=chico" class="view-all-cities">
              <i class="fas fa-map-marker-alt"></i> Ver todas las ciudades
            </a>
          </div>
        </div>

        <div class="nav-item">
          <a href="index.php?type=trans" class="nav-link">
            <i class="fas fa-transgender"></i>Trans
            <i class="fas fa-chevron-down dropdown-arrow" onclick="event.preventDefault(); event.stopPropagation();"></i>
          </a>
          <div class="nav-dropdown">
            <div class="dropdown-header">
              <div class="dropdown-title">✨ Trans Disponibles</div>
              <div class="dropdown-subtitle">Selecciona tu ciudad</div>
            </div>
            <div class="cities-dropdown-grid" id="desktop-dropdown-trans">
              <!-- Cidades serão carregadas aqui via JavaScript -->
            </div>
            <a href="index.php?type=trans" class="view-all-cities">
              <i class="fas fa-map-marker-alt"></i> Ver todas las ciudades
            </a>
          </div>
        </div>

        <a href="https://t.me/CitasNortes" target="_blank" class="nav-link telegram"><i class="fab fa-telegram"></i>Telegram</a>
      </nav>
      
      <?php if (isLoggedIn()): ?>
        <?php $current_user = getCurrentUser(); ?>
        <div class="user-menu">
          <div class="user-info">
            <div class="user-avatar">
              <?= strtoupper(substr($current_user['username'], 0, 1)) ?>
            </div>
            <span><?= htmlspecialchars($current_user['username']) ?></span>
          </div>
          <div class="user-dropdown">
            <a href="dashboard.php" class="dropdown-item">
              <i class="fas fa-user"></i> Dashboard
            </a>
            <?php if (isAdmin()): ?>
            <a href="admin_escorts.php" class="dropdown-item">
              <i class="fas fa-cog"></i> Admin
            </a>
            <?php endif; ?>
            <a href="?logout=1" class="dropdown-item logout" onclick="return confirm('Sair?')">
              <i class="fas fa-sign-out-alt"></i> Sair
            </a>
          </div>
        </div>
      <?php else: ?>
        <div class="header-actions">
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" placeholder="Search" class="search-input" />
          </div>
          <a href="login.php" class="btn btn-outline">Login</a>
          <a href="unete.php" class="btn btn-outline">Únete</a>
        </div>
      <?php endif; ?>
    </div>
  </header>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($escort['name']) ?> - <?= $age ?> años - Citas</title>
    <meta name="description" content="<?= htmlspecialchars(mb_strimwidth($description, 0, 150, '...')) ?>">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        :root {
            --gradient-primary: linear-gradient(135deg, #ff6b6b, #feca57);
            --gradient-secondary: linear-gradient(135deg, #667eea, #764ba2);
            --bg-dark: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #0f0f0f 100%);
            --bg-card: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            --text-primary: #ffffff;
            --text-secondary: #e0e0e0;
            --text-muted: #aaaaaa;
            --border-color: rgba(255, 255, 255, 0.1);
            --shadow-card: 0 4px 15px rgba(0,0,0,0.2);
            --shadow-hover: 0 6px 20px rgba(255, 107, 107, 0.15);
            --radius: 12px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: var(--bg-dark); 
            color: var(--text-primary); 
            font-family: 'Segoe UI', system-ui, sans-serif; 
            min-height: 100vh; 
            line-height: 1.5; 
            font-size: 14px;
        }

        /* Header - Compacto */
        .header { 
            background: rgba(20, 20, 20, 0.95); 
            backdrop-filter: blur(20px); 
            border-bottom: 1px solid var(--border-color); 
            position: sticky; 
            top: 0; 
            z-index: 1000; 
            box-shadow: var(--shadow-card); 
        }
        .header-content { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 0.8rem 1rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        }
        .logo { 
            font-size: 1.4rem; 
            font-weight: bold; 
            background: var(--gradient-primary); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            text-decoration: none; 
        }
        .nav { display: flex; gap: 1rem; align-items: center; }
        .nav-link { 
            color: var(--text-primary); 
            text-decoration: none; 
            font-weight: 500; 
            padding: 0.3rem 0.6rem; 
            border-radius: 15px; 
            transition: var(--transition); 
            font-size: 0.85rem;
        }
        .nav-link:hover { 
            background: var(--gradient-primary); 
            transform: translateY(-1px); 
        }

        /* User Menu - Compacto */
        .user-menu { position: relative; display: flex; align-items: center; gap: 0.6rem; }
        .user-info { 
            display: flex; 
            align-items: center; 
            gap: 0.3rem; 
            padding: 0.3rem 0.6rem; 
            background: rgba(255, 255, 255, 0.1); 
            border-radius: 15px; 
            cursor: pointer; 
            transition: all 0.3s ease; 
        }
        .user-avatar { 
            width: 24px; 
            height: 24px; 
            border-radius: 50%; 
            background: linear-gradient(135deg, #ff6b6b, #feca57); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
            font-size: 0.7rem; 
            color: white; 
        }

        /* Container - Mobile First */
        .profile-container { 
            max-width: 420px; 
            margin: 0 auto; 
            padding: 0 0.5rem; 
        }
        
        .back-btn { 
            display: inline-flex; 
            align-items: center; 
            gap: 0.3rem; 
            color: var(--text-muted); 
            text-decoration: none; 
            padding: 0.4rem 0.8rem; 
            border-radius: 8px; 
            background: rgba(255, 255, 255, 0.05); 
            transition: var(--transition); 
            width: fit-content; 
            font-size: 0.85rem;
            margin: 0.5rem 0;
        }
        .back-btn:hover { 
            color: #ff6b6b; 
            background: rgba(255, 107, 107, 0.1); 
            transform: translateX(-3px); 
        }

        /* Profile Card - Mobile Optimized */
        .profile-card { 
            background: var(--bg-card); 
            border-radius: var(--radius); 
            overflow: hidden;
            box-shadow: var(--shadow-card); 
            border: 1px solid var(--border-color); 
            margin-bottom: 1rem;
        }

        /* Main Image Section */
        .main-image-container {
            position: relative;
            width: 100%;
            aspect-ratio: 3/4;
            overflow: hidden;
        }
        
        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .main-image:hover {
            transform: scale(1.05);
        }

        /* Profile Badges */
        .profile-badges { 
            position: absolute; 
            top: 0.5rem; 
            right: 0.5rem; 
            display: flex; 
            flex-direction: column; 
            gap: 0.3rem; 
        }
        .profile-badge { 
            padding: 0.2rem 0.5rem; 
            border-radius: 12px; 
            font-size: 0.65rem; 
            font-weight: bold; 
            color: white; 
            text-align: center; 
            backdrop-filter: blur(10px); 
        }
        .profile-badge.super-vip { 
            background: linear-gradient(135deg, #ff0844, #ff6b6b); 
            animation: glow-vip 2s ease-in-out infinite alternate; 
        }
        .profile-badge.vip { background: linear-gradient(135deg, #ff6b6b, #feca57); }
        .profile-badge.verificado { background: linear-gradient(135deg, #48dbfb, #0abde3); }
        @keyframes glow-vip { 
            0% { box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3), 0 0 8px rgba(255, 8, 68, 0.5); } 
            100% { box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3), 0 0 15px rgba(255, 8, 68, 0.8); } 
        }

        /* Profile Info - Grid Compacto */
        .profile-info {
            padding: 1rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
        
        .info-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            color: white;
            flex-shrink: 0;
        }
        
        .info-content {
            flex: 1;
            min-width: 0;
        }
        
        .info-label {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-bottom: 0.1rem;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .info-value {
            font-size: 0.85rem;
            color: var(--text-primary);
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Nationality Flag */
        .nationality-flag { 
            width: 20px; 
            height: 14px; 
            border-radius: 2px; 
            object-fit: cover; 
            margin-left: 0.3rem;
        }

        /* About Section */
        .about-section {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            padding: 0.8rem;
            border: 1px solid var(--border-color);
            margin-bottom: 1rem;
        }
        
        .about-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .about-content {
            color: var(--text-secondary);
            font-size: 0.85rem;
            line-height: 1.5;
        }

        /* Contact Buttons */
        .contact-buttons { 
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .contact-btn { 
            display: flex; 
            align-items: center; 
            justify-content: center;
            gap: 0.4rem; 
            padding: 0.7rem; 
            border-radius: 8px; 
            font-weight: 600; 
            text-decoration: none; 
            transition: var(--transition); 
            color: white; 
            font-size: 0.8rem;
        }
        .whatsapp-btn { background: linear-gradient(135deg, #25D366, #128C7E); }
        .telegram-btn { background: linear-gradient(135deg, #0088cc, #0066aa); }
        .contact-btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); 
        }

        /* Actions Bar */
        .actions-bar { 
            background: var(--bg-card); 
            border-radius: var(--radius); 
            padding: 0.8rem; 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 0.5rem; 
            box-shadow: var(--shadow-card); 
            border: 1px solid var(--border-color); 
            margin-bottom: 1rem;
        }
        .action-btn { 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            gap: 0.3rem; 
            padding: 0.8rem 0.4rem; 
            background: rgba(255, 255, 255, 0.05); 
            border-radius: 8px; 
            cursor: pointer; 
            transition: var(--transition); 
            border: 2px solid transparent; 
            text-decoration: none; 
            color: var(--text-primary); 
            text-align: center; 
        }
        .action-btn:hover { 
            background: rgba(255, 255, 255, 0.1); 
            transform: translateY(-2px); 
        }
        .action-btn.liked { 
            border-color: #ff6b6b; 
            background: rgba(255, 107, 107, 0.2); 
        }
        .action-btn.disabled { opacity: 0.5; cursor: not-allowed; }
        .action-icon { 
            font-size: 1.2rem; 
            background: var(--gradient-primary); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }
        .action-text { font-size: 0.75rem; font-weight: 600; }
        .action-count { font-size: 0.7rem; color: var(--text-muted); }

        /* Sections */
        .section { 
            background: var(--bg-card); 
            border-radius: var(--radius); 
            padding: 1rem; 
            box-shadow: var(--shadow-card); 
            border: 1px solid var(--border-color); 
            margin-bottom: 1rem;
        }
        .section-title { 
            font-size: 1.2rem; 
            margin-bottom: 1rem; 
            text-align: center; 
            background: var(--gradient-primary); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
            position: relative; 
        }
        .section-title::after { 
            content: ''; 
            position: absolute; 
            bottom: -3px; 
            left: 50%; 
            transform: translateX(-50%); 
            width: 40px; 
            height: 2px; 
            background: var(--gradient-primary); 
            border-radius: 1px; 
        }

        /* Gallery Grid */
        .gallery-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr); 
            gap: 0.5rem; 
        }
        .gallery-item { 
            position: relative; 
            border-radius: 8px; 
            overflow: hidden; 
            cursor: pointer; 
            transition: var(--transition); 
            aspect-ratio: 3/4; 
        }
        .gallery-item:hover { 
            transform: scale(1.05); 
        }
        .gallery-item img { width: 100%; height: 100%; object-fit: cover; }

        /* Premium Content */
        .premium-grid { 
            display: grid; 
            grid-template-columns: 1fr; 
            gap: 0.8rem; 
            margin-top: 0.8rem; 
        }
        .premium-item { 
            background: rgba(255, 255, 255, 0.05); 
            border-radius: 8px; 
            padding: 1rem; 
            text-align: center; 
            transition: var(--transition); 
            cursor: pointer; 
            border: 2px solid transparent; 
        }
        .premium-item:hover { 
            transform: translateY(-3px); 
            box-shadow: var(--shadow-hover); 
            border-color: rgba(255, 107, 107, 0.3); 
        }
        .premium-item.locked { opacity: 0.6; cursor: not-allowed; }
        .premium-icon { 
            font-size: 2rem; 
            margin-bottom: 0.5rem; 
            background: var(--gradient-primary); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }

        /* Map */
        .map-container { 
            border-radius: 8px; 
            overflow: hidden; 
            box-shadow: var(--shadow-card); 
            margin-top: 0.5rem; 
        }
        #escort-map { height: 250px !important; width: 100% !important; }
        .map-info { 
            background: rgba(72, 219, 251, 0.1); 
            border: 1px solid rgba(72, 219, 251, 0.3); 
            border-radius: 6px; 
            padding: 0.6rem; 
            margin-bottom: 0.5rem; 
            color: #48dbfb; 
            display: flex; 
            align-items: center; 
            gap: 0.3rem; 
            font-size: 0.8rem;
        }

        /* Comments */
        .comment-form { 
            margin-bottom: 1rem; 
            padding: 0.8rem; 
            background: rgba(255, 255, 255, 0.05); 
            border-radius: 8px; 
            border: 1px solid var(--border-color); 
        }
        .comment-input { 
            width: 100%; 
            background: rgba(255, 255, 255, 0.1); 
            border: 2px solid var(--border-color); 
            border-radius: 6px; 
            padding: 0.6rem; 
            color: var(--text-primary); 
            resize: vertical; 
            min-height: 60px; 
            outline: none; 
            font-family: inherit; 
            font-size: 0.85rem;
        }
        .comment-input:focus { border-color: #ff6b6b; }
        .comment-actions { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-top: 0.6rem; 
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .emoji-picker { display: flex; gap: 0.3rem; flex-wrap: wrap; }
        .emoji-btn { 
            padding: 0.3rem; 
            background: rgba(255, 255, 255, 0.1); 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 0.9rem; 
            transition: var(--transition); 
        }
        .emoji-btn:hover { 
            background: rgba(255, 255, 255, 0.2); 
            transform: scale(1.1); 
        }
        .emoji-btn.selected { 
            background: rgba(255, 107, 107, 0.3); 
        }

        .comment-item { 
            padding: 0.8rem; 
            margin-bottom: 0.6rem; 
            background: rgba(255, 255, 255, 0.05); 
            border-radius: 8px; 
            border: 1px solid var(--border-color); 
        }
        .comment-item.highlighted { 
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 215, 0, 0.05)); 
            border-left: 3px solid #FFD700; 
        }
        .comment-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 0.5rem; 
            flex-wrap: wrap;
            gap: 0.3rem;
        }
        .comment-author { 
            display: flex; 
            align-items: center; 
            gap: 0.3rem; 
            font-weight: bold; 
            font-size: 0.8rem;
        }
        .plan-badge { 
            padding: 0.1rem 0.4rem; 
            border-radius: 8px; 
            font-size: 0.6rem; 
            font-weight: bold; 
            color: white; 
        }

        /* Random Escorts */
        .random-escorts-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); 
            gap: 0.8rem; 
        }
        .random-escort-card { 
            background: var(--bg-card); 
            border-radius: 8px; 
            overflow: hidden; 
            transition: var(--transition); 
            box-shadow: var(--shadow-card); 
            border: 1px solid var(--border-color); 
            text-decoration: none; 
            color: var(--text-primary); 
            display: flex; 
            flex-direction: column; 
        }
        .random-escort-card:hover { 
            transform: translateY(-3px); 
            box-shadow: var(--shadow-hover); 
        }
        .random-escort-image { 
            position: relative; 
            aspect-ratio: 3/4; 
            overflow: hidden; 
        }
        .random-escort-image img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
            transition: var(--transition); 
        }
        .random-escort-card:hover .random-escort-image img { transform: scale(1.1); }
        .random-escort-info { 
            padding: 0.8rem; 
            flex: 1; 
        }
        .random-escort-name { 
            font-size: 0.85rem; 
            font-weight: 700; 
            margin-bottom: 0.3rem; 
            background: var(--gradient-primary); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }

        /* Modals */
        .modal { 
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
        .modal-content { 
            background: var(--bg-card); 
            border-radius: 15px; 
            padding: 1.5rem; 
            max-width: 350px; 
            margin: 1rem; 
            text-align: center; 
            position: relative; 
            border: 1px solid var(--border-color); 
        }
        .modal-close { 
            position: absolute; 
            top: 0.5rem; 
            right: 0.5rem; 
            background: none; 
            border: none; 
            color: var(--text-muted); 
            font-size: 1.3rem; 
            cursor: pointer; 
            transition: var(--transition); 
        }
        .modal-close:hover { color: var(--text-primary); }
        .lightbox-media { max-width: 90vw; max-height: 90vh; border-radius: 8px; }
        .lightbox-nav { 
            position: fixed; 
            top: 50%; 
            transform: translateY(-50%); 
            background: rgba(0, 0, 0, 0.8); 
            border: none; 
            color: white;
            font-size: 1.2rem; 
            padding: 8px 12px; 
            border-radius: 50%; 
            cursor: pointer; 
            transition: var(--transition); 
        }
        .lightbox-prev { left: 20px; }
        .lightbox-next { right: 20px; }
        .lightbox-nav:hover { background: rgba(0, 0, 0, 0.9); transform: translateY(-50%) scale(1.1); }

        /* Utilities */
        .btn { 
            padding: 0.8rem 1.5rem; 
            background: var(--gradient-primary); 
            color: white; 
            text-decoration: none; 
            border-radius: 20px; 
            font-weight: bold; 
            transition: var(--transition); 
            display: inline-flex; 
            align-items: center; 
            gap: 0.4rem; 
            border: none; 
            cursor: pointer; 
            font-size: 0.85rem;
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4); }
        .upgrade-prompt { 
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(254, 202, 87, 0.1)); 
            border: 2px solid rgba(255, 107, 107, 0.3); 
            border-radius: 12px; 
            padding: 1.5rem; 
            text-align: center; 
            margin: 1rem 0; 
        }
        .upgrade-icon { 
            font-size: 2.5rem; 
            margin-bottom: 0.8rem; 
            background: var(--gradient-primary); 
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent; 
        }
        .status-online { 
            width: 6px; 
            height: 6px; 
            background: #00ff88; 
            border-radius: 50%; 
            animation: pulse-online 2s infinite; 
        }
        @keyframes pulse-online { 
            0% { transform: scale(1); opacity: 1; } 
            50% { transform: scale(1.3); opacity: 0.7; } 
            100% { transform: scale(1); opacity: 1; } 
        }

        /* Desktop overrides */
        @media (min-width: 768px) {
            .profile-container { max-width: 600px; }
            .info-grid { grid-template-columns: repeat(3, 1fr); }
            .actions-bar { grid-template-columns: repeat(4, 1fr); }
            .gallery-grid { grid-template-columns: repeat(4, 1fr); }
            .contact-buttons { grid-template-columns: repeat(2, 1fr); }
            .random-escorts-grid { grid-template-columns: repeat(3, 1fr); }
        }

        @media (max-width: 480px) {
            .header-content { padding: 0.6rem 0.8rem; }
            .nav { display: none; }
            .profile-container { padding: 0 0.3rem; }
            .info-grid { gap: 0.5rem; }
            .actions-bar { gap: 0.4rem; padding: 0.6rem; }
            .gallery-grid { gap: 0.3rem; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.1); border-radius: 3px; }
        ::-webkit-scrollbar-thumb { background: var(--gradient-primary); border-radius: 3px; }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">🔥 Citas</a>
            
            <nav class="nav">
                <a href="index.php?type=chica" class="nav-link">
                    <i class="fas fa-venus"></i> Chicas
                </a>
                <a href="index.php?type=chico" class="nav-link">
                    <i class="fas fa-mars"></i> Chicos
                </a>
                <a href="index.php?type=trans" class="nav-link">
                    <i class="fas fa-transgender"></i> Trans
                </a>
                <a href="https://t.me/CitasNortes" target="_blank" class="nav-link">
                    <i class="fab fa-telegram"></i> Telegram
                </a>
            </nav>
            
            <?php if (isLoggedIn()): ?>
                <?php $current_user = getCurrentUser(); ?>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?= strtoupper(substr($current_user['username'], 0, 1)) ?>
                        </div>
                        <span><?= htmlspecialchars($current_user['username']) ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <div class="profile-container">
        <a href="javascript:history.back()" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Voltar
        </a>

        <!-- Profile Card -->
        <div class="profile-card">
            <!-- Main Image -->
            <div class="main-image-container">
                <img src="<?= htmlspecialchars($gallery_photos[0]['image_url']) ?>" 
                     alt="<?= htmlspecialchars($escort['name']) ?>" 
                     class="main-image" 
                     onclick="openLightbox(0)">
                
                <div class="profile-badges">
                    <?php if (!empty($escort['super_vip'])): ?>
                        <div class="profile-badge super-vip">SUPER VIP</div>
                    <?php elseif (!empty($escort['vip'])): ?>
                        <div class="profile-badge vip">VIP</div>
                    <?php elseif (!empty($escort['top'])): ?>
                        <div class="profile-badge top">TOP</div>
                    <?php endif; ?>

                    <?php if (!empty($escort['verificado'])): ?>
                        <div class="profile-badge verificado">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="profile-info">
                <!-- Info Grid -->
                <div class="info-grid">
                    <!-- Nome -->
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Nome</div>
                            <div class="info-value"><?= htmlspecialchars($escort['name']) ?></div>
                        </div>
                    </div>

                    <!-- Idade -->
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-birthday-cake"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Idade</div>
                            <div class="info-value"><?= $age ?> años</div>
                        </div>
                    </div>

                    <!-- Localização -->
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Localización</div>
                            <div class="info-value">
                                <?= htmlspecialchars($location) ?>
                                <?php if (!empty($escort['city_name'])): ?>
                                    <br><small style="color: var(--text-muted); font-size: 0.7rem;"><?= htmlspecialchars($escort['city_name']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Nacionalidade -->
                    <?php if (!empty($escort['nationality'])): ?>
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-flag"></i>
                        </div>
                        <div class="info-content">
                            <div class="info-label">Nacionalidad</div>
                            <div class="info-value">
                                <?php
                                $nacionalidades = [
                                    'es' => 'Española', 'br' => 'Brasileña', 'co' => 'Colombiana', 'ar' => 'Argentina',
                                    've' => 'Venezolana', 'mx' => 'Mexicana', 'pe' => 'Peruana', 'ec' => 'Ecuatoriana',
                                    'cl' => 'Chilena', 'uy' => 'Uruguaya', 'fr' => 'Francesa', 'it' => 'Italiana',
                                    'ru' => 'Rusa', 'ua' => 'Ucraniana', 'ro' => 'Rumana', 'pl' => 'Polaca'
                                ];
                                $nacionalidad_completa = $nacionalidades[strtolower($escort['nationality'])] ?? strtoupper($escort['nationality']);
                                echo htmlspecialchars($nacionalidad_completa);
                                ?>
                                <img src="https://flagcdn.com/w40/<?= strtolower(htmlspecialchars($escort['nationality'])) ?>.png" 
                                     alt="<?= htmlspecialchars($escort['nationality']) ?>"
                                     class="nationality-flag"
                                     onerror="this.style.display='none'">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- About Section -->
                <div class="about-section">
                    <div class="about-title">
                        <div class="info-icon">
                            <i class="fas fa-comment-alt"></i>
                        </div>
                        Sobre Mi
                    </div>
                    <div class="about-content">
                        <?= nl2br(htmlspecialchars($description)) ?>
                    </div>
                </div>

                <!-- Contact Buttons -->
                <div class="contact-buttons">
                    <?php if (!empty($phone)): ?>
                        <a href="https://wa.me/<?= htmlspecialchars($phone) ?>" target="_blank" class="contact-btn whatsapp-btn">
                            <i class="fab fa-whatsapp"></i>
                            WhatsApp
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($escort['telegram'])): ?>
                        <a href="https://t.me/<?= htmlspecialchars($escort['telegram']) ?>" target="_blank" class="contact-btn telegram-btn">
                            <i class="fab fa-telegram"></i>
                            Telegram
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Actions Bar -->
        <?php if (isLoggedIn()): ?>
        <div class="actions-bar">
            <div class="action-btn <?= $like_status['user_liked'] ? 'liked' : '' ?> <?= !$userPermissions['can_like'] ? 'disabled' : '' ?>" 
                 onclick="<?= $userPermissions['can_like'] ? 'toggleLike()' : 'showUpgradePrompt(\'Bronze\', \'curtir perfis\')' ?>">
                <div class="action-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="action-text">Curtir</div>
                <div class="action-count"><?= $like_status['total_likes'] ?></div>
            </div>

            <div class="action-btn <?= !$userPermissions['can_comment'] ? 'disabled' : '' ?>" 
                 onclick="<?= $userPermissions['can_comment'] ? 'focusCommentInput()' : 'showUpgradePrompt(\'Silver\', \'comentar\')' ?>">
                <div class="action-icon">
                    <i class="fas fa-comment"></i>
                </div>
                <div class="action-text">Comentar</div>
                <div class="action-count"><?= count($comments) ?></div>
            </div>

            <a href="private_media.php?escort_id=<?= $escort['id'] ?>&type=photos" class="action-btn <?= !$userPermissions['can_view_private_photos'] ? 'disabled' : '' ?>"
               onclick="<?= !$userPermissions['can_view_private_photos'] ? 'event.preventDefault(); showUpgradePrompt(\'Silver+\', \'ver fotos privadas\'); return false;' : '' ?>">
                <div class="action-icon">
                    <i class="fas fa-images"></i>
                </div>
                <div class="action-text">Fotos</div>
                <div class="action-count"><?= $private_media_count['photos'] ?></div>
            </a>

            <a href="private_media.php?escort_id=<?= $escort['id'] ?>&type=videos" class="action-btn <?= !$userPermissions['can_view_private_videos'] ? 'disabled' : '' ?>"
               onclick="<?= !$userPermissions['can_view_private_videos'] ? 'event.preventDefault(); showUpgradePrompt(\'Gold\', \'ver vídeos privados\'); return false;' : '' ?>">
                <div class="action-icon">
                    <i class="fas fa-video"></i>
                </div>
                <div class="action-text">Vídeos</div>
                <div class="action-count"><?= $private_media_count['videos'] ?></div>
            </a>
        </div>
        <?php else: ?>
        <div class="upgrade-prompt">
            <div class="upgrade-icon">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <h3 style="color: #ff6b6b; margin-bottom: 0.8rem; font-size: 1.1rem;">Faça Login para Interagir</h3>
            <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.85rem;">
                Para curtir, comentar e acessar conteúdo exclusivo, você precisa estar logado.
            </p>
            <a href="login.php" class="btn">
                <i class="fas fa-sign-in-alt"></i>
                Fazer Login
            </a>
        </div>
        <?php endif; ?>

        <!-- Premium Content -->
        <?php if ($private_media_count['photos'] > 0 || $private_media_count['videos'] > 0): ?>
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-crown"></i>
                Conteúdo Premium
            </h2>

            <div class="premium-grid">
                <?php if ($private_media_count['photos'] > 0): ?>
                <div class="premium-item <?= !$userPermissions['can_view_private_photos'] ? 'locked' : '' ?>" 
                     onclick="<?= $userPermissions['can_view_private_photos'] ? 'window.location.href=\'private_media.php?escort_id=' . $escort['id'] . '&type=photos\'' : 'showUpgradePrompt(\'Silver+\', \'ver fotos privadas\')' ?>">
                    <div class="premium-icon">
                        <i class="fas fa-images"></i>
                    </div>
                    <h3 style="font-size: 1rem; margin-bottom: 0.4rem;">Fotos Privadas</h3>
                    <div style="color: var(--text-muted); margin-bottom: 0.8rem; font-size: 0.8rem;"><?= $private_media_count['photos'] ?> fotos exclusivas</div>
                    <?php if (!$userPermissions['can_view_private_photos']): ?>
                        <div style="color: #ff6b6b; font-weight: bold; font-size: 0.8rem;">
                            <i class="fas fa-lock"></i>
                            Requer plano Silver+
                        </div>
                    <?php else: ?>
                        <div style="color: #00ff88; font-weight: bold; font-size: 0.8rem;">
                            <i class="fas fa-unlock"></i>
                            Desbloqueado
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($private_media_count['videos'] > 0): ?>
                <div class="premium-item <?= !$userPermissions['can_view_private_videos'] ? 'locked' : '' ?>" 
                     onclick="<?= $userPermissions['can_view_private_videos'] ? 'window.location.href=\'private_media.php?escort_id=' . $escort['id'] . '&type=videos\'' : 'showUpgradePrompt(\'Gold\', \'ver vídeos privados\')' ?>">
                    <div class="premium-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3 style="font-size: 1rem; margin-bottom: 0.4rem;">Vídeos Privados</h3>
                    <div style="color: var(--text-muted); margin-bottom: 0.8rem; font-size: 0.8rem;"><?= $private_media_count['videos'] ?> vídeos exclusivos</div>
                    <?php if (!$userPermissions['can_view_private_videos']): ?>
                        <div style="color: #ff6b6b; font-weight: bold; font-size: 0.8rem;">
                            <i class="fas fa-lock"></i>
                            Requer plano Gold
                        </div>
                    <?php else: ?>
                        <div style="color: #00ff88; font-weight: bold; font-size: 0.8rem;">
                            <i class="fas fa-unlock"></i>
                            Desbloqueado
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Gallery Section -->
        <?php if (count($gallery_photos) > 1): ?>
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-images"></i>
                Galería de Fotos
            </h2>
            <div class="gallery-grid">
                <?php foreach ($gallery_photos as $index => $photo): ?>
                <div class="gallery-item" onclick="openLightbox(<?= $index ?>)">
                    <img src="<?= htmlspecialchars($photo['image_url']) ?>" 
                         alt="<?= htmlspecialchars($photo['image_title'] ?? 'Foto') ?>"
                         loading="lazy">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Map Section -->
        <?php if ($escort['latitude'] && $escort['longitude']): ?>
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-map-marker-alt"></i>
                Localización
            </h2>
            
            <div class="map-info">
                <i class="fas fa-info-circle"></i>
                <span>Ubicación aproximada de <?= htmlspecialchars($escort['name']) ?></span>
            </div>

            <div class="map-container">
                <div id="escort-map"></div>
            </div>

            <?php if ($escort['map_address']): ?>
            <div style="background: rgba(255, 255, 255, 0.05); border-radius: 8px; padding: 0.8rem; margin-top: 0.8rem; display: flex; align-items: center; gap: 0.8rem;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div>
                    <div style="font-weight: bold; margin-bottom: 0.2rem; font-size: 0.9rem;">Dirección</div>
                    <div style="color: var(--text-muted); font-size: 0.8rem;"><?= htmlspecialchars($escort['map_address']) ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Comments Section -->
        <?php if (isLoggedIn()): ?>
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-comments"></i>
                Comentários (<?= count($comments) ?>)
            </h2>

            <?php if ($userPermissions['can_comment']): ?>
            <div class="comment-form">
                <textarea id="comment-input" class="comment-input" placeholder="Escreva seu comentário..."></textarea>
                <div class="comment-actions">
                    <?php if ($subscriptionManager->canUseEmojiComments($_SESSION['user_id'])): ?>
                    <div class="emoji-picker">
                        <button class="emoji-btn" onclick="addEmoji('😍')">😍</button>
                        <button class="emoji-btn" onclick="addEmoji('🔥')">🔥</button>
                        <button class="emoji-btn" onclick="addEmoji('💕')">💕</button>
                        <button class="emoji-btn" onclick="addEmoji('👑')">👑</button>
                        <button class="emoji-btn" onclick="addEmoji('⭐')">⭐</button>
                    </div>
                    <?php endif; ?>
                    
                    <button onclick="submitComment()" class="btn">
                        <i class="fas fa-paper-plane"></i>
                        Comentar
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div class="upgrade-prompt">
                <div class="upgrade-icon">
                    <i class="fas fa-comment-slash"></i>
                </div>
                <h3 style="color: #ff6b6b; margin-bottom: 0.8rem; font-size: 1.1rem;">Comentários Premium</h3>
                <p style="color: var(--text-muted); margin-bottom: 1rem; font-size: 0.85rem;">
                    Para comentar você precisa do plano Silver ou superior.
                    <br>Seu plano atual: <strong><?= ucfirst($userPermissions['current_plan']) ?></strong>
                </p>
                <a href="plans.php" class="btn">
                    <i class="fas fa-crown"></i>
                    Fazer Upgrade
                </a>
            </div>
            <?php endif; ?>

            <div id="comments-list">
                <?php foreach ($comments as $comment): ?>
                <div class="comment-item <?= $comment['is_highlighted'] ? 'highlighted' : '' ?>">
                    <div class="comment-header">
                        <div class="comment-author">
                            <?= htmlspecialchars($comment['username']) ?>
                            <?php if ($comment['plan_name']): ?>
                                <span class="plan-badge" style="background-color: <?= $comment['plan_color'] ?>;">
                                    <i class="<?= $comment['plan_icon'] ?>"></i>
                                    <?= $comment['plan_name'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div style="color: var(--text-muted); font-size: 0.7rem;">
                            <?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?>
                        </div>
                    </div>
                    <div style="color: var(--text-secondary); line-height: 1.5; font-size: 0.85rem;">
                        <?= $comment['has_emoji'] ? '<span style="font-size: 1.1rem; margin-right: 0.4rem;">' : '' ?>
                        <?= nl2br(htmlspecialchars($comment['content'])) ?>
                        <?= $comment['has_emoji'] ? '</span>' : '' ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (empty($comments)): ?>
                <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                    <i class="fas fa-comment" style="font-size: 2.5rem; margin-bottom: 0.8rem; opacity: 0.3;"></i>
                    <p style="font-size: 0.9rem;">Seja o primeiro a comentar!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Random Escorts Section -->
        <?php if (!empty($random_escorts)): ?>
        <div class="section">
            <h2 class="section-title">
                <i class="fas fa-users"></i>
                Otras Acompañantes
            </h2>
            
            <div class="random-escorts-grid">
                <?php foreach ($random_escorts as $random_escort): ?>
                <a href="perfil.php?id=<?= $random_escort['id'] ?>" class="random-escort-card">
                    <div class="random-escort-image">
                        <img src="<?= htmlspecialchars($random_escort['image_url']) ?>" 
                             alt="<?= htmlspecialchars($random_escort['name']) ?>"
                             loading="lazy">
                        
                        <div class="profile-badges">
                            <?php if (!empty($random_escort['super_vip'])): ?>
                                <div class="profile-badge super-vip">SUPER VIP</div>
                            <?php elseif (!empty($random_escort['vip'])): ?>
                                <div class="profile-badge vip">VIP</div>
                            <?php elseif (!empty($random_escort['top'])): ?>
                                <div class="profile-badge top">TOP</div>
                            <?php endif; ?>

                            <?php if (!empty($random_escort['verificado'])): ?>
                                <div class="profile-badge verificado">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="random-escort-info">
                        <div class="random-escort-name">
                            <?= htmlspecialchars($random_escort['name']) ?>
                            <?php 
                            $random_age = (!empty($random_escort['age']) && $random_escort['age'] > 0) 
                                ? $random_escort['age'] 
                                : rand(22, 28); 
                            ?>
                            - <?= $random_age ?> años
                        </div>
                        
                        <div style="color: var(--text-muted); margin-bottom: 0.8rem; display: flex; align-items: center; gap: 0.2rem; font-size: 0.75rem;">
                            <i class="fas fa-map-marker-alt" style="color: #ff6b6b;"></i>
                            <?php 
                            $random_location = !empty($random_escort['city_name']) 
                                ? $random_escort['city_name'] 
                                : (!empty($random_escort['location']) ? $random_escort['location'] : 'Disponible');
                            ?>
                            <?= htmlspecialchars($random_location) ?>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.4rem; margin-top: auto;">
                            <div class="status-online"></div>
                            <span style="color: #00ff88; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;">Disponible</span>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="index.php" class="btn">
                    <i class="fas fa-search"></i>
                    Ver Más Acompañantes
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="modal">
        <div style="position: relative; max-width: 90%; max-height: 90%;">
            <button class="modal-close" onclick="closeLightbox()">×</button>
            <img id="lightbox-image" class="lightbox-media" src="" alt="">
            <button class="lightbox-nav lightbox-prev" onclick="prevImage()">❮</button>
            <button class="lightbox-nav lightbox-next" onclick="nextImage()">❯</button>
        </div>
    </div>

    <!-- Upgrade Modal -->
    <div id="upgradeModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeUpgradeModal()">×</button>
            
            <div class="upgrade-icon">
                <i class="fas fa-crown"></i>
            </div>
            <h3 id="upgradeTitle" style="font-size: 1.3rem; margin-bottom: 0.8rem; color: #ff6b6b;">Upgrade Necessário</h3>
            <p id="upgradeText" style="color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5; font-size: 0.85rem;">Para acessar esta funcionalidade, você precisa fazer upgrade do seu plano.</p>
            <a href="plans.php" class="btn">
                <i class="fas fa-crown"></i>
                Ver Planos
            </a>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        const galleryPhotos = <?= json_encode($gallery_photos) ?>;
        let currentImageIndex = 0;
        let selectedEmoji = '';
        let escortMap = null;

        // Initialize map
        <?php if ($escort['latitude'] && $escort['longitude']): ?>
        function initEscortMap() {
            try {
                const lat = <?= $escort['latitude'] ?>;
                const lng = <?= $escort['longitude'] ?>;
                const name = '<?= addslashes($escort['name']) ?>';
                const address = '<?= addslashes($escort['map_address'] ?: $location) ?>';
                
                escortMap = L.map('escort-map').setView([lat, lng], 15);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(escortMap);

                const customIcon = L.divIcon({
                    html: `<div style="background: linear-gradient(135deg, #ff6b6b, #feca57); width: 40px; height: 40px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center;"><i class="fas fa-heart" style="color: white; font-size: 14px; transform: rotate(45deg);"></i></div>`,
                    className: 'custom-marker',
                    iconSize: [40, 40],
                    iconAnchor: [20, 35],
                    popupAnchor: [0, -35]
                });

                const marker = L.marker([lat, lng], { icon: customIcon }).addTo(escortMap);
                
                marker.bindPopup(`<div style="text-align: center; padding: 10px;"><h3 style="margin: 0 0 10px 0; color: #ff6b6b;">${name}</h3><p style="margin: 0; color: #ddd; font-size: 14px;"><i class="fas fa-map-marker-alt" style="color: #ff6b6b;"></i> ${address}</p></div>`).openPopup();

                L.circle([lat, lng], { color: '#ff6b6b', fillColor: '#ff6b6b', fillOpacity: 0.1, radius: 200, weight: 2 }).addTo(escortMap);
            } catch (error) {
                console.error('Erro ao inicializar mapa:', error);
            }
        }
        <?php endif; ?>

        // Lightbox functions
        function openLightbox(index) {
            currentImageIndex = index;
            document.getElementById('lightbox-image').src = galleryPhotos[index].image_url;
            document.getElementById('lightbox').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            document.getElementById('lightbox').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function nextImage() {
            currentImageIndex = (currentImageIndex + 1) % galleryPhotos.length;
            document.getElementById('lightbox-image').src = galleryPhotos[currentImageIndex].image_url;
        }

        function prevImage() {
            currentImageIndex = (currentImageIndex - 1 + galleryPhotos.length) % galleryPhotos.length;
            document.getElementById('lightbox-image').src = galleryPhotos[currentImageIndex].image_url;
        }

        // Like system
        function toggleLike() {
            fetch('like_comment_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=like&target_type=escort&target_id=<?= $escort['id'] ?>'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const likeBtn = document.querySelector('.action-btn');
                    const countEl = likeBtn.querySelector('.action-count');
                    
                    if (data.action === 'liked') {
                        likeBtn.classList.add('liked');
                    } else {
                        likeBtn.classList.remove('liked');
                    }
                    
                    countEl.textContent = data.total_likes + ' curtidas';
                } else if (data.upgrade_required) {
                    showUpgradePrompt(data.required_plan, 'curtir perfis');
                } else {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao processar like');
            });
        }

        // Comment system
        function addEmoji(emoji) {
            selectedEmoji = emoji;
            const emojiButtons = document.querySelectorAll('.emoji-btn');
            
            emojiButtons.forEach(btn => btn.classList.remove('selected'));
            event.target.classList.add('selected');
        }

        function submitComment() {
            const content = document.getElementById('comment-input').value.trim();
            
            if (!content) {
                alert('Digite um comentário');
                return;
            }

            fetch('like_comment_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=comment&target_type=escort&target_id=<?= $escort['id'] ?>&content=${encodeURIComponent(content)}&emoji=${encodeURIComponent(selectedEmoji)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const commentsList = document.getElementById('comments-list');
                    const newComment = document.createElement('div');
                    newComment.className = 'comment-item' + (data.comment.is_highlighted ? ' highlighted' : '');
                    
                    newComment.innerHTML = `
                        <div class="comment-header">
                            <div class="comment-author">
                                ${data.comment.username}
                                ${data.comment.user_plan !== 'free' ? `<span class="plan-badge" style="background-color: ${data.comment.plan_color};"><i class="${data.comment.plan_icon}"></i> ${data.comment.user_plan}</span>` : ''}
                            </div>
                            <div style="color: #aaa; font-size: 0.9rem;">${data.comment.created_at}</div>
                        </div>
                        <div class="comment-content">
                            ${data.comment.has_emoji ? '<span class="comment-emoji">' : ''}
                            ${data.comment.content}
                            ${data.comment.has_emoji ? '</span>' : ''}
                        </div>
                    `;
                    
                    commentsList.insertBefore(newComment, commentsList.firstChild);
                    
                    document.getElementById('comment-input').value = '';
                    selectedEmoji = '';
                    document.querySelectorAll('.emoji-btn').forEach(btn => btn.classList.remove('selected'));
                    
                    const commentCount = document.querySelectorAll('.comment-item').length;
                    document.querySelector('.action-btn:nth-child(2) .action-count').textContent = commentCount + ' comentários';
                    
                } else if (data.upgrade_required) {
                    showUpgradePrompt(data.required_plan, 'comentar');
                } else {
                    alert(data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erro ao enviar comentário');
            });
        }

        function focusCommentInput() {
            document.getElementById('comment-input').focus();
        }

        // Upgrade modal
        function showUpgradePrompt(plan, feature) {
            document.getElementById('upgradeTitle').textContent = `Plano ${plan} Necessário`;
            document.getElementById('upgradeText').textContent = `Para ${feature}, você precisa do plano ${plan} ou superior. Seu plano atual: <?= ucfirst($userPermissions['current_plan']) ?>`;
            document.getElementById('upgradeModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeUpgradeModal() {
            document.getElementById('upgradeModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Keyboard controls
        document.addEventListener('keydown', function(e) {
            if (document.getElementById('lightbox').style.display === 'flex' && e.key === 'Escape') {
                closeLightbox();
            }
            if (document.getElementById('upgradeModal').style.display === 'flex' && e.key === 'Escape') {
                closeUpgradeModal();
            }
        });

        // Click outside to close modals
        document.getElementById('lightbox').addEventListener('click', function(e) {
            if (e.target === this) closeLightbox();
        });

        document.getElementById('upgradeModal').addEventListener('click', function(e) {
            if (e.target === this) closeUpgradeModal();
        });

        // Initialize everything when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Página carregada, inicializando componentes...');
            
            <?php if ($escort['latitude'] && $escort['longitude']): ?>
            // Initialize map after a short delay to ensure container is ready
            setTimeout(() => {
                if (document.getElementById('escort-map')) {
                    initEscortMap();
                } else {
                    console.error('Container do mapa não encontrado');
                }
            }, 300);
            <?php endif; ?>
        });
    </script>
</body>
</html>
<footer class="footer">
        <div class="footer-links">
          <div class="footer-column">
            <a href="#">Putas maduras en Madrid</a>
            <a href="#">Putas españolas en Madrid</a>
            <a href="#">Masajes eróticos en Madrid</a>
            <a href="#">Putas latinas en Madrid</a>
          </div>
          <div class="footer-column">
            <a href="#">Putas maduras en Barcelona</a>
            <a href="#">Putas chinas en Barcelona</a>
            <a href="#">Putas españolas en Barcelona</a>
            <a href="#">Masajes eróticos en Zaragoza</a>
          </div>
          <div class="footer-column">
            <a href="#">Masajes eróticos en Bilbao</a>
            <a href="#">Masaje erótico en San Sebastián</a>
            <a href="#">Masajes eróticos en Vitoria</a>
            <a href="#">Putas españolas en Pamplona</a>
          </div>
        </div>
        
        <div class="legal-notice">
          AVISO LEGAL=> Los menores de edad tienen terminantemente prohibida la entrada a este sitio web.
        </div>
      </footer>
    </div>
  </main>

  <script>
    // Global variables
// Global variables
var showingAllCities = false;
var citiesData = []; // Armazenar dados das cidades
var isSearchActive = false; // Nova variável para controlar estado da busca

// NOVO: Função para carregar cidades do banco de dados
function loadCitiesData() {
  <?php
  // Recriar a conexão para garantir que está ativa
  $conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");
  $result = $conn->query("SELECT * FROM cities ORDER BY title ASC");
  
  echo "citiesData = [";
  $first = true;
  while ($city = $result->fetch_assoc()) {
    if (!$first) echo ",";
    echo json_encode($city);
    $first = false;
  }
  echo "];";
  ?>
}

// NOVO: Função para popular dropdowns desktop
function populateDesktopDropdowns() {
  const types = ['chica', 'chico', 'trans'];
  
  types.forEach(type => {
    const container = document.getElementById(`desktop-dropdown-${type}`);
    if (!container) return;
    
    container.innerHTML = '';
    
    citiesData.forEach(city => {
      const cityItem = document.createElement('a');
      cityItem.href = `cidade.php?slug=${city.slug}&type=${type}`;
      cityItem.className = 'city-dropdown-item';
      cityItem.innerHTML = `
        <div class="city-dropdown-icon"></div>
        <span>${city.title}</span>
      `;
      container.appendChild(cityItem);
    });
  });
}

// NOVO: Função para popular dropdowns mobile
function populateMobileDropdowns() {
  const types = ['chica', 'chico', 'trans'];
  
  types.forEach(type => {
    const container = document.getElementById(`mobile-dropdown-${type}`);
    if (!container) return;
    
    container.innerHTML = '';
    
    citiesData.forEach(city => {
      const cityItem = document.createElement('a');
      cityItem.href = `cidade.php?slug=${city.slug}&type=${type}`;
      cityItem.innerHTML = `
        <i class="city-dropdown-icon" style="width: 8px; height: 8px; background: #00ff00; border-radius: 50%; display: inline-block; margin-right: 0.8rem;"></i>
        ${city.title}
      `;
      container.appendChild(cityItem);
    });
  });
}

// NOVO: Função para toggle do dropdown mobile
function toggleMobileCitiesDropdown(type) {
  const dropdown = document.getElementById(`mobile-dropdown-${type}`);
  const arrow = document.getElementById(`arrow-${type}`);
  
  if (!dropdown || !arrow) return;
  
  // Fechar outros dropdowns
  const allDropdowns = document.querySelectorAll('.mobile-cities-dropdown');
  const allArrows = document.querySelectorAll('.mobile-nav-toggle .dropdown-arrow');
  
  allDropdowns.forEach(dd => {
    if (dd !== dropdown) {
      dd.classList.remove('active');
    }
  });
  
  allArrows.forEach(arr => {
    if (arr !== arrow) {
      arr.style.transform = 'rotate(0deg)';
    }
  });
  
  // Toggle do dropdown atual
  dropdown.classList.toggle('active');
  
  if (dropdown.classList.contains('active')) {
    arrow.style.transform = 'rotate(180deg)';
  } else {
    arrow.style.transform = 'rotate(0deg)';
  }
}

// CORRIGIDO: Função para mostrar/ocultar cidades no mobile
function applyCityVisibility() {
  const cityCards = document.querySelectorAll('.city-card');
  const isMobile = window.innerWidth <= 768;
  
  cityCards.forEach((card, index) => {
    if (isSearchActive) {
      // Durante a busca: a visibilidade é controlada pela função de busca
      return;
    }
    
    if (isMobile) {
      // Mobile sem busca: aplicar regra das 12 primeiras
      if (!showingAllCities && index >= 12) {
        card.style.display = 'none';
      } else {
        card.style.display = 'block';
      }
    } else {
      // Desktop: sempre mostrar todas
      card.style.display = 'block';
    }
  });
}

// CORRIGIDO: Toggle do botão "Ver mais cidades"
function toggleMoreCities() {
  console.log('toggleMoreCities called');
  
  const citiesGrid = document.getElementById('citiesGrid');
  const showMoreBtn = document.getElementById('showMoreBtn');
  const showMoreText = document.getElementById('showMoreText');
  const icon = showMoreBtn.querySelector('i');
  
  if (!showingAllCities) {
    citiesGrid.classList.add('show-all');
    showMoreText.textContent = 'Ver menos ciudades';
    icon.className = 'fas fa-minus';
    showingAllCities = true;
    console.log('Showing all cities');
  } else {
    citiesGrid.classList.remove('show-all');
    showMoreText.textContent = 'Ver más ciudades';
    icon.className = 'fas fa-plus';
    showingAllCities = false;
    console.log('Hiding extra cities');
    
    citiesGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }
  
  // Reaplicar visibilidade das cidades
  if (!isSearchActive) {
    applyCityVisibility();
  }
}

function toggleMobileMenu() {
  var menu = document.getElementById('mobileMenu');
  var overlay = document.getElementById('mobileOverlay');
  
  menu.classList.toggle('active');
  overlay.classList.toggle('active');
  
  if (menu.classList.contains('active')) {
    document.body.style.overflow = 'hidden';
  }
}

function closeMobileMenu() {
  var menu = document.getElementById('mobileMenu');
  var overlay = document.getElementById('mobileOverlay');
  
  menu.classList.remove('active');
  overlay.classList.remove('active');
  document.body.style.overflow = 'auto';
}

// CORRIGIDO: Busca mobile completamente reescrita
function initMobileSearch() {
  const searchInput = document.getElementById('mobileSearchInput');
  console.log('Init mobile search, input found:', !!searchInput);
  
  if (!searchInput) return;
  
  searchInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const cityCards = document.querySelectorAll('.city-card');
    const showMoreSection = document.getElementById('showMoreCities');
    
    console.log('Search term:', searchTerm, 'Cards found:', cityCards.length);
    
    if (searchTerm === '') {
      // SEM BUSCA: voltar ao estado normal
      isSearchActive = false;
      
      // Mostrar botão no mobile
      if (window.innerWidth <= 768 && showMoreSection) {
        showMoreSection.style.display = 'block';
      }
      
      // Aplicar regras normais de visibilidade
      applyCityVisibility();
      
    } else {
      // COM BUSCA: ativar modo de busca
      isSearchActive = true;
      
      // Esconder botão "Ver mais"
      if (showMoreSection) {
        showMoreSection.style.display = 'none';
      }
      
      // Mostrar TODAS as cidades que correspondem ao termo (ignorando limite de 12)
      let visibleCount = 0;
      cityCards.forEach(function(card) {
        const cityName = card.getAttribute('data-city-name') || '';
        const cardText = card.textContent.toLowerCase();
        const isMatch = cityName.includes(searchTerm) || cardText.includes(searchTerm);
        
        if (isMatch) {
          card.style.display = 'block';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });
      
      console.log('Search results:', visibleCount, 'cities found');
    }
  });
  
  // Limpar busca com ESC
  searchInput.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      this.value = '';
      this.dispatchEvent(new Event('input'));
    }
  });
}

function initDesktopSearch() {
  var searchInput = document.querySelector('.search-input');
  
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      var searchTerm = this.value.toLowerCase();
      var cards = document.querySelectorAll('.escort-card, .city-card');
      
      cards.forEach(function(card) {
        var text = card.textContent.toLowerCase();
        var isVisible = text.includes(searchTerm);
        card.style.display = isVisible ? 'block' : 'none';
      });
    });
  }
}

function animateOnScroll() {
  var elements = document.querySelectorAll('.animate-on-scroll');
  
  elements.forEach(function(element) {
    var elementTop = element.getBoundingClientRect().top;
    var elementVisible = 150;
    
    if (elementTop < window.innerHeight - elementVisible) {
      element.classList.add('visible');
    }
  });
}

// CORRIGIDO: Handle resize simplificado
function handleResize() {
  console.log('Window resized, width:', window.innerWidth);
  
  const showMoreSection = document.getElementById('showMoreCities');
  
  if (window.innerWidth > 768) {
    // DESKTOP: esconder botão e mostrar todas as cidades
    if (showMoreSection) showMoreSection.style.display = 'none';
    
    if (!isSearchActive) {
      const cityCards = document.querySelectorAll('.city-card');
      cityCards.forEach(function(card) {
        card.style.display = 'block';
      });
    }
  } else {
    // MOBILE: mostrar botão se não estiver em busca
    if (showMoreSection && !isSearchActive) {
      showMoreSection.style.display = 'block';
    }
    
    // Aplicar visibilidade das cidades
    if (!isSearchActive) {
      applyCityVisibility();
    }
  }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM loaded');
  
  // NOVO: Carregar dados das cidades e popular dropdowns
  loadCitiesData();
  populateDesktopDropdowns();
  populateMobileDropdowns();
  
  // Add event listeners
  var showMoreBtn = document.getElementById('showMoreBtn');
  if (showMoreBtn) {
    showMoreBtn.addEventListener('click', toggleMoreCities);
    console.log('Show more button listener added');
  }
  
  var mobileMenuBtn = document.getElementById('mobileMenuBtn');
  if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', toggleMobileMenu);
  }
  
  var mobileMenuClose = document.getElementById('mobileMenuClose');
  if (mobileMenuClose) {
    mobileMenuClose.addEventListener('click', closeMobileMenu);
  }
  
  var mobileOverlay = document.getElementById('mobileOverlay');
  if (mobileOverlay) {
    mobileOverlay.addEventListener('click', closeMobileMenu);
  }
  
  // Initialize functions
  initDesktopSearch();
  initMobileSearch();
  animateOnScroll();
  handleResize();
  
  window.addEventListener('scroll', animateOnScroll);
  window.addEventListener('resize', handleResize);
  
  console.log('All functions initialized');
});

// Close menu on escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeMobileMenu();
  }
});

// NOVO: Tornar função global para uso inline
window.toggleMobileCitiesDropdown = toggleMobileCitiesDropdown;
  </script>
</body>
</html>