<script setup>
import { ref, reactive, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    auth: Object
});

// --- Estado ---
const activeTab = ref('em_andamento');

// Dados iniciais (Mock)
const metas = ref([
    { id: '1', name: 'Playstation 5', valorAlvo: 4000, contaVinculada: 'Caixinha Nubank', valorAcumulado: 2500, status: 'em_andamento' },
    { id: '2', name: 'Viagem para Europa', valorAlvo: 15000, contaVinculada: 'Poupança', valorAcumulado: 15000, status: 'concluida' },
    { id: '3', name: 'Reserva de Emergência', valorAlvo: 10000, contaVinculada: 'Investimento X', valorAcumulado: 6800, status: 'em_andamento' },
]);

// Contas (Mock)
const contas = ['Conta Corrente', 'Poupança', 'Carteira', 'Caixinha Nubank', 'Investimento X'];

// Formulário Nova Meta
const metaForm = reactive({ name: '', valorAlvo: '', contaVinculada: '' });

// Modal Adicionar Valor
const showAddValueModal = ref(null); // Guarda o ID da meta
const valorAdicionar = ref('');
const origemSelecionada = ref('');

// --- Computados ---
const metasEmAndamento = computed(() => metas.value.filter(m => m.status === 'em_andamento'));
const metasConcluidas = computed(() => metas.value.filter(m => m.status === 'concluida'));

const tabs = computed(() => [
    { id: 'em_andamento', label: 'Em Andamento', count: metasEmAndamento.value.length },
    { id: 'concluida', label: 'Concluídas', count: metasConcluidas.value.length },
]);

// --- Ações ---
const handleAddMeta = () => {
    if (!metaForm.name || !metaForm.valorAlvo || !metaForm.contaVinculada) {
        return alert('Preencha todos os campos');
    }

    metas.value.push({
        id: Date.now().toString(),
        name: metaForm.name,
        valorAlvo: parseFloat(metaForm.valorAlvo),
        contaVinculada: metaForm.contaVinculada,
        valorAcumulado: 0,
        status: 'em_andamento'
    });

    // Reset form
    metaForm.name = '';
    metaForm.valorAlvo = '';
    metaForm.contaVinculada = '';
};

const handleDeleteMeta = (id) => {
    if (confirm('Deseja excluir esta meta?')) {
        metas.value = metas.value.filter(m => m.id !== id);
    }
};

const handleAddValue = (metaId) => {
    if (!valorAdicionar.value || parseFloat(valorAdicionar.value) <= 0) return alert('Valor inválido');
    if (!origemSelecionada.value) return alert('Selecione a origem');

    const metaIndex = metas.value.findIndex(m => m.id === metaId);
    if (metaIndex !== -1) {
        const meta = metas.value[metaIndex];
        const novoValor = meta.valorAcumulado + parseFloat(valorAdicionar.value);

        metas.value[metaIndex] = {
            ...meta,
            valorAcumulado: novoValor,
            status: novoValor >= meta.valorAlvo ? 'concluida' : 'em_andamento'
        };
    }

    closeModal();
};

const closeModal = () => {
    showAddValueModal.value = null;
    valorAdicionar.value = '';
    origemSelecionada.value = '';
};

const calcularProgresso = (meta) => {
    return Math.min((meta.valorAcumulado / meta.valorAlvo) * 100, 100);
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};
</script>

