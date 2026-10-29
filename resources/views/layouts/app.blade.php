<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strawberry Repair</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-800 font-sans antialiased">

<!-- Главный контейнер: flex на весь экран -->
<div class="flex h-screen overflow-hidden">

    <!-- Левое меню (Sidebar) -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
        <!-- Логотип -->
        <div class="h-16 flex items-center px-6 border-b border-gray-200">
            <span class="text-xl font-bold text-gray-900">Strawberry Repair</span>
        </div>

        <!-- Здесь будет сам компонент меню -->
        <div class="flex-1 overflow-y-auto">
            <livewire:sidebar />
        </div>
    </aside>

    <!-- Рабочая область (Workspace) -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <!-- Здесь будет жить компонент Dashboard -->
        {{ $slot }}
    </main>

</div>

@livewireScripts
</body>
</html>
