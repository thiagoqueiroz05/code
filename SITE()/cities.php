<?php

<?php include 'cards.php'; ?>

// Array de cidades (pode vir de banco de dados)
$cities = [
    [
        'title' => 'San Sebastián',
        'image' => 'https://media.emasex.com/394630/Donosti.jpg',
        'slug' => 'san-sebastian'
    ],
    [
        'title' => 'Putas Bilbao',
        'image' => 'https://media.emasex.com/389331/POTADA-BILBAO.jpeg',
        'slug' => 'bilbao'
    ],
    // ... mais cidades
];

// Array de escorts (pode vir de banco de dados)
$escorts = [
    [
        'name' => 'Catalina',
        'age' => 25,
        'image' => 'https://media.emasex.com/404677/conversions/WhatsApp-Image-2025-07-12-at-12.20.33-medium-size.jpg',
        'location' => 'Valencia',
        'zone' => 'Zona Centro',
        'description' => 'Hola soy Catalina una joven dulce y risueña en Valencia...',
        'phone' => '658987765',
        'nationality' => null
    ],
    // ... mais escorts
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMASEX - Escorts en España</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header (mesmo código HTML) -->
    
    <main class="main">
        <div class="container">
            <!-- Cities Grid -->
            <div class="cities-grid">
                <?php foreach($cities as $city): ?>
                <div class="city-card">
                    <div class="city-image">
                        <img src="<?= htmlspecialchars($city['image']) ?>" alt="<?= htmlspecialchars($city['title']) ?>">
                        <div class="city-overlay"></div>
                        <div class="city-indicator"></div>
                        <div class="city-icon">
                            <i class="fas fa-chevron-right"></i>
                        </div>
                        <div class="city-info">
                            <h3><?= htmlspecialchars($city['title']) ?></h3>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Latest Escorts Section -->
            <section class="escorts-section">
                <h2 class="section-title">Últimas escorts en Emasex</h2>
                
                <div class="escorts-grid">
                    <?php foreach($escorts as $escort): ?>
                    <div class="escort-card">
                        <div class="escort-image">
                            <img src="<?= htmlspecialchars($escort['image']) ?>" alt="<?= htmlspecialchars($escort['name']) ?>">
                            <?php if($escort['nationality']): ?>
                            <div class="escort-badge nationality"><?= htmlspecialchars($escort['nationality']) ?></div>
                            <?php endif; ?>
                            <div class="escort-badge whatsapp">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                        </div>
                        <div class="escort-info">
                            <h3><?= htmlspecialchars($escort['name']) ?> <?= $escort['age'] ?></h3>
                            <div class="location">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?= htmlspecialchars($escort['location']) ?><?= $escort['zone'] ? ' - ' . htmlspecialchars($escort['zone']) : '' ?></span>
                            </div>
                            <p class="description"><?= htmlspecialchars($escort['description']) ?></p>
                            <div class="phone"><?= htmlspecialchars($escort['phone']) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- Footer (mesmo código HTML) -->
        </div>
    </main>
</body>
</html>
