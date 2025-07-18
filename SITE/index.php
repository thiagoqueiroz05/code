<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CitasNortes - Escorts en España</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="selos.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css?v=10">
  <link rel="stylesheet" href="selos.css?v=3">
</head>
<body>
  <!-- Header -->
  <header class="header">
    <div class="container">
      <div class="logo">
        <span class="logo-icon">🔥</span>
        <span class="logo-text">CitasNortes</span>
      </div>
      <nav class="nav">
        <a href="index.php?type=chica" class="nav-link"><i class="fas fa-user"></i>Chicas</a>
        <a href="index.php?type=trans" class="nav-link"><i class="fas fa-comments"></i>Trans</a>
      </nav>
      <div class="header-actions">
        <div class="search-container">
          <i class="fas fa-search search-icon"></i>
          <input type="text" placeholder="Search" class="search-input" />
        </div>
        <button class="btn btn-outline">Login</button>
        <button class="btn btn-outline">Únete</button>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main class="main">
    <div class="container">
      <!-- Cities Grid -->
      <div class="cities-grid">
        <?php
        $conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");
        $result = $conn->query("SELECT * FROM cities ORDER BY id DESC");

        while ($city = $result->fetch_assoc()):
        ?>
          <a href="cidade.php?slug=<?= $city['slug'] ?>&type=<?= $_GET['type'] ?? 'chica' ?>" class="city-card">
            <div class="city-image">
              <img src="<?= $city['image_url'] ?>" alt="<?= htmlspecialchars($city['title']) ?>">
              <div class="city-overlay"></div>
              <div class="city-indicator"></div>
              <div class="city-icon"><i class="fas fa-chevron-right"></i></div>
              <div class="city-info">
                <h3><?= htmlspecialchars($city['title']) ?></h3>
              </div>
            </div>
          </a>
        <?php endwhile; ?>
      </div>     

<h4>Escorts En España</h4>

      <!-- Latest Escorts Section -->
      <?php include "cards.php"; ?>

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
 AVISO LEGAL=> Los menores de edad tienen terminantamente prohibida la entrada a este sitio web.
                </div>
            </footer>
        </div>
    </main>
</body>
</html>