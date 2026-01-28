<script>
import { Head, router, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';

// Função utilitária de debounce (fora do componente para ser pura)
function debounce(fn, delay) {
    let timeoutID = null;
    return function (...args) {
        clearTimeout(timeoutID);
        timeoutID = setTimeout(() => {
            fn.apply(this, args);
        }, delay);
    };
}

export default {
    components: {
        Head,
        Link,
        MainLayout
    },
    props: {
        auth: Object,
        filters: Object,
        activeTab: String,
        reportData: [Object, Array], // Pode ser paginado ou lista
        summary: Object,
        categories: Array,
        accounts: Array,
    },
    data() {
        return {
            form: {
                date_from: this.filters.date_from || '',
                date_to: this.filters.date_to || '',
                type: this.filters.type || 'todos',
                category_id: this.filters.category_id || 'todas',
                account_id: this.filters.account_id || 'todas',
                tab: this.activeTab || 'extrato'
            },
            showExportDropdown: false,
        };
    },
    created() {
        // Cria a versão com delay da função de atualização
        this.debouncedUpdate = debounce(this.updateReport, 500);
    },
    watch: {
        // Observa qualquer mudança no objeto form
        form: {
            handler() {
                this.debouncedUpdate();
            },
            deep: true
        }
    },
    methods: {
        updateReport() {
            router.get(route('reports.index'), this.form, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        },
        changeTab(tabName) {
            this.form.tab = tabName;
            // O watch disparará o updateReport automaticamente
        },
        handleExport() {
            const query = new URLSearchParams(this.form).toString();
            window.open(route('reports.export') + '?' + query, '_blank');
            this.showExportDropdown = false;
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL',
            }).format(value || 0);
        },
        formatDate(dateString) {
            if (!dateString) return '-';
            // Garante que a data seja interpretada corretamente independente do formato
            const date = new Date(dateString);
            return date.toLocaleDateString('pt-BR', { timeZone: 'UTC' });
        }
    }
};
</script>

<template>
    <Head title="Relatórios Financeiros" />

    <MainLayout :user="auth.user">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Relatórios Financeiros</h1>
            <p class="text-gray-600">Análise detalhada das suas finanças</p>
        </div>

        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex gap-8 overflow-x-auto">
                    <button
                        v-for="tab in ['extrato', 'categoria', 'metas']"
                        :key="tab"
                        @click="changeTab(tab)"
                        class="py-4 px-1 border-b-2 font-medium text-sm transition-colors capitalize whitespace-nowrap"
                        :class="form.tab === tab
                            ? 'border-blue-600 text-blue-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    >
                        {{ tab === 'extrato' ? 'Extrato Detalhado' : (tab === 'categoria' ? 'Resumo por Categoria' : 'Histórico de Metas') }}
                    </button>
                </nav>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-2 mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-600"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                <h2 class="text-lg font-bold text-gray-900">Filtros</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="col-span-1 md:col-span-2 grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">De</label>
                        <input type="date" v-model="form.date_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Até</label>
                        <input type="date" v-model="form.date_to" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                </div>

                <template v-if="form.tab !== 'metas'">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                        <select v-model="form.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="todos">Todos</option>
                            <option value="income">Entradas</option>
                            <option value="expense">Saídas</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
                        <select v-model="form.category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="todas">Todas</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Conta</label>
                        <select v-model="form.account_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="todas">Todas</option>
                            <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                        </select>
                    </div>
                </template>
            </div>
        </div>

        <div v-if="form.tab !== 'metas'" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-600">Entradas</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-green-600"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                </div>
                <p class="text-2xl font-bold text-green-600">{{ formatCurrency(summary.income) }}</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-600">Saídas</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>
                </div>
                <p class="text-2xl font-bold text-red-600">{{ formatCurrency(summary.expense) }}</p>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-gray-600">Saldo do Período</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                <p :class="['text-2xl font-bold', summary.balance >= 0 ? 'text-green-600' : 'text-red-600']">
                    {{ formatCurrency(summary.balance) }}
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

            <div class="bg-gray-50 p-4 flex items-center justify-between border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 capitalize">
                    {{ form.tab === 'extrato' ? 'Transações' : (form.tab === 'categoria' ? 'Agrupado por Categoria' : 'Histórico de Metas') }}
                </h3>
                <div class="relative">
                    <button @click="showExportDropdown = !showExportDropdown" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Exportar CSV
                    </button>
                    <div v-if="showExportDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                        <button @click="handleExport" class="block w-full text-left px-4 py-2 hover:bg-gray-100 text-sm text-gray-700">
                            Baixar Relatório Atual
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="form.tab === 'extrato'" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descrição</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Conta</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in reportData.data" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.date) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.category?.name || 'S/ Cat' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.account?.name || 'S/ Conta' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right" :class="item.type === 'income' ? 'text-green-600' : 'text-red-600'">
                                {{ formatCurrency(item.amount) }}
                            </td>
                        </tr>
                        <tr v-if="!reportData.data || !reportData.data.length">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhum registro encontrado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="form.tab === 'categoria'" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="(item, index) in reportData" :key="index" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <span :class="['px-2 py-1 rounded-full text-xs font-semibold', item.type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                                    {{ item.type === 'income' ? 'Entrada' : 'Saída' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right text-gray-900">
                                {{ formatCurrency(item.total) }}
                            </td>
                        </tr>
                        <tr v-if="!reportData || !reportData.length">
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">Nenhum registro encontrado.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="form.tab === 'metas'" class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Meta</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Valor Depositado</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in reportData.data" :key="item.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(item.date) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.meta_name || 'Meta' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right text-blue-600">
                                {{ formatCurrency(item.amount) }}
                            </td>
                        </tr>
                        <tr v-if="!reportData.data || !reportData.data.length">
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">Nenhum registro de meta neste período.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="reportData.links && reportData.links.length > 3" class="bg-gray-50 px-6 py-4 border-t border-gray-200 flex justify-center">
                <div class="flex gap-1">
                    <Link
                        v-for="(link, k) in reportData.links"
                        :key="k"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-3 py-1 border rounded text-sm transition-colors"
                        :class="{
                            'bg-blue-600 text-white border-blue-600': link.active,
                            'bg-white text-gray-700 border-gray-300 hover:bg-gray-50': !link.active,
                            'opacity-50 cursor-not-allowed pointer-events-none': !link.url
                        }"
                    />
                </div>
            </div>
        </div>
    </MainLayout>
</template>
