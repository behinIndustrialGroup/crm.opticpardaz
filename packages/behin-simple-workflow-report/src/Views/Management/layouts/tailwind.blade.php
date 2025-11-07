<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'گزارش مدیریتی')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <a href="{{ url()->previous() }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                بازگشت
            </a>
        </div>
        <div class="mb-10">
            <h1 class="text-3xl font-bold text-slate-900">@yield('title', 'گزارش مدیریتی')</h1>
            @hasSection('subtitle')
                <p class="mt-2 text-slate-600">@yield('subtitle')</p>
            @endif
        </div>
        @yield('content')
    </div>
</body>
</html>
