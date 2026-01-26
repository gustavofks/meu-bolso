<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

// Recebendo os dados do backend
const props = defineProps({
    auth: Object,
    dbCategories: Array,
    dbAccounts: Array,
    dbPaymentMethods: Array,
    dbTags: Array,
});

// --- Estado das Abas ---
const activeTab = ref('categorias');

const tabs = [
    { id: 'categorias', label: 'Categorias' },
    { id: 'pagamentos', label: 'Formas de Pagamento' },
    { id: 'contas', label: 'Contas' },
    { id: 'tags', label: 'Tags' },
];

// --- Lógica: Categorias ---
const categoryForm = useForm({ name: '', type: 'income' });

const handleAddCategory = () => {
    categoryForm.post(route('categories.store'), {
        onSuccess: () => categoryForm.reset(),
    });
};

const handleDeleteCategory = (id) => {
    if (confirm('Excluir categoria?')) {
        router.delete(route('categories.destroy', id));
    }
};

// --- Lógica: Formas de Pagamento ---
const paymentForm = useForm({ name: '' });

const handleAddPayment = () => {
    paymentForm.post(route('payment-methods.store'), {
        onSuccess: () => paymentForm.reset(),
    });
};

const handleDeletePayment = (id) => {
    if (confirm('Excluir forma de pagamento?')) {
        router.delete(route('payment-methods.destroy', id));
    }
};

// --- Lógica: Contas ---
const accountForm = useForm({ name: '' });

const handleAddAccount = () => {
    accountForm.post(route('accounts.store'), {
        onSuccess: () => accountForm.reset(),
    });
};

const handleDeleteAccount = (id) => {
    if (confirm('Excluir conta?')) {
        router.delete(route('accounts.destroy', id));
    }
};

// --- Lógica: Tags ---
const tagForm = useForm({ name: '' });

const handleAddTag = () => {
    tagForm.post(route('tags.store'), {
        onSuccess: () => tagForm.reset(),
    });
};

const handleDeleteTag = (id) => {
    if (confirm('Excluir tag?')) {
        router.delete(route('tags.destroy', id));
    }
};
</script>

