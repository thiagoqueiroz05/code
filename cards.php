<?php
$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");

$type = $_GET['type'] ?? 'chica';
$filter = $_GET['filter'] ?? null;

$where = "type = ?";
$params = [$type];
$types = "s";

if ($filter === "super_vip") {
    $where .= " AND super_vip = 1";
} elseif ($filter === "vip") {
    $where .= " AND vip = 1 AND super_vip = 0";
} elseif ($filter === "top") {
    $where .= " AND top = 1 AND vip = 0 AND super_vip = 0";
} elseif ($filter === "others") {
    $where .= " AND super_vip = 0 AND vip = 0 AND top = 0";
}

$query = "SELECT * FROM escorts WHERE $where ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) return;
?>

<div class="escorts-grid">
<?php while ($row = $result->fetch_assoc()): 
    // Definir valores padrão se estiverem vazios
    $age = (!empty($row['age']) && $row['age'] > 0) ? $row['age'] : rand(22, 28);
    $location = !empty($row['location']) ? $row['location'] : 'Disponível';
    $zone = !empty($row['zone']) ? $row['zone'] : '';
    $description = !empty($row['description']) ? $row['description'] : 'Linda acompanhante disponível para encontros discretos.';
    $phone = !empty($row['phone']) ? $row['phone'] : '';
    
    // NOVA LÓGICA: Determinar classe do card baseado no status
    $cardClass = '';
    if (!empty($row['super_vip'])) {
        $cardClass = 'super-vip';
    } elseif (!empty($row['vip'])) {
        $cardClass = 'vip';
    } elseif (!empty($row['top'])) {
        $cardClass = 'top';
    } else {
        $cardClass = 'outras';
    }
?>
  <a href="perfil.php?id=<?= $row['id'] ?>" class="escort-card-link">
    <div class="escort-card <?= $cardClass ?>">
      <div class="escort-image">
        <!-- Imagem principal -->
        <img src="<?= htmlspecialchars($row['image_url']) ?>" 
             alt="<?= htmlspecialchars($row['name']) ?>"
             loading="lazy"
             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgdmlld0JveD0iMCAwIDMwMCAzMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIzMDAiIGhlaWdodD0iMzAwIiBmaWxsPSIjMzMzIi8+Cjx0ZXh0IHg9IjE1MCIgeT0iMTUwIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBmaWxsPSIjNjY2IiBmb250LXNpemU9IjE2Ij5JbWFnZW0gbsOjbyBlbmNvbnRyYWRhPC90ZXh0Pgo8L3N2Zz4K'">

        <!-- Bandeira da nacionalidade -->
        <?php if (!empty($row['nationality'])): ?>
        <div class="escort-flag">
          <img src="https://flagcdn.com/w40/<?= strtolower(htmlspecialchars($row['nationality'])) ?>.png" 
               alt="<?= htmlspecialchars($row['nationality']) ?>"
               loading="lazy"
               onerror="this.style.display='none'">
        </div>
        <?php endif; ?>

        <!-- Badges de classificação - SEMPRE EXIBIR APENAS UM EM HIERARQUIA -->
        <?php if (!empty($row['super_vip'])): ?>
          <div class="escort-badge super-vip">SUPER VIP</div>
        <?php elseif (!empty($row['vip'])): ?>
          <div class="escort-badge vip">VIP</div>
        <?php elseif (!empty($row['top'])): ?>
          <div class="escort-badge top">TOP</div>
        <?php endif; ?>

        <!-- Badge de verificação - SEMPRE SEPARADO -->
        <?php if (!empty($row['verificado'])): ?>
          <div class="escort-badge verificado"><i class="fas fa-check-circle"></i></div>
        <?php endif; ?>

        <!-- Badges de contato - SEMPRE SEPARADOS -->
        <?php if (!empty($row['telegram'])): ?>
          <div class="escort-badge telegram"><i class="fab fa-telegram"></i></div>
        <?php endif; ?>

        <?php if (!empty($row['phone'])): ?>
          <div class="escort-badge whatsapp"><i class="fab fa-whatsapp"></i></div>
        <?php endif; ?>
      </div>

      <div class="escort-info">
        <!-- NOME E IDADE NA MESMA LINHA -->
        <h3 class="escort-name"><?= htmlspecialchars($row['name']) ?> - <?= $age ?> años</h3>
        
        <!-- LOCALIZAÇÃO COMPLETA -->
        <div class="escort-location">
          <i class="fas fa-map-marker-alt"></i>
          <span><?= htmlspecialchars($location) ?></span>
        </div>
        
        <!-- ZONA SEPARADA -->
        <?php if ($zone): ?>
        <div class="escort-zone"><?= htmlspecialchars($zone) ?></div>
        <?php endif; ?>
        
        <!-- TELEFONE -->
        <?php if ($phone): ?>
        <div class="escort-phone">
          <i class="fab fa-whatsapp"></i>
          <span><?= htmlspecialchars($phone) ?></span>
        </div>
        <?php endif; ?>

        <!-- STATUS ONLINE -->
        <div class="escort-status">
          <div class="status-online"></div>
          <span class="status-text">Online</span>
        </div>
      </div>
    </div>
  </a>
