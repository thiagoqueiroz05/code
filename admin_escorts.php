<?php
// Incluir sistema de autenticação
include 'auth.php';

// Verificar se é admin
requireAdmin();

$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$success = '';
$error = '';

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $name = trim($_POST['name']);
                $age = intval($_POST['age']);
                $image_url = trim($_POST['image_url']);
                $location = trim($_POST['location']);
                $zone = trim($_POST['zone']);
                $description = trim($_POST['description']);
                $phone = trim($_POST['phone']);
                $nationality = trim($_POST['nationality']);
                $type = $_POST['type'];
                $city_id = intval($_POST['city_id']);
                
                // Coordenadas do mapa
                $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
                $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
                $map_address = trim($_POST['map_address']);
                
                // Badges
                $vip = isset($_POST['vip']) ? 1 : 0;
                $top = isset($_POST['top']) ? 1 : 0;
                $super_vip = isset($_POST['super_vip']) ? 1 : 0;
                $verificado = isset($_POST['verificado']) ? 1 : 0;
                $telegram = isset($_POST['telegram']) ? 1 : 0;
                $whatsapp = isset($_POST['whatsapp']) ? 1 : 0;

                if (!empty($name) && !empty($image_url)) {
                    $stmt = $conn->prepare("
                        INSERT INTO escorts 
                        (name, age, image_url, location, zone, description, phone, nationality, type, city_id, 
                         latitude, longitude, map_address, vip, top, super_vip, verificado, telegram, whatsapp) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ");
                    
                    $stmt->bind_param("sissssssiddsiiiiiii", 
                        $name, $age, $image_url, $location, $zone, $description, 
                        $phone, $nationality, $type, $city_id, $latitude, $longitude, $map_address,
                        $vip, $top, $super_vip, $verificado, $telegram, $whatsapp
                    );
                    
                    if ($stmt->execute()) {
                        $success = 'Escort criada com sucesso!';
                        logActivity('escort_created', "Criou escort: $name", $_SESSION['user_id']);
                    } else {
                        $error = 'Erro ao criar escort: ' . $stmt->error;
                    }
                } else {
                    $error = 'Nome e imagem são obrigatórios.';
                }
                break;

            case 'update':
                $id = intval($_POST['id']);
                $name = trim($_POST['name']);
                $age = intval($_POST['age']);
                $image_url = trim($_POST['image_url']);
                $location = trim($_POST['location']);
                $zone = trim($_POST['zone']);
                $description = trim($_POST['description']);
                $phone = trim($_POST['phone']);
                $nationality = trim($_POST['nationality']);
                $type = $_POST['type'];
                $city_id = intval($_POST['city_id']);
                
                $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
                $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
                $map_address = trim($_POST['map_address']);
                
                $vip = isset($_POST['vip']) ? 1 : 0;
                $top = isset($_POST['top']) ? 1 : 0;
                $super_vip = isset($_POST['super_vip']) ? 1 : 0;
                $verificado = isset($_POST['verificado']) ? 1 : 0;
                $telegram = isset($_POST['telegram']) ? 1 : 0;
                $whatsapp = isset($_POST['whatsapp']) ? 1 : 0;

                $stmt = $conn->prepare("
                    UPDATE escorts SET 
                    name = ?, age = ?, image_url = ?, location = ?, zone = ?, description = ?, 
                    phone = ?, nationality = ?, type = ?, city_id = ?, latitude = ?, longitude = ?, map_address = ?,
                    vip = ?, top = ?, super_vip = ?, verificado = ?, telegram = ?, whatsapp = ?
                    WHERE id = ?
                ");
                
                $stmt->bind_param("sisssssssiddsiiiiiii", 
                    $name, $age, $image_url, $location, $zone, $description, 
                    $phone, $nationality, $type, $city_id, $latitude, $longitude, $map_address,
                    $vip, $top, $super_vip, $verificado, $telegram, $whatsapp, $id
                );
                
                if ($stmt->execute()) {
                    $success = 'Escort atualizada com sucesso!';
                    logActivity('escort_updated', "Atualizou escort: $name", $_SESSION['user_id']);
                } else {
                    $error = 'Erro ao atualizar escort: ' . $stmt->error;
                }
                break;

            case 'delete':
                $id = intval($_POST['id']);
                $stmt = $conn->prepare("DELETE FROM escorts WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $success = 'Escort deletada com sucesso!';
                    logActivity('escort_deleted', "Deletou escort ID: $id", $_SESSION['user_id']);
                } else {
                    $error = 'Erro ao deletar escort: ' . $stmt->error;
                }
                break;
        }
    }
}

// Filtros
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$city_filter = $_GET['city'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Construir query
$where_conditions = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where_conditions[] = "(name LIKE ? OR location LIKE ? OR description LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $types .= "sss";
}

