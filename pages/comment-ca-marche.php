<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php'; 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processus Nexora - Votre Guide vers l'Excellence</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0f172a;
            --accent: #4f46e5;
            --accent-soft: #eef2ff;
            --success: #10b981;
            --text-muted: #64748b;
            --glass: rgba(255, 255, 255, 0.9);
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: var(--primary); 
            background: #fff;
            line-height: 1.7;
        }

        h1, h2, h3, h4 { font-weight: 800; letter-spacing: -1.5px; }

        /* --- Header Premium --- */
        .process-hero {
            padding: 140px 0 90px;
            background: radial-gradient(circle at 100% 0%, var(--accent-soft) 0%, #ffffff 60%);
            border-bottom: 1px solid #f1f5f9;
        }

        /* --- Tabs Custom (Noir & Violet) --- */
        .nav-pills-custom {
            background: #f1f5f9;
            padding: 8px;
            border-radius: 50px;
            display: inline-flex;
            margin-bottom: 3rem;
        }
        .nav-pills-custom .nav-link {
            color: var(--text-muted);
            font-weight: 600;
            padding: 12px 35px;
            border-radius: 50px;
            transition: 0.3s;
        }
        .nav-pills-custom .nav-link.active {
            background-color: var(--primary);
            color: #fff;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.1);
        }

        /* --- Timeline Alternée --- */
        .timeline-section { padding: 80px 0; }
        
        .step-row {
            display: flex;
            align-items: center;
            margin-bottom: 120px;
            position: relative;
        }

        .step-content { flex: 1; padding: 0 50px; }
        .step-image-container { flex: 1; text-align: center; padding: 0 30px; }

        .step-badge {
            width: 55px; height: 55px;
            background: var(--accent);
            color: white;
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 1.3rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(79, 70, 229, 0.3);
        }

        .dashboard-mockup {
            background: #fff;
            border-radius: 28px;
            padding: 20px;
            box-shadow: 0 40px 80px -15px rgba(0,0,0,0.12);
            border: 1px solid #f1f5f9;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-width: 550px;
        }
        .dashboard-mockup:hover { transform: scale(1.03) translateY(-15px); }

        .info-tag {
            display: inline-block;
            padding: 6px 16px;
            background: var(--accent-soft);
            color: var(--accent);
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* --- CTA FINAL --- */
        .cta-box {
            background: var(--primary);
            border-radius: 40px;
            padding: 80px 60px;
            color: white;
            margin-bottom: 100px;
            position: relative;
            overflow: hidden;
        }
        .cta-box::after {
            content: ""; position: absolute; bottom: -50px; right: -50px;
            width: 200px; height: 200px;
            background: var(--accent); border-radius: 50%; opacity: 0.2;
        }

        footer { background: #f8fafc; padding: 80px 0 40px; border-top: 1px solid #f1f5f9; }
    </style>
</head>
<body>

<header class="process-hero text-center">
    <div class="container">
        <span class="info-tag" data-aos="fade-up">Centre d'aide & Guide</span>
        <h1 class="display-3 mb-4" data-aos="fade-up" data-aos-delay="100">L'Expérience Nexora</h1>
        <p class="lead text-muted mx-auto mb-5" style="max-width: 750px;" data-aos="fade-up" data-aos-delay="200">
            Découvrez comment nous connectons les porteurs de projets ambitieux avec l'élite des prestataires. Transparence, sécurité et excellence à chaque étape.
        </p>

        <ul class="nav nav-pills nav-pills-custom" id="pills-tab" role="tablist" data-aos="fade-up" data-aos-delay="300">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="client-tab" data-bs-toggle="pill" data-bs-target="#pills-client" type="button" role="tab"><i class="bi bi-briefcase-fill me-2"></i>Je suis Client</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="expert-tab" data-bs-toggle="pill" data-bs-target="#pills-expert" type="button" role="tab"><i class="bi bi-stars me-2"></i>Je suis Expert</button>
            </li>
        </ul>
    </div>
</header>

<div class="container">
    <div class="tab-content" id="pills-tabContent">
        
        <div class="tab-pane fade show active" id="pills-client" role="tabpanel">
            <div class="timeline-section">
                
                <div class="step-row" data-aos="fade-up">
                    <div class="step-content pe-lg-5">
                        <div class="step-badge">01</div>
                        <h2 class="fw-bold display-6">Exprimez votre vision</h2>
                        <p class="text-muted fs-5 mb-4">L'aventure commence ici. Remplissez notre formulaire intuitif. Pas de jargon complexe, juste votre vision. Définissez votre budget, vos délais et les compétences clés.</p>
                        <div class="p-4 bg-light rounded-4 border-start border-primary border-4">
                            <ul class="list-unstyled m-0 fw-600 small text-primary">
                                <li class="mb-2"><i class="bi bi-check-circle-fill me-2"></i> Notre IA analyse votre besoin</li>
                                <li><i class="bi bi-check-circle-fill me-2"></i> Notification aux experts compatibles</li>
                            </ul>
                        </div>
                    </div>
                    <div class="step-image-container">
                        <div class="dashboard-mockup">
                            <img src="https://images.unsplash.com/photo-1551288049-bbbda536339a?auto=format&fit=crop&q=80&w=800" class="img-fluid rounded-4" alt="Dashboard Publication Client">
                        </div>
                    </div>
                </div>

                <div class="step-row flex-row-reverse" data-aos="fade-up">
                    <div class="step-content ps-lg-5">
                        <div class="step-badge">02</div>
                        <h2 class="fw-bold display-6">Comparez l'Élite</h2>
                        <p class="text-muted fs-5 mb-4">Recevez des propositions détaillées. Ne choisissez pas au hasard : examinez leurs portfolios certifiés, leurs avis et discutez en direct via notre messagerie sécurisée.</p>
                        <div class="d-flex align-items-center gap-3">
                            <i class="bi bi-people-fill fs-2 text-primary"></i>
                            <span class="text-muted small"><strong>Plus de 15k+</strong> prestations ont été réalisées avec succès.</span>
                        </div>
                    </div>
                    <div class="step-image-container">
                        <div class="dashboard-mockup">
                            <img src="https://images.unsplash.com/photo-1522204523234-8729aa6e3d5f?auto=format&fit=crop&q=80&w=800" class="img-fluid rounded-4" alt="Collaboration d'équipe">
                        </div>
                    </div>
                </div>

                <div class="step-row" data-aos="fade-up">
                    <div class="step-content pe-lg-5">
                        <div class="step-badge">03</div>
                        <h2 class="fw-bold display-6">Protection Totale (Escrow)</h2>
                        <p class="text-muted fs-5 mb-4">Lancez le projet en toute sérénité. Votre paiement est sécurisé et bloqué sur un compte tiers de confiance. Le prestataire n'est payé que lorsque vous validez la livraison.</p>
                        <ul class="list-unstyled mt-4 fw-600 text-muted small">
                            <li class="mb-2"><i class="bi bi-shield-fill-check text-success me-2"></i> Protection acheteur garantie</li>
                            <li><i class="bi bi-cash-stack text-success me-2"></i> Pas d'avances injustifiées</li>
                        </ul>
                    </div>
                    <div class="step-image-container">
                        <div class="dashboard-mockup">
                            <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&q=80&w=800" class="img-fluid rounded-4" alt="Système de paiement sécurisé">
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="tab-pane fade" id="pills-expert" role="tabpanel">
            <div class="timeline-section">
                
                <div class="step-row" data-aos="fade-up">
                    <div class="step-content pe-lg-5">
                        <div class="step-badge">01</div>
                        <h2 class="fw-bold display-6">Profil Vérifié & Premium</h2>
                        <p class="text-muted fs-5 mb-4">Mettez en valeur votre excellence. Importez vos diplômes, portfolio et références. Obtenez le badge "Vérifié" pour rassurer les clients exigeants.</p>
                        <div class="p-3 bg-light rounded-4 text-center">
                            <img src="https://i.pravatar.cc/100?img=5" class="rounded-pill border border-4 border-white shadow mb-2">
                            <small class="fw-bold d-block text-success">Badge Nexora Vérifié</small>
                        </div>
                    </div>
                    <div class="step-image-container">
                        <div class="dashboard-mockup">
                            <img src="https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&q=80&w=800" class="img-fluid rounded-4" alt="Portfolio expert en ligne">
                        </div>
                    </div>
                </div>

                <div class="step-row flex-row-reverse" data-aos="fade-up">
                    <div class="step-content ps-lg-5">
                        <div class="step-badge">02</div>
                        <h2 class="fw-bold display-6">Outils de Gestion</h2>
                        <p class="text-muted fs-5 mb-4">Votre tableau de bord centralise tout : chat, étapes du projet, partage de fichiers et devis. Concentrez-vous sur votre art, nous gérons la logistique.</p>
                    </div>
                    <div class="step-image-container">
                        <div class="dashboard-mockup">
                            <img src="https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&q=80&w=800" class="img-fluid rounded-4" alt="Gestion de projet fluide">
                        </div>
                    </div>
                </div>

                <div class="step-row" data-aos="fade-up">
                    <div class="step-content pe-lg-5">
                        <div class="step-badge">03</div>
                        <h2 class="fw-bold display-6">Rétribution Instantanée</h2>
                        <p class="text-muted fs-5 mb-4">Dès que le client valide votre travail, les fonds bloqués sont libérés instantanément sur votre compte Nexora. Plus de factures impayées.</p>
                        <div class="p-3 bg-light rounded-4 text-center">
                            <i class="bi bi-wallet2 fs-1 text-primary"></i>
                            <small class="fw-bold d-block text-primary">Virement garanti par Nexora</small>
                        </div>
                    </div>
                    <div class="step-image-container">
                        <div class="dashboard-mockup">
                            <img src="https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&q=80&w=800" class="img-fluid rounded-4" alt="Virement et retrait faciles">
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <div class="cta-box text-center shadow-lg animate" data-aos="zoom-in">
        <h2 class="display-5 fw-bold mb-4">Votre prochain projet d'excellence commence ici.</h2>
        <p class="lead mb-5 opacity-75">Que vous ayez besoin d'un talent ou que vous soyez ce talent, rejoignez Nexora.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="register.php" class="btn btn-light btn-lg px-5 rounded-pill fw-bold text-primary">Créer mon compte</a>
            <a href="index.php" class="btn btn-outline-light btn-lg px-5 rounded-pill">Explorer la plateforme</a>
        </div>
    </div>
</div>

<footer>
    <div class="container py-4">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <h4 class="fw-bold"><i class="bi bi-lightning-charge-fill text-primary"></i> <?= APP_NAME ?></h4>
                <p class="text-muted small">Connecter l'ambition à l'expertise.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <p class="text-muted small m-0">&copy; 2024 Nexora. Tous droits réservés.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    AOS.init({ 
        duration: 900,
        once: true,
        offset: 150
    });
</script>
</body>
</html>