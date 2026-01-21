<!DOCTYPE html>
<html>
<head>
    <title>Choose Subscription Plan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-700 to-purple-700 min-h-screen flex items-center justify-center px-4">

<div class="max-w-5xl w-full bg-white rounded-2xl shadow-2xl p-10">

    <h1 class="text-3xl font-bold text-center text-gray-800 mb-3">
        Choose Your Subscription Plan
    </h1>

    <p class="text-center text-gray-600 mb-10">
        Upgrade to continue using all premium features
    </p>

    <form method="POST" action="{{ route('subscription.subscribe') }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- BASIC -->
            <label class="border rounded-xl p-6 cursor-pointer hover:shadow-lg transition">
                <input type="radio" name="plan" value="basic" class="hidden peer" required>

                <div class="peer-checked:border-indigo-600 peer-checked:ring-2 peer-checked:ring-indigo-600 rounded-xl">
                    <h2 class="text-xl font-semibold text-gray-800">Basic</h2>
                    <p class="text-gray-500 mt-2">For small institutions</p>

                    <p class="mt-4 text-3xl font-bold text-indigo-600">
                        ₹1,999 <span class="text-base text-gray-500">/ year</span>
                    </p>

                    <ul class="mt-4 space-y-2 text-gray-600">
                        <li>✔ Student management</li>
                        <li>✔ Attendance</li>
                        <li>✔ Email support</li>
                    </ul>
                </div>
            </label>

            <!-- STANDARD (POPULAR) -->
            <label class="border-2 border-indigo-600 rounded-xl p-6 cursor-pointer shadow-lg relative">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-indigo-600 text-white text-xs px-3 py-1 rounded-full">
                    MOST POPULAR
                </span>

                <input type="radio" name="plan" value="standard" class="hidden peer">

                <div class="peer-checked:ring-2 peer-checked:ring-indigo-600 rounded-xl">
                    <h2 class="text-xl font-semibold text-gray-800">Standard</h2>
                    <p class="text-gray-500 mt-2">Best value</p>

                    <p class="mt-4 text-3xl font-bold text-indigo-600">
                        ₹2,999 <span class="text-base text-gray-500">/ year</span>
                    </p>

                    <ul class="mt-4 space-y-2 text-gray-600">
                        <li>✔ Everything in Basic</li>
                        <li>✔ Reports & analytics</li>
                        <li>✔ Priority support</li>
                    </ul>
                </div>
            </label>

            <!-- PREMIUM -->
            <label class="border rounded-xl p-6 cursor-pointer hover:shadow-lg transition">
                <input type="radio" name="plan" value="premium" class="hidden peer">

                <div class="peer-checked:border-indigo-600 peer-checked:ring-2 peer-checked:ring-indigo-600 rounded-xl">
                    <h2 class="text-xl font-semibold text-gray-800">Premium</h2>
                    <p class="text-gray-500 mt-2">For large institutions</p>

                    <p class="mt-4 text-3xl font-bold text-indigo-600">
                        ₹4,999 <span class="text-base text-gray-500">/ year</span>
                    </p>

                    <ul class="mt-4 space-y-2 text-gray-600">
                        <li>✔ Everything in Standard</li>
                        <li>✔ Multi-campus support</li>
                        <li>✔ Dedicated support</li>
                    </ul>
                </div>
            </label>

        </div>

        <!-- CTA -->
        <div class="mt-10 text-center">
            <button type="submit"
                class="px-10 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition">
                Activate Subscription
            </button>

            <p class="mt-4 text-sm text-gray-500">
                Secure checkout • Instant activation
            </p>
        </div>

    </form>

</div>

</body>
</html>
