<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php'; 

// On récupère l'utilisateur pour que la Navbar ne plante pas
$user = isLoggedIn() ? currentUser() : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Plateforme de Prestations d'Excellence</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0f172a;
            --accent: #4f46e5;
            --accent-hover: #4338ca;
            --text-muted: #64748b;
            --glass: rgba(255, 255, 255, 0.8);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--primary);
            overflow-x: hidden;
            background-color: #ffffff;
        }

        /* Navbar Custom */
        .navbar {
            backdrop-filter: blur(10px);
            background: var(--glass);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        /* Hero Section */
        .hero {
            padding: 160px 0 100px;
            background: radial-gradient(circle at top right, #eef2ff 0%, #ffffff 50%);
        }
        .hero-badge {
            background: #e0e7ff;
            color: var(--accent);
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 1.5rem;
        }
        .hero h1 { 
            font-weight: 800; 
            font-size: 4rem; 
            line-height: 1.1;
            letter-spacing: -0.02em;
        }
        .hero-content { text-align: left; }
        .hero-social-proof { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }

        .hero-img {
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
        }

        /* Stats Section */
        .stats-bar {
            background: var(--primary);
            color: white;
            padding: 40px 0;
            margin-top: -50px;
            border-radius: 20px;
            position: relative;
            z-index: 10;
        }
        .stats-bar .col-md-4 { border-left: none !important; border-right: none !important; }

        /* Features */
        .section-title { font-weight: 700; font-size: 2.5rem; }
        .feature-card {
            border: 1px solid #f1f5f9;
            padding: 2.5rem;
            border-radius: 20px;
            background: #fff;
            transition: all 0.3s ease;
            height: 100%;
        }
        .feature-card:hover {
            border-color: var(--accent);
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        }
        .icon-box {
            width: 60px; height: 60px;
            background: var(--accent);
            color: white;
            display: flex; align-items: center; justify-content: center;
            border-radius: 15px; margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(rgba(79, 70, 229, 0.9), rgba(79, 70, 229, 0.9)), 
                        url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&q=80');
            background-size: cover;
            background-position: center;
            border-radius: 30px;
            padding: 80px 40px;
            color: white;
        }

        /* CHATBOT STYLES */
        .chatbot-launcher {
            position: fixed; bottom: 30px; right: 30px;
            width: 65px; height: 65px;
            background: var(--primary);
            color: white; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.8rem; cursor: pointer; z-index: 9999;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.3);
        }
        .chatbot-launcher:hover { transform: scale(1.1); background: var(--accent); }

        .chatbot-container {
            position: fixed; bottom: 110px; right: 30px;
            width: 380px; height: 550px;
            background: white; border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            display: flex; flex-direction: column;
            z-index: 9999; overflow: hidden;
            border: 1px solid rgba(0,0,0,0.05);
            display: none;
        }

        .chatbot-header {
            background: var(--primary); color: white;
            padding: 1.5rem; display: flex;
            justify-content: space-between; align-items: center;
        }

        .chatbot-messages {
            flex-grow: 1; padding: 1.5rem;
            overflow-y: auto; background: #f8fafc;
            display: flex; flex-direction: column; gap: 1rem;
        }

        .chat-msg {
            padding: 0.8rem 1rem; border-radius: 15px;
            font-size: 0.9rem; max-width: 80%;
        }
        .chat-msg.bot { background: white; color: var(--primary); align-self: flex-start; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .chat-msg.user { background: var(--accent); color: white; align-self: flex-end; }

        .chatbot-input-area {
            padding: 1rem; border-top: 1px solid #eee;
            display: flex; gap: 0.5rem; background: white;
        }
        .chatbot-input-area input {
            flex-grow: 1; border: 1px solid #e2e8f0;
            border-radius: 12px; padding: 0.6rem 1rem; outline: none;
        }
        .chatbot-input-area button {
            background: var(--accent); border: none;
            color: white; border-radius: 10px; width: 45px;
        }

        footer { background: #f8fafc; padding: 60px 0 30px; }

        /* ================================================
           RESPONSIVE BREAKPOINTS
           xs: < 480px | sm: 480-767px | md: 768-991px | lg: 992px+
        ================================================ */

        @media (max-width: 767px) {
            /* Hero: column layout */
            .hero {
                padding: 120px 0 60px !important;
                text-align: center;
            }
            .hero h1 { font-size: 2.2rem !important; }
            .hero-badge { display: block; margin: 0 auto 1.5rem; }
            .hero-content { text-align: center !important; }

            /* Hide hero image on mobile, show only on lg */
            .hero .col-lg-6:last-child { display: none; }

            /* CTA buttons → column */
            .hero .d-flex.gap-3 {
                flex-direction: column !important;
                align-items: center;
            }
            .hero .d-flex.gap-3 .btn {
                width: 100% !important;
                max-width: 340px;
                text-align: center;
            }

            /* Social proof avatars centered */
            .hero .mt-4.d-flex {
                justify-content: center !important;
            }

            /* Stats bar: single column on mobile, no side borders */
            .stats-bar {
                border-radius: 12px;
                padding: 1.5rem !important;
                margin-top: -30px;
            }
            .stats-bar .col-md-4 {
                border-left: none !important;
                border-right: none !important;
            }
            .stats-bar .border-start,
            .stats-bar .border-end {
                border: none !important;
            }

            /* Feature cards: 1 column mobile */
            .feature-card { padding: 1.5rem; }

            /* Typography */
            .section-title { font-size: 1.8rem !important; }

            /* Spacing */
            .py-100 { padding-top: 3rem !important; padding-bottom: 3rem !important; }
            section { padding: 2.5rem 0 !important; }

            /* CTA Section */
            .cta-section {
                padding: 40px 20px !important;
                border-radius: 20px;
            }
            .cta-section .d-flex {
                flex-direction: column !important;
                align-items: center !important;
            }
            .cta-section .btn {
                width: 100% !important;
                max-width: 320px;
            }

            /* Chatbot */
            #botWindow {
                width: calc(100vw - 40px) !important;
                right: 10px !important;
                left: 20px;
            }

            /* Global btn-lg */
            .btn-lg { width: 100% !important; text-align: center; }

            /* Container padding */
            .container { padding-left: 1rem !important; padding-right: 1rem !important; }

            /* Footer */
            footer { padding: 40px 0 20px; }
        }

        @media (max-width: 480px) {
            body { font-size: 0.9rem; }
            .hero { padding: 100px 0 50px !important; }
            .hero h1 { font-size: 1.9rem !important; }
            .stats-bar { border-radius: 12px; padding: 1.25rem !important; }
            .stats-bar h2 { font-size: 1.5rem; }
            #botBtn { width: 50px !important; height: 50px !important; bottom: 15px !important; right: 15px !important; }
        }

        /* Tablet: feature cards 2 columns (Bootstrap col-md-6 handles this) */
        @media (min-width: 768px) and (max-width: 991px) {
            .hero h1 { font-size: 2.8rem; }
            .section-title { font-size: 2rem; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="#">
            <i class="bi bi-lightning-charge-fill text-primary"></i> <?= APP_NAME ?>
        </a>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>/pages/login.php" class="btn btn-link text-decoration-none text-dark fw-semibold">Connexion</a>
            <a href="<?= APP_URL ?>/pages/register.php" class="btn btn-primary px-4 rounded-pill">Démarrer gratuitement</a>
        </div>
    </div>
</nav>

<header class="hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <span class="hero-badge">✨ N°1 de la prestation de service</span>
                <h1>Réalisez vos projets avec les <span class="text-primary text-decoration-underline">meilleurs</span> experts.</h1>
                <p class="lead text-muted mt-4 mb-5">
                    Que vous soyez une entreprise à la recherche de talents ou un prestataire prêt à briller, nous créons la connexion parfaite.
                </p>
                <div class="d-flex gap-3">
    <a href="#explore" class="btn btn-primary btn-lg px-5 py-3 rounded-pill">Explorer les services</a>
    <a href="<?= APP_URL ?>/pages/comment-ca-marche.php" class="btn btn-outline-dark btn-lg px-4 py-3 rounded-pill">Comment ça marche ?</a>
</div>
                <div class="mt-4 d-flex align-items-center gap-2">
                    <div class="d-flex -space-x-2">
                        <img src="https://i.pravatar.cc/40?img=11" class="border border-white" style="width:40px; height:40px; border-radius:50%" alt="User">
                        <img src="https://i.pravatar.cc/40?img=12" class="border border-white" style="width:40px; height:40px; border-radius:50%" alt="User">
                        <img src="https://i.pravatar.cc/40?img=13" class="border border-white" style="width:40px; height:40px; border-radius:50%" alt="User">
                    </div>
                    <span class="text-sm text-muted"><strong>+2,000</strong> membres nous font confiance</span>
                </div>
            </div>
            <div class="col-lg-6 d-none d-lg-block" data-aos="zoom-in">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=80" class="hero-img" alt="Collaboration sur Nexora">
            </div>
        </div>
    </div>
</header>

<div class="container" data-aos="fade-up">
    <div class="stats-bar px-5">
        <div class="row text-center g-4">
            <div class="col-4 col-md-4">
                <h2 class="fw-bold mb-0">98%</h2>
                <p class="text-white-50 mb-0 small">Satisfaction client</p>
            </div>
            <div class="col-4 col-md-4 border-start border-end border-secondary">
                <h2 class="fw-bold mb-0">15k+</h2>
                <p class="text-white-50 mb-0 small">Prestations réalisées</p>
            </div>
            <div class="col-4 col-md-4">
                <h2 class="fw-bold mb-0">24/7</h2>
                <p class="text-white-50 mb-0 small">Support réactif</p>
            </div>
        </div>
    </div>
</div>

<section id="explore" class="py-100 mt-5">
    <div class="container py-5">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="section-title">Pourquoi choisir <?= APP_NAME ?> ?</h2>
            <p class="text-muted mx-auto" style="max-width: 600px;">Une plateforme pensée pour la fluidité, la sécurité et la croissance de votre activité.</p>
        </div>
        <div class="row g-4">
            <div class="col-12 col-sm-6 col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-card">
                    <div class="icon-box"><i class="bi bi-shield-check"></i></div>
                    <h4>Transactions Sécurisées</h4>
                    <p class="text-muted">Vos fonds sont protégés par un système d'escrow. Le paiement n'est débloqué qu'après validation du service.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-card">
                    <div class="icon-box" style="background: #10b981;"><i class="bi bi-stars"></i></div>
                    <h4>Talents Vérifiés</h4>
                    <p class="text-muted">Chaque prestataire passe par un processus de vérification rigoureux pour garantir un niveau de qualité optimal.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-card">
                    <div class="icon-box" style="background: #f59e0b;"><i class="bi bi-graph-up-arrow"></i></div>
                    <h4>Suivi en Temps Réel</h4>
                    <p class="text-muted">Gérez vos projets via un tableau de bord intuitif : chat intégré, suivi d'étapes et notifications instantanées.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6" data-aos="fade-right">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&q=80" class="img-fluid rounded-4 shadow" alt="Gestion de projet">
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <h2 class="fw-bold mb-4">Gérez tout depuis un seul endroit</h2>
                <ul class="list-unstyled">
                    <li class="mb-3 d-flex gap-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        <span><strong>Messagerie instantanée :</strong> Communiquez directement avec vos clients ou prestataires.</span>
                    </li>
                    <li class="mb-3 d-flex gap-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        <span><strong>Gestion des contrats :</strong> Des devis clairs et des factures générées automatiquement.</span>
                    </li>
                    <li class="mb-3 d-flex gap-3">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        <span><strong>Système d'avis :</strong> Fiez-vous aux retours d'expérience de la communauté.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>

<div class="container my-5">
    <section class="cta-section text-center" data-aos="zoom-in">
        <h2 class="display-5 fw-bold mb-4">Prêt à transformer vos idées en réalité ?</h2>
        <p class="lead mb-5 opacity-75">Rejoignez des milliers de professionnels et commencez dès aujourd'hui.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="<?= APP_URL ?>/pages/register.php" class="btn btn-light btn-lg px-5 py-3 rounded-pill fw-bold text-primary">Créer mon compte</a>
        </div>
    </section>
</div>

<div id="botBtn" style="position:fixed; bottom:20px; right:20px; width:60px; height:60px; background:#4f46e5; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; cursor:pointer; box-shadow:0 10px 20px rgba(0,0,0,0.2); z-index:9999;">
    <i class="bi bi-robot fs-3"></i>
</div>

<div id="botWindow" style="position:fixed; bottom:90px; right:20px; width:350px; height:480px; background:white; border-radius:20px; box-shadow:0 15px 40px rgba(0,0,0,0.2); display:none; flex-direction:column; overflow:hidden; z-index:9999; border:1px solid #eee;">
    
    <div style="background:#0f172a; color:white; padding:15px; font-weight:bold; display:flex; justify-content:space-between;">
        <span><i class="bi bi-stars"></i> Nexora AI</span>
        <span onclick="toggleChat()" style="cursor:pointer;">&times;</span>
    </div>

    <div id="chatBody" style="flex:1; padding:15px; overflow-y:auto; background:#f8fafc; display:flex; flex-direction:column; gap:10px;">
        <div style="background:white; padding:10px; border-radius:12px; max-width:85%; font-size:0.9rem; border:1px solid #e2e8f0;">
            Bonjour ! Je suis l'IA de Nexora. En quoi puis-je vous aider ?
        </div>
    </div>

    <div style="padding:10px; background:white; border-top:1px solid #eee; display:flex; gap:5px;">
        <input type="text" id="botInput" class="form-control" placeholder="Posez votre question..." style="border-radius:10px;">
        <button id="sendMsgBtn" onclick="sendMsg()" style="border-radius:10px; background:#4f46e5; border:none; color:white; width:45px;">
            <i class="bi bi-send"></i>
        </button>
    </div>
</div>

<script>
const botWin = document.getElementById('botWindow');
const chatBody = document.getElementById('chatBody');
const botInput = document.getElementById('botInput');
const botBtn = document.getElementById('botBtn');

function toggleChat() {
    if(botWin) botWin.style.display = (botWin.style.display === 'none' || botWin.style.display === '') ? 'flex' : 'none';
}

if(botBtn) botBtn.onclick = toggleChat;

function sendMsg() {
    const text = botInput.value.trim();
    if(!text) return;

    // 1. Afficher message utilisateur
    chatBody.innerHTML += `<div style="align-self:flex-end; background:#4f46e5; color:white; padding:10px; border-radius:12px; max-width:85%; font-size:0.9rem;">${text}</div>`;
    botInput.value = '';
    chatBody.scrollTop = chatBody.scrollHeight;

    // 2. Envoyer au PHP
    fetch('../actions/bot_engine.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({message: text})
    })
    .then(res => res.json())
    .then(data => {
        chatBody.innerHTML += `<div style="background:white; padding:10px; border-radius:12px; max-width:85%; font-size:0.9rem; border:1px solid #e2e8f0;">${data.reply}</div>`;
        chatBody.scrollTop = chatBody.scrollHeight;
    });
}

// Support de la touche Entrée
if(botInput) botInput.onkeypress = (e) => { if(e.key === 'Enter') sendMsg(); };
</script>

<footer class="text-center text-lg-start">
    <div class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="fw-bold mb-4"><i class="bi bi-lightning-charge-fill text-primary"></i> <?= APP_NAME ?></h5>
                <p class="text-muted">La plateforme de référence pour connecter les experts et les porteurs de projets ambitieux.</p>
            </div>
            <div class="col-lg-2">
                <h6 class="fw-bold mb-4">Plateforme</h6>
                <ul class="list-unstyled text-muted">
                    <li><a href="#" class="text-decoration-none text-muted">Services</a></li>
                    <li><a href="#" class="text-decoration-none text-muted">Prestataires</a></li>
                    <li><a href="#" class="text-decoration-none text-muted">Tarifs</a></li>
                </ul>
            </div>
            <div class="col-lg-2">
                <h6 class="fw-bold mb-4">Support</h6>
                <ul class="list-unstyled text-muted">
                    <li><a href="#" class="text-decoration-none text-muted">Aide / FAQ</a></li>
                    <li><a href="#" class="text-decoration-none text-muted">Contact</a></li>
                    <li><a href="#" class="text-decoration-none text-muted">Sécurité</a></li>
                </ul>
            </div>
            <div class="col-lg-4">
                <h6 class="fw-bold mb-4">Newsletter</h6>
                <div class="input-group mb-3">
                    <input type="email" class="form-control border-0 bg-light" placeholder="votre@email.com">
                    <button class="btn btn-primary" type="button">OK</button>
                </div>
            </div>
        </div>
        <hr class="my-5 opacity-10">
        <p class="text-center text-muted mb-0">&copy; 2024 <?= APP_NAME ?>. Tous droits réservés.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ duration: 800, once: true });
</script>
</body>
</html>