<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { watch, computed } from 'vue';

const props = defineProps({
    show: Boolean,
    type: String,
    transaction: Object, // Se preenchido, modo edição
    categories: Array,
    accounts: Array,
    payment_methods: Array,
    tags: Array
});

const emit = defineEmits(['close']);

const form = useForm({
    description: '',
    amount: '',
    date: new Date().toISOString().split('T')[0],
    type: '',
    category_id: '',
    account_id: '',
    payment_method_id: '',
    tags: []
});

watch(() => props.show, (isVisible) => {
    if (isVisible) {
        // Sempre define o tipo baseado na coluna onde o "+" foi clicado ou no registro original
        form.type = props.type;

        if (props.transaction) {
            // Editar
            form.description = props.transaction.description;
            form.amount = props.transaction.amount;
            form.date = props.transaction.date;
            form.category_id = props.transaction.category_id || '';
            form.account_id = props.transaction.account_id || '';
            form.payment_method_id = props.transaction.payment_method_id || '';
            form.tags = props.transaction.tags ? props.transaction.tags.map(tag => tag.id) : [];
        } else {
            // Registrar
            form.reset();
            form.description = '';
            form.amount = '';
            form.date = new Date().toISOString().split('T')[0];
            form.category_id = '';
            form.account_id = '';
            form.payment_method_id = '';
            form.tags = [];
            // Reatribui o type após o reset
            form.type = props.type;
        }
    }
});

const isEditing = computed(() => !!props.transaction);

const filteredCategories = () => {
    return props.categories ? props.categories.filter(c => c.type === props.type) : [];
};

const toggleTag = (tagId) => {
    const index = form.tags.indexOf(tagId);
    if (index === -1) {
        form.tags.push(tagId);
    } else {
        form.tags.splice(index, 1);
    }
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('transactions.update', props.transaction.id), {
            onSuccess: () => emit('close')
        });
    } else {
        form.post(route('transactions.store'), {
            onSuccess: () => {
                form.reset();
                emit('close');
            }
        });
    }
};

const deleteTransaction = () => {
    if (confirm('Deseja excluir este registro permanentemente?')) {
        router.delete(route('transactions.destroy', props.transaction.id), {
            onSuccess: () => emit('close')
        });
    }
};
</script>

<template>
    <div v-if="show"
         @click.self="$emit('close')"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 transition-opacity cursor-pointer">

        <div class="bg-white rounded-xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto transform transition-all cursor-default">

            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-2xl font-bold flex items-center gap-3"
                    :class="type === 'income' ? 'text-green-600' : 'text-red-600'">

                    <svg v-if="type === 'income'" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline><polyline points="17 18 23 18 23 12"></polyline></svg>

                    {{ isEditing ? 'Editar' : 'Nova' }} {{ type === 'income' ? 'Entrada' : 'Saída' }}
                </h3>

                <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <form @submit.prevent="submit" class="p-8 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Descrição *</label>
                        <input v-model="form.description" type="text" required autofocus class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 transition-shadow" placeholder="Ex: Salário, Mercado...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Valor (R$) *</label>
                        <input v-model="form.amount" type="number" step="0.01" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 transition-shadow" placeholder="0,00">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data *</label>
                        <input v-model="form.date" type="date" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 transition-shadow">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
                        <select v-model="form.category_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">Nenhuma</option> <option v-for="cat in filteredCategories()" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Conta</label>
                        <select v-model="form.account_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">Nenhuma</option> <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Forma de Pagamento</label>
                        <select v-model="form.payment_method_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 bg-white">
                            <option value="">Nenhuma</option>
                            <option v-for="method in payment_methods" :key="method.id" :value="method.id">{{ method.name }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tags Adicionais</label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="tag in tags"
                            :key="tag.id"
                            type="button"
                            @click="toggleTag(tag.id)"
                            class="px-3 py-1.5 rounded-full text-sm font-medium transition-all border"
                            :class="form.tags.includes(tag.id)
                                ? 'bg-blue-100 text-blue-700 border-blue-200 ring-2 ring-blue-500 ring-offset-1'
                                : 'bg-gray-50 text-gray-600 border-gray-200 hover:bg-gray-100'"
                        >
                            {{ tag.name }}
                        </button>
                        <p v-if="tags.length === 0" class="text-sm text-gray-400 italic">Nenhuma tag cadastrada.</p>
                    </div>
                </div>

                <div class="flex gap-4 pt-6 border-t border-gray-100 mt-2">
                    <button v-if="isEditing" type="button" @click="deleteTransaction" class="px-6 py-3 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition-colors font-medium flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        Excluir
                    </button>

                    <button type="button" @click="$emit('close')" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-lg hover:bg-gray-200 font-medium">Cancelar</button>

                    <button type="submit" :disabled="form.processing" class="flex-1 text-white py-3 rounded-lg font-medium shadow-md transition-colors" :class="type === 'income' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">
                        <span v-if="form.processing">Salvando...</span>
                        <span v-else>{{ isEditing ? 'Salvar Alterações' : 'Adicionar' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
