<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

// Definindo props
const props = defineProps({
    auth: Object
});

// Dados falsos para visualização (enquanto não conectamos o Axios/Backend real)
const entradas = [
    { id: 1, description: 'Salário', category: 'Salário', amount: 5000.00, date: '2026-01-14' },
    { id: 2, description: 'Freelance', category: 'Trabalho Extra', amount: 1500.00, date: '2026-01-19' },
    { id: 3, description: 'Investimentos', category: 'Rendimentos', amount: 300.00, date: '2026-01-21' },
];

const saidas = [
    { id: 4, description: 'Aluguel', category: 'Moradia', amount: 1200.00, date: '2026-01-04' },
    { id: 5, description: 'Supermercado', category: 'Alimentação', amount: 450.00, date: '2026-01-09' },
    { id: 6, description: 'Conta de Luz', category: 'Contas', amount: 180.00, date: '2026-01-11' },
    { id: 7, description: 'Internet', category: 'Contas', amount: 99.90, date: '2026-01-14' },
    { id: 8, description: 'Combustível', category: 'Transporte', amount: 250.00, date: '2026-01-22' },
];

// Computados
const totalEntradas = entradas.reduce((acc, t) => acc + t.amount, 0);
const totalSaidas = saidas.reduce((acc, t) => acc + t.amount, 0);
const saldo = totalEntradas - totalSaidas;

// Formatadores
const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('pt-BR');
};
</script>

<template>
    <Head title="Home" />

    <MainLayout :user="auth.user">

        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Bem-vindo, {{ auth.user.name }}!</h1>
            <p class="text-gray-500 mt-1">Aqui está o resumo das suas finanças</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between h-32">
                <div class="flex justify-between items-start">
                    <span class="text-gray-500 font-medium">Saldo Total</span>
                    <span class="text-blue-500 bg-blue-50 p-1 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold" :class="saldo >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ formatCurrency(saldo) }}
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between h-32">
                <div class="flex justify-between items-start">
                    <span class="text-gray-500 font-medium">Total Entradas</span>
                    <span class="text-green-500 bg-green-50 p-1 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-green-600">
                    {{ formatCurrency(totalEntradas) }}
                </p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between h-32">
                <div class="flex justify-between items-start">
                    <span class="text-gray-500 font-medium">Total Saídas</span>
                    <span class="text-red-500 bg-red-50 p-1 rounded">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    </span>
                </div>
                <p class="text-3xl font-bold text-red-600">
                    {{ formatCurrency(totalSaidas) }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
                <div class="bg-green-600 px-6 py-3 flex items-center gap-2">
                    <svg class="text-white" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <h2 class="text-white font-bold text-lg">Entradas</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div v-for="item in entradas" :key="item.id" class="p-4 border border-gray-100 rounded-lg bg-white hover:bg-gray-50 transition-colors flex justify-between items-center shadow-sm">
                        <div>
                            <p class="font-bold text-gray-800">{{ item.description }}</p>
                            <p class="text-xs text-gray-500">{{ item.category }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatDate(item.date) }}</p>
                        </div>
                        <span class="text-green-600 font-bold">{{ formatCurrency(item.amount) }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
                <div class="bg-red-600 px-6 py-3 flex items-center gap-2">
                    <svg class="text-white" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                    <h2 class="text-white font-bold text-lg">Saídas</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div v-for="item in saidas" :key="item.id" class="p-4 border border-gray-100 rounded-lg bg-white hover:bg-gray-50 transition-colors flex justify-between items-center shadow-sm">
                        <div>
                            <p class="font-bold text-gray-800">{{ item.description }}</p>
                            <p class="text-xs text-gray-500">{{ item.category }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ formatDate(item.date) }}</p>
                        </div>
                        <span class="text-red-600 font-bold">{{ formatCurrency(item.amount) }}</span>
                    </div>
                </div>
            </div>

        </div>
    </MainLayout>
</template>
