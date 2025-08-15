<?php
$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");

$slug = $_GET['slug'] ?? '';
$type = $_GET['type'] ?? 'chica';
$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = intval($_GET['page'] ?? 1);
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Buscar informações da cidade
$stmt = $conn->prepare("SELECT id, title FROM cities WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("HTTP/1.0 404 Not Found");
    echo "Cidade não encontrada.";
    exit;
}

$city = $result->fetch_assoc();
$city_id = $city['id'];

// Função para renderizar seção de escorts
function renderEscortSection($conn, $city_id, $type, $filter, $title, $emoji, $search = '') {
    // Construir query com filtros
    $where = "city_id = ? AND type = ?";
    $params = [$city_id, $type];
    $types = "is";

    // Aplicar filtros específicos
    if ($filter === "super_vip") {
        $where .= " AND super_vip = 1";
    } elseif ($filter === "vip") {
        $where .= " AND vip = 1 AND super_vip = 0";
    } elseif ($filter === "top") {
        $where .= " AND top = 1 AND vip = 0 AND super_vip = 0";
    } elseif ($filter === "others") {
        $where .= " AND super_vip = 0 AND vip = 0 AND top = 0";
    }

    // Aplicar busca
    if (!empty($search)) {
        $where .= " AND (name LIKE ? OR description LIKE ? OR location LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
        $types .= "sss";
    }

    // Query para buscar escorts
    $query = "SELECT * FROM escorts WHERE $where ORDER BY super_vip DESC, vip DESC, top DESC, created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $escorts = $stmt->get_result();

    if ($escorts->num_rows === 0) {
        return; // Não renderiza seção vazia
    }

    echo "<section class='escort-section animate-on-scroll'>";
    echo "<h2 class='section-title'>$emoji $title</h2>";
    echo "<div class='escorts-grid'>";

    while ($row = $escorts->fetch_assoc()) {
        // Definir valores padrão se estiverem vazios
        $age = (!empty($row['age']) && $row['age'] > 0) ? $row['age'] : rand(22, 28);
        $location = !empty($row['location']) ? $row['location'] : 'Disponível';
        $zone = !empty($row['zone']) ? $row['zone'] : '';
        $description = !empty($row['description']) ? $row['description'] : 'Linda acompanhante disponível para encontros discretos.';
        $phone = !empty($row['phone']) ? $row['phone'] : '';
        
        echo "<a href='perfil.php?id={$row['id']}' class='escort-card-link'>
                <div class='escort-card'>
                    <div class='escort-image'>
                        <img src='" . htmlspecialchars($row['image_url']) . "' 
                             alt='" . htmlspecialchars($row['name']) . "'
                             loading='lazy'
                             onerror=\"this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDMwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjMzMzIi8+Cjx0ZXh0IHg9IjE1MCIgeT0iMTUwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjY2IiBmb250LXNpemU9IjE2Ij5JbWFnZW0gbsOjbyBlbmNvbnRyYWRhPC90ZXh0Pgo8L3N2Zz4K'\">";

        // Bandeira da nacionalidade
        if (!empty($row['nationality'])) {
            echo "<div class='escort-flag'>
                    <img src='https://flagcdn.com/w40/" . strtolower(htmlspecialchars($row['nationality'])) . ".png' 
                         alt='" . htmlspecialchars($row['nationality']) . "'
                         loading='lazy'
                         onerror=\"this.style.display='none'\">
                  </div>";
        }

        // Badges de classificação - SEMPRE EXIBIR APENAS UM EM HIERARQUIA
        if (!empty($row['super_vip'])) {
            echo "<div class='escort-badge super-vip'>SUPER VIP</div>";
        } elseif (!empty($row['vip'])) {
            echo "<div class='escort-badge vip'>VIP</div>";
        } elseif (!empty($row['top'])) {
            echo "<div class='escort-badge top'>TOP</div>";
        }

        // Badge de verificação - SEMPRE SEPARADO
        if (!empty($row['verificado'])) {
            echo "<div class='escort-badge verificado'><i class='fas fa-check-circle'></i></div>";
        }

        // Badges de contato - SEMPRE SEPARADOS
        if (!empty($row['telegram'])) {
            echo "<div class='escort-badge telegram'><i class='fab fa-telegram'></i></div>";
        }

        if (!empty($row['phone'])) {
            echo "<div class='escort-badge whatsapp'><i class='fab fa-whatsapp'></i></div>";
        }

        echo "    </div>
                    <div class='escort-info'>
                        <h3 class='escort-name'>" . htmlspecialchars($row['name']) . " - $age años</h3>
                        
                        <div class='escort-location'>
                            <i class='fas fa-map-marker-alt'></i>
                            <span>" . htmlspecialchars($location) . ($zone ? ' - ' . htmlspecialchars($zone) : '') . "</span>
                        </div>
                        
                        <p class='escort-description'>
                            " . htmlspecialchars(mb_substr($description, 0, 100)) . (strlen($description) > 100 ? '...' : '') . "
                        </p>";
        
        if ($phone) {
            echo "<div class='escort-phone'>
                    <i class='fab fa-whatsapp'></i>
                    <span>" . htmlspecialchars($phone) . "</span>
                  </div>";
        }

        echo "        <div class='escort-status'>
                            <div class='status-online'></div>
                            <span class='status-text'>Online</span>
                        </div>
                    </div>
                </div>
              </a>";
    }
    echo "</div></section>";
}

