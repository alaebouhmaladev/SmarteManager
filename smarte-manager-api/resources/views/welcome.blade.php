<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartManager</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-900 antialiased">

    <div class="min-h-screen flex flex-col justify-center items-center px-6">

        <div class="mb-6">
            <svg class="w-20 h-20 text-blue-600" viewBox="0 0 80 80" fill="none"
                 xmlns="http://www.w3.org/2000/svg">
                <rect x="8" y="12" width="64" height="48" rx="10"
                      class="fill-blue-50 stroke-blue-500"
                      stroke-width="2" />
                <!-- Bars -->
                <rect x="20" y="40" width="8" height="14" rx="2" class="fill-blue-500" />
                <rect x="34" y="32" width="8" height="22" rx="2" class="fill-blue-400" />
                <rect x="48" y="26" width="8" height="28" rx="2" class="fill-blue-300" />
                <!-- Dot / status -->
                <circle cx="24" cy="24" r="3" class="fill-emerald-500" />
                <!-- Line under bars -->
                <line x1="18" y1="56" x2="62" y2="56" class="stroke-blue-400" stroke-width="1.5" />
            </svg>
        </div>

        <h1 class="text-4xl font-bold text-blue-700 mb-3 text-center">
            SmartManager
        </h1>

        <p class="text-gray-600 text-lg max-w-2xl text-center mb-2">
            All-in-one HR, Time Tracking, Inventory and Expense Manager
            for restaurants, coffee shops and small businesses.
        </p>

        <p class="text-gray-400 text-sm text-center max-w-xl">
            Track employee hours, control your stock, follow supplier costs and
            get a clear overview of your daily performance — in a single dashboard.
        </p>
    </div>

</body>
</html>
