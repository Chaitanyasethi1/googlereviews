<?php
$page = 'users';

global $conn;

$userId = $_GET['route_params']['id'] ?? null;

if (!$userId || !is_numeric($userId)) {
    header('Location: ' . url('/owner/users'));
    exit;
}

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    ob_start();
    ?>
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center max-w-xl mx-auto my-12">
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">
            <i class="fas fa-user-slash"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 mb-2">User Not Found</h2>
        <p class="text-gray-500 mb-6">The requested user with ID #<?= htmlspecialchars($userId) ?> does not exist or has been removed.</p>
        <a href="<?= url('/owner/users') ?>" class="inline-flex items-center px-5 py-2.5 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </a>
    </div>
    <?php
    $content = ob_get_clean();
    require __DIR__ . '/layout.php';
    exit;
}

// Fetch user's review cards with stats
$stmt = $conn->prepare("
    SELECT rc.*,
           COUNT(DISTINCT r.id) as review_count,
           COUNT(DISTINCT f.id) as feedback_count,
           AVG(r.rating) as avg_rating
    FROM review_cards rc
    LEFT JOIN reviews r ON rc.id = r.card_id
    LEFT JOIN feedbacks f ON rc.id = f.card_id
    WHERE rc.user_id = ?
    GROUP BY rc.id
    ORDER BY rc.created_at DESC
");
$stmt->execute([$userId]);
$userCards = $stmt->fetchAll();

// Fetch recent reviews for user's cards
$stmt = $conn->prepare("
    SELECT r.*, rc.name as card_name, rc.slug as card_slug
    FROM reviews r
    JOIN review_cards rc ON r.card_id = rc.id
    WHERE rc.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$recentReviews = $stmt->fetchAll();

// Fetch recent feedback for user's cards
$stmt = $conn->prepare("
    SELECT f.*, rc.name as card_name, rc.slug as card_slug
    FROM feedbacks f
    JOIN review_cards rc ON f.card_id = rc.id
    WHERE rc.user_id = ?
    ORDER BY f.created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$recentFeedbacks = $stmt->fetchAll();

// Fetch plans for assignment
$stmt = $conn->query("SELECT * FROM plans ORDER BY price ASC");
$plans = $stmt->fetchAll();

// Calculate total stats
$totalCards = count($userCards);
$totalReviews = 0;
$totalFeedbacks = 0;
$ratingsSum = 0;
$ratedCardsCount = 0;

foreach ($userCards as $card) {
    $totalReviews += (int)$card['review_count'];
    $totalFeedbacks += (int)$card['feedback_count'];
    if (!empty($card['avg_rating'])) {
        $ratingsSum += (float)$card['avg_rating'];
        $ratedCardsCount++;
    }
}
$avgRating = $ratedCardsCount > 0 ? round($ratingsSum / $ratedCardsCount, 1) : 0;

ob_start();
?>

<!-- Header & Breadcrumbs -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <div class="flex items-center space-x-2 text-sm text-gray-500 mb-1">
            <a href="<?= url('/owner/users') ?>" class="hover:text-purple-600 transition-colors">Users</a>
            <span>/</span>
            <span class="text-gray-900 font-medium">User #<?= $user['id'] ?></span>
        </div>
        <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
            <span><?= htmlspecialchars($user['email']) ?></span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-mono font-medium bg-gray-100 text-gray-600">ID: #<?= $user['id'] ?></span>
        </h2>
    </div>
    
    <div class="flex items-center space-x-3">
        <a href="<?= url('/owner/users') ?>" class="px-4 py-2 border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors text-sm font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back to Users
        </a>
        <button onclick="openResetPasswordModal(<?= $user['id'] ?>, '<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>')" 
                class="px-4 py-2 bg-purple-600 text-white rounded-xl hover:bg-purple-700 transition-colors text-sm font-medium shadow-sm">
            <i class="fas fa-key mr-2"></i> Reset Password
        </button>
        <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
            <button onclick="deleteUser(<?= $user['id'] ?>, '<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>')" 
                    class="px-4 py-2 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors text-sm font-medium">
                <i class="fas fa-trash mr-1.5"></i> Delete User
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Overview -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Total Cards</p>
            <h3 class="text-2xl font-bold text-gray-900"><?= $totalCards ?></h3>
            <p class="text-xs text-gray-400 mt-1">Review landing pages</p>
        </div>
        <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-xl">
            <i class="fas fa-id-card"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Google Reviews</p>
            <h3 class="text-2xl font-bold text-gray-900"><?= number_format($totalReviews) ?></h3>
            <p class="text-xs text-gray-400 mt-1">Positive 4-5★ clicks</p>
        </div>
        <div class="w-12 h-12 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-xl">
            <i class="fas fa-star"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Private Feedbacks</p>
            <h3 class="text-2xl font-bold text-gray-900"><?= number_format($totalFeedbacks) ?></h3>
            <p class="text-xs text-gray-400 mt-1">Complaints intercepted</p>
        </div>
        <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-xl">
            <i class="fas fa-comment-dots"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm flex items-center justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Member Since</p>
            <h3 class="text-base font-bold text-gray-900"><?= date('M d, Y', strtotime($user['created_at'])) ?></h3>
            <p class="text-xs text-gray-400 mt-1"><?= round((time() - strtotime($user['created_at'])) / (60 * 60 * 24)) ?> days ago</p>
        </div>
        <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-xl">
            <i class="fas fa-calendar-alt"></i>
        </div>
    </div>
</div>

<!-- Review Cards Section -->
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Review Cards</h3>
            <p class="text-xs text-gray-500">Cards created and managed by this user</p>
        </div>
        <span class="px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-semibold">
            <?= $totalCards ?> <?= $totalCards === 1 ? 'Card' : 'Cards' ?>
        </span>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Card Details</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Membership</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviews</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php if (empty($userCards)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            <i class="fas fa-id-card text-gray-300 text-3xl mb-2"></i>
                            <p class="text-sm">This user hasn't created any review cards yet.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($userCards as $card): 
                        $isExpired = $card['expiry_date'] && strtotime($card['expiry_date']) < time();
                        $membershipLower = strtolower($card['membership'] ?? 'trial');
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900"><?= htmlspecialchars($card['name']) ?></div>
                                <a href="<?= url('/review/' . $card['slug']) ?>" target="_blank" class="text-xs text-purple-600 hover:text-purple-800 font-mono inline-flex items-center gap-1 mt-0.5">
                                    /review/<?= htmlspecialchars($card['slug']) ?> <i class="fas fa-external-link-alt text-[10px]"></i>
                                </a>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= 
                                    $membershipLower === 'pro' ? 'bg-purple-100 text-purple-800' : 
                                    ($membershipLower === 'enterprise' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') ?>">
                                    <?= ucfirst($card['membership'] ?? 'trial') ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <?php if ($card['expiry_date']): ?>
                                    <div class="<?= $isExpired ? 'text-red-600 font-medium' : 'text-gray-700' ?>">
                                        <?= date('M d, Y', strtotime($card['expiry_date'])) ?>
                                    </div>
                                    <div class="text-xs <?= $isExpired ? 'text-red-500 font-semibold' : 'text-gray-400' ?>">
                                        <?= $isExpired ? 'Expired' : round((strtotime($card['expiry_date']) - time()) / (60 * 60 * 24)) . ' days left' ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">Never</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                                    <i class="fas fa-star text-[10px] mr-1"></i> <?= $card['review_count'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                                    <i class="fas fa-comment text-[10px] mr-1"></i> <?= $card['feedback_count'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="<?= url('/review/' . $card['slug']) ?>" target="_blank" 
                                       class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="View Public Page">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button onclick="openMembershipModal(<?= $card['id'] ?>, '<?= htmlspecialchars($card['name'], ENT_QUOTES) ?>', '<?= $card['membership'] ?>', '<?= $card['expiry_date'] ?>')" 
                                            class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg transition-colors" title="Change Membership">
                                        <i class="fas fa-crown"></i>
                                    </button>
                                    <button onclick="deleteCard(<?= $card['id'] ?>, '<?= htmlspecialchars($card['name'], ENT_QUOTES) ?>')" 
                                            class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete Card">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Two Column Grid for Recent Reviews & Recent Feedbacks -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
    <!-- Recent Reviews -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-star text-green-500"></i> Recent Reviews
            </h3>
            <span class="text-xs text-gray-400">Latest 10</span>
        </div>
        <div class="divide-y divide-gray-100">
            <?php if (empty($recentReviews)): ?>
                <div class="p-8 text-center text-gray-400 text-sm">No reviews recorded yet</div>
            <?php else: ?>
                <?php foreach ($recentReviews as $review): ?>
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-sm text-gray-900">
                                <?= htmlspecialchars($review['customer_name'] ?: 'Anonymous') ?>
                            </span>
                            <div class="flex text-amber-400 text-xs">
                                <?php for ($s = 1; $s <= 5; $s++): ?>
                                    <i class="fas fa-star <?= $s <= $review['rating'] ? '' : 'text-gray-200' ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Card: <span class="font-medium text-gray-700"><?= htmlspecialchars($review['card_name']) ?></span></span>
                            <span><?= date('M d, Y h:i A', strtotime($review['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Recent Feedbacks -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle text-amber-500"></i> Recent Feedbacks (Issues)
            </h3>
            <span class="text-xs text-gray-400">Latest 10</span>
        </div>
        <div class="divide-y divide-gray-100">
            <?php if (empty($recentFeedbacks)): ?>
                <div class="p-8 text-center text-gray-400 text-sm">No negative feedback recorded</div>
            <?php else: ?>
                <?php foreach ($recentFeedbacks as $fb): ?>
                    <div class="p-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-medium text-sm text-gray-900">
                                <?= htmlspecialchars($fb['customer_name']) ?>
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-red-50 text-red-600">
                                <?= $fb['rating'] ?>★ Rating
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2 italic">"<?= htmlspecialchars($fb['message']) ?>"</p>
                        <div class="flex flex-wrap items-center justify-between text-xs text-gray-500 gap-2">
                            <span>Phone: <a href="tel:<?= htmlspecialchars($fb['phone']) ?>" class="text-purple-600 hover:underline"><?= htmlspecialchars($fb['phone']) ?></a></span>
                            <span><?= date('M d, Y h:i A', strtotime($fb['created_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Reset User Password</h3>
            <button onclick="closeResetPasswordModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <p class="text-sm text-gray-600 mb-4">
            Setting a new password for <span id="resetUserEmail" class="font-bold text-gray-900"></span>
        </p>
        <form id="resetPasswordForm" onsubmit="handleResetPasswordSubmit(event)">
            <input type="hidden" id="resetUserId" name="user_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">New Password *</label>
                <input type="password" id="newPassword" name="new_password" required minlength="6"
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none text-sm"
                       placeholder="Enter at least 6 characters">
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="closeResetPasswordModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">Cancel</button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                    Save New Password
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Membership Modal -->
<div id="membershipModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">Change Membership</h3>
            <button onclick="closeMembershipModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <p class="text-gray-600 mb-4 text-sm">
            Card: <span id="membershipCardName" class="font-bold text-gray-900"></span>
        </p>
        
        <form id="membershipForm" onsubmit="updateMembership(event)">
            <input type="hidden" id="membershipCardId" name="card_id">
            
            <div class="mb-4">
                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Current Membership</label>
                <span id="currentMembership" class="inline-block px-3 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-semibold"></span>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Plan or Preset</label>
                <select id="planSelect" name="plan_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none text-sm">
                    <option value="">Choose a plan or preset...</option>
                    <optgroup label="Quick Presets">
                        <option value="preset_trial_7" data-duration="7" data-name="trial">Free Trial (7 Days)</option>
                        <option value="preset_trial_14" data-duration="14" data-name="trial">Free Trial (14 Days)</option>
                        <option value="preset_trial_30" data-duration="30" data-name="trial">Free Trial (30 Days)</option>
                        <option value="preset_pro_30" data-duration="30" data-name="pro">Pro Monthly (30 Days)</option>
                        <option value="preset_pro_365" data-duration="365" data-name="pro">Pro Yearly (365 Days)</option>
                        <option value="preset_enterprise_365" data-duration="365" data-name="enterprise">Enterprise Yearly (365 Days)</option>
                        <option value="preset_lifetime" data-duration="3650" data-name="enterprise">Lifetime Access (10 Years)</option>
                    </optgroup>
                    <?php if (!empty($plans)): ?>
                        <optgroup label="Saved Subscription Plans">
                            <?php foreach ($plans as $plan): ?>
                                <option value="<?= $plan['id'] ?>" 
                                        data-duration="<?= $plan['duration'] ?>"
                                        data-name="<?= htmlspecialchars($plan['name']) ?>">
                                    <?= htmlspecialchars($plan['name']) ?> - ₹<?= number_format($plan['sale_price'] ?? $plan['price']) ?> (<?= $plan['duration'] ?> days)
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
                <p class="text-xs text-gray-400 mt-1">Select to auto-fill duration and type, or customize manually below.</p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Membership Type *</label>
                <div class="flex gap-2 mb-2">
                    <button type="button" onclick="setMembershipType('trial')" class="px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 transition-colors">Trial</button>
                    <button type="button" onclick="setMembershipType('pro')" class="px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 transition-colors">Pro</button>
                    <button type="button" onclick="setMembershipType('enterprise')" class="px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 hover:bg-purple-100 text-gray-700 hover:text-purple-700 transition-colors">Enterprise</button>
                </div>
                <input type="text" id="membershipType" name="membership" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none text-sm"
                       placeholder="e.g., trial, pro, enterprise">
            </div>
            
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date & Time *</label>
                <input type="datetime-local" id="expiryDate" name="expiry_date" required
                       class="w-full px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 outline-none text-sm mb-2">
                <div class="flex flex-wrap gap-1.5">
                    <span class="text-xs text-gray-500 self-center mr-1">Quick add:</span>
                    <button type="button" onclick="addDaysToExpiry(7)" class="px-2 py-0.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors">+7d</button>
                    <button type="button" onclick="addDaysToExpiry(14)" class="px-2 py-0.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors">+14d</button>
                    <button type="button" onclick="addDaysToExpiry(30)" class="px-2 py-0.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors">+30d</button>
                    <button type="button" onclick="addDaysToExpiry(90)" class="px-2 py-0.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors">+90d</button>
                    <button type="button" onclick="addDaysToExpiry(365)" class="px-2 py-0.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors">+1 Year</button>
                </div>
            </div>
            
            <div class="flex space-x-3">
                <button type="button" onclick="closeMembershipModal()" 
                        class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium">Cancel</button>
                <button type="submit" 
                        class="flex-1 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium shadow-sm hover:shadow">
                    <i class="fas fa-crown mr-1.5"></i> Update Membership
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function formatLocalDateTime(d) {
    const pad = n => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function setMembershipType(type) {
    $('#membershipType').val(type);
}

function addDaysToExpiry(days) {
    const now = new Date();
    now.setDate(now.getDate() + parseInt(days));
    $('#expiryDate').val(formatLocalDateTime(now));
}

function openMembershipModal(cardId, cardName, currentMembership, currentExpiry) {
    $('#membershipCardId').val(cardId);
    $('#membershipCardName').text(cardName);
    const displayMembership = (currentMembership || 'trial');
    $('#currentMembership').text(displayMembership.charAt(0).toUpperCase() + displayMembership.slice(1));
    $('#membershipType').val(displayMembership);
    
    // Set expiry date if exists
    if (currentExpiry && currentExpiry !== '' && currentExpiry !== 'null') {
        const expiry = new Date(currentExpiry.replace(' ', 'T'));
        if (!isNaN(expiry.getTime())) {
            $('#expiryDate').val(formatLocalDateTime(expiry));
        } else {
            const defaultExpiry = new Date();
            defaultExpiry.setDate(defaultExpiry.getDate() + 30);
            $('#expiryDate').val(formatLocalDateTime(defaultExpiry));
        }
    } else {
        const defaultExpiry = new Date();
        defaultExpiry.setDate(defaultExpiry.getDate() + 30);
        $('#expiryDate').val(formatLocalDateTime(defaultExpiry));
    }
    
    $('#planSelect').val('');
    $('#membershipModal').removeClass('hidden').addClass('flex');
}

function closeMembershipModal() {
    $('#membershipModal').removeClass('flex').addClass('hidden');
}

// Auto-fill when plan or preset is selected
$('#planSelect').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    const planName = selectedOption.data('name');
    const duration = selectedOption.data('duration');
    
    if (planName && duration) {
        const nameLower = String(planName).toLowerCase();
        if (nameLower.includes('trial')) {
            $('#membershipType').val('trial');
        } else if (nameLower.includes('enterprise')) {
            $('#membershipType').val('enterprise');
        } else if (nameLower.includes('pro')) {
            $('#membershipType').val('pro');
        } else {
            $('#membershipType').val(nameLower);
        }
        
        const expiryDate = new Date();
        expiryDate.setDate(expiryDate.getDate() + parseInt(duration));
        $('#expiryDate').val(formatLocalDateTime(expiryDate));
    }
});

function updateMembership(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const submitBtn = $(e.target).find('button[type="submit"]');
    const originalHtml = submitBtn.html();
    
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> Updating...');
    
    $.ajax({
        url: '<?= url("/api/owner/cards?action=updateMembership") ?>',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Unknown error'));
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        },
        error: function(xhr) {
            let errorMsg = 'Error updating membership.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                errorMsg = xhr.responseJSON.error;
            }
            alert(errorMsg);
            submitBtn.prop('disabled', false).html(originalHtml);
        }
    });
}

function deleteCard(id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"? This action cannot be undone and will delete all associated reviews and feedbacks.`)) {
        return;
    }
    
    if (!confirm('Please confirm again. This will permanently delete all data.')) {
        return;
    }
    
    $.ajax({
        url: '<?= url("/api/owner/cards?action=delete") ?>',
        method: 'POST',
        data: { id: id },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + (response.error || 'Unknown error'));
            }
        },
        error: function(xhr) {
            alert('Error deleting card.');
        }
    });
}

function openResetPasswordModal(userId, email) {
    $('#resetUserId').val(userId);
    $('#resetUserEmail').text(email);
    $('#newPassword').val('');
    $('#resetPasswordModal').removeClass('hidden').addClass('flex');
}

function closeResetPasswordModal() {
    $('#resetPasswordModal').removeClass('flex').addClass('hidden');
}

function handleResetPasswordSubmit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const submitBtn = $(e.target).find('button[type="submit"]');
    const originalText = submitBtn.text();
    submitBtn.prop('disabled', true).text('Saving...');
    
    $.ajax({
        url: '<?= url("/api/owner/users?action=resetPassword") ?>',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                alert('Password reset successfully!');
                closeResetPasswordModal();
            } else {
                alert('Error: ' + (res.error || 'Failed to reset password'));
            }
            submitBtn.prop('disabled', false).text(originalText);
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.error || 'Failed to reset password');
            submitBtn.prop('disabled', false).text(originalText);
        }
    });
}

function deleteUser(userId, email) {
    if (!confirm(`Are you sure you want to delete user "${email}" (ID: #${userId})? This will permanently delete ALL cards, reviews, and feedbacks belonging to this user!`)) {
        return;
    }
    if (!confirm(`CONFIRM AGAIN: This cannot be undone!`)) {
        return;
    }
    
    $.ajax({
        url: '<?= url("/api/owner/users?action=deleteUser") ?>',
        method: 'POST',
        data: { user_id: userId },
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                alert('User deleted successfully.');
                window.location.href = '<?= url("/owner/users") ?>';
            } else {
                alert('Error: ' + (res.error || 'Failed to delete user'));
            }
        },
        error: function(xhr) {
            alert(xhr.responseJSON?.error || 'Failed to delete user');
        }
    });
}

// Close modals on escape or backdrop click
$(document).keydown(function(e) {
    if (e.key === 'Escape') {
        closeMembershipModal();
        closeResetPasswordModal();
    }
});

$('#membershipModal').click(function(e) {
    if (e.target === this) closeMembershipModal();
});
$('#resetPasswordModal').click(function(e) {
    if (e.target === this) closeResetPasswordModal();
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
