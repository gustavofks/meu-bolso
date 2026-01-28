<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    user: Object
});

// Função para verificar se a rota é a atual (para deixar o botão azul)
const isActive = (route) => {
    return window.location.pathname === route;
};
</script>

<template>
    <aside class="w-64 bg-[#0F172A] text-white flex flex-col justify-between h-full shadow-xl z-10 transition-all duration-300">
        <div>
            <div class="h-20 flex items-center px-6 border-b border-gray-700">
                <span class="font-bold text-lg tracking-wide">Meu Bolso</span>
            </div>

            <nav class="mt-6 px-4 space-y-2">

                <Link href="/"
                    class="w-full flex items-center px-4 py-3 rounded-lg transition-colors group"
                    :class="isActive('/') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white'">

                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    <span class="ml-3 font-medium">Home</span>
                </Link>

                <Link href="/reports"
                    class="w-full flex items-center px-4 py-3 rounded-lg transition-colors group"
                    :class="isActive('/reports') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    <span class="ml-3 font-medium">Relatórios</span>
                </Link>

                <Link href="/cadastros"
                    class="w-full flex items-center px-4 py-3 rounded-lg transition-colors group"
                    :class="isActive('/cadastros') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white'">

                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                    <span class="ml-3 font-medium">Cadastros</span>
                </Link>

                <Link href="/metas"
                    class="w-full flex items-center px-4 py-3 rounded-lg transition-colors group"
                    :class="isActive('/metas') ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' : 'text-gray-400 hover:bg-gray-800 hover:text-white'">

                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <circle cx="12" cy="12" r="6"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                    </svg>
                    <span class="ml-3 font-medium">Metas</span>
                </Link>

            </nav>
        </div>

        <div class="p-4 border-t border-gray-700">
            <div class="flex items-center gap-3 mb-4 px-2">
                <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold">
                    {{ user?.name?.charAt(0) || 'U' }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">{{ user?.name || 'Usuário' }}</p>
                    <p class="text-xs text-gray-400 truncate">Perfil</p>
                </div>
            </div>

            <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center justify-center px-4 py-2 border border-gray-600 rounded-md text-sm text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Sair
            </Link>
        </div>
    </aside>
</template>
