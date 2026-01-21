<!DOCTYPE html>
<html>
<head>
    <title>Subscription Required</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-700 to-purple-700">
    <div class="bg-white rounded-2xl shadow-2xl p-10 max-w-md text-center">
        <div class="text-6xl mb-4">🔒</div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            Subscription Expired
        </h1>

        <p class="text-gray-600 mb-6">
            Your free trial has ended. Please purchase a subscription to continue using the application.
        </p>

        <a href="{{ url('/subscription/plans') }}"
           class="block w-full py-3 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition">
            Purchase Subscription
        </a>

        <p class="text-sm text-gray-400 mt-4">
            Need help? Contact support
        </p>
    </div>
</div>
</body>
</html>
