
<?php
$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");

$slug = $_GET['slug'] ?? '';
$type = $_GET['type'] ?? 'chica';

// Buscar o ID da cidade com base no slug
$stmt = $conn->prepare("SELECT id, title FROM cities WHERE slug = ?");
$stmt->bind_param("s", $slug);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Cidade não encontrada.";
    exit;
}

$city = $result->fetch_assoc();
$city_id = $city['id'];

$query = "
  SELECT * FROM escorts
  WHERE city_id = ? AND type = ?
  ORDER BY 
    super_vip DESC,
    vip DESC,
    top DESC,
    verificado DESC,
    created_at DESC
";
$stmt2 = $conn->prepare($query);
$stmt2->bind_param("is", $city_id, $type);
$stmt2->execute();
$escorts = $stmt2->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($city['title']) ?> - Acompanhantes</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="selos.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
  <main class="main">
    <div class="container">
      <h2 class="section-title">Acompanhantes <?= $type === 'trans' ? 'Trans' : 'Chicas' ?> em <?= htmlspecialchars($city['title']) ?></h2>
      <div class="escorts-grid">
        <?php while ($row = $escorts->fetch_assoc()): ?>
          <div class="escort-card">
            <div class="escort-image">
              <img src="<?= $row['image_url'] ?>" alt="<?= htmlspecialchars($row['name']) ?>">
              <?php if (!empty($row['nationality'])): ?>
                <div class="escort-badge nationality"><?= strtoupper($row['nationality']) ?></div>
              <?php endif; ?>
              <?php if (!empty($row['phone'])): ?>
                <div class="escort-badge whatsapp"><i class="fab fa-whatsapp"></i></div>
              <?php endif; ?>
              <?php if (!empty($row['telegram'])): ?>
                <div class="escort-badge telegram"><i class="fab fa-telegram"></i></div>
              <?php endif; ?>
              <?php if (!empty($row['vip'])): ?>
                <div class="escort-badge vip">VIP</div>
              <?php endif; ?>
              <?php if (!empty($row['top'])): ?>
                <div class="escort-badge top">TOP</div>
              <?php endif; ?>
              <?php if (!empty($row['super_vip'])): ?>
                <div class="escort-badge super-vip">SUPER VIP</div>
              <?php endif; ?>
              <?php if (!empty($row['verificado'])): ?>
                <div class="escort-badge verificado">✔ Verificado</div>
              <?php endif; ?>
            </div>
            <div class="escort-info">
              <h3><?= htmlspecialchars($row['name']) ?> <?= $row['age'] ?></h3>
              <div class="location">
                <i class="fas fa-map-marker-alt"></i>
                <span><?= $row['location'] ?> - <?= $row['zone'] ?></span>
              </div>
              <p class="description"><?= mb_strimwidth($row['description'], 0, 100, "...") ?></p>
              <div class="phone"><?= $row['phone'] ?></div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    </div>
  </main>
</body>
</html>