<template>
    <Head title="Metas Financeiras" />

    <MainLayout :user="auth.user">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Metas Financeiras</h1>
            <p class="text-gray-500 mt-1">Defina objetivos e acompanhe sua evolução</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

            <div class="lg:col-span-1 h-fit">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Nova Meta
                    </h2>

                    <form @submit.prevent="handleAddMeta" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Meta</label>
                            <input v-model="metaForm.name" type="text" placeholder="Ex: Playstation 5" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Valor Alvo (R$)</label>
                            <input v-model="metaForm.valorAlvo" type="number" step="0.01" placeholder="0,00" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Conta Vinculada</label>
                            <select v-model="metaForm.contaVinculada" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Selecione...</option>
                                <option v-for="conta in contas" :key="conta" :value="conta">{{ conta }}</option>
                            </select>
                        </div>

                        <button type="submit" class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-medium hover:bg-blue-700 transition-colors flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Criar Meta
                        </button>
                    </form>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mt-6">
                    <h3 class="font-bold text-gray-800 mb-4">Resumo</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Total de Metas</span>
                            <span class="font-bold text-gray-900">{{ metas.length }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Em Andamento</span>
                            <span class="font-bold text-orange-600">{{ metasEmAndamento.length }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Concluídas</span>
                            <span class="font-bold text-green-600">{{ metasConcluidas.length }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden min-h-[500px]">

                    <div class="border-b border-gray-100 bg-gray-50/50">
                        <div class="flex">
                            <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                                class="flex-1 py-4 text-sm font-medium transition-all border-b-2 flex items-center justify-center gap-2"
                                :class="activeTab === tab.id ? 'border-blue-600 text-blue-600 bg-white' : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                            >
                                <svg v-if="tab.id === 'em_andamento'" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                <svg v-else xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                {{ tab.label }} ({{ tab.count }})
                            </button>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">

                        <div v-if="(activeTab === 'em_andamento' && metasEmAndamento.length === 0) || (activeTab === 'concluida' && metasConcluidas.length === 0)" class="text-center py-12">
                            <div class="inline-flex bg-gray-100 p-4 rounded-full mb-4">
                                <svg class="text-gray-400" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            </div>
                            <p class="text-gray-500">Nenhuma meta encontrada nesta seção.</p>
                        </div>

                        <div v-for="meta in (activeTab === 'em_andamento' ? metasEmAndamento : metasConcluidas)" :key="meta.id"
                            class="rounded-xl border p-5 transition-shadow hover:shadow-md"
                            :class="meta.status === 'concluida' ? 'bg-gradient-to-r from-green-50 to-emerald-50 border-green-200' : 'bg-white border-gray-100 shadow-sm'"
                        >
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :class="meta.status === 'concluida' ? 'text-green-600' : 'text-blue-600'"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg>
                                        {{ meta.name }}
                                    </h3>
                                    <p class="text-sm text-gray-500 mt-1">Conta: {{ meta.contaVinculada }}</p>
                                </div>
                                <button @click="handleDeleteMeta(meta.id)" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </div>

                            <div v-if="meta.status === 'concluida'">
                                <p class="text-lg font-bold text-green-700 flex items-center gap-2">
                                    {{ formatCurrency(meta.valorAlvo) }} - Meta Atingida! 🎉
                                </p>
                            </div>

                            <div v-else>
                                <div class="flex justify-between text-sm mb-2 font-medium">
                                    <span class="text-gray-600">{{ formatCurrency(meta.valorAcumulado) }} de {{ formatCurrency(meta.valorAlvo) }}</span>
                                    <span class="text-blue-600">{{ calcularProgresso(meta).toFixed(1) }}%</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden mb-4">
                                    <div class="bg-blue-600 h-full rounded-full transition-all duration-500" :style="{ width: `${calcularProgresso(meta)}%` }"></div>
                                </div>
                                <button @click="showAddValueModal = meta.id" class="w-full py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="19" x2="12" y2="5"></line><polyline points="5 12 12 5 19 12"></polyline></svg>
                                    Adicionar Valor
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div v-if="showAddValueModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6 transform transition-all">
                <h3 class="text-xl font-bold text-gray-900 mb-4">Adicionar Valor</h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor a Adicionar</label>
                    <input v-model="valorAdicionar" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" placeholder="0,00" autoFocus>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Origem do Valor</label>
                    <select v-model="origemSelecionada" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Selecione...</option>
                        <option v-for="conta in contas" :key="conta" :value="conta">{{ conta }}</option>
                    </select>
                </div>

                <div class="flex gap-3">
                    <button @click="closeModal" class="flex-1 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">Cancelar</button>
                    <button @click="handleAddValue(showAddValueModal)" class="flex-1 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">Confirmar</button>
                </div>
            </div>
        </div>

    </MainLayout>
</template>