<template>
    <Head title="Cadastros" />

    <MainLayout :user="auth.user">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Cadastros</h1>
            <p class="text-gray-500 mt-1">Gerencie suas categorias, contas e classificações</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden min-h-[500px]">

            <div class="border-b border-gray-100 bg-gray-50/50">
                <div class="flex overflow-x-auto">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        class="px-6 py-4 text-sm font-medium transition-all duration-200 border-b-2 flex items-center gap-2 whitespace-nowrap"
                        :class="activeTab === tab.id
                            ? 'border-blue-600 text-blue-600 bg-white'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:bg-gray-100'"
                    >
                        <svg v-if="tab.id === 'categorias'" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                        <svg v-if="tab.id === 'pagamentos'" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        <svg v-if="tab.id === 'contas'" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                        <svg v-if="tab.id === 'tags'" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>

                        {{ tab.label }}
                    </button>
                </div>
            </div>

            <div class="p-6">

                <div v-if="activeTab === 'categorias'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 bg-gray-50 p-6 rounded-xl h-fit">
                        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Nova Categoria
                        </h3>
                        <form @submit.prevent="handleAddCategory">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <button type="button"
                                        @click="categoryForm.type = 'income'"
                                        class="py-2 px-3 rounded-lg border text-sm font-medium flex items-center justify-center gap-2 transition-all"
                                        :class="categoryForm.type === 'income' ? 'bg-green-100 text-green-700 border-green-200' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                                        Entrada
                                    </button>
                                    <button type="button"
                                        @click="categoryForm.type = 'expense'"
                                        class="py-2 px-3 rounded-lg border text-sm font-medium flex items-center justify-center gap-2 transition-all"
                                        :class="categoryForm.type === 'expense' ? 'bg-red-100 text-red-700 border-red-200' : 'bg-white border-gray-200 text-gray-600 hover:bg-gray-50'"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                                        Saída
                                    </button>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                <input v-model="categoryForm.name" type="text" placeholder="Ex: Salário, Mercado..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <div v-if="categoryForm.errors.name" class="text-red-500 text-xs mt-1">{{ categoryForm.errors.name }}</div>
                            </div>
                            <button type="submit" :disabled="categoryForm.processing" class="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50">
                                {{ categoryForm.processing ? 'Salvando...' : 'Adicionar' }}
                            </button>
                        </form>
                    </div>

                    <div class="lg:col-span-2 space-y-3">
                        <div v-if="dbCategories.length === 0" class="text-gray-400 text-center py-8">Nenhuma categoria cadastrada.</div>

                        <div v-for="cat in dbCategories" :key="cat.id" class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-xl shadow-sm hover:border-gray-200 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="p-2 rounded-lg" :class="cat.type === 'income' ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-600'">
                                    <svg v-if="cat.type === 'income'" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                                </div>
                                <span class="font-medium text-gray-800">{{ cat.name }}</span>
                            </div>
                            <button @click="handleDeleteCategory(cat.id)" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'pagamentos'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 bg-gray-50 p-6 rounded-xl h-fit">
                        <h3 class="font-bold text-gray-800 mb-4">Nova Forma de Pagamento</h3>
                        <form @submit.prevent="handleAddPayment">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                <input v-model="paymentForm.name" type="text" placeholder="Ex: PIX, Cartão..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <div v-if="paymentForm.errors.name" class="text-red-500 text-xs mt-1">{{ paymentForm.errors.name }}</div>
                            </div>
                            <button type="submit" :disabled="paymentForm.processing" class="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50">
                                {{ paymentForm.processing ? 'Salvando...' : 'Adicionar' }}
                            </button>
                        </form>
                    </div>
                    <div class="lg:col-span-2 space-y-3">
                        <div v-if="dbPaymentMethods.length === 0" class="text-gray-400 text-center py-8">Nenhuma forma de pagamento cadastrada.</div>

                        <div v-for="method in dbPaymentMethods" :key="method.id" class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-xl shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                </div>
                                <span class="font-medium text-gray-800">{{ method.name }}</span>
                            </div>
                            <button @click="handleDeletePayment(method.id)" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'contas'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 bg-gray-50 p-6 rounded-xl h-fit">
                        <h3 class="font-bold text-gray-800 mb-4">Nova Conta</h3>
                        <form @submit.prevent="handleAddAccount">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Conta</label>
                                <input v-model="accountForm.name" type="text" placeholder="Ex: Carteira, Banco X..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <div v-if="accountForm.errors.name" class="text-red-500 text-xs mt-1">{{ accountForm.errors.name }}</div>
                            </div>
                            <button type="submit" :disabled="accountForm.processing" class="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50">
                                {{ accountForm.processing ? 'Salvando...' : 'Adicionar' }}
                            </button>
                        </form>
                    </div>
                    <div class="lg:col-span-2 space-y-3">
                        <div v-if="dbAccounts.length === 0" class="text-gray-400 text-center py-8">Nenhuma conta cadastrada.</div>

                        <div v-for="acc in dbAccounts" :key="acc.id" class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-xl shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="12" y1="8" x2="12" y2="16"></line><line x1="8" y1="12" x2="16" y2="12"></line></svg>
                                </div>
                                <span class="font-medium text-gray-800">{{ acc.name }}</span>
                            </div>
                            <button @click="handleDeleteAccount(acc.id)" class="text-gray-400 hover:text-red-500 hover:bg-red-50 p-2 rounded-lg transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="activeTab === 'tags'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <div class="lg:col-span-1 bg-gray-50 p-6 rounded-xl h-fit">
                        <h3 class="font-bold text-gray-800 mb-4">Nova Tag</h3>
                        <form @submit.prevent="handleAddTag">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Tag</label>
                                <input v-model="tagForm.name" type="text" placeholder="Ex: Lazer, Fixo..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <div v-if="tagForm.errors.name" class="text-red-500 text-xs mt-1">{{ tagForm.errors.name }}</div>
                            </div>
                            <button type="submit" :disabled="tagForm.processing" class="w-full bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors disabled:opacity-50">
                                {{ tagForm.processing ? 'Salvando...' : 'Adicionar' }}
                            </button>
                        </form>
                    </div>
                    <div class="lg:col-span-2">
                        <div v-if="dbTags.length === 0" class="text-gray-400 text-center py-8">Nenhuma tag cadastrada.</div>

                        <div class="flex flex-wrap gap-2">
                            <div v-for="tag in dbTags" :key="tag.id" class="flex items-center gap-2 px-4 py-2 bg-gray-50 border border-gray-200 rounded-full hover:bg-gray-100 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-500"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                                <span class="font-medium text-gray-700">{{ tag.name }}</span>
                                <button @click="handleDeleteTag(tag.id)" class="ml-1 text-gray-400 hover:text-red-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </MainLayout>
</template>
