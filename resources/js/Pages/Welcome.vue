<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Bem-vindo" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 text-gray-800">
        <h1 class="text-4xl font-bold mb-6 text-indigo-600">Meu Bolso</h1>
        <p class="mb-8 text-lg">Sistema de Gestão Financeira Pessoal</p>

        <div v-if="canLogin" class="space-x-4">
            <Link
                :href="route('login')"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold shadow"
            >
                Entrar (Login)
            </Link>

            <Link
                v-if="canRegister"
                :href="route('register')"
                class="px-6 py-3 bg-white text-indigo-600 border border-indigo-600 rounded-lg hover:bg-gray-50 font-semibold shadow"
            >
                Criar Conta
            </Link>
        </div>
    </div>
</template>
