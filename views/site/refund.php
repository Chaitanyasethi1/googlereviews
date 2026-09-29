<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Refund Policy - <?= env('APP_NAME', 'ReviewAI') ?></title>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; }
    </style>
</head>
<body>
    <div class="max-w-4xl mx-auto px-4 py-12">
        <a href="<?= url('/') ?>" class="text-indigo-600 hover:underline mb-8 inline-block">&larr; Back to Home</a>
        <h1 class="text-4xl font-bold mb-8">Refund Policy</h1>
        <p class="text-gray-600 mb-6">Last updated: <?= date('F d, Y') ?></p>

        <div class="prose max-w-none text-gray-700 space-y-6">
            <h2 class="text-2xl font-semibold text-gray-900 mt-8">1. Physical Products (NFC Cards)</h2>
            <p>For custom printed NFC Review Cards, refunds are only provided in the following circumstances:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>The physical card arrives damaged or defective.</li>
                <li>The NFC chip is non-functional upon arrival.</li>
            </ul>
            <p>You must notify us within 7 days of receiving the product to be eligible for a replacement or refund. Custom printed items are not eligible for refunds due to change of mind.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">2. Digital Subscriptions</h2>
            <p>We offer a free trial period for you to evaluate our software platform before committing to a paid subscription.</p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Monthly Subscriptions:</strong> Payments for monthly subscriptions are non-refundable. You may cancel at any time to prevent future billing.</li>
                <li><strong>Annual Subscriptions:</strong> If you are dissatisfied with an annual subscription, you may request a prorated refund within the first 14 days of the purchase date.</li>
            </ul>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">3. How to Request a Refund</h2>
            <p>To request a refund, please contact our support team at support@kenettechnologies.com with your order number and the reason for your request. We will review your request and process eligible refunds within 5-7 business days.</p>

            <h2 class="text-2xl font-semibold text-gray-900 mt-8">4. Contact Us</h2>
            <p>If you have any questions about our Refund Policy, please contact us at:</p>
            <p>Kenet Technologies<br>Email: contact@kenettechnologies.com</p>
        </div>
    </div>
</body>
</html>
