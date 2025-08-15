<?php include 'auth.php'; ?>
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

  <!-- Main Content -->
  <main class="main">
    <div class="container">
      <!-- Mobile Search -->
      <div class="mobile-search">
        <input type="text" class="mobile-search-input" placeholder="🔍 Buscar ciudades..." id="mobileSearchInput">
      </div>

      <!-- Cities Grid -->
      <div class="cities-grid" id="citiesGrid">
        <?php
        $conn = new mysqli("localhost", "u333528817_escorts", "At081093@", "u333528817_escorts");
        $result = $conn->query("SELECT * FROM cities ORDER BY id DESC");

        while ($city = $result->fetch_assoc()):
        ?>
          <a href="cidade.php?slug=<?= $city['slug'] ?>&type=<?= $_GET['type'] ?? 'chica' ?>" class="city-card" data-city-name="<?= strtolower(htmlspecialchars($city['title'])) ?>">
            <div class="city-image">
              <img src="<?= $city['image_url'] ?>" alt="<?= htmlspecialchars($city['title']) ?>" loading="lazy">
              <div class="city-overlay"></div>
              <div class="city-indicator"></div>
              <div class="city-icon"><i class="fas fa-chevron-right"></i></div>
            </div>
            <div class="city-info">
              <h3><?= htmlspecialchars($city['title']) ?></h3>
            </div>
          </a>
        <?php endwhile; ?>
      </div>

      <!-- Show More Cities Button (Mobile Only) -->
      <div class="show-more-cities" id="showMoreCities">
        <button class="show-more-btn" id="showMoreBtn">
          <i class="fas fa-plus"></i>
          <span id="showMoreText">Ver más ciudades</span>
        </button>
      </div>

      <?php
      // Incluir cards das escorts usando o sistema original
      function renderSection($conn, $filter, $title, $emoji) {
        $_GET['filter'] = $filter;
        ob_start();
        include 'cards.php';
        $cards = trim(ob_get_clean());
        if (!empty($cards)) {
          echo "<section class='animate-on-scroll'><h2>$emoji $title</h2>$cards</section>";
        }
      }

      renderSection($conn, 'super_vip', 'Super VIP', '🔥');
      renderSection($conn, 'vip', 'VIP', '💎');
      renderSection($conn, 'top', 'TOP', '⭐');

      // Outras acompanhantes
      $type = $_GET['type'] ?? 'chica';
      $query = "SELECT * FROM escorts WHERE type = ? AND super_vip = 0 AND vip = 0 AND top = 0 ORDER BY created_at DESC";
      $stmt = $conn->prepare($query);
      $stmt->bind_param("s", $type);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($result->num_rows > 0) {
        echo "<section class='animate-on-scroll'><h2>💋 Outras Acompanhantes</h2>";
        
        // Usar o sistema de cards original
        $_GET['filter'] = 'others';
        ob_start();
        include 'cards.php';
        $cards = trim(ob_get_clean());
        echo $cards;
        
        echo "</section>";
      }
      ?>

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