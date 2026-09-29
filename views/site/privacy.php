<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Privacy Policy - <?= env('APP_NAME', 'ReviewAI') ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
    </style>
</head>
<body>
    <div class="max-w-4xl mx-auto px-4 py-12">
        <a href="<?= url('/') ?>" class="text-indigo-600 hover:underline mb-8 inline-block">&larr; Back to Home</a>
        <h1 class="text-4xl font-bold mb-8">Privacy Policy</h1>
        <p class="text-gray-600 mb-6">Last updated: <?= date('F d, Y') ?></p>

        <div class="prose max-w-none text-gray-700 space-y-6">
            <h2 class="text-2xl font-semibold text-gray-900 mt-8">1. Introduction</h2>
            <p>Welcome to <?= env('APP_NAME', 'ReviewAI') ?>, operated by Kenet Technologies. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services. We are committed to protecting your personal data and complying with applicable data protection laws, including the Digital Personal Data Protection (DPDP) Act, 2023 of India.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">2. Information We Collect</h2>
            <p>We believe in data minimization and only collect information necessary to provide our services:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Account Information:</strong> Name, email address, and password for account creation and authentication.</li>
                <li><strong>Business Information:</strong> Google Business Profile details, business name, and logo to generate review cards.</li>
                <li><strong>Usage Data:</strong> Basic analytics such as IP address, browser type, and interaction metrics to improve our platform.</li>
                <li><strong>End-User Feedback:</strong> Reviews, ratings, and feedback submitted through our QR codes or NFC cards on behalf of our business clients.</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">3. How We Use Your Information</h2>
            <p>We process your personal data for the following legitimate purposes:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>To provide, maintain, and improve our review collection services.</li>
                <li>To process your payments and manage your subscriptions.</li>
                <li>To communicate with you regarding updates, support, and security alerts.</li>
                <li>To generate AI-assisted review drafts (using anonymized prompts where possible).</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">4. Data Sharing and Third Parties</h2>
            <p>We do not sell your personal data. We may share information with trusted third-party service providers (e.g., hosting providers, payment processors, analytics tools) solely for the purpose of operating our business. These processors are contractually bound to protect your data.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">5. DPDP Act Rights & Your Choices</h2>
            <p>If you are a resident of India, under the DPDP Act, you have the right to:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Access information about your personal data processing.</li>
                <li>Request correction or erasure of your personal data.</li>
                <li>Withdraw consent at any time (where consent is the basis for processing).</li>
                <li>Nominate another individual to exercise your rights in the event of death or incapacity.</li>
                <li>Grievance redressal regarding data processing.</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">6. Data Security</h2>
            <p>We implement robust technical and organizational security measures to protect your data against unauthorized access, alteration, disclosure, or destruction. However, no internet transmission is completely secure, and we cannot guarantee absolute security.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">7. Cookies and Tracking</h2>
            <p>We use essential cookies to maintain user sessions and secure our platform. We may also use non-essential analytics cookies to understand how our site is used. You can manage your cookie preferences through our cookie consent interface.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">8. Contact Us</h2>
            <p>For any questions or grievances regarding this Privacy Policy or your data rights, please contact our Data Protection Officer at:</p>
            <p>Kenet Technologies<br>Email: contact@kenettechnologies.com</p>
        </div>
    </div>
</body>
</html>