if (!empty($type_filter)) {
    $where_conditions[] = "type = ?";
    $params[] = $type_filter;
    $types .= "s";
}

if (!empty($city_filter)) {
    $where_conditions[] = "city_id = ?";
    $params[] = $city_filter;
    $types .= "i";
}

if (!empty($status_filter)) {
    switch ($status_filter) {
        case 'super_vip':
            $where_conditions[] = "super_vip = 1";
            break;
        case 'vip':
            $where_conditions[] = "vip = 1 AND super_vip = 0";
            break;
        case 'top':
            $where_conditions[] = "top = 1 AND vip = 0 AND super_vip = 0";
            break;
        case 'verificado':
            $where_conditions[] = "verificado = 1";
            break;
    }
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

$query = "
    SELECT e.*, c.title as city_name 
    FROM escorts e 
    LEFT JOIN cities c ON e.city_id = c.id 
    $where_clause 
    ORDER BY e.created_at DESC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$escorts = $stmt->get_result();

// Buscar cidades para o select
$cities = $conn->query("SELECT id, title FROM cities ORDER BY title ASC");

// Estatísticas
$stats_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN type = 'chica' THEN 1 ELSE 0 END) as chicas,
        SUM(CASE WHEN type = 'chico' THEN 1 ELSE 0 END) as chicos,
        SUM(CASE WHEN type = 'trans' THEN 1 ELSE 0 END) as trans,
        SUM(CASE WHEN super_vip = 1 THEN 1 ELSE 0 END) as super_vip,
        SUM(CASE WHEN vip = 1 THEN 1 ELSE 0 END) as vip,
        SUM(CASE WHEN top = 1 THEN 1 ELSE 0 END) as top,
        SUM(CASE WHEN verificado = 1 THEN 1 ELSE 0 END) as verificadas
    FROM escorts
