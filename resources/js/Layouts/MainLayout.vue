<script setup>
import Sidebar from '@/Components/Sidebar.vue';

defineProps({
    user: Object
});
</script>

<template>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        <Sidebar :user="user" />

        <div class="flex-1 flex flex-col overflow-hidden">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
