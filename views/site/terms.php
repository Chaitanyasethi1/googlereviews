<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Terms & Conditions - <?= env('APP_NAME', 'ReviewAI') ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
    </style>
</head>
<body>
    <div class="max-w-4xl mx-auto px-4 py-12">
        <a href="<?= url('/') ?>" class="text-indigo-600 hover:underline mb-8 inline-block">&larr; Back to Home</a>
        <h1 class="text-4xl font-bold mb-8">Terms & Conditions</h1>
        <p class="text-gray-600 mb-6">Last updated: <?= date('F d, Y') ?></p>

        <div class="prose max-w-none text-gray-700 space-y-6">
            <h2 class="text-2xl font-semibold text-gray-900 mt-8">1. Acceptance of Terms</h2>
            <p>By accessing or using <?= env('APP_NAME', 'ReviewAI') ?> (the "Service") provided by Kenet Technologies, you agree to be bound by these Terms & Conditions. If you disagree with any part of the terms, you may not access the Service.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">2. Use of Service</h2>
            <p>You agree to use the Service only for lawful purposes and in accordance with these Terms. You are responsible for maintaining the confidentiality of your account and password.</p>
            <p>You agree NOT to use the Service to:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Violate any applicable laws, including but not limited to the DPDP Act of India or Google's Terms of Service.</li>
                <li>Generate fake, misleading, or paid reviews on Google or any other platform.</li>
                <li>Engage in "review gating" (selectively soliciting positive reviews while preventing negative ones from being posted publicly), as this violates Google's policies.</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">3. AI-Generated Content</h2>
            <p>The Service provides AI-generated suggestions for review content. These are suggestions only. The end-user remains solely responsible for the content of the reviews they choose to post on third-party platforms.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">4. Subscriptions and Payments</h2>
            <p>Some features of the Service are billed on a subscription basis. You will be billed in advance on a recurring and periodic basis. Payments are non-refundable except as stated in our Refund Policy.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">5. Intellectual Property</h2>
            <p>The Service and its original content, features, and functionality are and will remain the exclusive property of Kenet Technologies and its licensors.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">6. Limitation of Liability</h2>
            <p>In no event shall Kenet Technologies, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your access to or use of or inability to access or use the Service.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">7. Governing Law</h2>
            <p>These Terms shall be governed and construed in accordance with the laws of India, without regard to its conflict of law provisions.</p>
        </div>
    </div>
</body>
</html>