";
$stats = $conn->query($stats_query)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gerenciar Escorts | Citas</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            --warning-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            --dark-bg: #0a0a0a;
            --card-bg: linear-gradient(145deg, #1e1e1e, #2a2a2a);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --border-color: rgba(255, 255, 255, 0.1);
            --text-primary: #ffffff;
            --text-secondary: #b8b8b8;
            --text-muted: #666666;
        }

        body {
            background: var(--dark-bg);
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(102, 126, 234, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(240, 147, 251, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(0, 242, 254, 0.1) 0%, transparent 50%);
            color: var(--text-primary);
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            font-weight: 400;
            line-height: 1.6;
        }

        /* Enhanced Header */
        .header {
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .header-content {
            max-width: 1600px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 2rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            font-weight: 500;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: var(--primary-gradient);
            transition: left 0.3s ease;
            z-index: -1;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            left: 0;
        }

        .nav-link:hover,
        .nav-link.active {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        /* Enhanced Container */
        .container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Modern Page Header */
        .page-header {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 3rem 2rem;
            margin-bottom: 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .page-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 1.25rem;
            font-weight: 400;
        }

        /* Enhanced Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 2rem 1.5rem;
            text-align: center;
            border: 1px solid var(--border-color);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
            background: var(--primary-gradient);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            background: var(--primary-gradient);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.95rem;
        }

        /* Enhanced Controls */
        .controls {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .controls-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .controls-title {
            font-size: 1.75rem;
            font-weight: 600;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Enhanced Buttons */
        .btn {
            padding: 0.875rem 1.75rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-danger {
            background: var(--danger-gradient);
            color: white;
            box-shadow: 0 4px 15px rgba(250, 112, 154, 0.3);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(250, 112, 154, 0.4);
        }

        .btn-secondary {
            background: var(--glass-bg);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        /* Enhanced Filters */
        .filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .filter-group input,
        .filter-group select {
            padding: 1rem;
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-primary);
            outline: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 0.08);
        }

        .filter-group select option {
            background: #1e1e1e;
            color: white;
        }

        /* Enhanced Table */
        .escorts-table {
            background: var(--card-bg);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }

        .table-header {
            background: rgba(255, 255, 255, 0.03);
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
        }

        .table-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1.25rem;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        th {
            background: rgba(255, 255, 255, 0.02);
            font-weight: 600;
            color: #667eea;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr {
            transition: all 0.3s ease;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.03);
        }

        .escort-image {
            width: 70px;
            height: 90px;
            border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        /* Enhanced Badges */
        .badge {
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            margin: 0.2rem;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-super-vip {
            background: linear-gradient(135deg, #ff0844, #ff6b6b);
            box-shadow: 0 2px 8px rgba(255, 8, 68, 0.3);
        }

        .badge-vip {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
        }

        .badge-top {
            background: linear-gradient(135deg, #feca57, #ff9ff3);
            box-shadow: 0 2px 8px rgba(254, 202, 87, 0.3);
        }

        .badge-verificado {
            background: linear-gradient(135deg, #48dbfb, #0abde3);
            box-shadow: 0 2px 8px rgba(72, 219, 251, 0.3);
        }

        .badge-chica {
            background: var(--secondary-gradient);
            box-shadow: 0 2px 8px rgba(240, 147, 251, 0.3);
        }

        .badge-chico {
            background: var(--primary-gradient);
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .badge-trans {
            background: var(--success-gradient);
            box-shadow: 0 2px 8px rgba(79, 172, 254, 0.3);
        }

        /* Enhanced Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 10000;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background: var(--card-bg);
            border-radius: 24px;
            padding: 2rem;
            max-width: 800px;
            width: 95%;
            max-height: 95vh;
            overflow-y: auto;
            position: relative;
            border: 1px solid var(--border-color);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-title {
            font-size: 2rem;
            font-weight: 600;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .close-modal {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        /* Enhanced Form */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            background: var(--glass-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-primary);
            outline: none;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 0.08);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        /* Enhanced Map Section */
        .map-section {
            margin: 2rem 0;
            padding: 1.5rem;
            background: var(--glass-bg);
            border-radius: 16px;
            border: 1px solid var(--border-color);
        }

        .map-section h4 {
            margin-bottom: 1rem;
            color: var(--text-primary);
            font-weight: 600;
        }

        #map {
            width: 100%;
            height: 300px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .map-controls {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            margin-top: 1rem;
        }

        /* Enhanced Checkbox Group */
        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
            padding: 1rem;
            background: var(--glass-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.02);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .checkbox-item:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .checkbox-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
        }

        .checkbox-item label {
            margin: 0;
            cursor: pointer;
            font-weight: 500;
        }

        /* Enhanced Alerts */
        .alert {
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 500;
            border: 1px solid;
        }

        .alert-success {
            background: rgba(79, 172, 254, 0.1);
            border-color: rgba(79, 172, 254, 0.3);
            color: #4facfe;
        }

        .alert-error {
            background: rgba(250, 112, 154, 0.1);
            border-color: rgba(250, 112, 154, 0.3);
            color: #fa709a;
        }

        /* Enhanced Actions */
        .actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* Enhanced Responsive */
        @media (max-width: 1024px) {
            .container {
                padding: 1rem;
            }

            .page-title {
                font-size: 2.5rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .controls-header {
                flex-direction: column;
                align-items: stretch;
            }

            .filters {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }

            .table-wrapper {
                font-size: 0.9rem;
            }

            .escort-image {
                width: 50px;
                height: 70px;
            }

            th, td {
                padding: 0.75rem 0.5rem;
            }

            .nav-links {
                display: none;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .modal-content {
                padding: 1.5rem;
                margin: 1rem;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #667eea;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Smooth transitions */
        * {
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-crown"></i>
                Citas Admin
            </a>
            
            <nav class="nav-links">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <a href="admin_escorts.php" class="nav-link active">
                    <i class="fas fa-users"></i>
                    Escorts
                </a>
                <a href="admin_cidades.php" class="nav-link">
                    <i class="fas fa-map-marker-alt"></i>
                    Cidades
                </a>
                <a href="?logout=1" class="nav-link" onclick="return confirm('Sair?')">
                    <i class="fas fa-sign-out-alt"></i>
                    Sair
                </a>
            </nav>
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

        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-users-cog"></i>
                Gerenciar Escorts
            </h1>
            <p class="page-subtitle">Painel administrativo completo para gerenciar todas as escorts e acompanhantes</p>
        </div>

        <!-- Enhanced Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?= $stats['total'] ?></div>
                <div class="stat-label">Total de Perfis</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-venus"></i>
                </div>
                <div class="stat-number"><?= $stats['chicas'] ?></div>
                <div class="stat-label">Chicas</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-mars"></i>
                </div>
                <div class="stat-number"><?= $stats['chicos'] ?></div>
                <div class="stat-label">Chicos</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-transgender"></i>
                </div>
                <div class="stat-number"><?= $stats['trans'] ?></div>
                <div class="stat-label">Trans</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-number"><?= $stats['super_vip'] ?></div>
                <div class="stat-label">Super VIP</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-gem"></i>
                </div>
                <div class="stat-number"><?= $stats['vip'] ?></div>
                <div class="stat-label">VIP</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-number"><?= $stats['top'] ?></div>
                <div class="stat-label">TOP</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-shield-check"></i>
                </div>
                <div class="stat-number"><?= $stats['verificadas'] ?></div>
                <div class="stat-label">Verificadas</div>
            </div>
        </div>

        <!-- Enhanced Controls -->
        <div class="controls">
            <div class="controls-header">
                <h2 class="controls-title">
                    <i class="fas fa-filter"></i>
                    Filtros e Controles
                </h2>
                <button onclick="openModal('create')" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Nova Escort
                </button>
            </div>

            <form method="GET" class="filters">
                <div class="filter-group">
                    <label>
                        <i class="fas fa-search"></i>
                        Buscar
                    </label>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Nome, localização, descrição...">
                </div>
                <div class="filter-group">
                    <label>
                        <i class="fas fa-user-tag"></i>
                        Tipo
                    </label>
                    <select name="type">
                        <option value="">Todos os tipos</option>
                        <option value="chica" <?= $type_filter === 'chica' ? 'selected' : '' ?>>👩 Chicas</option>
                        <option value="chico" <?= $type_filter === 'chico' ? 'selected' : '' ?>>👨 Chicos</option>
                        <option value="trans" <?= $type_filter === 'trans' ? 'selected' : '' ?>>🏳️‍⚧️ Trans</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>
                        <i class="fas fa-map-marker-alt"></i>
                        Cidade
                    </label>
                    <select name="city">
                        <option value="">Todas as cidades</option>
                        <?php $cities->data_seek(0); while ($city = $cities->fetch_assoc()): ?>
                        <option value="<?= $city['id'] ?>" <?= $city_filter == $city['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($city['title']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>
                        <i class="fas fa-medal"></i>
                        Status
                    </label>
                    <select name="status">
                        <option value="">Todos os status</option>
                        <option value="super_vip" <?= $status_filter === 'super_vip' ? 'selected' : '' ?>>🔥 Super VIP</option>
                        <option value="vip" <?= $status_filter === 'vip' ? 'selected' : '' ?>>💎 VIP</option>
                        <option value="top" <?= $status_filter === 'top' ? 'selected' : '' ?>>⭐ TOP</option>
                        <option value="verificado" <?= $status_filter === 'verificado' ? 'selected' : '' ?>>✅ Verificadas</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search"></i>
                        Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>

        <!-- Enhanced Table -->
        <div class="escorts-table">
            <div class="table-header">
                <h3 class="table-title">
                    <i class="fas fa-list"></i>
                    Lista de Escorts (<?= $escorts->num_rows ?> resultados)
                </h3>
            </div>
            
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nome & Info</th>
                            <th>Tipo</th>
                            <th>Localização</th>
                            <th>Status & Badges</th>
                            <th>Contato</th>
                            <th>Mapa</th>
                            <th>Criado</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($escort = $escorts->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($escort['image_url']) ?>" 
                                     alt="<?= htmlspecialchars($escort['name']) ?>" 
                                     class="escort-image" 
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzAiIGhlaWdodD0iOTAiIHZpZXdCb3g9IjAgMCA3MCA5MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjcwIiBoZWlnaHQ9IjkwIiBmaWxsPSIjMzMzIi8+Cjx0ZXh0IHg9IjM1IiB5PSI0NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzY2NiIgZm9udC1zaXplPSIxMiI+Tm8gSW1hZ2U8L3RleHQ+Cjwvc3ZnPgo='">
                            </td>
                            <td>
                                <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;">
                                    <?= htmlspecialchars($escort['name']) ?>
                                </div>
                                <?php if ($escort['age']): ?>
                                    <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                        <i class="fas fa-birthday-cake"></i>
                                        <?= $escort['age'] ?> años
                                    </div>
                                <?php endif; ?>
                                <?php if ($escort['nationality']): ?>
                                    <div style="color: var(--text-secondary); font-size: 0.9rem; margin-top: 0.25rem;">
                                        <img src="https://flagcdn.com/w20/<?= strtolower($escort['nationality']) ?>.png" 
                                             style="width: 16px; margin-right: 0.5rem;" 
                                             onerror="this.style.display='none'">
                                        <?= strtoupper($escort['nationality']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= $escort['type'] ?>">
                                    <?php if ($escort['type'] === 'chica'): ?>
                                        <i class="fas fa-venus"></i> CHICA
                                    <?php elseif ($escort['type'] === 'chico'): ?>
                                        <i class="fas fa-mars"></i> CHICO
                                    <?php else: ?>
                                        <i class="fas fa-transgender"></i> TRANS
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($escort['location']): ?>
                                    <div style="font-weight: 500; margin-bottom: 0.25rem;">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?= htmlspecialchars($escort['location']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($escort['zone']): ?>
                                    <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                        <?= htmlspecialchars($escort['zone']) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($escort['city_name']): ?>
                                    <div style="color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem;">
                                        <i class="fas fa-city"></i>
                                        <?= htmlspecialchars($escort['city_name']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($escort['super_vip']): ?>
                                    <span class="badge badge-super-vip">
                                        <i class="fas fa-crown"></i> SUPER VIP
                                    </span>
                                <?php elseif ($escort['vip']): ?>
                                    <span class="badge badge-vip">
                                        <i class="fas fa-gem"></i> VIP
                                    </span>
                                <?php elseif ($escort['top']): ?>
                                    <span class="badge badge-top">
                                        <i class="fas fa-star"></i> TOP
                                    </span>
                                <?php endif; ?>
                                
                                <?php if ($escort['verificado']): ?>
                                    <span class="badge badge-verificado">
                                        <i class="fas fa-shield-check"></i> VERIFICADO
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($escort['phone']): ?>
                                    <div style="margin-bottom: 0.5rem;">
                                        <i class="fab fa-whatsapp" style="color: #25D366; margin-right: 0.5rem;"></i>
                                        <span style="font-size: 0.9rem;"><?= htmlspecialchars($escort['phone']) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($escort['telegram']): ?>
                                    <div style="margin-bottom: 0.5rem;">
                                        <i class="fab fa-telegram" style="color: #0088cc; margin-right: 0.5rem;"></i>
                                        <span style="font-size: 0.9rem;">Telegram</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!$escort['phone'] && !$escort['telegram']): ?>
                                    <span style="color: var(--text-muted); font-style: italic;">
                                        <i class="fas fa-phone-slash"></i>
                                        Sem contato
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($escort['latitude'] && $escort['longitude']): ?>
                                    <div style="text-align: center;">
                                        <button onclick="showMapPreview(<?= $escort['latitude'] ?>, <?= $escort['longitude'] ?>, '<?= htmlspecialchars($escort['name']) ?>')" 
                                                class="btn btn-secondary btn-sm" title="Ver no Mapa">
                                            <i class="fas fa-map-marked-alt"></i>
                                        </button>
                                        <?php if ($escort['map_address']): ?>
                                            <div style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 0.25rem;">
                                                <?= htmlspecialchars(mb_strimwidth($escort['map_address'], 0, 30, '...')) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-style: italic;">
                                        <i class="fas fa-map-marked"></i>
                                        Sem localização
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="font-weight: 500;">
                                    <?= date('d/m/Y', strtotime($escort['created_at'])) ?>
                                </div>
                                <div style="color: var(--text-secondary); font-size: 0.9rem;">
                                    <?= date('H:i', strtotime($escort['created_at'])) ?>
                                </div>
                            </td>
                            <td>
                                <div class="actions">
                                    <button onclick="viewEscort(<?= $escort['id'] ?>)" 
                                            class="btn btn-secondary btn-sm" 
                                            title="Ver Perfil">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editEscort(<?= $escort['id'] ?>)" 
                                            class="btn btn-primary btn-sm" 
                                            title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="manageMedia(<?= $escort['id'] ?>)" 
                                            class="btn btn-secondary btn-sm" 
                                            title="Gerenciar Mídia">
                                        <i class="fas fa-images"></i>
                                    </button>
                                    <button onclick="deleteEscort(<?= $escort['id'] ?>, '<?= htmlspecialchars($escort['name']) ?>')" 
                                            class="btn btn-danger btn-sm" 
                                            title="Deletar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if ($escorts->num_rows === 0): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 4rem; color: var(--text-muted);">
                                <div style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.3;">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">
                                    Nenhuma escort encontrada
                                </div>
                                <div style="font-size: 0.9rem;">
                                    Tente ajustar os filtros ou criar uma nova escort
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Enhanced Modal -->
    <div id="escortModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle" class="modal-title">
                    <i class="fas fa-user-plus"></i>
                    Nova Escort
                </h2>
                <button type="button" class="close-modal" onclick="closeModal()">×</button>
            </div>
            
            <form id="escortForm" method="POST">
                <input type="hidden" name="action" id="formAction" value="create">
                <input type="hidden" name="id" id="escortId">
                
                <!-- Basic Info -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="name">
                            <i class="fas fa-user"></i>
                            Nome *
                        </label>
                        <input type="text" id="name" name="name" required placeholder="Nome da escort">
                    </div>
                    
                    <div class="form-group">
                        <label for="age">
                            <i class="fas fa-birthday-cake"></i>
                            Idade
                        </label>
                        <input type="number" id="age" name="age" min="18" max="99" placeholder="Idade">
                    </div>
                    
                    <div class="form-group">
                        <label for="type">
                            <i class="fas fa-user-tag"></i>
                            Tipo *
                        </label>
                        <select id="type" name="type" required>
                            <option value="chica">👩 Chica</option>
                            <option value="chico">👨 Chico</option>
                            <option value="trans">🏳️‍⚧️ Trans</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="city_id">
                            <i class="fas fa-city"></i>
                            Cidade
                        </label>
                        <select id="city_id" name="city_id">
                            <option value="">Selecione uma cidade</option>
                            <?php $cities->data_seek(0); while ($city = $cities->fetch_assoc()): ?>
                            <option value="<?= $city['id'] ?>"><?= htmlspecialchars($city['title']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image_url">
                        <i class="fas fa-image"></i>
                        URL da Imagem *
                    </label>
                    <input type="url" id="image_url" name="image_url" required placeholder="https://exemplo.com/imagem.jpg">
                </div>
                
                <!-- Location Info -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="location">
                            <i class="fas fa-map-marker-alt"></i>
                            Localização
                        </label>
                        <input type="text" id="location" name="location" placeholder="Ex: Madrid">
                    </div>
                    
                    <div class="form-group">
                        <label for="zone">
                            <i class="fas fa-map-pin"></i>
                            Zona
                        </label>
                        <input type="text" id="zone" name="zone" placeholder="Ex: Centro, Zona Norte">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i>
                        Descrição
                    </label>
                    <textarea id="description" name="description" placeholder="Descrição detalhada da escort..."></textarea>
                </div>
                
                <!-- Contact Info -->
                <div class="form-grid">
                    <div class="form-group">
                        <label for="phone">
                            <i class="fab fa-whatsapp"></i>
                            Telefone/WhatsApp
                        </label>
                        <input type="text" id="phone" name="phone" placeholder="Ex: +34 666 123 456">
                    </div>
                    
                    <div class="form-group">
                        <label for="nationality">
                            <i class="fas fa-flag"></i>
                            Nacionalidade
                        </label>
                        <select id="nationality" name="nationality">
                            <option value="">Selecione</option>
                            <option value="es">🇪🇸 España</option>
                            <option value="br">🇧🇷 Brasil</option>
                            <option value="co">🇨🇴 Colombia</option>
                            <option value="ar">🇦🇷 Argentina</option>
                            <option value="ve">🇻🇪 Venezuela</option>
                            <option value="mx">🇲🇽 México</option>
                            <option value="pe">🇵🇪 Perú</option>
                            <option value="ec">🇪🇨 Ecuador</option>
                            <option value="cl">🇨🇱 Chile</option>
                            <option value="uy">🇺🇾 Uruguay</option>
                            <option value="fr">🇫🇷 Francia</option>
                            <option value="it">🇮🇹 Italia</option>
                            <option value="ru">🇷🇺 Rusia</option>
                            <option value="ua">🇺🇦 Ucrania</option>
                            <option value="ro">🇷🇴 Rumania</option>
                        </select>
                    </div>
                </div>

                <!-- Map Section -->
                <div class="map-section">
                    <h4>
                        <i class="fas fa-map-marked-alt"></i>
                        Localização no Mapa
                    </h4>
                    <div id="map"></div>
                    <div class="map-controls">
                        <input type="text" id="map_address" name="map_address" placeholder="Digite um endereço para buscar no mapa">
                        <button type="button" onclick="searchAddress()" class="btn btn-secondary">
                            <i class="fas fa-search"></i>
                            Buscar
                        </button>
                    </div>
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">
                </div>
                
                <!-- Badges & Status -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-medals"></i>
                        Badges e Status
                    </label>
                    <div class="checkbox-group">
                        <div class="checkbox-item">
                            <input type="checkbox" id="super_vip" name="super_vip">
                            <label for="super_vip">
                                <i class="fas fa-crown"></i>
                                Super VIP
                            </label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="vip" name="vip">
                            <label for="vip">
                                <i class="fas fa-gem"></i>
                                VIP
                            </label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="top" name="top">
                            <label for="top">
                                <i class="fas fa-star"></i>
                                TOP
                            </label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="verificado" name="verificado">
                            <label for="verificado">
                                <i class="fas fa-shield-check"></i>
                                Verificado
                            </label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="telegram" name="telegram">
                            <label for="telegram">
                                <i class="fab fa-telegram"></i>
                                Telegram
                            </label>
                        </div>
                        <div class="checkbox-item">
                            <input type="checkbox" id="whatsapp" name="whatsapp">
                            <label for="whatsapp">
                                <i class="fab fa-whatsapp"></i>
                                WhatsApp
                            </label>
                        </div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--border-color);">
                    <button type="button" onclick="closeModal()" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Salvar Escort
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Map Preview Modal -->
    <div id="mapPreviewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">
                    <i class="fas fa-map-marked-alt"></i>
                    Localização no Mapa
                </h2>
                <button type="button" class="close-modal" onclick="closeMapPreview()">×</button>
            </div>
            <div id="mapPreview" style="width: 100%; height: 400px; border-radius: 12px;"></div>
        </div>
    </div>

    <script>
        // Global variables
        let map;
        let marker;
        let mapPreview;
        let escorts = [];

        // Load escorts data
        <?php 
        $escorts->data_seek(0);
        echo "escorts = [";
        $escort_data = [];
        while ($escort = $escorts->fetch_assoc()) {
            $escort_data[] = json_encode($escort);
        }
        echo implode(',', $escort_data);
        echo "];";
        ?>

        // Initialize Leaflet Map
        function initMap() {
            // Default to Madrid
            const defaultLocation = [40.4168, -3.7038];
            
            // Create map
            map = L.map('map', {
                center: defaultLocation,
                zoom: 13,
                zoomControl: true
            });

            // Add tile layer (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Add marker
            marker = L.marker(defaultLocation, { draggable: true }).addTo(map);

            // Update coordinates when marker is dragged
            marker.on('dragend', function(e) {
                const position = e.target.getLatLng();
                document.getElementById('latitude').value = position.lat.toFixed(6);
                document.getElementById('longitude').value = position.lng.toFixed(6);
                
                // Reverse geocoding using Nominatim
                reverseGeocode(position.lat, position.lng);
            });

            // Click on map to place marker
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
                
                // Reverse geocoding
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });
        }

        // Reverse geocoding using Nominatim (OpenStreetMap)
        function reverseGeocode(lat, lng) {
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    if (data && data.display_name) {
                        document.getElementById('map_address').value = data.display_name;
                    }
                })
                .catch(error => {
                    console.log('Reverse geocoding error:', error);
                });
        }

        // Search address function using Nominatim
        function searchAddress() {
            const address = document.getElementById('map_address').value;
            if (!address) {
                alert('Digite um endereço para buscar');
                return;
            }

            // Show loading
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Buscando...';
            button.disabled = true;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1&addressdetails=1`)
                .then(response => response.json())
                .then(data => {
                    button.innerHTML = originalText;
                    button.disabled = false;

                    if (data && data.length > 0) {
                        const result = data[0];
                        const lat = parseFloat(result.lat);
                        const lng = parseFloat(result.lon);
                        
                        map.setView([lat, lng], 15);
                        marker.setLatLng([lat, lng]);
                        document.getElementById('latitude').value = lat.toFixed(6);
                        document.getElementById('longitude').value = lng.toFixed(6);
                        document.getElementById('map_address').value = result.display_name;
                        
                        // Success message
                        showNotification('Endereço encontrado!', 'success');
                    } else {
                        showNotification('Endereço não encontrado. Tente ser mais específico.', 'error');
                    }
                })
                .catch(error => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    console.error('Geocoding error:', error);
                    showNotification('Erro ao buscar endereço. Tente novamente.', 'error');
                });
        }

        // Show notification
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '10001';
            notification.style.minWidth = '300px';
            notification.style.animation = 'slideInRight 0.3s ease';
            
            const icon = type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-triangle' : 'info-circle';
            notification.innerHTML = `
                <i class="fas fa-${icon}"></i>
                ${message}
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Show map preview
        function showMapPreview(lat, lng, name) {
            const modal = document.getElementById('mapPreviewModal');
            modal.style.display = 'flex';
            
            setTimeout(() => {
                const location = [parseFloat(lat), parseFloat(lng)];
                
                // Clear previous map
                const mapElement = document.getElementById('mapPreview');
                mapElement.innerHTML = '';
                
                mapPreview = L.map('mapPreview', {
                    center: location,
                    zoom: 15,
                    zoomControl: true
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors',
                    maxZoom: 19
                }).addTo(mapPreview);

                // Custom marker with popup
                const customIcon = L.divIcon({
                    className: 'custom-marker',
                    html: '<i class="fas fa-map-marker-alt" style="color: #667eea; font-size: 2rem;"></i>',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30]
                });

                L.marker(location, { icon: customIcon })
                    .addTo(mapPreview)
                    .bindPopup(`<strong>${name}</strong><br>Lat: ${lat}<br>Lng: ${lng}`)
                    .openPopup();
            }, 100);
        }

        function closeMapPreview() {
            document.getElementById('mapPreviewModal').style.display = 'none';
            if (mapPreview) {
                mapPreview.remove();
                mapPreview = null;
            }
        }

        // Modal functions
        function openModal(action, escortId = null) {
            const modal = document.getElementById('escortModal');
            const form = document.getElementById('escortForm');
            const modalTitle = document.getElementById('modalTitle');
            const formAction = document.getElementById('formAction');
            
            if (action === 'create') {
                modalTitle.innerHTML = '<i class="fas fa-user-plus"></i> Nova Escort';
                formAction.value = 'create';
                form.reset();
                document.getElementById('escortId').value = '';
                
                // Reset map
                if (map) {
                    const defaultLocation = [40.4168, -3.7038];
                    map.setView(defaultLocation, 13);
                    marker.setLatLng(defaultLocation);
                    document.getElementById('latitude').value = '';
                    document.getElementById('longitude').value = '';
                }
            } else if (action === 'edit' && escortId) {
                modalTitle.innerHTML = '<i class="fas fa-user-edit"></i> Editar Escort';
                formAction.value = 'update';
                document.getElementById('escortId').value = escortId;
                
                const escort = escorts.find(e => e.id == escortId);
                if (escort) {
                    fillForm(escort);
                }
            }
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Initialize map after modal is shown
            setTimeout(() => {
                if (!map) {
                    initMap();
                }
            }, 100);
        }

        function closeModal() {
            document.getElementById('escortModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function fillForm(escort) {
            document.getElementById('name').value = escort.name || '';
            document.getElementById('age').value = escort.age || '';
            document.getElementById('type').value = escort.type || 'chica';
            document.getElementById('city_id').value = escort.city_id || '';
            document.getElementById('image_url').value = escort.image_url || '';
            document.getElementById('location').value = escort.location || '';
            document.getElementById('zone').value = escort.zone || '';
            document.getElementById('description').value = escort.description || '';
            document.getElementById('phone').value = escort.phone || '';
            document.getElementById('nationality').value = escort.nationality || '';
            document.getElementById('map_address').value = escort.map_address || '';
            document.getElementById('latitude').value = escort.latitude || '';
            document.getElementById('longitude').value = escort.longitude || '';
            
            // Checkboxes
            document.getElementById('super_vip').checked = escort.super_vip == 1;
            document.getElementById('vip').checked = escort.vip == 1;
            document.getElementById('top').checked = escort.top == 1;
            document.getElementById('verificado').checked = escort.verificado == 1;
            document.getElementById('telegram').checked = escort.telegram == 1;
            document.getElementById('whatsapp').checked = escort.whatsapp == 1;
            
            // Update map if coordinates exist
            if (escort.latitude && escort.longitude && map) {
                const location = [parseFloat(escort.latitude), parseFloat(escort.longitude)];
                map.setView(location, 15);
                marker.setLatLng(location);
            }
        }

        // Action functions
        function viewEscort(id) {
            window.open(`perfil.php?id=${id}`, '_blank');
        }

        function editEscort(id) {
            openModal('edit', id);
        }

        function manageMedia(id) {
            window.open(`escort_media_manager.php?escort_id=${id}`, '_blank');
        }

        function deleteEscort(id, name) {
            if (confirm(`⚠️ Tem certeza que deseja deletar "${name}"?\n\nEsta ação não pode ser desfeita!`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Close modals clicking outside
            document.getElementById('escortModal').addEventListener('click', function(e) {
                if (e.target === this) closeModal();
            });

            document.getElementById('mapPreviewModal').addEventListener('click', function(e) {
                if (e.target === this) closeMapPreview();
            });

            // ESC key to close modals
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                    closeMapPreview();
                }
            });

            // Auto-hide alerts
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);

            // Exclusive badge validation
            const superVipCheckbox = document.getElementById('super_vip');
            const vipCheckbox = document.getElementById('vip');
            const topCheckbox = document.getElementById('top');

            [superVipCheckbox, vipCheckbox, topCheckbox].forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        [superVipCheckbox, vipCheckbox, topCheckbox].forEach(cb => {
                            if (cb !== this) cb.checked = false;
                        });
                    }
                });
            });

            // Image URL validation
            document.getElementById('image_url').addEventListener('input', function() {
                const url = this.value;
                if (url) {
                    if (!url.match(/\.(jpg|jpeg|png|gif|webp)$/i)) {
                        this.style.borderColor = '#fa709a';
                        this.title = 'URL deve terminar com .jpg, .png, .gif ou .webp';
                    } else {
                        this.style.borderColor = '#4facfe';
                        this.title = 'URL válida';
                    }
                }
            });

            // Real-time search
            let searchTimeout;
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        if (this.value.length >= 3 || this.value.length === 0) {
                            this.closest('form').submit();
                        }
                    }, 500);
                });
            }

            // Animate stats cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Address search on Enter key
            document.getElementById('map_address').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    searchAddress();
                }
            });

            // Map resize fix
            window.addEventListener('resize', function() {
                if (map) {
                    setTimeout(() => map.invalidateSize(), 100);
                }
            });
        });

        // CSS for animations
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            .custom-marker {
                background: none !important;
                border: none !important;
            }
            
            .leaflet-popup-content-wrapper {
                background: var(--card-bg);
                border-radius: 8px;
                color: white;
            }
            
            .leaflet-popup-tip {
                background: var(--card-bg);
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>