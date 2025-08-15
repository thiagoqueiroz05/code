<?php include 'auth.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Únete a Citas - Acompañantes</title>
    <meta name="description" content="¿Quieres ser una acompañante en Citas? Únete a nuestra plataforma y aumenta tu visibilidad. Contáctanos para más información.">
    
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

        .back-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #aaa;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            background: rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            color: #ff6b6b;
            background: rgba(255, 107, 107, 0.1);
            transform: translateX(-5px);
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            padding: 4rem 2rem;
            margin-bottom: 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(135deg, #ff6b6b, #feca57, #48dbfb);
            z-index: 1;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            position: relative;
            z-index: 2;
        }

        .hero-subtitle {
            font-size: 1.3rem;
            color: #ddd;
            margin-bottom: 2rem;
            line-height: 1.6;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .hero-icon {
            font-size: 5rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Benefits Grid */
        .benefits-section {
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 3rem;
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

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .benefit-card {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .benefit-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
        }

        .benefit-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(255, 107, 107, 0.2);
        }

        .benefit-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .benefit-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: white;
        }

        .benefit-description {
            color: #ccc;
            line-height: 1.6;
        }

        /* Schedule Section */
        .schedule-section {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            padding: 3rem 2rem;
            margin-bottom: 3rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            position: relative;
        }

        .schedule-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #48dbfb, #0abde3);
        }

        .schedule-title {
            font-size: 2rem;
            margin-bottom: 2rem;
            text-align: center;
            background: linear-gradient(135deg, #48dbfb, #0abde3);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            text-align: center;
        }

        .schedule-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(72, 219, 251, 0.2);
        }

        .schedule-day {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 1rem;
            color: #48dbfb;
        }

        .schedule-time {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            margin-bottom: 0.5rem;
        }

        .schedule-note {
            color: #aaa;
            font-size: 0.9rem;
        }

        .schedule-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #00ff88, #26de81);
            color: #1a1a1a;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9rem;
            margin-top: 1rem;
            animation: pulse-status 2s infinite;
        }

        @keyframes pulse-status {
            0% { box-shadow: 0 0 10px rgba(0, 255, 136, 0.3); }
            50% { box-shadow: 0 0 20px rgba(0, 255, 136, 0.6); }
            100% { box-shadow: 0 0 10px rgba(0, 255, 136, 0.3); }
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #1a1a1a;
            border-radius: 50%;
            animation: pulse-dot 1.5s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }

        /* Contact Section */
        .contact-section {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            position: relative;
            margin-bottom: 3rem;
        }

        .contact-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #0088cc, #54a0ff);
        }

        .contact-title {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #0088cc, #54a0ff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .contact-description {
            font-size: 1.2rem;
            color: #ddd;
            margin-bottom: 3rem;
            line-height: 1.6;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .telegram-btn {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem 3rem;
            background: linear-gradient(135deg, #0088cc, #54a0ff);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 1.3rem;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 136, 204, 0.3);
            position: relative;
            overflow: hidden;
        }

        .telegram-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .telegram-btn:hover::before {
            left: 100%;
        }

        .telegram-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 136, 204, 0.5);
        }

        .telegram-icon {
            font-size: 2rem;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-10px); }
            60% { transform: translateY(-5px); }
        }

        /* Requirements Section */
        .requirements-section {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 25px;
            padding: 3rem 2rem;
            margin-bottom: 3rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
        }

        .requirements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .requirement-item:hover {
            background: rgba(255, 255, 255, 0.08);
            transform: translateX(5px);
        }

        .requirement-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b6b, #feca57);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .requirement-text {
            flex: 1;
            font-size: 1.1rem;
            color: #ddd;
        }

        /* FAQ Section */
        .faq-section {
            margin-bottom: 3rem;
        }

        .faq-item {
            background: linear-gradient(145deg, #2a2a2a, #1e1e1e);
            border-radius: 15px;
            margin-bottom: 1rem;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .faq-question {
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .faq-question:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .faq-answer {
            padding: 0 1.5rem 1.5rem;
            color: #ccc;
            line-height: 1.6;
            display: none;
        }

        .faq-answer.active {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .faq-icon {
            transition: transform 0.3s ease;
        }

        .faq-item.active .faq-icon {
            transform: rotate(45deg);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .benefits-grid {
                grid-template-columns: 1fr;
            }

            .schedule-grid {
                grid-template-columns: 1fr;
            }

            .requirements-grid {
                grid-template-columns: 1fr;
            }

            .telegram-btn {
                padding: 1.2rem 2rem;
                font-size: 1.1rem;
            }

            .header-content {
                padding: 1rem;
            }
        }

        @media (max-width: 480px) {
            .hero-section {
                padding: 2rem 1rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .telegram-btn {
                padding: 1rem 1.5rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">🔥 Citas</a>
            
            <a href="javascript:history.back()" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
        </div>
    </header>

    <div class="container">
        <!-- Hero Section -->
        <div class="hero-section">
            <div class="hero-icon">
                <i class="fas fa-star"></i>
            </div>
            <h1 class="hero-title">¡Únete a Citas!</h1>
            <p class="hero-subtitle">
                ¿Eres una acompañante profesional? Únete a la plataforma líder en España 
                y aumenta tu visibilidad, conecta con más clientes y haz crecer tu negocio.
            </p>
        </div>

        <!-- Benefits Section -->
        <div class="benefits-section">
            <h2 class="section-title">¿Por qué elegir Citas?</h2>
            
            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 class="benefit-title">Mayor Visibilidad</h3>
                    <p class="benefit-description">
                        Tu perfil será visto por miles de usuarios diariamente en toda España.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="benefit-title">Perfil Verificado</h3>
                    <p class="benefit-description">
                        Obtén el sello de verificación que aumenta la confianza de los clientes.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h3 class="benefit-title">Opciones Premium</h3>
                    <p class="benefit-description">
                        Accede a posiciones destacadas: TOP, VIP y SUPER VIP.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="benefit-title">Más Clientes</h3>
                    <p class="benefit-description">
                        Conecta con una base de usuarios activos y encuentra más oportunidades.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3 class="benefit-title">Plataforma Móvil</h3>
                    <p class="benefit-description">
                        Gestiona tu perfil desde cualquier dispositivo, en cualquier momento.
                    </p>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3 class="benefit-title">Soporte 24/7</h3>
                    <p class="benefit-description">
                        Nuestro equipo está disponible para ayudarte cuando lo necesites.
                    </p>
                </div>
            </div>
        </div>

        <!-- Schedule Section -->
        <div class="schedule-section">
            <h2 class="schedule-title">
                <i class="fas fa-clock"></i>
                Horario de Atención
            </h2>
            
            <div class="schedule-grid">
                <div class="schedule-card">
                    <div class="schedule-day">Lunes a Viernes</div>
                    <div class="schedule-time">09:00 - 22:00</div>
                    <div class="schedule-note">Horario comercial</div>
                </div>

                <div class="schedule-card">
                    <div class="schedule-day">Sábados</div>
                    <div class="schedule-time">10:00 - 20:00</div>
                    <div class="schedule-note">Atención reducida</div>
                </div>

                <div class="schedule-card">
                    <div class="schedule-day">Domingos</div>
                    <div class="schedule-time">12:00 - 18:00</div>
                    <div class="schedule-note">Emergencias</div>
                </div>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <div class="schedule-status">
                    <div class="status-dot"></div>
                    <span>¡Estamos Online!</span>
                </div>
            </div>
        </div>

        <!-- Requirements Section -->
        <div class="requirements-section">
            <h2 class="section-title">Requisitos</h2>
            
            <div class="requirements-grid">
                <div class="requirement-item">
                    <div class="requirement-icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <div class="requirement-text">Ser mayor de 18 años</div>
                </div>

                <div class="requirement-item">
                    <div class="requirement-icon">
                        <i class="fas fa-camera"></i>
                    </div>
                    <div class="requirement-text">Fotos de calidad profesional</div>
                </div>

                <div class="requirement-item">
                    <div class="requirement-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="requirement-text">Perfil completo y actualizado</div>
                </div>

                <div class="requirement-item">
                    <div class="requirement-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="requirement-text">Número de teléfono válido</div>
                </div>

                <div class="requirement-item">
                    <div class="requirement-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="requirement-text">Actitud profesional</div>
                </div>

                <div class="requirement-item">
                    <div class="requirement-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="requirement-text">Ubicación en España</div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <h2 class="section-title">Preguntas Frecuentes</h2>
            
            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>¿Cuánto cuesta registrarse?</span>
                    <i class="fas fa-plus faq-icon"></i>
                </div>
                <div class="faq-answer">
                    El registro básico es completamente gratuito. Ofrecemos opciones premium como TOP, VIP y SUPER VIP con costos adicionales para mayor visibilidad.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>¿Cómo funciona la verificación?</span>
                    <i class="fas fa-plus faq-icon"></i>
                </div>
                <div class="faq-answer">
                    El proceso de verificación incluye validación de identidad, fotos y información de contacto. Una vez aprobado, obtienes el sello de verificación.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>¿Puedo gestionar mi perfil yo misma?</span>
                    <i class="fas fa-plus faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Sí, tendrás acceso completo a tu panel de control donde podrás actualizar fotos, información, horarios y gestionar tu perfil en tiempo real.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question" onclick="toggleFAQ(this)">
                    <span>¿Qué tipos de promoción ofrecen?</span>
                    <i class="fas fa-plus faq-icon"></i>
                </div>
                <div class="faq-answer">
                    Ofrecemos diferentes niveles: perfil estándar (gratuito), TOP (destacado), VIP (posición privilegiada) y SUPER VIP (máxima visibilidad).
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="contact-section">
            <h2 class="contact-title">¡Contáctanos Ahora!</h2>
            <p class="contact-description">
                ¿Lista para dar el siguiente paso? Contáctanos por Telegram y uno de nuestros 
                representantes te ayudará con el proceso de registro.
            </p>
            
            <a href="https://t.me/CitasSupport" target="_blank" class="telegram-btn">
                <i class="fab fa-telegram telegram-icon"></i>
                <span>Entrar en Contacto</span>
            </a>
            
            <div style="margin-top: 2rem; color: #aaa; font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i>
                Respuesta garantizada en menos de 2 horas
            </div>
        </div>
    </div>

    <script>
        // FAQ Toggle Function
        function toggleFAQ(element) {
            const faqItem = element.parentNode;
            const answer = faqItem.querySelector('.faq-answer');
            const isActive = faqItem.classList.contains('active');
            
            // Close all other FAQs
            document.querySelectorAll('.faq-item').forEach(item => {
                item.classList.remove('active');
                item.querySelector('.faq-answer').classList.remove('active');
            });
            
            // Toggle current FAQ
            if (!isActive) {
                faqItem.classList.add('active');
                answer.classList.add('active');
            }
        }

        // Animate cards on scroll
        function animateOnScroll() {
            const elements = document.querySelectorAll('.benefit-card, .requirement-item, .schedule-card');
            
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const elementVisible = 150;
                
                if (elementTop < window.innerHeight - elementVisible) {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }
            });
        }

        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial state for animated elements
            const elements = document.querySelectorAll('.benefit-card, .requirement-item, .schedule-card');
            elements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(30px)';
                element.style.transition = 'all 0.6s ease';
                element.style.transitionDelay = `${index * 0.1}s`;
            });

            // Run animation
            setTimeout(animateOnScroll, 100);
            window.addEventListener('scroll', animateOnScroll);
        });

        // Update status based on current time
        function updateStatus() {
            const now = new Date();
            const hour = now.getHours();
            const day = now.getDay(); // 0 = Sunday, 1 = Monday, etc.
            const statusElement = document.querySelector('.schedule-status');
            const statusDot = document.querySelector('.status-dot');
            
            let isOnline = false;
            
            // Check if online based on schedule
            if (day >= 1 && day <= 5) { // Monday to Friday
                isOnline = hour >= 9 && hour < 22;
            } else if (day === 6) { // Saturday
                isOnline = hour >= 10 && hour < 20;
            } else { // Sunday
                isOnline = hour >= 12 && hour < 18;
            }
            
            if (isOnline) {
                statusElement.innerHTML = '<div class="status-dot"></div><span>¡Estamos Online!</span>';
                statusElement.style.background = 'linear-gradient(135deg, #00ff88, #26de81)';
            } else {
                statusElement.innerHTML = '<div class="status-dot offline"></div><span>Fuera de Horario</span>';
                statusElement.style.background = 'linear-gradient(135deg, #ff6b6b, #ff4757)';
            }
        }

        // Update status every minute
        setInterval(updateStatus, 60000);
        updateStatus(); // Initial update
    </script>

    <!-- Additional CSS for offline status -->
    <style>
        .status-dot.offline {
            background: #1a1a1a !important;
        }
        
        .schedule-status[style*="ff6b6b"] {
            animation: pulse-offline 2s infinite !important;
        }
        
        @keyframes pulse-offline {
            0% { box-shadow: 0 0 10px rgba(255, 107, 107, 0.3); }
            50% { box-shadow: 0 0 20px rgba(255, 107, 107, 0.6); }
            100% { box-shadow: 0 0 10px rgba(255, 107, 107, 0.3); }
        }
    </style>
</body>
</html>