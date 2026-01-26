<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import TransactionModal from '@/Components/TransactionModal.vue';

const props = defineProps({
    auth: Object,
    dbEntradas: Array,
    dbSaidas: Array,
    totalEntradas: Number,
    totalSaidas: Number,
    saldoTotal: Number,
    categories: Array,
    accounts: Array,
    payment_methods: Array, // Recebido do Controller
    tags: Array,            // Recebido do Controller
    currentPeriod: String
});

// --- Lógica do Modal ---
const showModal = ref(false);
const modalType = ref('income');
const editingTransaction = ref(null); // Guarda a transação sendo editada

// Abre para CRIAR
const openCreateModal = (type) => {
    modalType.value = type;
    editingTransaction.value = null; // Garante que não é edição
    showModal.value = true;
};

// Abre para EDITAR (Ao clicar no item da lista)
const openEditModal = (transaction, type) => {
    modalType.value = type;
    editingTransaction.value = transaction; // Passa os dados para o modal
    showModal.value = true;
};

const setPeriod = (period) => {
    router.get(route('dashboard'), { period }, { preserveState: true, preserveScroll: true });
};

const formatCurrency = (value) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
const formatDate = (dateString) => new Date(dateString).toLocaleDateString('pt-BR');
</script>

<template>
    <Head title="Home" />

    <MainLayout :user="auth.user">

        <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Bem-vindo, {{ auth.user.name }}!</h1>
                <p class="text-gray-500 mt-1">Aqui está o resumo das suas finanças</p>
            </div>
            <div class="bg-white p-1 rounded-lg border border-gray-200 flex shadow-sm">
                <button @click="setPeriod('month')" class="px-4 py-1.5 text-sm font-medium rounded-md transition-all" :class="currentPeriod === 'month' ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50'">Mês Atual</button>
                <button @click="setPeriod('today')" class="px-4 py-1.5 text-sm font-medium rounded-md transition-all" :class="currentPeriod === 'today' ? 'bg-blue-100 text-blue-700 shadow-sm' : 'text-gray-500 hover:bg-gray-50'">Hoje</button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between h-32">
                <span class="text-gray-500 font-medium">Saldo do Período</span>
                <p class="text-3xl font-bold" :class="saldoTotal >= 0 ? 'text-green-600' : 'text-red-600'">{{ formatCurrency(saldoTotal) }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between h-32">
                <span class="text-gray-500 font-medium">Entradas</span>
                <p class="text-3xl font-bold text-green-600">{{ formatCurrency(totalEntradas) }}</p>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col justify-between h-32">
                <span class="text-gray-500 font-medium">Saídas</span>
                <p class="text-3xl font-bold text-red-600">{{ formatCurrency(totalSaidas) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
                <div class="bg-green-600 px-6 py-3 flex items-center justify-between">
                    <h2 class="text-white font-bold text-lg flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        Entradas Recentes
                    </h2>
                    <button @click="openCreateModal('income')" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                </div>
                <div class="p-4 space-y-3">
                    <div v-for="item in dbEntradas" :key="item.id"
                         @click="openEditModal(item, 'income')"
                         class="p-4 border border-gray-100 rounded-lg hover:bg-green-50/50 cursor-pointer transition-colors flex justify-between items-center group">
                        <div>
                            <p class="font-bold text-gray-800 group-hover:text-green-700">{{ item.description }}</p>
                            <p class="text-xs text-gray-500">{{ item.category?.name || 'Sem Categoria' }} • {{ formatDate(item.date) }}</p>
                        </div>
                        <span class="text-green-600 font-bold">{{ formatCurrency(item.amount) }}</span>
                    </div>
                    <p v-if="dbEntradas.length === 0" class="text-center text-gray-400 py-10 italic">Nenhuma entrada.</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full">
                <div class="bg-red-600 px-6 py-3 flex items-center justify-between">
                    <h2 class="text-white font-bold text-lg flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                        Saídas Recentes
                    </h2>
                    <button @click="openCreateModal('expense')" class="bg-white/20 hover:bg-white/30 text-white p-1.5 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    </button>
                </div>
                <div class="p-4 space-y-3">
                    <div v-for="item in dbSaidas" :key="item.id"
                         @click="openEditModal(item, 'expense')"
                         class="p-4 border border-gray-100 rounded-lg hover:bg-red-50/50 cursor-pointer transition-colors flex justify-between items-center group">
                        <div>
                            <p class="font-bold text-gray-800 group-hover:text-red-700">{{ item.description }}</p>
                            <p class="text-xs text-gray-500">{{ item.category?.name || 'Sem Categoria' }} • {{ formatDate(item.date) }}</p>
                        </div>
                        <span class="text-red-600 font-bold">{{ formatCurrency(item.amount) }}</span>
                    </div>
                    <p v-if="dbSaidas.length === 0" class="text-center text-gray-400 py-10 italic">Nenhuma saída.</p>
                </div>
            </div>
        </div>

        <TransactionModal
            :show="showModal"
            :type="modalType"
            :transaction="editingTransaction"
            :categories="categories"
            :accounts="accounts"
            :payment_methods="payment_methods"
            :tags="tags"
            @close="showModal = false"
        />

    </MainLayout>
</template>
