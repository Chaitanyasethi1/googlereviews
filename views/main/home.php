<?php
global $conn;

// Get stats
$stmt = $conn->query("SELECT COUNT(*) as count FROM review_cards");
$totalCards = $stmt->fetch()['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM reviews");
$totalReviews = $stmt->fetch()['count'];

$stmt = $conn->query("SELECT COUNT(*) as count FROM users");
$totalUsers = $stmt->fetch()['count'];

$stmt = $conn->query("SELECT AVG(rating) as avg FROM reviews");
$avgRating = $stmt->fetch()['avg'] ?? 0;

// Get recent reviews
$stmt = $conn->query("
    SELECT r.*, rc.name as business_name 
    FROM reviews r 
    JOIN review_cards rc ON r.card_id = rc.id 
    ORDER BY r.created_at DESC 
    LIMIT 6
");
$recentReviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <title><?= env('APP_NAME', 'ReviewAI') ?> - AI-Powered Google Review Collector</title>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        },
                        light: {
                            900: '#f8fafc',
                            800: '#f1f5f9',
                            700: '#e2e8f0',
                        }
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'glow-light': 'glowLight 2s ease-in-out infinite alternate',
                    },
                    keyframes: {
                        blob: {
                            '0%': { transform: 'translate(0px, 0px) scale(1)' },
                            '33%': { transform: 'translate(30px, -50px) scale(1.1)' },
                            '66%': { transform: 'translate(-20px, 20px) scale(0.9)' },
                            '100%': { transform: 'translate(0px, 0px) scale(1)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        glowLight: {
                            '0%': { boxShadow: '0 0 20px rgba(139, 92, 246, 0.1)' },
                            '100%': { boxShadow: '0 0 40px rgba(139, 92, 246, 0.3)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;700;800;900&display=swap');
        
        body { 
            background-color: #f8fafc;
            color: #0f172a;
            overflow-x: hidden;
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 1);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            border-color: rgba(139, 92, 246, 0.2);
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(139, 92, 246, 0.15);
        }

        .text-gradient {
            background: linear-gradient(to right, #7c3aed, #ec4899, #f97316);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .text-gradient-purple {
            background: linear-gradient(to right, #6d28d9, #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-glow {
            position: absolute;
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, rgba(255,255,255,0) 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            pointer-events: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            color: white !important;
        }
        
        .btn-primary::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-primary:hover::after {
            left: 100%;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            color: #0f172a !important;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .btn-secondary:hover {
            background: #f1f5f9;
            border-color: rgba(0, 0, 0, 0.15);
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.05);
        }

        .grid-pattern {
            background-image: linear-gradient(rgba(0, 0, 0, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0, 0, 0, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            background-position: center center;
        }
    </style>
</head>
<body class="antialiased selection:bg-brand-500 selection:text-white">

    <!-- Ambient Background Effects -->
    <div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-300/40 rounded-full mix-blend-multiply filter blur-[100px] animate-blob"></div>
        <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-pink-300/40 rounded-full mix-blend-multiply filter blur-[100px] animate-blob" style="animation-delay: 2s;"></div>
        <div class="absolute -bottom-32 left-1/2 w-96 h-96 bg-blue-300/40 rounded-full mix-blend-multiply filter blur-[100px] animate-blob" style="animation-delay: 4s;"></div>
        <div class="absolute inset-0 grid-pattern"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-white/50 to-light-900"></div>
    </div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 glass-nav z-50 transition-all duration-300 py-3" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <a href="<?= url('/') ?>" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-gradient-to-tr from-brand-500 to-fuchsia-500 rounded-xl flex items-center justify-center shadow-lg shadow-brand-500/30 group-hover:shadow-brand-500/50 transition-all">
                        <i class="fas fa-bolt text-white text-lg"></i>
                    </div>
                    <span class="text-2xl font-display font-bold text-gray-900 tracking-tight"><?= env('APP_NAME', 'ReviewAI') ?></span>
                </a>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#how-it-works" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">How it works</a>
                    <a href="#features" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Features</a>
                    <a href="#testimonials" class="text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Testimonials</a>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="<?= url('/login') ?>" class="hidden sm:block text-sm font-medium text-gray-600 hover:text-brand-600 transition-colors">Sign in</a>
                    <a href="<?= url('/signup') ?>" class="px-5 py-2.5 btn-primary text-white text-sm font-semibold rounded-xl shadow-lg shadow-brand-500/25">
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-40 pb-20 sm:pt-48 sm:pb-32 overflow-hidden">
        <div class="hero-glow"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <div class="max-w-4xl mx-auto" data-aos="fade-up" data-aos-duration="1000">
                <!-- Badge -->
                <div class="inline-flex items-center px-4 py-2 rounded-full glass-card text-brand-600 text-sm font-semibold mb-8 cursor-default border-brand-100 bg-brand-50/80">
                    <span class="flex h-2 w-2 relative mr-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-600"></span>
                    </span>
                    Intelligent Reputation Management
                </div>
                
                <!-- Headline -->
                <h1 class="text-5xl sm:text-7xl font-display font-extrabold text-gray-900 mb-8 leading-[1.1] tracking-tight">
                    Transform Customer Experiences into <br class="hidden sm:block" />
                    <span class="text-gradient">Verifiable Growth</span>
                </h1>
                
                <p class="text-xl sm:text-2xl text-gray-600 mb-10 max-w-2xl mx-auto font-light leading-relaxed">
                    Streamline your review collection process with smart NFC and QR technology. Empower your customers to share authentic feedback effortlessly and scale your local SEO presence.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-5">
                    <a href="<?= url('/signup') ?>" 
                       class="w-full sm:w-auto px-8 py-4 btn-primary text-white font-bold rounded-2xl shadow-xl hover:scale-105 text-lg transition-transform flex items-center justify-center">
                        Start Your Free Trial
                        <i class="fas fa-rocket ml-3"></i>
                    </a>
                    <a href="#how-it-works" 
                       class="w-full sm:w-auto px-8 py-4 btn-secondary font-bold rounded-2xl hover:scale-105 text-lg transition-transform flex items-center justify-center">
                        Explore Platform
                        <i class="fas fa-play-circle ml-3 text-brand-500"></i>
                    </a>
                </div>
            </div>

            <!-- Dashboard Mockup (CSS only) -->
            <div class="mt-20 relative mx-auto max-w-5xl" data-aos="fade-up" data-aos-delay="300" data-aos-duration="1200">
                <div class="rounded-3xl glass-card p-2 border border-white/80 shadow-2xl overflow-hidden animate-float bg-white/40">
                    <div class="bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-sm">
                        <!-- Browser Header -->
                        <div class="bg-gray-50 border-b border-gray-100 px-4 py-3 flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                            <div class="flex-1 text-center">
                                <div class="inline-block bg-white border border-gray-200 rounded-md px-4 py-1 text-xs text-gray-500 font-mono shadow-sm">
                                    <i class="fas fa-lock mr-2 text-gray-400"></i> dashboard.<?= env('APP_NAME', 'reviewai') ?>.com
                                </div>
                            </div>
                        </div>
                        <!-- Dashboard Content Placeholder -->
                        <div class="p-6 sm:p-8 bg-white relative h-[300px] sm:h-[500px]">
                            <!-- Stats row -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 mb-3"></div>
                                    <div class="w-16 h-4 bg-gray-200 rounded mb-2"></div>
                                    <div class="w-24 h-6 bg-gray-300 rounded"></div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                                    <div class="w-8 h-8 rounded-lg bg-green-100 mb-3"></div>
                                    <div class="w-16 h-4 bg-gray-200 rounded mb-2"></div>
                                    <div class="w-24 h-6 bg-gray-300 rounded"></div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hidden md:block">
                                    <div class="w-8 h-8 rounded-lg bg-purple-100 mb-3"></div>
                                    <div class="w-16 h-4 bg-gray-200 rounded mb-2"></div>
                                    <div class="w-24 h-6 bg-gray-300 rounded"></div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-4 border border-gray-100 hidden md:block">
                                    <div class="w-8 h-8 rounded-lg bg-orange-100 mb-3"></div>
                                    <div class="w-16 h-4 bg-gray-200 rounded mb-2"></div>
                                    <div class="w-24 h-6 bg-gray-300 rounded"></div>
                                </div>
                            </div>
                            <!-- Chart area -->
                            <div class="bg-gray-50 rounded-xl p-6 border border-gray-100 h-48 sm:h-64 flex items-end space-x-2">
                                <?php for($i=0; $i<12; $i++): $h = rand(20, 90); ?>
                                <div class="flex-1 bg-gradient-to-t from-brand-500 to-brand-300 rounded-t-sm" style="height: <?= $h ?>%;"></div>
                                <?php endfor; ?>
                            </div>
                            
                            <!-- Floating absolute badge -->
                            <div class="absolute bottom-10 left-10 bg-white p-4 rounded-xl flex items-center space-x-4 animate-glow-light shadow-xl border border-gray-100">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-star text-green-500"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900">New 5-Star Review</p>
                                    <p class="text-xs text-gray-500">Just now via AI Card</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Trust Bar -->
        <div class="border-y border-gray-200/60 mt-20 bg-white/60 backdrop-blur-md relative z-20">
            <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
                <p class="text-center text-sm font-bold text-gray-400 uppercase tracking-widest mb-8">Trusted by thriving businesses</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-gray-200/60">
                    <div data-aos="fade-up" data-aos-delay="100">
                        <p class="text-4xl font-display font-extrabold text-gray-900 mb-1"><?= number_format($totalUsers) ?>+</p>
                        <p class="text-sm text-gray-500 font-medium">Active Accounts</p>
                    </div>
                    <div data-aos="fade-up" data-aos-delay="200">
                        <p class="text-4xl font-display font-extrabold text-gray-900 mb-1"><?= number_format($totalCards) ?>+</p>
                        <p class="text-sm text-gray-500 font-medium">Active Cards</p>
                    </div>
                    <div data-aos="fade-up" data-aos-delay="300">
                        <p class="text-4xl font-display font-extrabold text-gray-900 mb-1"><?= number_format($totalReviews) ?>+</p>
                        <p class="text-sm text-gray-500 font-medium">Reviews Collected</p>
                    </div>
                    <div data-aos="fade-up" data-aos-delay="400">
                        <p class="text-4xl font-display font-extrabold text-gray-900 mb-1"><?= number_format($avgRating, 1) ?> <span class="text-yellow-400 text-2xl drop-shadow-sm">★</span></p>
                        <p class="text-sm text-gray-500 font-medium">Average Rating</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 sm:py-32 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-20" data-aos="fade-up">
                <h2 class="text-brand-600 font-bold tracking-wide uppercase text-sm mb-3">Enterprise Capabilities</h2>
                <h3 class="text-3xl sm:text-5xl font-display font-extrabold text-gray-900 mb-6">
                    Engineered for <span class="text-gradient-purple">Operational Excellence</span>
                </h3>
                <p class="text-lg text-gray-600">
                    We've designed our platform to reduce friction, drive engagement, and consistently elevate your brand's digital reputation.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card p-8 rounded-3xl bg-white/80" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-14 h-14 bg-gradient-to-br from-brand-100 to-fuchsia-100 border border-brand-200 rounded-2xl flex items-center justify-center mb-6 text-brand-600 text-2xl shadow-sm">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-gray-900 mb-3">Intelligent Review Assistance</h4>
                    <p class="text-gray-600 leading-relaxed text-sm">
                        Reduce cognitive load for your clients. Our AI processes their sentiment to propose articulate, personalized feedback they can publish instantly.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card p-8 rounded-3xl bg-white/80" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-100 to-cyan-100 border border-blue-200 rounded-2xl flex items-center justify-center mb-6 text-blue-600 text-2xl shadow-sm">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-gray-900 mb-3">Strategic Feedback Routing</h4>
                    <p class="text-gray-600 leading-relaxed text-sm">
                        Direct unsatisfied customers to a secure internal channel, granting your management team the opportunity to resolve concerns prior to public escalation.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card p-8 rounded-3xl bg-white/80" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-100 to-emerald-100 border border-green-200 rounded-2xl flex items-center justify-center mb-6 text-green-600 text-2xl shadow-sm">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-gray-900 mb-3">Frictionless Capture Matrix</h4>
                    <p class="text-gray-600 leading-relaxed text-sm">
                        Deploy beautifully integrated QR codes across your physical assets—receipts, point-of-sale, or packaging—for immediate tap-and-review access.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="glass-card p-8 rounded-3xl bg-white/80" data-aos="fade-up" data-aos-delay="400">
                    <div class="w-14 h-14 bg-gradient-to-br from-orange-100 to-red-100 border border-orange-200 rounded-2xl flex items-center justify-center mb-6 text-orange-600 text-2xl shadow-sm">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-gray-900 mb-3">Real-time Resolution Integration</h4>
                    <p class="text-gray-600 leading-relaxed text-sm">
                        Facilitate immediate dialogue by routing critical service issues directly to your WhatsApp Business endpoint for rapid service recovery.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="glass-card p-8 rounded-3xl bg-white/80" data-aos="fade-up" data-aos-delay="500">
                    <div class="w-14 h-14 bg-gradient-to-br from-pink-100 to-rose-100 border border-pink-200 rounded-2xl flex items-center justify-center mb-6 text-pink-600 text-2xl shadow-sm">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-gray-900 mb-3">Brand-Aligned Interfaces</h4>
                    <p class="text-gray-600 leading-relaxed text-sm">
                        Maintain corporate identity throughout the review process with fully customizable environments, logos, and localized messaging.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="glass-card p-8 rounded-3xl bg-white/80" data-aos="fade-up" data-aos-delay="600">
                    <div class="w-14 h-14 bg-gradient-to-br from-yellow-100 to-amber-100 border border-yellow-200 rounded-2xl flex items-center justify-center mb-6 text-yellow-600 text-2xl shadow-sm">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-gray-900 mb-3">Comprehensive Analytics</h4>
                    <p class="text-gray-600 leading-relaxed text-sm">
                        Monitor conversion funnels, scan metrics, and longitudinal sentiment trends via our centralized, data-rich executive dashboard.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-24 relative overflow-hidden bg-white border-y border-gray-200/60 shadow-sm">
        <div class="absolute left-0 top-0 w-1/2 h-full bg-gradient-to-r from-brand-50/50 to-transparent pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div data-aos="fade-right">
                    <h2 class="text-3xl sm:text-5xl font-display font-extrabold text-gray-900 mb-6 leading-tight">
                        A Streamlined Path to <br/>
                        <span class="text-gradient">Digital Trust</span>
                    </h2>
                    <p class="text-lg text-gray-600 mb-10">
                        We have optimized the implementation process so your organization can deploy and begin capturing verifiable feedback immediately.
                    </p>
                    
                    <div class="space-y-8">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-brand-100 border border-brand-200 flex items-center justify-center text-brand-600 font-display font-bold text-xl mt-1 shadow-sm">
                                1
                            </div>
                            <div class="ml-6">
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Deploy Your Interface</h4>
                                <p class="text-gray-600 text-sm">Register your physical locations, upload corporate assets, and configure your localized feedback environment.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-fuchsia-100 border border-fuchsia-200 flex items-center justify-center text-fuchsia-600 font-display font-bold text-xl mt-1 shadow-sm">
                                2
                            </div>
                            <div class="ml-6">
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Distribute Collection Points</h4>
                                <p class="text-gray-600 text-sm">Download high-resolution matrices or provision NFC hardware for deployment across your service footprint.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center text-blue-600 font-display font-bold text-xl mt-1 shadow-sm">
                                3
                            </div>
                            <div class="ml-6">
                                <h4 class="text-xl font-bold text-gray-900 mb-2">Automate Authentic Reviews</h4>
                                <p class="text-gray-600 text-sm">Clients authenticate securely, submit their sentiment, and our engine assists in articulating their positive experience.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative" data-aos="fade-left">
                    <div class="absolute inset-0 bg-gradient-to-tr from-brand-200/50 to-fuchsia-200/50 filter blur-[80px] rounded-full"></div>
                    <div class="relative bg-white border border-gray-100 shadow-2xl rounded-3xl p-6 sm:p-10 text-center transform rotate-2 hover:rotate-0 transition-transform duration-500">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Rate your experience</h3>
                        <p class="text-gray-500 mb-8 text-sm">Tap a star to leave a review</p>
                        
                        <div class="flex justify-center space-x-2 sm:space-x-4 mb-8">
                            <?php for($i=1; $i<=5; $i++): ?>
                            <i class="fas fa-star text-4xl sm:text-5xl <?= $i<=4 ? 'text-yellow-400 drop-shadow-[0_0_15px_rgba(250,204,21,0.5)]' : 'text-gray-200 hover:text-yellow-400' ?> cursor-pointer transition-colors"></i>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 text-left mb-6 relative overflow-hidden group cursor-pointer shadow-inner">
                            <div class="absolute inset-0 bg-brand-50 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <p class="text-gray-700 text-sm italic relative z-10 font-medium">
                                "Absolutely incredible service! The staff was incredibly welcoming, and everything exceeded my expectations. I will definitely be coming back..."
                            </p>
                            <div class="mt-3 flex items-center justify-between relative z-10">
                                <span class="text-xs text-brand-600 font-bold bg-brand-50 px-2 py-1 rounded"><i class="fas fa-magic mr-1"></i> AI Generated</span>
                                <span class="bg-white text-gray-800 text-xs font-bold px-3 py-1 rounded-full border border-gray-200 shadow-sm"><i class="fas fa-copy mr-1"></i> Copy</span>
                            </div>
                        </div>
                        
                        <button class="w-full py-4 bg-[#4285F4] text-white font-bold rounded-xl flex items-center justify-center space-x-3 hover:bg-[#3367D6] transition-colors shadow-lg shadow-blue-500/20">
                            <i class="fab fa-google text-xl bg-white text-[#4285F4] rounded-full p-1 text-sm"></i>
                            <span>Post to Google</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Wall of Love -->
    <section id="testimonials" class="py-24 sm:py-32 relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16" data-aos="fade-up">
                <h2 class="text-3xl sm:text-5xl font-display font-extrabold text-gray-900 mb-4">
                    Wall of <span class="text-gradient">Love</span>
                </h2>
                <p class="text-lg text-gray-600">Actual reviews collected by businesses using our platform.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (count($recentReviews) > 0): ?>
                    <?php foreach ($recentReviews as $index => $review): ?>
                        <div class="glass-card p-6 rounded-2xl flex flex-col h-full bg-white/90" data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex space-x-1">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star text-sm <?= $i < $review['rating'] ? 'text-yellow-400 drop-shadow-[0_0_5px_rgba(250,204,21,0.5)]' : 'text-gray-200' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-xs text-blue-500 bg-blue-50 p-1 rounded-full w-6 h-6 flex items-center justify-center"><i class="fab fa-google"></i></span>
                            </div>
                            
                            <p class="text-gray-700 text-sm leading-relaxed mb-6 flex-grow font-medium italic">
                                "<?= htmlspecialchars($review['body'] ?? 'Outstanding service! Highly recommended.') ?>"
                            </p>
                            
                            <div class="flex items-center space-x-3 pt-4 border-t border-gray-100 mt-auto">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand-100 to-fuchsia-100 flex items-center justify-center font-bold text-brand-700 text-sm border border-brand-200">
                                    <?= strtoupper(substr($review['customer_name'] ?? 'A', 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($review['customer_name'] ?? 'Anonymous') ?></p>
                                    <p class="text-xs text-gray-500">Reviewed <?= htmlspecialchars($review['business_name']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-20 bg-white rounded-3xl border-dashed border-2 border-gray-200">
                        <i class="fas fa-comment-slash text-4xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No reviews collected yet</h3>
                        <p class="text-gray-500">Sign up and be the first to display your AI-generated reviews here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-brand-50/50 pointer-events-none"></div>
        <div class="hero-glow opacity-30"></div>
        
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="bg-white rounded-[3rem] p-10 sm:p-20 text-center border border-brand-100 shadow-[0_20px_100px_rgba(124,58,237,0.1)] relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-brand-50/80 to-transparent"></div>
                
                <div class="relative z-10" data-aos="zoom-in">
                    <h2 class="text-4xl sm:text-6xl font-display font-extrabold text-gray-900 mb-6">
                        Ready to elevate your <br/> digital presence?
                    </h2>
                    <p class="text-xl text-gray-600 mb-10 max-w-2xl mx-auto font-light">
                        Join <?= number_format($totalUsers) ?>+ enterprise and regional organizations actively managing their digital reputation through intelligent automation.
                    </p>
                    
                    <a href="<?= url('/signup') ?>" 
                       class="inline-flex items-center px-10 py-5 btn-primary text-white font-bold rounded-2xl shadow-xl hover:scale-105 text-xl transition-all">
                        Deploy Your Platform
                        <i class="fas fa-arrow-right ml-3"></i>
                    </a>
                    <p class="text-gray-500 mt-6 text-sm font-medium">Risk-free trial available. Immediate provisioning.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 lg:gap-8 mb-16">
                <div class="col-span-1 md:col-span-5">
                    <a href="<?= url('/') ?>" class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-gradient-to-tr from-brand-500 to-fuchsia-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bolt text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-display font-bold text-gray-900 tracking-tight"><?= env('APP_NAME', 'ReviewAI') ?></span>
                    </a>
                    <p class="text-gray-500 text-sm leading-relaxed max-w-sm">
                        The world's most advanced AI review collection platform. We help local businesses build unbreakable online reputations through smart automation.
                    </p>
                    <div class="flex space-x-4 mt-6">
                        <a href="#" aria-label="Twitter" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition-colors shadow-sm border border-gray-100"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="Instagram" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition-colors shadow-sm border border-gray-100"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="LinkedIn" class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 hover:text-brand-600 hover:bg-brand-50 transition-colors shadow-sm border border-gray-100"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-span-1 md:col-span-2 md:col-start-7">
                    <h4 class="font-display font-bold text-gray-900 mb-6 uppercase text-sm tracking-wider">Product</h4>
                    <ul class="space-y-4">
                        <li><a href="#features" class="text-gray-500 hover:text-brand-600 text-sm transition-colors font-medium">Features</a></li>
                        <li><a href="<?= url('/pricing') ?>" class="text-gray-500 hover:text-brand-600 text-sm transition-colors font-medium">Pricing</a></li>
                        <li><a href="#testimonials" class="text-gray-500 hover:text-brand-600 text-sm transition-colors font-medium">Wall of Love</a></li>
                    </ul>
                </div>
                
                <div class="col-span-1 md:col-span-2">
                    <h4 class="font-display font-bold text-gray-900 mb-6 uppercase text-sm tracking-wider">Company</h4>
                    <ul class="space-y-4">
                        <li><a href="<?= url('/login') ?>" class="text-gray-500 hover:text-brand-600 text-sm transition-colors font-medium">Sign In</a></li>
                        <li><a href="<?= url('/signup') ?>" class="text-gray-500 hover:text-brand-600 text-sm transition-colors font-medium">Sign Up</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-brand-600 text-sm transition-colors font-medium">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-span-1 md:col-span-2">
                    <h4 class="font-display font-bold text-gray-900 mb-6 uppercase text-sm tracking-wider">Legal</h4>
                    <ul class="space-y-4">
                        <li><a href="<?= url('/privacy') ?>" class="text-gray-500 hover:text-gray-900 text-sm transition-colors font-medium">Privacy Policy</a></li>
                        <li><a href="<?= url('/terms') ?>" class="text-gray-500 hover:text-gray-900 text-sm transition-colors font-medium">Terms of Service</a></li>
                        <li><a href="<?= url('/refund') ?>" class="text-gray-500 hover:text-gray-900 text-sm transition-colors font-medium">Refund Policy</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-200 pt-8 flex flex-col md:flex-row items-center justify-between">
                <p class="text-gray-500 text-sm font-medium">&copy; <?= date('Y') ?> <?= env('APP_NAME', 'ReviewAI') ?>. All rights reserved.</p>
                <div class="flex items-center space-x-2 mt-4 md:mt-0 text-sm text-gray-500 font-medium">
                    <span>Developed by</span>
                    <a href="#" class="text-brand-600 font-bold hover:text-brand-700 hover:underline">Kenet Technologies</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Cookie Consent Banner -->
    <div id="cookie-banner" class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] z-50 transform translate-y-full transition-transform duration-500">
        <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-600 font-medium text-center sm:text-left">
                We use cookies to improve your experience and analyze site traffic. By continuing to use our site, you consent to our use of cookies in accordance with our <a href="<?= url('/privacy') ?>" class="text-brand-600 hover:underline">Privacy Policy</a>.
            </p>
            <div class="flex items-center space-x-3 shrink-0">
                <button onclick="acceptCookies()" class="px-6 py-2 bg-gray-900 text-white text-sm font-bold rounded-lg hover:bg-gray-800 transition-colors">Accept</button>
            </div>
        </div>
    </div>

    <script>
        // Cookie Consent Logic
        function acceptCookies() {
            localStorage.setItem('cookieConsent', 'true');
            $('#cookie-banner').removeClass('translate-y-0').addClass('translate-y-full');
        }

        $(document).ready(function() {
            if (!localStorage.getItem('cookieConsent')) {
                setTimeout(function() {
                    $('#cookie-banner').removeClass('translate-y-full').addClass('translate-y-0');
                }, 1000);
            }
        });

        // Initialize AOS animations
        AOS.init({
            once: true,
            offset: 50,
            duration: 800,
            easing: 'ease-out-cubic',
        });

        // Navbar blur on scroll
        $(window).scroll(function() {
            if ($(window).scrollTop() > 10) {
                $('#navbar').addClass('shadow-md bg-white/90').removeClass('bg-white/70').css('padding', '0.75rem 0');
            } else {
                $('#navbar').removeClass('shadow-md bg-white/90').addClass('bg-white/70').css('padding', '1.5rem 0');
            }
        });
    </script>
</body>
</html>