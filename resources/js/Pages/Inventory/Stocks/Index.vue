<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stocks: Array,
    items: Array,
    warehouses: Array,
});

const showAdjustModal = ref(false);
const adjustForm = useForm({
    inventory_item_id: '',
    warehouse_id: '',
    quantity: 0,
    reason: '',
});

const openAdjust = () => {
    adjustForm.reset();
    showAdjustModal.value = true;
};

const submitAdjustment = () => {
    adjustForm.post(route('inventory.stocks.adjust'), {
        onSuccess: () => {
            showAdjustModal.value = false;
            adjustForm.reset();
        }
    });
};
</script>

<template>
    <Head title="Real-time Stock" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1C0D82]">Stock Management</h2>
                    <p class="text-sm text-gray-500 font-medium">Monitor real-time inventory levels across all warehouses.</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('inventory.stocks.logs')" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all font-semibold uppercase tracking-widest text-xs">
                        <i class="pi pi-history mr-2"></i>
                        Stock Logs
                    </Link>
                    <button @click="openAdjust" class="inline-flex items-center px-4 py-2 bg-[#EAB308] text-white rounded-lg hover:bg-yellow-600 transition-all font-semibold shadow-md active:scale-95 uppercase tracking-widest text-xs">
                        <i class="pi pi-sliders-h mr-2"></i>
                        Quick Adjustment
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Item Detail</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Warehouse</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">On Hand Qty</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Threshold Status</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Last Updated</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="stock in stocks" :key="stock.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-black text-gray-900 leading-none">{{ stock.item.name }}</p>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase mt-1">{{ stock.item.category.name }} • {{ stock.item.item_code }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                                            <i class="pi pi-home text-xs"></i>
                                        </div>
                                        <span class="text-xs font-black text-gray-700 uppercase tracking-tight">{{ stock.warehouse.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-lg font-black" :class="stock.quantity <= stock.item.min_stock_level ? 'text-red-600' : 'text-[#1C0D82]'">
                                        {{ stock.quantity }}
                                    </span>
                                    <span class="text-[10px] font-bold text-gray-400 ml-1 uppercase">{{ stock.item.uom }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span v-if="stock.quantity <= stock.item.min_stock_level" class="px-2 py-0.5 bg-red-100 text-red-700 text-[10px] font-black uppercase rounded tracking-wider">
                                        Critical Low
                                    </span>
                                    <span v-else-if="stock.quantity <= stock.item.reorder_point" class="px-2 py-0.5 bg-yellow-100 text-yellow-700 text-[10px] font-black uppercase rounded tracking-wider">
                                        Reorder Point
                                    </span>
                                    <span v-else class="px-2 py-0.5 bg-green-100 text-green-700 text-[10px] font-black uppercase rounded tracking-wider">
                                        Healthy
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs text-gray-500 font-medium">{{ new Date(stock.updated_at).toLocaleString() }}</span>
                                </td>
                            </tr>
                            <tr v-if="stocks.length === 0">
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">No stock records found across warehouses.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Adjustment Modal -->
        <div v-if="showAdjustModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden" @click.stop>
                 <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-black text-[#1C0D82] uppercase tracking-wider">Stock Adjustment</h3>
                    <button @click="showAdjustModal = false" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                </div>
                <form @submit.prevent="submitAdjustment" class="p-6 space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Select Item</label>
                        <select v-model="adjustForm.inventory_item_id" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm bg-white">
                            <option value="" disabled>Choose item...</option>
                            <option v-for="item in items" :key="item.id" :value="item.id">{{ item.item_code }} - {{ item.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Warehouse</label>
                        <select v-model="adjustForm.warehouse_id" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm bg-white">
                            <option value="" disabled>Choose warehouse...</option>
                            <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Adjustment Quantity (+ for in, - for out)</label>
                        <input v-model="adjustForm.quantity" type="number" step="0.01" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm font-black text-[#1C0D82]">
                        <p class="text-[10px] text-gray-400 font-medium italic">Example: Enter 5 to add stock, -3 to remove stock.</p>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Adjustment Reason</label>
                        <textarea v-model="adjustForm.reason" rows="3" placeholder="Explain the adjustment..." class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm"></textarea>
                    </div>
                    <div class="pt-4">
                        <button type="submit" :disabled="adjustForm.processing" class="w-full py-3 rounded-xl bg-[#EAB308] text-white text-sm font-black hover:bg-yellow-600 transition-all uppercase tracking-widest shadow-lg shadow-yellow-200 active:scale-95 disabled:opacity-70">
                            Process Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
