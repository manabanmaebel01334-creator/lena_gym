<?php
// index.php - Combined single file with login integration
// FIX: Move session_start() to the very top before any output is sent.
session_start();
// Simple session check for logged-in users (integrated from login.php logic)
require_once 'config.php';
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
$user_role = $_SESSION['role'] ?? '';

$cta_url = $is_logged_in ? 'customer/dashboard/billing.php' : 'login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Lena Gym Fitness</title>
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#ec1313",
                        "background-light": "#f8f6f6",
                        "background-dark": "#000000",
                    },
                    fontFamily: {
                        "display": ["Lexend"]
                    },
                    borderRadius: {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                },
            },
        }
    </script>
    <style>
        body {
            min-height: max(884px, 100dvh);
        }
        html {
            scroll-behavior: smooth;
        }
        /* Parallax effect for hero background */
        .hero-bg {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        /* Sticky header - now transparent */
        .sticky-header {
            position: absolute;
            top: 0;
            background: transparent;
            backdrop-filter: none;
            transition: all 0.3s ease;
            z-index: 10;
        }
        /* Nav links text shadow for visibility */
        .nav-links a {
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }
        .logo-text {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }
        /* Scroll-triggered animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .slide-in-left {
            opacity: 0;
            transform: translateX(-50px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .slide-in-left.visible {
            opacity: 1;
            transform: translateX(0);
        }
        .slide-in-right {
            opacity: 0;
            transform: translateX(50px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }
        .slide-in-right.visible {
            opacity: 1;
            transform: translateX(0);
        }
        /* Attractive effects for About section */
        .about-text {
            transition: all 0.4s ease;
        }
        .about-text:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 15px rgba(236, 19, 19, 0.2);
        }
        .mission-item {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .mission-item:hover {
            transform: translateX(10px);
            background: rgba(236, 19, 19, 0.1);
            border-left: 4px solid #ec1313;
        }
        .mission-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(236, 19, 19, 0.1), transparent);
            transition: left 0.5s;
        }
        .mission-item:hover::before {
            left: 100%;
        }
        /* Attractive image effects */
        .about-image {
            transition: all 0.4s ease;
            overflow: hidden;
            border-radius: 0.5rem;
        }
        .about-image:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(236, 19, 19, 0.3);
        }
        .about-image img {
            transition: transform 0.4s ease;
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: cover;
        }
        .about-image:hover img {
            transform: scale(1.1);
        }
        /* Service image effects */
        .service-image {
            overflow: hidden;
            border-radius: 0.5rem;
            transition: all 0.4s ease;
            margin-bottom: 1rem;
        }
        .service-image:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(236, 19, 19, 0.3);
        }
        .service-image img {
            transition: transform 0.4s ease;
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .service-image:hover img {
            transform: scale(1.1);
        }
        /* Section styles */
        .section {
            padding: 100px 0;
        }
        .about-bg {
            background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
        }
        .services-bg {
            background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
        }
        .membership-bg {
            background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
        }
        .contact-bg {
            background: linear-gradient(135deg, #000000 0%, #1a0000 50%, #000000 100%);
        }
        /* Button hover effects */
        .btn-hover {
            transition: all 0.3s ease;
        }
        .btn-hover:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(236, 19, 19, 0.3);
        }
        /* Mobile nav toggle */
        .mobile-menu {
            display: none;
        }
        @media (max-width: 768px) {
            .mobile-menu {
                display: block;
            }
            .nav-links {
                display: none;
            }
            .nav-links.active {
                display: block;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(0, 0, 0, 0.95);
                padding: 1rem;
            }
            .hero-bg {
                background-attachment: scroll; /* Disable fixed on mobile */
            }
            .hero-text p {
                font-size: 0.875rem; /* Smaller on mobile */
            }
            /* Logo adjustments for mobile */
            .logo-text {
                font-size: 1.5rem !important;
            }
            .service-img {
                height: 200px;
            }
            .about-image img {
                max-height: 300px;
            }
        }
        /* Parallax on scroll */
        @keyframes parallax {
            0% { transform: translateY(0); }
            100% { transform: translateY(-20px); }
        }
        .parallax-element {
            animation: parallax 20s infinite alternate ease-in-out;
        }
        /* Pricing cards */
        .pricing-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .pricing-card:hover {
            transform: translateY(-10px);
        }
        .pricing-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(236, 19, 19, 0.1), transparent);
            transition: left 0.5s;
            border-radius: inherit;
        }
        .pricing-card:hover::before {
            left: 100%;
        }
        .popular-badge {
            position: absolute;
            top: -10px;
            right: 20px;
            background: #ec1313;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        /* Hero text adjustment */
        .hero-text {
            line-height: 1.1;
        }
        .hero-text h2 {
            margin-bottom: 0.5rem;
        }
        .hero-text p {
            line-height: 1.3;
            hyphens: none;
            overflow-wrap: break-word;
            word-break: keep-all;
        }
        .hero-text .subtitle {
            margin-top: 0.5rem;
        }
        /* Dark form styles */
        .dark-form input, .dark-form textarea {
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }
        .dark-form input::placeholder, .dark-form textarea::placeholder {
            color: rgba(255,255,255,0.5);
        }
        .dark-form input:focus, .dark-form textarea:focus {
            border-color: #ec1313;
            outline: none;
        }
        /* Logo styling - Red for Gym Fitness */
        .logo-text .highlight {
            color: #ec1313;
            font-weight: 900;
            text-shadow: 0 0 10px rgba(236, 19, 19, 0.5);
        }
        /* Service image styles */
        .service-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
        /* Improved mobile hero adjustments */
        @media (max-width: 768px) {
            .hero-text h2 {
                font-size: 2.5rem; /* Slightly smaller but readable on mobile */
                line-height: 1.2;
            }
            .hero-text p {
                font-size: 1rem;
                margin-bottom: 0.5rem;
            }
            .mt-8 {
                margin-top: 2rem; /* Adjusted for mobile */
            }
            .h-12 {
                height: 3rem; /* Slightly taller buttons on mobile */
            }
        }
        /* Header on scroll - make it solid */
        .header-scrolled {
            background: rgba(0, 0, 0, 0.95) !important;
            backdrop-filter: blur(10px) !important;
        }
        /* Contact item effects */
        .contact-item {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            padding: 1rem;
            border-radius: 0.5rem;
            background: rgba(0,0,0,0.2);
            margin-bottom: 0.5rem;
        }
        .contact-item:hover {
            transform: translateX(10px);
            background: rgba(236, 19, 19, 0.1);
            border-left: 4px solid #ec1313;
        }
        .contact-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(236, 19, 19, 0.1), transparent);
            transition: left 0.5s;
        }
        .contact-item:hover::before {
            left: 100%;
        }
        /* Contact form submission styles */
        .contact-form input, .contact-form textarea {
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
            transition: border-color 0.3s ease;
        }
        .contact-form input::placeholder, .contact-form textarea::placeholder {
            color: rgba(255,255,255,0.5);
        }
        .contact-form input:focus, .contact-form textarea:focus {
            border-color: #ec1313;
            outline: none;
        }
        .contact-success {
            background: rgba(34, 197, 94, 0.2);
            border: 1px solid rgba(34, 197, 94, 0.5);
            color: #22c55e;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }
        .contact-error {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #ef4444;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body class="bg-background-light dark:bg-background-dark font-display text-white">
    
    <div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
        <header class="sticky-header z-50 w-full p-4">
            <div class="container mx-auto flex items-center justify-between">
                <h1 class="text-3xl font-bold logo-text">Lena <span class="highlight">Gym Fitness</span></h1>
                <button class="md:hidden mobile-menu text-white" onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <nav class="nav-links hidden md:flex space-x-8 text-lg font-medium text-white">
                    <a class="hover:text-primary btn-hover" href="#about">About</a>
                    <a class="hover:text-primary btn-hover" href="#services">Services</a>
                    <a class="hover:text-primary btn-hover" href="#membership">Membership</a>
                    <<?php if ($is_logged_in): ?>
                        <a class="hover:text-primary btn-hover" href="dashboard.php">Dashboard</a>
                        <a class="hover:text-primary btn-hover" href="logout.php">Logout</a> 
                        <span class="text-primary">Hi, <?php echo htmlspecialchars($user_name); ?>!</span>
                    <?php else: ?>
                        <a class="hover:text-primary btn-hover" href="login.php">Login</a>
                    <?php endif; ?>
                    </nav>
            </div>
            <div id="mobile-nav" class="md:hidden nav-links">
    <?php if ($is_logged_in): ?>
        <a class="block py-4 text-white hover:text-primary text-lg" href="dashboard.php">Dashboard</a>
        <a class="block py-4 text-white hover:text-primary text-lg" href="logout.php">Logout</a> 
        <span class="block py-4 text-primary text-lg">Hi, <?php echo htmlspecialchars($user_name); ?>!</span>
    <?php else: ?>
        <a class="block py-4 text-white hover:text-primary text-lg" href="login.php">Login</a>
    <?php endif; ?>
    </div>
        </header>
        <main>
            <div id="hero" class="relative flex h-screen min-h-[600px] flex-col items-center justify-center bg-cover bg-center px-4 text-center hero-bg" style='background-image: url("https://i.pinimg.com/originals/98/a8/16/98a8167962ef295419ee2194c8d933d2.jpg");'>
                <div class="absolute inset-0 bg-black/40"></div> <div class="max-w-2xl parallax-element hero-text fade-in">
                    <h2 class="text-4xl font-black leading-tight tracking-tight text-white md:text-5xl">Transform Your Body, Elevate Your Strength.</h2>
                    <p class="text-sm font-light text-white/80 md:text-base mt-2">Unleash your potential with our expert trainers and state-of-the-art facilities.</p>
                    <p class="text-sm font-light text-white/80 md:text-base">Join us on a journey to a healthier, stronger you.</p>
                </div>
                <div class="mt-8 relative z-10 flex flex-col gap-4 sm:flex-row sm:justify-center">
                    <a href="<?php echo $cta_url; ?>"
                       class="flex h-12 w-full items-center justify-center rounded-full bg-primary px-8 text-lg font-bold text-white shadow-lg transition-transform hover:scale-105 sm:w-auto btn-hover">
                        <?php echo $is_logged_in ? 'Go to Billing' : 'Join Now'; ?>
                    </a>
                    <button onclick="document.getElementById('about').scrollIntoView({ behavior: 'smooth' });" class="flex h-12 w-full items-center justify-center rounded-full border-2 border-white bg-transparent text-white transition-colors hover:bg-white/10 sm:w-auto px-8 text-lg font-bold btn-hover">
                        Learn More
                    </button>
                </div>
            </div>

            <section id="about" class="section about-bg">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-12 fade-in about-text">
                        <h2 class="text-4xl font-black text-white mb-4">About Lena Gym Fitness</h2>
                        <p class="text-lg text-gray-300 max-w-4xl mx-auto">Lena Gym Bocaue is a dedicated fitness center serving the Bocaue community with passion and expertise. We provide personalized training and coaching services to support effective, goal-oriented workouts, targeting students and young adults enthusiastic about gym routines. Conveniently located near schools, our community-oriented approach inspires youth fitness and builds lasting habits.</p>
                    </div>
                    <div class="grid md:grid-cols-2 gap-12 items-center">
                        <div class="slide-in-left">
                            <h3 class="text-3xl font-bold text-primary mb-6">Our Mission</h3>
                            <p class="text-gray-300 mb-8">Increase the number of customers by delivering high-quality fitness experiences that motivate and transform lives in Bocaue.</p>
                            <ul class="space-y-4 text-gray-300">
                                <li class="flex items-start mission-item p-3 rounded-lg"><span class="text-primary mr-3">✓</span> Personalized training and coaching for individual progress.</li>
                                <li class="flex items-start mission-item p-3 rounded-lg"><span class="text-primary mr-3">✓</span> Tailored for students and young gym enthusiasts.</li>
                                <li class="flex items-start mission-item p-3 rounded-lg"><span class="text-primary mr-3">✓</span> Community-driven, near schools to promote youth wellness.</li>
                            </ul>
                        </div>
                        <div class="slide-in-right">
                            <div class="about-image">
                                <img src="https://i.pinimg.com/1200x/22/80/97/2280974f48b0f84dfb6e596aed350213.jpg" alt="Gym Interior">
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="services" class="section services-bg">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-12">
                        <h2 class="text-4xl font-black text-white mb-4 fade-in">Our Services</h2>
                        <p class="text-xl text-gray-300 max-w-3xl mx-auto fade-in">Discover a range of services tailored to your fitness journey, from personal training to group classes.</p>
                    </div>
                    <div class="grid md:grid-cols-3 justify-items-center gap-8">
                        <div class="bg-black/20 p-6 rounded-lg slide-in-left">
                            <div class="service-image">
                                <img src="https://i.pinimg.com/736x/78/5d/9b/785d9b0f2696f070b799192a529cc761.jpg" alt="Membership Packages" class="service-img">
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-4">Membership Packages</h3>
                            <p class="text-gray-300 text-center">Monthly/annual membership, day/trial pass, family/group packages</p>
                        </div>
                        <div class="bg-black/20 p-6 rounded-lg fade-in">
                            <div class="service-image">
                                <img src="https://i.pinimg.com/1200x/47/c8/a4/47c8a49ba4f9b300aabe2e27e04ce1a2.jpg" alt="Fitness & Training" class="service-img">
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-4">Fitness & Training</h3>
                            <p class="text-gray-300 text-center">Personal training, group classes (Zumba, Yoga, HIIT, etc.), strength & conditioning, functional training, bootcamp</p>
                        </div>
                        <div class="bg-black/20 p-6 rounded-lg slide-in-right">
                            <div class="service-image">
                                <img src="https://images.pexels.com/photos/1640777/pexels-photo-1640777.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1" alt="Health & Wellness" class="service-img">
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-4">Health & Wellness</h3>
                            <p class="text-gray-300 text-center">Nutrition consultations, body composition check, wellness & stress management programs</p>
                        </div>
                        <div class="bg-black/20 p-6 rounded-lg slide-in-left">
                            <div class="service-image">
                                <img src="https://i.pinimg.com/736x/52/bd/15/52bd154c246009c6d3605fec2183a92b.jpg" alt="Facilities & Amenities" class="service-img">
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-4">Facilities & Amenities</h3>
                            <p class="text-gray-300 text-center">Cardio & strength equipment, free weights, locker/shower, sauna/steam, pool (if any), juice bar/café, lounge</p>
                        </div>
                        <div class="bg-black/20 p-6 rounded-lg fade-in">
                            <div class="service-image">
                                <img src="https://i.pinimg.com/1200x/9f/98/49/9f9849c038cfa7833e370f899c751241.jpg" alt="Proper Gym Wear & Safety" class="service-img">
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-4">Proper Gym Wear & Safety</h3>
                            <p class="text-gray-300 text-center">Orientation on attire & dress code, reminders on what not to wear, equipment safety & sanitation rules</p>
                        </div>
                        <div class="bg-black/20 p-6 rounded-lg slide-in-right">
                            <div class="service-image">
                                <img src="https://i.pinimg.com/1200x/ef/b0/5c/efb05c08bbd3710f9d061a7304fba02e.jpg" alt="Discounts & Promos" class="service-img">
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-4">Discounts & Promos</h3>
                            <p class="text-gray-300 text-center">Student/senior/corporate/referral discounts, loyalty points, anniversary rewards, seasonal promos, bundle packages</p>
                        </div>
                    </div>
                </div>
            </section>

            <section id="membership" class="section membership-bg">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-12 fade-in">
                        <h2 class="text-4xl font-black text-white mb-4">All-in-One Membership</h2>
                        <p class="text-xl text-gray-300 max-w-3xl mx-auto">One membership, endless possibilities. Choose the plan that fuels your ambition.</p>
                    </div>
                    <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                        <div class="pricing-card relative bg-black/20 rounded-lg p-6 text-center border border-white/20 slide-in-left">
                            <h3 class="text-2xl font-bold text-white mb-4">Basic</h3>
                            <div class="text-4xl font-black text-primary mb-4">₱50<span class="text-lg text-gray-300">/month</span></div>
                            <ul class="space-y-2 text-gray-300 mb-6">
                                <li class="flex items-center justify-center"><span class="text-green-400 mr-2">✓</span> Gym Access</li>
                                <li class="flex items-center justify-center"><span class="text-green-400 mr-2">✓</span> Basic Equipment</li>
                                <li class="flex items-center justify-center"><span class="text-red-400 mr-2">✗</span> Group Classes</li>
                                <li class="flex items-center justify-center"><span class="text-red-400 mr-2">✗</span> 1 Free Guest Pass/Month</li>
                            </ul>
                            <a href="<?php echo $is_logged_in ? 'dashboard.php' : 'login.php'; ?>" class="w-full bg-primary text-white py-3 rounded-lg font-bold btn-hover inline-block text-center">Choose Plan</a>
                        </div>
                        <div class="pricing-card relative bg-black/20 rounded-lg p-6 text-center border-2 border-primary fade-in">
                            <div class="popular-badge">POPULAR</div>
                            <h3 class="text-2xl font-bold text-white mb-4">Monthly</h3>
                            <div class="text-4xl font-black text-primary mb-4">₱999<span class="text-lg text-gray-300">/month</span></div>
                            <ul class="space-y-2 text-gray-300 mb-6">
                                <li class="flex items-center justify-center"><span class="text-green-400 mr-2">✓</span> Gym Access</li>
                                <li class="flex items-center justify-center"><span class="text-green-400 mr-2">✓</span> All Equipment</li>
                                <li class="flex items-center justify-center"><span class="text-green-400 mr-2">✓</span> Unlimited Group Classes</li>
                                <li class="flex items-center justify-center"><span class="text-red-400 mr-2">✗</span> Free Guest Pass/Month</li>
                            </ul>
                            <a href="<?php echo $is_logged_in ? 'dashboard.php' : 'login.php'; ?>" class="w-full bg-primary text-white py-3 rounded-lg font-bold btn-hover inline-block text-center">Choose Plan</a>
                        </div>
                        <div class="pricing-card relative bg-black/20 rounded-lg p-6 text-center border border-white/20 slide-in-right">
                            <h3 class="text-2xl font-bold text-white mb-4">Premium</h3>
                            <div class="text-4xl font-black text-primary mb-4">₱1499<span class="text-lg text-gray-300">/month</span></div>
                            <ul class="space-y-2 text-gray-300 mb-6">
                                <li class="flex items-center justify-center"><span class="text-green-400 mr-2">✓</span> Gym Access</li>
                                <li class="flex items-center justify-center"><span class="text-green-400 mr-2">✓</span> All Equipment</li>
                                <li class="flex items-center justify-center"><span class="text-green-400 mr-2">✓</span> Unlimited Group Classes</li>
                                <li class="flex items-center justify-center"><span class="text-green-400 mr-2">✓</span> Free Guest Pass/Month</li>
                            </ul>
                            <a href="<?php echo $is_logged_in ? 'dashboard.php' : 'login.php'; ?>" class="w-full bg-primary text-white py-3 rounded-lg font-bold btn-hover inline-block text-center">Choose Plan</a>
                        </div>
                    </div>
                </div>
            </section>

            <section id="contact" class="section contact-bg">
                <div class="container mx-auto px-4">
                    <div class="text-center mb-12 fade-in">
                        <h2 class="text-4xl font-black text-white mb-4">Get in Touch</h2>
                        <p class="text-xl text-gray-300 max-w-3xl mx-auto">Ready to start your fitness journey? Contact us today!</p>
                    </div>
                    <?php
                    // Handle contact form submission (simple email simulation - integrate with mail() or external service)
                    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['contact_submit'])) {
                        $contact_name = trim($_POST['contact_name'] ?? '');
                        $contact_email = trim($_POST['contact_email'] ?? '');
                        $contact_message = trim($_POST['contact_message'] ?? '');

                        if (!empty($contact_name) && !empty($contact_email) && !empty($contact_message)) {
                            // Here, you can integrate mail() function or save to DB
                            // For now, show success message
                            echo '<div class="contact-success">Thank you for your message! We\'ll get back to you soon.</div>';
                        } else {
                            echo '<div class="contact-error">Please fill all fields.</div>';
                        }
                    }
                    ?>
                    <div class="grid md:grid-cols-2 gap-12">
                        <div class="slide-in-left contact-form">
                            <form class="space-y-4" method="POST">
                                <input type="hidden" name="contact_submit" value="1">
                                <input type="text" name="contact_name" placeholder="Your Name" class="w-full p-4 rounded-lg" required>
                                <input type="email" name="contact_email" placeholder="Your Email" class="w-full p-4 rounded-lg" required>
                                <textarea name="contact_message" placeholder="Your Message" rows="5" class="w-full p-4 rounded-lg" required></textarea>
                                <button type="submit" class="w-full bg-primary text-white py-4 rounded-lg font-bold btn-hover">Send Message</button>
                            </form>
                        </div>
                        <div class="slide-in-right space-y-6 text-white">
                            <div class="flex items-start contact-item">
                                <svg class="w-6 h-6 mr-3 text-primary flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                <span>Biñang 2nd Mendieta 2nd floor Mc.Arthur Highway, Bocaue, Philippines</span>
                            </div>
                            <div class="flex items-center contact-item">
                                <svg class="w-6 h-6 mr-3 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                <span>+63 963 008 2196</span>
                            </div>
                            <div class="flex items-center contact-item">
                                <svg class="w-6 h-6 mr-3 text-primary flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path></svg>
                                <span>nosyajdrianeda@gmail.com</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <footer id="footer-section" class="bg-black py-12 px-6">
            <div class="container mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="font-bold text-lg mb-4 text-white">Quick Links</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a class="hover:text-white transition-colors" href="#about">About Us</a></li>
                        <li><a class="hover:text-white transition-colors" href="#services">Services</a></li>
                        <li><a class="hover:text-white transition-colors" href="#membership">Membership Plans</a></li>
                        <?php if ($is_logged_in): ?>
                            <li><a class="hover:text-white transition-colors" href="dashboard.php">Dashboard</a></li>
                        <?php else: ?>
                            <li><a class="hover:text-white transition-colors" href="login.php">Login</a></li>
                        <?php endif; ?>
                        <li><a class="hover:text-white transition-colors" href="#contact">Contact Us</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-4 text-white">Resources</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a class="hover:text-white transition-colors" href="#">Franchise FAQ</a></li>
                        <li><a class="hover:text-white transition-colors" href="#">Privacy Policy</a></li>
                        <li><a class="hover:text-white transition-colors" href="#">Terms of Service</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-bold text-lg mb-4 text-white">Follow Us</h3>
                    <div class="flex space-x-4">
                        <a class="text-gray-400 hover:text-white transition-colors" href="https://www.facebook.com/share/19rCEN7ZGa/?mibextid=wwXIfr">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35C.59 0 0 .59 0 1.325v21.35C0 23.41.59 24 1.325 24H12.82v-9.29H9.692v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12V24h5.713c.735 0 1.325-.59 1.325-1.325V1.325C24 .59 23.41 0 22.675 0z"></path></svg>
                        </a>
                        <a class="text-gray-400 hover:text-white transition-colors" href="#">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.85s-.011 3.584-.069 4.85c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07s-3.584-.012-4.85-.07c-3.252-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.85s.011-3.584.069-4.85c.149-3.225 1.664-4.771 4.919-4.919C8.416 2.175 8.796 2.163 12 2.163zm0 1.802C9.042 3.965 8.71 3.977 7.436 4.04c-2.483.114-3.633 1.264-3.746 3.746-.064 1.274-.076 1.606-.076 4.214s.012 2.94.076 4.214c.113 2.483 1.263 3.633 3.746 3.746 1.274.064 1.606.076 4.214.076s2.94-.012 4.214-.076c2.483-.113 3.633-1.263 3.746-3.746.064-1.274.076-1.606.076-4.214s-.012-2.94-.076-4.214c-.113-2.482-1.264-3.633-3.746-3.746C15.29 3.977 14.958 3.965 12 3.965zm0 2.978c-2.653 0-4.805 2.152-4.805 4.805s2.152 4.805 4.805 4.805 4.805-2.152 4.805-4.805S14.653 6.943 12 6.943zm0 7.81c-1.657 0-3.005-1.348-3.005-3.005s1.348-3.005 3.005-3.005 3.005 1.348 3.005 3.005-1.348 3.005-3.005 3.005zm6.205-7.708c0-.623-.505-1.128-1.128-1.128s-1.128.505-1.128 1.128.505 1.128 1.128 1.128 1.128-.505 1.128-1.128z"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-800 text-center">
                <p class="text-sm text-gray-500">© 2025 Lena Fitness Gym. All rights reserved.</p>
            </div>
        </footer>
    </div>

    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const nav = document.getElementById('mobile-nav');
            nav.classList.toggle('active');
        }

        // Scroll-triggered animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in, .slide-in-left, .slide-in-right').forEach(el => {
            observer.observe(el);
        });

        // Header scroll effect - add solid background on scroll
        window.addEventListener('scroll', () => {
            const header = document.querySelector('.sticky-header');
            if (window.scrollY > 100) {
                header.classList.add('header-scrolled');
            } else {
                header.classList.remove('header-scrolled');
            }
        });
    </script>
</body>
</html>