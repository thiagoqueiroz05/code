
<?php
$conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");
$type = $_GET['type'] ?? 'chica';

$query = "
  SELECT * FROM escorts
  WHERE type = ?
  ORDER BY 
    super_vip DESC,
    vip DESC,
    top DESC,
    verificado DESC,
    created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $type);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="escorts-grid">
<?php while ($row = $result->fetch_assoc()): ?>
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
