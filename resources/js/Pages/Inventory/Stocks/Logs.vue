<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    logs: Array,
});
</script>

<template>
    <Head title="Stock Movement Logs" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1C0D82]">Stock History & Correction Logs</h2>
                    <p class="text-sm text-gray-500 font-medium">Audit trail of all inventory movements and manual adjustments.</p>
                </div>
                <Link :href="route('inventory.stocks.index')" class="text-xs font-black text-[#1C0D82] hover:underline uppercase tracking-widest flex items-center">
                    <i class="pi pi-arrow-left mr-2"></i>
                    Back to Stocks
                </Link>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Date & Time</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Movement Type</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Warehouse</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Item Detail</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Change Qty</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Reason / Reference</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Performed By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-xs font-black text-gray-900">{{ new Date(log.created_at).toLocaleDateString() }}</span>
                                        <span class="text-[10px] text-gray-400 font-bold uppercase">{{ new Date(log.created_at).toLocaleTimeString() }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                     <span :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tight',
                                        log.type === 'In' ? 'bg-green-100 text-green-700' : 
                                        (log.type === 'Out' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700')
                                    ]">
                                        {{ log.type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-gray-700 uppercase">{{ log.warehouse.name }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-black text-gray-900 leading-none">{{ log.item.name }}</p>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase mt-1">{{ log.item.item_code }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="['text-sm font-black', log.quantity > 0 ? 'text-green-600' : 'text-red-600']">
                                        {{ log.quantity > 0 ? '+' : '' }}{{ log.quantity }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 max-w-xs">
                                    <div class="flex flex-col">
                                         <span class="text-xs font-medium text-gray-700">{{ log.reason || 'N/A' }}</span>
                                         <span v-if="log.reference_type" class="text-[9px] text-[#EAB308] font-black uppercase mt-0.5">{{ log.reference_type }} #{{ log.reference_id }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-xs font-bold text-gray-600">{{ log.user?.name || 'System' }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
