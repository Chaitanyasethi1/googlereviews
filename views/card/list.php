<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . url('/login'));
    exit;
}

// Fetch user's review cards
global $conn;
$stmt = $conn->prepare("
    SELECT rc.*, 
           COUNT(r.id) as review_count,
           AVG(r.rating) as avg_rating
    FROM review_cards rc
    LEFT JOIN reviews r ON rc.id = r.card_id
    WHERE rc.user_id = ?
    GROUP BY rc.id
    ORDER BY rc.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$cards = $stmt->fetchAll();

// Redirect to onboard if no cards
if (empty($cards)) {
    header('Location: ' . url('/onboard'));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>My Dashboard | <?= env("APP_NAME", "Premium App") ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(229, 231, 235, 0.5);
        }
        .animated-bg {
            background: linear-gradient(-45deg, #f3f4f6, #e0e7ff, #f3e8ff, #ffffff);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body class="animated-bg min-h-screen text-gray-800">

    <!-- Premium Navigation -->
    <nav class="glass-nav sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-200">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-gray-900 to-gray-600 tracking-tight">
                        <?= env("APP_NAME", "NexusReviews") ?>
                    </h1>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="hidden md:flex flex-col text-right">
                        <span class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Welcome back</span>
                        <span class="text-sm text-gray-900 font-medium"><?= htmlspecialchars($_SESSION['user_email']) ?></span>
                    </div>
                    <div class="h-8 w-px bg-gray-300"></div>
                    <a href="<?= url('/api/auth?action=logout') ?>" 
                       class="group flex items-center text-sm font-semibold text-gray-600 hover:text-red-600 transition-colors duration-300">
                        <span>Logout</span>
                        <svg class="w-4 h-4 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        
        <!-- Success Message -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="mb-8 transform transition-all duration-500 translate-y-0 opacity-100 flex items-center p-4 text-sm text-emerald-800 border border-emerald-200 bg-emerald-50 rounded-2xl shadow-sm" role="alert">
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="font-medium"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 space-y-4 md:space-y-0">
            <div>
                <h2 class="text-4xl font-extrabold text-gray-900 tracking-tight">Your Dashboard</h2>
                <p class="text-gray-500 mt-2 text-lg">Manage and scale your AI-powered review cards seamlessly.</p>
            </div>
            <a href="<?= url('/onboard') ?>" 
               class="group relative inline-flex items-center justify-center px-8 py-3.5 text-base font-bold text-white transition-all duration-300 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 rounded-full shadow-lg hover:shadow-indigo-500/30 overflow-hidden">
                <span class="absolute w-0 h-0 transition-all duration-500 ease-out bg-white rounded-full group-hover:w-56 group-hover:h-56 opacity-10"></span>
                <svg class="w-5 h-5 mr-2 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span class="relative z-10">Create New Card</span>
            </a>
        </div>

        <!-- Card Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($cards as $card): ?>
                <div class="group glass-card rounded-3xl overflow-hidden hover:-translate-y-2 transition-all duration-400 ease-out hover:shadow-2xl hover:shadow-indigo-100">
                    
                    <!-- Card Header -->
                    <div class="p-8 pb-6 border-b border-gray-100/50">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3 w-3/4">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform duration-300">
                                    <span class="text-2xl font-bold text-indigo-600"><?= strtoupper(substr($card['name'], 0, 1)) ?></span>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 truncate">
                                    <?= htmlspecialchars($card['name']) ?>
                                </h3>
                            </div>
                            <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider rounded-full shadow-sm
                                <?= $card['membership'] === 'trial' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' ?>">
                                <?= ucfirst($card['membership']) ?>
                            </span>
                        </div>
                        
                        <div class="flex items-center text-sm text-gray-500 bg-gray-50/50 rounded-xl p-3 border border-gray-100">
                            <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            <span class="truncate"><?= env('APP_URL') ?>/review/<?= htmlspecialchars($card['slug']) ?></span>
                        </div>
                        
                        <!-- Stats -->
                        <div class="flex space-x-6 mt-6">
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 uppercase font-semibold tracking-wider">Reviews</span>
                                <div class="flex items-center mt-1">
                                    <span class="text-xl font-bold text-gray-900"><?= $card['review_count'] ?></span>
                                </div>
                            </div>
                            <div class="h-10 w-px bg-gray-200"></div>
                            <div class="flex flex-col">
                                <span class="text-xs text-gray-500 uppercase font-semibold tracking-wider">Rating</span>
                                <div class="flex items-center mt-1 space-x-1">
                                    <span class="text-xl font-bold text-gray-900"><?= $card['review_count'] > 0 ? number_format($card['avg_rating'], 1) : '0.0' ?></span>
                                    <svg class="w-5 h-5 text-amber-400 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card Footer Actions -->
                    <div class="px-8 py-5 flex space-x-4 bg-white/50">
                        <a href="<?= url('/review/' . $card['slug']) ?>" 
                           target="_blank"
                           class="flex-1 inline-flex justify-center items-center px-4 py-2.5 text-sm font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 hover:bg-indigo-600 hover:text-white rounded-xl transition-all duration-300">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            View
                        </a>
                        <a href="<?= url('/edit/' . $card['slug']) ?>" 
                           class="flex-1 inline-flex justify-center items-center px-4 py-2.5 text-sm font-bold text-gray-700 bg-white border border-gray-200 hover:border-gray-900 hover:bg-gray-900 hover:text-white rounded-xl transition-all duration-300 shadow-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Manage
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</body>

</html>