<?php endwhile; ?>
</div>

<style>
/* ===============================================
   SEU CSS ORIGINAL + BORDAS COLORIDAS ADICIONADAS
   =============================================== */

/* Grid principal - cards retangulares verticais */
.escorts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 300px));
    gap: 1rem;
    padding: 1rem 0;
    max-width: 1400px;
    margin: 0 auto;
    justify-content: center;
}

/* Link wrapper - garantindo que todo o card seja clicável */
.escort-card-link {
    text-decoration: none;
    color: inherit;
    display: block;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

/* Card principal - formato retangular vertical */
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

/* ===== NOVAS BORDAS COLORIDAS POR STATUS ===== */

/* Super VIP - Borda vermelha brilhante com animação */
.escort-card.super-vip {
    border: 4px solid transparent;
    background: linear-gradient(145deg, #2a2a2a, #1e1e1e) padding-box,
               linear-gradient(135deg, #ff0844, #ff6b6b, #ff0844) border-box;
    box-shadow: 
        0 10px 30px rgba(255, 8, 68, 0.4),
        0 0 40px rgba(255, 8, 68, 0.2),
        inset 0 0 20px rgba(255, 8, 68, 0.1);
    animation: glow-super-vip 3s ease-in-out infinite alternate;
}

/* VIP - Borda laranja/amarela */
.escort-card.vip {
    border: 4px solid transparent;
    background: linear-gradient(145deg, #2a2a2a, #1e1e1e) padding-box,
               linear-gradient(135deg, #ff6b6b, #feca57, #ff6b6b) border-box;
    box-shadow: 
        0 10px 30px rgba(255, 107, 107, 0.3),
        0 0 30px rgba(254, 202, 87, 0.2);
}

/* TOP - Borda amarela/rosa */
.escort-card.top {
    border: 4px solid transparent;
    background: linear-gradient(145deg, #2a2a2a, #1e1e1e) padding-box,
               linear-gradient(135deg, #feca57, #ff9ff3, #feca57) border-box;
    box-shadow: 
        0 10px 30px rgba(254, 202, 87, 0.3),
        0 0 25px rgba(255, 159, 243, 0.2);
}

/* Outras - Borda azul sutil */
.escort-card.outras {
    border: 3px solid rgba(72, 219, 251, 0.3);
    box-shadow: 
        0 10px 30px rgba(0, 0, 0, 0.4),
        0 0 20px rgba(72, 219, 251, 0.1);
}

/* Efeitos de hover atualizados */
.escort-card.super-vip:hover {
    transform: translateY(-15px) scale(1.03);
    box-shadow: 
        0 25px 60px rgba(255, 8, 68, 0.6),
        0 15px 40px rgba(0, 0, 0, 0.3),
        0 0 60px rgba(255, 8, 68, 0.4);
    z-index: 10;
}

.escort-card.vip:hover {
    transform: translateY(-12px) scale(1.02);
    box-shadow: 
        0 25px 60px rgba(255, 107, 107, 0.4),
        0 15px 40px rgba(0, 0, 0, 0.3),
        0 0 50px rgba(254, 202, 87, 0.3);
    z-index: 10;
}

.escort-card.top:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 
        0 25px 60px rgba(254, 202, 87, 0.4),
        0 15px 40px rgba(0, 0, 0, 0.3),
        0 0 40px rgba(255, 159, 243, 0.3);
    z-index: 10;
}

.escort-card.outras:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow: 
        0 25px 60px rgba(72, 219, 251, 0.2),
        0 15px 40px rgba(0, 0, 0, 0.3);
    z-index: 10;
}

/* Animação para Super VIP */
@keyframes glow-super-vip {
    0% {
        box-shadow: 
            0 10px 30px rgba(255, 8, 68, 0.4),
            0 0 40px rgba(255, 8, 68, 0.2),
            inset 0 0 20px rgba(255, 8, 68, 0.1);
    }
    100% {
        box-shadow: 
            0 15px 40px rgba(255, 8, 68, 0.6),
            0 0 60px rgba(255, 8, 68, 0.4),
            inset 0 0 30px rgba(255, 8, 68, 0.2);
    }
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

.escort-phone {
    color: #25D366;
    font-size: 0.85rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.escort-phone i {
    font-size: 1rem;
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

/* BADGES - CORREÇÃO PRINCIPAL PARA CLIQUES */
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
    pointer-events: none; /* IMPEDE INTERCEPTAÇÃO DE CLIQUES */
}

/* Posicionamento dos badges */
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
    pointer-events: none; /* IMPEDE INTERCEPTAÇÃO DE CLIQUES */
}

.escort-flag img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Cores dos badges */
.super-vip {
    background: linear-gradient(135deg, #ff0844, #ff6b6b);
    animation: glow-super-vip 2s ease-in-out infinite alternate;
}

.vip {
    background: linear-gradient(135deg, #ff6b6b, #feca57);
}

.top {
    background: linear-gradient(135deg, #feca57, #ff9ff3);
}

.verificado {
    background: linear-gradient(135deg, #48dbfb, #0abde3);
}

.telegram {
    background: linear-gradient(135deg, #0088cc, #54a0ff);
}

.whatsapp {
    background: linear-gradient(135deg, #25D366, #128C7E);
}

/* RESPONSIVIDADE - Seu código original */
@media (max-width: 1200px) {
    .escorts-grid {
        grid-template-columns: repeat(auto-fit, minmax(240px, 280px));
        gap: 0.8rem;
    }
    
    .escort-card {
        height: 500px;
    }
}

@media (max-width: 768px) {
    .escorts-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.6rem;
        padding: 0.5rem;
    }
    
    .escort-card {
        height: 460px;
        max-width: none;
    }
    
    .escort-image {
        height: 300px;
    }
    
    .escort-info {
        padding: 0.8rem;
        min-height: 140px;
    }
    
    .escort-name {
        font-size: 1rem;
    }
    
    .escort-location {
        font-size: 0.8rem;
        margin-bottom: 0.3rem;
    }
    
    .escort-zone {
        font-size: 0.75rem;
        margin-bottom: 0.4rem;
    }
    
    .escort-phone {
        font-size: 0.8rem;
        margin-bottom: 0.4rem;
    }
    
    .escort-badge {
        font-size: 0.6rem;
        padding: 0.25rem 0.5rem;
    }
    
    .escort-badge.telegram {
        bottom: 45px;
        right: 8px;
    }

    .escort-badge.whatsapp {
        bottom: 8px;
        right: 8px;
    }
    
    .escort-badge.verificado {
        top: 45px;
        right: 8px;
    }
}

@media (max-width: 480px) {
    .escorts-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
        padding: 0.5rem;
    }
    
    .escort-card {
        height: 380px;
        max-width: none;
        margin: 0;
    }
    
    .escort-image {
        height: 250px;
    }
    
    .escort-info {
        padding: 0.6rem;
        min-height: 110px;
    }
    
    .escort-name {
        font-size: 0.9rem;
        margin-bottom: 0.3rem;
    }
    
    .escort-location {
        font-size: 0.75rem;
        margin-bottom: 0.2rem;
    }
    
    .escort-zone {
        font-size: 0.7rem;
        margin-bottom: 0.3rem;
    }
    
    .escort-phone {
        font-size: 0.75rem;
        margin-bottom: 0.3rem;
    }
    
    .escort-status {
        gap: 0.3rem;
    }
    
    .status-text {
        font-size: 0.65rem;
    }
    
    .escort-badge {
        font-size: 0.55rem;
        padding: 0.2rem 0.4rem;
    }
    
    .escort-flag {
        width: 20px;
        height: 14px;
        top: 8px;
        left: 8px;
    }
}

/* CORREÇÃO FINAL PARA GARANTIR CLIQUES */
.escort-card * {
    pointer-events: none;
}

.escort-card-link {
    pointer-events: auto;
}
</style>