// Estatísticas da cidade
$stats_query = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN super_vip = 1 THEN 1 ELSE 0 END) as super_vip_count,
    SUM(CASE WHEN vip = 1 AND super_vip = 0 THEN 1 ELSE 0 END) as vip_count,
    SUM(CASE WHEN top = 1 AND vip = 0 AND super_vip = 0 THEN 1 ELSE 0 END) as top_count,
    SUM(CASE WHEN super_vip = 0 AND vip = 0 AND top = 0 THEN 1 ELSE 0 END) as others_count
    FROM escorts WHERE city_id = ? AND type = ?";

$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("is", $city_id, $type);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($city['title']) ?> - <?= $type === 'trans' ? 'Trans' : 'Chicas' ?> - Citas</title>
    <meta name="description" content="Encuentra las mejores <?= $type === 'trans' ? 'trans' : 'chicas' ?> de <?= htmlspecialchars($city['title']) ?>. <?= $stats['total'] ?> perfiles disponibles.">
    
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

        /* HEADER */
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

        .nav-links {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .nav-link:hover, .nav-link.active {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            transform: translateY(-2px);
        }

        /* CONTAINER PRINCIPAL */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* BREADCRUMB */
        .breadcrumb {
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #888;
        }

        .breadcrumb a {
            color: #ff6b6b;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* CABEÇALHO DA CIDADE */
        .city-header {
            background: linear-gradient(135deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
        }

        .city-title {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .city-subtitle {
            font-size: 1.2rem;
            color: #ccc;
            margin-bottom: 2rem;
        }

        /* ESTATÍSTICAS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 107, 107, 0.1), rgba(254, 202, 87, 0.1));
            border: 1px solid rgba(255, 107, 107, 0.2);
            border-radius: 15px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.2);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #aaa;
            margin-top: 0.5rem;
        }

        /* FILTROS E BUSCA */
        .controls {
            background: linear-gradient(135deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        .search-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 250px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.8rem 1rem;
            border-radius: 15px;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: #ff6b6b;
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.2);
        }

        .search-input::placeholder {
            color: #aaa;
        }

        .sort-select {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.8rem 1rem;
            border-radius: 15px;
            outline: none;
            cursor: pointer;
        }

        .sort-select option {
            background: #2a2a2a;
            color: white;
        }

        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 0.5rem 1rem;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: transparent;
            color: white;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .filter-btn:hover, .filter-btn.active {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            border-color: transparent;
            transform: translateY(-2px);
        }

        .filter-btn.super-vip.active {
            background: linear-gradient(135deg, #ff0844, #ff6b6b);
        }

        .filter-btn.vip.active {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
        }

        .filter-btn.top.active {
            background: linear-gradient(135deg, #feca57, #ff9ff3);
        }

        /* SEÇÕES DE ESCORTS */
        .escort-section {
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
        }

        .section-title::after {
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

        /* GRID DE ESCORTS - USANDO O MESMO ESTILO DOS CARDS */
        .escorts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 300px));
            gap: 1rem;
            padding: 1rem 0;
            justify-content: center;
        }

        .escort-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .escort-card {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            overflow: hidden;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.4),
                0 4px 20px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.08);
            position: relative;
            backdrop-filter: blur(10px);
            height: 520px;
            width: 100%;
            max-width: 300px;
            display: flex;
            flex-direction: column;
        }

        .escort-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 
                0 25px 60px rgba(255, 107, 107, 0.15),
                0 15px 40px rgba(0, 0, 0, 0.3),
                0 5px 20px rgba(255, 107, 107, 0.1);
            border-color: rgba(255, 107, 107, 0.3);
            z-index: 10;
        }

        .escort-image {
            position: relative;
            height: 360px;
            overflow: hidden;
            background: linear-gradient(135deg, #333, #222);
            flex-shrink: 0;
        }

        .escort-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            filter: brightness(1) saturate(1.1);
            display: block;
        }

        .escort-card:hover .escort-image img {
            transform: scale(1.08);
            filter: brightness(1.1) saturate(1.2);
        }

        .escort-info {
            padding: 1.2rem;
            background: linear-gradient(135deg, rgba(42, 42, 42, 0.95), rgba(30, 30, 30, 0.95));
            backdrop-filter: blur(20px);
            position: relative;
            z-index: 3;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 160px;
        }

        .escort-name {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0 0 0.6rem 0;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.2;
        }

        .escort-location {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 0.6rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .escort-location i {
            color: #ff6b6b;
            font-size: 0.85rem;
            min-width: 14px;
        }

        .escort-description {
            color: #ccc;
            font-size: 0.85rem;
            line-height: 1.4;
            margin: 0.6rem 0;
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
        }

        .escort-phone {
            color: #25D366;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .escort-status {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            margin-top: auto;
        }

        .status-online {
            width: 6px;
            height: 6px;
            background: #00ff88;
            border-radius: 50%;
            animation: pulse-online 2s infinite;
            box-shadow: 0 0 8px #00ff88;
        }

        @keyframes pulse-online {
            0% { 
                box-shadow: 0 0 8px #00ff88;
                transform: scale(1);
            }
            50% { 
                box-shadow: 0 0 15px #00ff88, 0 0 20px #00ff88;
                transform: scale(1.2);
            }
            100% { 
                box-shadow: 0 0 8px #00ff88;
                transform: scale(1);
            }
        }

        .status-text {
            color: #00ff88;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* BADGES */
        .escort-badge {
            position: absolute;
            z-index: 5;
            font-size: 0.7rem;
            font-weight: bold;
            padding: 0.3rem 0.6rem;
            border-radius: 12px;
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        /* Posicionamento hierárquico */
        .escort-badge.super-vip,
        .escort-badge.vip,
        .escort-badge.top {
            top: 10px;
            right: 10px;
        }

        .escort-badge.verificado {
            top: 50px;
            right: 10px;
        }

        .escort-badge.telegram {
            bottom: 50px;
            right: 10px;
        }

        .escort-badge.whatsapp {
            bottom: 10px;
            right: 10px;
        }

        .escort-flag {
            position: absolute;
            top: 10px;
            left: 10px;
            width: 30px;
            height: 20px;
            border-radius: 4px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            z-index: 5;
            pointer-events: none;
        }

        /* Cores e efeitos dos badges */
        .escort-badge.super-vip {
            background: linear-gradient(135deg, #ff0844, #ff6b6b);
            animation: glow-super-vip 2s ease-in-out infinite alternate;
        }

        .escort-badge.vip {
            background: linear-gradient(135deg, #ff6b6b, #feca57);
        }

        .escort-badge.top {
            background: linear-gradient(135deg, #feca57, #ff9ff3);
        }

        .escort-badge.verificado {
            background: linear-gradient(135deg, #48dbfb, #0abde3);
        }

        .escort-badge.telegram {
            background: linear-gradient(135deg, #0088cc, #54a0ff);
        }

        .escort-badge.whatsapp {
            background: linear-gradient(135deg, #25D366, #128C7E);
        }

        .escort-flag img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        @keyframes glow-super-vip {
            0% {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3), 0 0 10px rgba(255, 8, 68, 0.5);
            }
            100% {
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3), 0 0 20px rgba(255, 8, 68, 0.8);
            }
        }

        /* MENSAGEM VAZIA */
        .no-results {
            text-align: center;
            padding: 3rem;
            background: linear-gradient(135deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            margin: 2rem 0;
        }

        .no-results i {
            font-size: 4rem;
            color: #666;
            margin-bottom: 1rem;
        }

        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #ccc;
        }

        .no-results p {
            color: #888;
        }

        /* RESPONSIVIDADE */
        @media (max-width: 1200px) {
            .escorts-grid {
                grid-template-columns: repeat(auto-fit, minmax(240px, 280px));
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .city-title {
                font-size: 2rem;
            }

            .search-bar {
                flex-direction: column;
            }

            .search-input {
                min-width: 100%;
            }

            .filter-buttons {
                justify-content: center;
            }

            .escorts-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.6rem;
            }

            .escort-card {
                height: 480px;
                max-width: none;
            }

            .escort-image {
                height: 320px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .header-content {
                padding: 1rem;
            }

            .nav-links {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .escorts-grid {
                grid-template-columns: 1fr;
            }

            .escort-card {
                max-width: 350px;
                margin: 0 auto;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animações */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                🔥 Citas
            </a>
            <nav class="nav-links">
                <a href="index.php?type=chica" class="nav-link <?= $type === 'chica' ? 'active' : '' ?>">
                    <i class="fas fa-venus"></i> Chicas
                </a>
                <a href="index.php?type=chico" class="nav-link <?= $type === 'chico' ? 'active' : '' ?>">
                    <i class="fas fa-mars"></i> Chicos
                </a>                
                <a href="index.php?type=trans" class="nav-link <?= $type === 'trans' ? 'active' : '' ?>">
                    <i class="fas fa-transgender"></i> Trans
                </a>
            </nav>
        </div>
    </header>

    <div class="container">
        <!-- BREADCRUMB -->
        <nav class="breadcrumb">
            <a href="index.php"><i class="fas fa-home"></i> Inicio</a>
            <i class="fas fa-chevron-right"></i>
            <a href="index.php?type=<?= $type ?>">
                <?= $type === 'trans' ? 'Trans' : 'Chicas' ?>
                <?= $type === 'chicos' ? 'Chicos' : 'Chicos' ?>
            </a>
            <i class="fas fa-chevron-right"></i>
            <span><?= htmlspecialchars($city['title']) ?></span>
        </nav>

        <!-- CABEÇALHO DA CIDADE -->
        <div class="city-header">
            <h1 class="city-title">
                <?= $type === 'trans' ? 'Trans' : 'Chicas' ?> en <?= htmlspecialchars($city['title']) ?>
            </h1>
            <p class="city-subtitle">
                Descubre las mejores acompañantes de <?= htmlspecialchars($city['title']) ?>
            </p>

            <!-- ESTATÍSTICAS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['total'] ?></div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['super_vip_count'] ?></div>
                    <div class="stat-label">Super VIP</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['vip_count'] ?></div>
                    <div class="stat-label">VIP</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['top_count'] ?></div>
                    <div class="stat-label">TOP</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $stats['others_count'] ?></div>
                    <div class="stat-label">Otras</div>
                </div>
            </div>
        </div>

        <!-- CONTROLES -->
        <div class="controls">
            <!-- BUSCA E ORDENAÇÃO -->
            <form method="GET" class="search-bar">
                <input type="hidden" name="slug" value="<?= htmlspecialchars($slug) ?>">
                <input type="hidden" name="type" value="<?= htmlspecialchars($type) ?>">
                
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                       placeholder="Buscar por nombre, descripción o zona..." class="search-input">
                
                <select name="sort" class="sort-select" onchange="this.form.submit()">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Más recientes</option>
                    <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Más antiguos</option>
                    <option value="vip" <?= $sort === 'vip' ? 'selected' : '' ?>>VIP primero</option>
                    <option value="age_asc" <?= $sort === 'age_asc' ? 'selected' : '' ?>>Edad: menor a mayor</option>
                    <option value="age_desc" <?= $sort === 'age_desc' ? 'selected' : '' ?>>Edad: mayor a menor</option>
                    <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Alfabético</option>
                </select>
                
                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </form>

            <!-- FILTROS -->
            <div class="filter-buttons">
                <a href="?slug=<?= $slug ?>&type=<?= $type ?>&sort=<?= $sort ?>&search=<?= urlencode($search) ?>" 
                   class="filter-btn <?= empty($filter) ? 'active' : '' ?>">
                    <i class="fas fa-list"></i> Todas
                </a>
                
                <a href="?slug=<?= $slug ?>&type=<?= $type ?>&filter=super_vip&sort=<?= $sort ?>&search=<?= urlencode($search) ?>" 
                   class="filter-btn super-vip <?= $filter === 'super_vip' ? 'active' : '' ?>">
                    <i class="fas fa-crown"></i> Super VIP (<?= $stats['super_vip_count'] ?>)
                </a>
                
                <a href="?slug=<?= $slug ?>&type=<?= $type ?>&filter=vip&sort=<?= $sort ?>&search=<?= urlencode($search) ?>" 
                   class="filter-btn vip <?= $filter === 'vip' ? 'active' : '' ?>">
                    <i class="fas fa-gem"></i> VIP (<?= $stats['vip_count'] ?>)
                </a>
                
                <a href="?slug=<?= $slug ?>&type=<?= $type ?>&filter=top&sort=<?= $sort ?>&search=<?= urlencode($search) ?>" 
                   class="filter-btn top <?= $filter === 'top' ? 'active' : '' ?>">
                    <i class="fas fa-star"></i> TOP (<?= $stats['top_count'] ?>)
                </a>

                <a href="?slug=<?= $slug ?>&type=<?= $type ?>&filter=others&sort=<?= $sort ?>&search=<?= urlencode($search) ?>" 
                   class="filter-btn <?= $filter === 'others' ? 'active' : '' ?>">
                    <i class="fas fa-heart"></i> Otras (<?= $stats['others_count'] ?>)
                </a>
            </div>
        </div>

        <!-- SEÇÕES DE ESCORTS SEPARADAS -->
        <?php
        // Se há filtro ativo, mostrar apenas a seção filtrada
        if (!empty($filter)) {
            switch($filter) {
                case 'super_vip':
                    renderEscortSection($conn, $city_id, $type, 'super_vip', 'Super VIP', '🔥', $search);
                    break;
                case 'vip':
                    renderEscortSection($conn, $city_id, $type, 'vip', 'VIP', '💎', $search);
                    break;
                case 'top':
                    renderEscortSection($conn, $city_id, $type, 'top', 'TOP', '⭐', $search);
                    break;
                case 'others':
                    renderEscortSection($conn, $city_id, $type, 'others', 'Otras Acompañantes', '💋', $search);
                    break;
            }
        } else {
            // Mostrar todas as seções em ordem hierárquica
            renderEscortSection($conn, $city_id, $type, 'super_vip', 'Super VIP', '🔥', $search);
            renderEscortSection($conn, $city_id, $type, 'vip', 'VIP', '💎', $search);
            renderEscortSection($conn, $city_id, $type, 'top', 'TOP', '⭐', $search);
            renderEscortSection($conn, $city_id, $type, 'others', 'Otras Acompañantes', '💋', $search);
        }

        // Verificar se não há resultados
        $total_query = "SELECT COUNT(*) as total FROM escorts WHERE city_id = ? AND type = ?";
        if (!empty($search)) {
            $total_query .= " AND (name LIKE ? OR description LIKE ? OR location LIKE ?)";
        }
        $total_stmt = $conn->prepare($total_query);
        if (!empty($search)) {
            $search_param = "%$search%";
            $total_stmt->bind_param("issss", $city_id, $type, $search_param, $search_param, $search_param);
        } else {
            $total_stmt->bind_param("is", $city_id, $type);
        }
        $total_stmt->execute();
        $total_result = $total_stmt->get_result()->fetch_assoc();
        
        if ($total_result['total'] == 0):
        ?>
        <!-- MENSAGEM QUANDO NÃO HÁ RESULTADOS -->
        <div class="no-results">
            <i class="fas fa-search"></i>
            <h3>Nenhum resultado encontrado</h3>
            <p>
                <?php if (!empty($search)): ?>
                    Não encontramos <?= $type === 'trans' ? 'trans' : 'chicas' ?> em <?= htmlspecialchars($city['title']) ?> 
                    que correspondam a "<strong><?= htmlspecialchars($search) ?></strong>".
                <?php else: ?>
                    Não há <?= $type === 'trans' ? 'trans' : 'chicas' ?> 
                    disponíveis em <?= htmlspecialchars($city['title']) ?> no momento.
                <?php endif; ?>
            </p>
            <br>
            <a href="?slug=<?= $slug ?>&type=<?= $type ?>" class="filter-btn">
                <i class="fas fa-arrow-left"></i> Ver todas as opções
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Busca em tempo real
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-input');
            let searchTimeout;

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

            // Animações de entrada
            function animateOnScroll() {
                const elements = document.querySelectorAll('.animate-on-scroll');
                
                elements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const elementVisible = 150;
                    
                    if (elementTop < window.innerHeight - elementVisible) {
                        element.classList.add('visible');
                    }
                });
            }

            // Executar animações
            animateOnScroll();
            window.addEventListener('scroll', animateOnScroll);

            // Animações iniciais dos cards
            const cards = document.querySelectorAll('.escort-card, .stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 50);
            });

            // Smooth scroll para resultados após busca
            if (window.location.search.includes('search=') || window.location.search.includes('filter=')) {
                setTimeout(() => {
                    const firstSection = document.querySelector('.escort-section');
                    if (firstSection) {
                        firstSection.scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'start' 
                        });
                    }
                }, 300);
            }
        });

        // Destacar termo de busca nos resultados
        function highlightSearchTerm() {
            const searchTerm = '<?= htmlspecialchars($search) ?>';
            if (!searchTerm) return;

            const elements = document.querySelectorAll('.escort-name, .escort-description');
            elements.forEach(element => {
                const regex = new RegExp(`(${searchTerm})`, 'gi');
                element.innerHTML = element.innerHTML.replace(regex, '<mark style="background: rgba(255, 107, 107, 0.3); padding: 0 0.2rem; border-radius: 3px;">$1</mark>');
            });
        }

        if ('<?= $search ?>') {
            highlightSearchTerm();
        }
    </script>
</body>
</html>