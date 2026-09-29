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
                            950: '#2e1065',
                        },
                        dark: {
                            900: '#0a0a0a',
                            800: '#121212',
                            700: '#1a1a1a',
                        }
                    },
                    animation: {
                        'blob': 'blob 7s infinite',
                        'float': 'float 6s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
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
                        glow: {
                            '0%': { boxShadow: '0 0 20px rgba(139, 92, 246, 0.2)' },
                            '100%': { boxShadow: '0 0 40px rgba(139, 92, 246, 0.6)' },
                        }
                    }
                }
            }
        }
    </script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;700;800;900&display=swap');
        
        body { 
            background-color: #0a0a0a;
            color: #ffffff;
            overflow-x: hidden;
        }

        .glass-nav {
            background: rgba(10, 10, 10, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .glass-card {
            background: rgba(25, 25, 25, 0.4);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            border-color: rgba(139, 92, 246, 0.3);
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(139, 92, 246, 0.1);
        }

        .text-gradient {
            background: linear-gradient(to right, #a78bfa, #f472b6, #fb923c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .text-gradient-purple {
            background: linear-gradient(to right, #c4b5fd, #8b5cf6, #4f46e5);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-glow {
            position: absolute;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, rgba(0,0,0,0) 70%);
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
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .grid-pattern {
            background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            background-position: center center;
        }
    </style>
</head>
<body class="antialiased selection:bg-brand-500 selection:text-white">

    <!-- Ambient Background Effects -->
    <div class="fixed inset-0 z-[-1] overflow-hidden pointer-events-none">
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-brand-600/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob"></div>
        <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-fuchsia-600/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob" style="animation-delay: 2s;"></div>
        <div class="absolute -bottom-32 left-1/2 w-96 h-96 bg-blue-600/20 rounded-full mix-blend-screen filter blur-[100px] animate-blob" style="animation-delay: 4s;"></div>
        <div class="absolute inset-0 grid-pattern"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-transparent via-dark-900/80 to-dark-900"></div>
    </div>

    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 glass-nav z-50 transition-all duration-300 py-3" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <a href="<?= url('/') ?>" class="flex items-center space-x-3 group">
                    <div class="w-10 h-10 bg-gradient-to-tr from-brand-600 to-fuchsia-600 rounded-xl flex items-center justify-center shadow-lg shadow-brand-500/30 group-hover:shadow-brand-500/50 transition-all">
                        <i class="fas fa-bolt text-white text-lg"></i>
                    </div>
                    <span class="text-2xl font-display font-bold text-white tracking-tight"><?= env('APP_NAME', 'NexusReviews') ?></span>
                </a>
                
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#how-it-works" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">How it works</a>
                    <a href="#features" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Features</a>
                    <a href="#testimonials" class="text-sm font-medium text-gray-300 hover:text-white transition-colors">Testimonials</a>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="<?= url('/login') ?>" class="hidden sm:block text-sm font-medium text-gray-300 hover:text-white transition-colors">Sign in</a>
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
                <div class="inline-flex items-center px-4 py-2 rounded-full glass-card text-brand-300 text-sm font-medium mb-8 cursor-default">
                    <span class="flex h-2 w-2 relative mr-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-brand-500"></span>
                    </span>
                    Next-Gen AI Review Collection is here
                </div>
                
                <!-- Headline -->
                <h1 class="text-5xl sm:text-7xl font-display font-extrabold text-white mb-8 leading-[1.1] tracking-tight">
                    Turn happy customers into <br class="hidden sm:block" />
                    <span class="text-gradient">5-Star Google Reviews</span>
                </h1>
                
                <p class="text-xl sm:text-2xl text-gray-400 mb-10 max-w-2xl mx-auto font-light leading-relaxed">
                    Automate your reputation management with our AI-powered NFC cards & QR codes. Stop begging for reviews, start generating them.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-5">
                    <a href="<?= url('/signup') ?>" 
                       class="w-full sm:w-auto px-8 py-4 btn-primary text-white font-bold rounded-2xl shadow-[0_0_40px_rgba(124,58,237,0.4)] hover:scale-105 text-lg transition-transform flex items-center justify-center">
                        Start Your Free Trial
                        <i class="fas fa-rocket ml-3"></i>
                    </a>
                    <a href="#how-it-works" 
                       class="w-full sm:w-auto px-8 py-4 btn-secondary text-white font-bold rounded-2xl hover:scale-105 text-lg transition-transform flex items-center justify-center">
                        See How It Works
                        <i class="fas fa-play-circle ml-3 text-gray-400"></i>
                    </a>
                </div>
            </div>

            <!-- Dashboard Mockup (CSS only) -->
            <div class="mt-20 relative mx-auto max-w-5xl" data-aos="fade-up" data-aos-delay="300" data-aos-duration="1200">
                <div class="rounded-3xl glass-card p-2 border border-white/10 shadow-2xl overflow-hidden animate-float">
                    <div class="bg-dark-800 rounded-2xl overflow-hidden border border-white/5">
                        <!-- Browser Header -->
                        <div class="bg-dark-900 border-b border-white/5 px-4 py-3 flex items-center space-x-2">
                            <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-yellow-500/80"></div>
                            <div class="w-3 h-3 rounded-full bg-green-500/80"></div>
                            <div class="flex-1 text-center">
                                <div class="inline-block bg-dark-700/50 rounded-md px-4 py-1 text-xs text-gray-500 font-mono">
                                    <i class="fas fa-lock mr-2 text-gray-600"></i> dashboard.<?= env('APP_NAME', 'nexusreviews') ?>.com
                                </div>
                            </div>
                        </div>
                        <!-- Dashboard Content Placeholder -->
                        <div class="p-6 sm:p-8 bg-dark-800 relative h-[300px] sm:h-[500px]">
                            <!-- Stats row -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                <div class="bg-dark-700/50 rounded-xl p-4 border border-white/5">
                                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 mb-3"></div>
                                    <div class="w-16 h-4 bg-gray-600 rounded mb-2"></div>
                                    <div class="w-24 h-6 bg-gray-400 rounded"></div>
                                </div>
                                <div class="bg-dark-700/50 rounded-xl p-4 border border-white/5">
                                    <div class="w-8 h-8 rounded-lg bg-green-500/20 mb-3"></div>
                                    <div class="w-16 h-4 bg-gray-600 rounded mb-2"></div>
                                    <div class="w-24 h-6 bg-gray-400 rounded"></div>
                                </div>
                                <div class="bg-dark-700/50 rounded-xl p-4 border border-white/5 hidden md:block">
                                    <div class="w-8 h-8 rounded-lg bg-purple-500/20 mb-3"></div>
                                    <div class="w-16 h-4 bg-gray-600 rounded mb-2"></div>
                                    <div class="w-24 h-6 bg-gray-400 rounded"></div>
                                </div>
                                <div class="bg-dark-700/50 rounded-xl p-4 border border-white/5 hidden md:block">
                                    <div class="w-8 h-8 rounded-lg bg-orange-500/20 mb-3"></div>
                                    <div class="w-16 h-4 bg-gray-600 rounded mb-2"></div>
                                    <div class="w-24 h-6 bg-gray-400 rounded"></div>
                                </div>
                            </div>
                            <!-- Chart area -->
                            <div class="bg-dark-700/50 rounded-xl p-6 border border-white/5 h-48 sm:h-64 flex items-end space-x-2">
                                <?php for($i=0; $i<12; $i++): $h = rand(20, 90); ?>
                                <div class="flex-1 bg-gradient-to-t from-brand-600/50 to-brand-400/20 rounded-t-sm" style="height: <?= $h ?>%;"></div>
                                <?php endfor; ?>
                            </div>
                            
                            <!-- Floating absolute badge -->
                            <div class="absolute bottom-10 left-10 glass-card p-4 rounded-xl flex items-center space-x-4 animate-glow">
                                <div class="w-10 h-10 bg-green-500/20 rounded-full flex items-center justify-center">
                                    <i class="fas fa-star text-green-400"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-white">New 5-Star Review</p>
                                    <p class="text-xs text-gray-400">Just now via AI Card</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Trust Bar -->
        <div class="border-y border-white/5 mt-20 bg-dark-900/50 backdrop-blur-sm relative z-20">
            <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">
                <p class="text-center text-sm font-semibold text-gray-500 uppercase tracking-widest mb-8">Trusted by thriving businesses</p>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center divide-x divide-white/5">
                    <div data-aos="fade-up" data-aos-delay="100">
                        <p class="text-4xl font-display font-extrabold text-white mb-1"><?= number_format($totalUsers) ?>+</p>
                        <p class="text-sm text-gray-500 font-medium">Active Accounts</p>
                    </div>
                    <div data-aos="fade-up" data-aos-delay="200">
                        <p class="text-4xl font-display font-extrabold text-white mb-1"><?= number_format($totalCards) ?>+</p>
                        <p class="text-sm text-gray-500 font-medium">Active Cards</p>
                    </div>
                    <div data-aos="fade-up" data-aos-delay="300">
                        <p class="text-4xl font-display font-extrabold text-white mb-1"><?= number_format($totalReviews) ?>+</p>
                        <p class="text-sm text-gray-500 font-medium">Reviews Collected</p>
                    </div>
                    <div data-aos="fade-up" data-aos-delay="400">
                        <p class="text-4xl font-display font-extrabold text-white mb-1"><?= number_format($avgRating, 1) ?> <span class="text-yellow-400 text-2xl">★</span></p>
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
                <h2 class="text-brand-400 font-semibold tracking-wide uppercase text-sm mb-3">Power Features</h2>
                <h3 class="text-3xl sm:text-5xl font-display font-extrabold text-white mb-6">
                    Built for <span class="text-gradient-purple">Maximum Conversion</span>
                </h3>
                <p class="text-lg text-gray-400">
                    We've engineered every feature to reduce friction and maximize the number of positive reviews your business receives.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass-card p-8 rounded-3xl" data-aos="fade-up" data-aos-delay="100">
                    <div class="w-14 h-14 bg-gradient-to-br from-brand-500/20 to-fuchsia-500/20 border border-brand-500/30 rounded-2xl flex items-center justify-center mb-6 text-brand-400 text-2xl">
                        <i class="fas fa-brain"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-white mb-3">AI Smart Responses</h4>
                    <p class="text-gray-400 leading-relaxed text-sm">
                        Customers hate writing. Our AI analyzes their sentiment and instantly suggests perfect, personalized review text they can post with one click.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card p-8 rounded-3xl" data-aos="fade-up" data-aos-delay="200">
                    <div class="w-14 h-14 bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border border-blue-500/30 rounded-2xl flex items-center justify-center mb-6 text-blue-400 text-2xl">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-white mb-3">Negative Feedback Shield</h4>
                    <p class="text-gray-400 leading-relaxed text-sm">
                        1-3 star ratings are secretly intercepted and sent directly to your private WhatsApp or email, preventing them from ever reaching Google.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card p-8 rounded-3xl" data-aos="fade-up" data-aos-delay="300">
                    <div class="w-14 h-14 bg-gradient-to-br from-green-500/20 to-emerald-500/20 border border-green-500/30 rounded-2xl flex items-center justify-center mb-6 text-green-400 text-2xl">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-white mb-3">Instant QR Magic</h4>
                    <p class="text-gray-400 leading-relaxed text-sm">
                        Generate beautifully branded QR codes instantly. Print them on receipts, table tents, or business cards for tap-and-go reviewing.
                    </p>
                </div>
                
                <!-- Feature 4 -->
                <div class="glass-card p-8 rounded-3xl" data-aos="fade-up" data-aos-delay="400">
                    <div class="w-14 h-14 bg-gradient-to-br from-orange-500/20 to-red-500/20 border border-orange-500/30 rounded-2xl flex items-center justify-center mb-6 text-orange-400 text-2xl">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-white mb-3">Direct WhatsApp Integration</h4>
                    <p class="text-gray-400 leading-relaxed text-sm">
                        Let unhappy customers instantly chat with you on WhatsApp instead of leaving a bad public review. Resolve issues in real-time.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="glass-card p-8 rounded-3xl" data-aos="fade-up" data-aos-delay="500">
                    <div class="w-14 h-14 bg-gradient-to-br from-pink-500/20 to-rose-500/20 border border-pink-500/30 rounded-2xl flex items-center justify-center mb-6 text-pink-400 text-2xl">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-white mb-3">Beautiful Customization</h4>
                    <p class="text-gray-400 leading-relaxed text-sm">
                        Make your review page match your brand. Add your logo, choose theme colors, and customize the background to build trust.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="glass-card p-8 rounded-3xl" data-aos="fade-up" data-aos-delay="600">
                    <div class="w-14 h-14 bg-gradient-to-br from-yellow-500/20 to-amber-500/20 border border-yellow-500/30 rounded-2xl flex items-center justify-center mb-6 text-yellow-400 text-2xl">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <h4 class="text-xl font-display font-bold text-white mb-3">Deep Analytics</h4>
                    <p class="text-gray-400 leading-relaxed text-sm">
                        Track scans, conversions, and rating trends over time. Know exactly what your customers think with our unified analytics dashboard.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-24 relative overflow-hidden bg-dark-900 border-y border-white/5">
        <div class="absolute left-0 top-0 w-1/2 h-full bg-gradient-to-r from-brand-900/10 to-transparent pointer-events-none"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <div data-aos="fade-right">
                    <h2 class="text-3xl sm:text-5xl font-display font-extrabold text-white mb-6 leading-tight">
                        Three steps to <br/>
                        <span class="text-gradient">Review Domination</span>
                    </h2>
                    <p class="text-lg text-gray-400 mb-10">
                        We've simplified the entire process so you can set it up in minutes and watch the 5-star reviews roll in automatically.
                    </p>
                    
                    <div class="space-y-8">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-brand-500/20 border border-brand-500/30 flex items-center justify-center text-brand-400 font-display font-bold text-xl mt-1">
                                1
                            </div>
                            <div class="ml-6">
                                <h4 class="text-xl font-bold text-white mb-2">Create & Customize</h4>
                                <p class="text-gray-400 text-sm">Enter your Google Business link, upload your logo, and customize your theme in our powerful dashboard.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-fuchsia-500/20 border border-fuchsia-500/30 flex items-center justify-center text-fuchsia-400 font-display font-bold text-xl mt-1">
                                2
                            </div>
                            <div class="ml-6">
                                <h4 class="text-xl font-bold text-white mb-2">Display QR Codes</h4>
                                <p class="text-gray-400 text-sm">Download your custom QR code instantly. Place it on tables, counters, packaging, or receipts.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-blue-500/20 border border-blue-500/30 flex items-center justify-center text-blue-400 font-display font-bold text-xl mt-1">
                                3
                            </div>
                            <div class="ml-6">
                                <h4 class="text-xl font-bold text-white mb-2">AI Does The Rest</h4>
                                <p class="text-gray-400 text-sm">Customers scan, tap 5 stars, and our AI writes the perfect review for them to post with one click.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="relative" data-aos="fade-left">
                    <div class="absolute inset-0 bg-gradient-to-tr from-brand-600/30 to-fuchsia-600/30 filter blur-[80px] rounded-full"></div>
                    <div class="relative glass-card border border-white/10 rounded-3xl p-6 sm:p-10 text-center transform rotate-2 hover:rotate-0 transition-transform duration-500">
                        <h3 class="text-2xl font-bold text-white mb-2">Rate your experience</h3>
                        <p class="text-gray-400 mb-8 text-sm">Tap a star to leave a review</p>
                        
                        <div class="flex justify-center space-x-2 sm:space-x-4 mb-8">
                            <?php for($i=1; $i<=5; $i++): ?>
                            <i class="fas fa-star text-4xl sm:text-5xl <?= $i<=4 ? 'text-yellow-400 drop-shadow-[0_0_15px_rgba(250,204,21,0.5)]' : 'text-gray-600 hover:text-yellow-400' ?> cursor-pointer transition-colors"></i>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="bg-dark-800 rounded-2xl p-4 border border-white/5 text-left mb-6 relative overflow-hidden group cursor-pointer">
                            <div class="absolute inset-0 bg-brand-500/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <p class="text-gray-300 text-sm italic relative z-10">
                                "Absolutely incredible service! The staff was incredibly welcoming, and everything exceeded my expectations. I will definitely be coming back..."
                            </p>
                            <div class="mt-3 flex items-center justify-between relative z-10">
                                <span class="text-xs text-brand-400 font-medium"><i class="fas fa-magic mr-1"></i> AI Generated</span>
                                <span class="bg-white text-black text-xs font-bold px-3 py-1 rounded-full"><i class="fas fa-copy mr-1"></i> Copy</span>
                            </div>
                        </div>
                        
                        <button class="w-full py-4 bg-[#4285F4] text-white font-bold rounded-xl flex items-center justify-center space-x-3 hover:bg-[#3367D6] transition-colors shadow-lg shadow-blue-500/20">
                            <i class="fab fa-google text-xl"></i>
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
                <h2 class="text-3xl sm:text-5xl font-display font-extrabold text-white mb-4">
                    Wall of <span class="text-gradient">Love</span>
                </h2>
                <p class="text-lg text-gray-400">Actual reviews collected by businesses using our platform.</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (count($recentReviews) > 0): ?>
                    <?php foreach ($recentReviews as $index => $review): ?>
                        <div class="glass-card p-6 rounded-2xl flex flex-col h-full" data-aos="fade-up" data-aos-delay="<?= ($index % 3) * 100 ?>">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex space-x-1">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i class="fas fa-star text-sm <?= $i < $review['rating'] ? 'text-yellow-400 drop-shadow-[0_0_5px_rgba(250,204,21,0.5)]' : 'text-gray-700' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="text-xs text-gray-500"><i class="fab fa-google"></i></span>
                            </div>
                            
                            <p class="text-gray-300 text-sm leading-relaxed mb-6 flex-grow italic">
                                "<?= htmlspecialchars($review['body'] ?? 'Outstanding service! Highly recommended.') ?>"
                            </p>
                            
                            <div class="flex items-center space-x-3 pt-4 border-t border-white/10 mt-auto">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-700 to-gray-800 border border-gray-600 flex items-center justify-center font-bold text-gray-300 text-sm">
                                    <?= strtoupper(substr($review['customer_name'] ?? 'A', 0, 1)) ?>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-white"><?= htmlspecialchars($review['customer_name'] ?? 'Anonymous') ?></p>
                                    <p class="text-xs text-brand-400">Reviewed <?= htmlspecialchars($review['business_name']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-20 glass-card rounded-3xl border-dashed border-2 border-white/20">
                        <i class="fas fa-comment-slash text-4xl text-gray-600 mb-4"></i>
                        <h3 class="text-xl font-bold text-white mb-2">No reviews collected yet</h3>
                        <p class="text-gray-400">Sign up and be the first to display your AI-generated reviews here.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-brand-900/20 pointer-events-none"></div>
        <div class="hero-glow opacity-50"></div>
        
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="glass-card rounded-[3rem] p-10 sm:p-20 text-center border border-brand-500/20 shadow-[0_0_100px_rgba(124,58,237,0.15)] relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-b from-brand-600/10 to-transparent"></div>
                
                <div class="relative z-10" data-aos="zoom-in">
                    <h2 class="text-4xl sm:text-6xl font-display font-extrabold text-white mb-6">
                        Ready to skyrocket your <br/> Google ranking?
                    </h2>
                    <p class="text-xl text-gray-400 mb-10 max-w-2xl mx-auto font-light">
                        Join <?= number_format($totalUsers) ?>+ smart businesses dominating their local SEO with an endless stream of 5-star reviews.
                    </p>
                    
                    <a href="<?= url('/signup') ?>" 
                       class="inline-flex items-center px-10 py-5 btn-primary text-white font-bold rounded-2xl shadow-xl hover:scale-105 text-xl transition-all">
                        Create Your Card Free
                        <i class="fas fa-arrow-right ml-3"></i>
                    </a>
                    <p class="text-gray-500 mt-6 text-sm">3-Day Free Trial. No credit card required.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark-900 border-t border-white/5 pt-20 pb-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-12 lg:gap-8 mb-16">
                <div class="col-span-1 md:col-span-5">
                    <a href="<?= url('/') ?>" class="flex items-center space-x-3 mb-6">
                        <div class="w-8 h-8 bg-gradient-to-tr from-brand-600 to-fuchsia-600 rounded-lg flex items-center justify-center">
                            <i class="fas fa-bolt text-white text-sm"></i>
                        </div>
                        <span class="text-xl font-display font-bold text-white tracking-tight"><?= env('APP_NAME', 'NexusReviews') ?></span>
                    </a>
                    <p class="text-gray-400 text-sm leading-relaxed max-w-sm">
                        The world's most advanced AI review collection platform. We help local businesses build unbreakable online reputations through smart automation.
                    </p>
                    <div class="flex space-x-4 mt-6">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-colors"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-colors"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-colors"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-span-1 md:col-span-2 md:col-start-7">
                    <h4 class="font-display font-bold text-white mb-6 uppercase text-sm tracking-wider">Product</h4>
                    <ul class="space-y-4">
                        <li><a href="#features" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Features</a></li>
                        <li><a href="<?= url('/pricing') ?>" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Pricing</a></li>
                        <li><a href="#testimonials" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Wall of Love</a></li>
                    </ul>
                </div>
                
                <div class="col-span-1 md:col-span-2">
                    <h4 class="font-display font-bold text-white mb-6 uppercase text-sm tracking-wider">Company</h4>
                    <ul class="space-y-4">
                        <li><a href="<?= url('/login') ?>" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Sign In</a></li>
                        <li><a href="<?= url('/signup') ?>" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Sign Up</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-brand-400 text-sm transition-colors">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-span-1 md:col-span-2">
                    <h4 class="font-display font-bold text-white mb-6 uppercase text-sm tracking-wider">Legal</h4>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white text-sm transition-colors">Refund Policy</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-white/5 pt-8 flex flex-col md:flex-row items-center justify-between">
                <p class="text-gray-500 text-sm">&copy; <?= date('Y') ?> <?= env('APP_NAME', 'NexusReviews') ?>. All rights reserved.</p>
                <div class="flex items-center space-x-2 mt-4 md:mt-0 text-sm text-gray-500">
                    <span>Designed with</span>
                    <i class="fas fa-heart text-red-500"></i>
                    <span>for local businesses</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
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
                $('#navbar').addClass('shadow-xl shadow-black/20').css('padding', '0.75rem 0');
            } else {
                $('#navbar').removeClass('shadow-xl shadow-black/20').css('padding', '1.5rem 0');
            }
        });
    </script>
</body>
</html>