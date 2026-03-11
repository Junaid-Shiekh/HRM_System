<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    transfers: Array,
    warehouses: Array,
    items: Array,
});

const showModal = ref(false);
const form = useForm({
    from_warehouse_id: '',
    to_warehouse_id: '',
    transfer_date: new Date().toISOString().substr(0, 10),
    notes: '',
    items: [
        { inventory_item_id: '', quantity: 1 }
    ],
});

const addItem = () => {
    form.items.push({ inventory_item_id: '', quantity: 1 });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submitTransfer = () => {
    form.post(route('inventory.transfers.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

const completeTransfer = (id) => {
    if (confirm('Verify that items have been physically moved and complete transfer?')) {
        router.post(route('inventory.transfers.complete', id));
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'Pending': return 'bg-orange-100 text-orange-700';
        case 'Completed': return 'bg-green-100 text-green-700';
        case 'Cancelled': return 'bg-gray-100 text-gray-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>

<template>
    <Head title="Warehouse Transfers" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1C0D82]">Inter-Warehouse Transfers</h2>
                    <p class="text-sm text-gray-500 font-medium">Relocate stock between different facility storage zones.</p>
                </div>
                <button @click="showModal = true" class="inline-flex items-center px-4 py-2 bg-[#1C0D82] text-white rounded-lg hover:bg-blue-900 transition-all font-semibold shadow-md active:scale-95 uppercase tracking-widest text-xs">
                    <i class="pi pi-arrow-right-arrow-left mr-2"></i>
                    Initiate Transfer
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Ref Number</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Movement</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Transfer Date</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Items Count</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="tr in transfers" :key="tr.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-black text-[#1C0D82]">{{ tr.transfer_number }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-gray-700 uppercase">{{ tr.from_warehouse.name }}</span>
                                        <i class="pi pi-arrow-right text-[10px] text-[#EAB308]"></i>
                                        <span class="text-xs font-bold text-gray-900 uppercase">{{ tr.to_warehouse.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-medium text-gray-600">{{ tr.transfer_date }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs font-black text-gray-900 bg-gray-100 px-2 py-0.5 rounded-full">{{ tr.items.length }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="['px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tight', getStatusClass(tr.status)]">
                                        {{ tr.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button v-if="tr.status === 'Pending'" @click="completeTransfer(tr.id)" class="text-xs font-black text-green-600 hover:underline uppercase tracking-widest mr-3">Complete</button>
                                    <button class="text-xs font-black text-gray-400 hover:text-gray-600 uppercase tracking-widest">Details</button>
                                </td>
                            </tr>
                            <tr v-if="transfers.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">No transfers initiated yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Transfer Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden" @click.stop>
                 <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-black text-[#1C0D82] uppercase tracking-wider">Inter-Warehouse Stock Transfer</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                </div>
                <form @submit.prevent="submitTransfer" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Source Warehouse (FROM)</label>
                            <select v-model="form.from_warehouse_id" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all bg-white">
                                <option value="" disabled>Select origin...</option>
                                <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Destination (TO)</label>
                            <select v-model="form.to_warehouse_id" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all bg-white">
                                <option value="" disabled>Select destination...</option>
                                <option v-for="wh in warehouses" :key="wh.id" :value="wh.id" :disabled="wh.id == form.from_warehouse_id">{{ wh.name }}</option>
                            </select>
                        </div>
                         <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Transfer Date</label>
                            <input v-model="form.transfer_date" type="date" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all">
                        </div>
                    </div>

                    <div class="mt-6 border border-gray-100 rounded-xl overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase">Inventory Item</th>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase text-center w-40">Transfer Qty</th>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase w-16"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="(item, index) in form.items" :key="index">
                                    <td class="px-4 py-3">
                                        <select v-model="item.inventory_item_id" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs bg-white">
                                            <option value="" disabled>Select item to move...</option>
                                            <option v-for="itm in items" :key="itm.id" :value="itm.id">[{{ itm.item_code }}] {{ itm.name }}</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input v-model="item.quantity" type="number" step="0.01" class="w-32 h-10 px-3 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs text-center font-black">
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" @click="removeItem(index)" v-if="form.items.length > 1" class="text-red-400 hover:text-red-600"><i class="pi pi-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="px-4 py-2 bg-gray-50/50">
                            <button type="button" @click="addItem" class="text-xs font-black text-[#1C0D82] hover:underline uppercase tracking-widest"><i class="pi pi-plus mr-1"></i> Add Another Item</button>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Movement Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs transition-all shadow-sm"></textarea>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-gray-100">
                        <button type="button" @click="showModal = false" class="px-6 py-2 rounded-lg border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-50 transition-colors uppercase tracking-widest">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="px-8 py-2 rounded-lg bg-[#EAB308] text-white text-xs font-black hover:bg-yellow-600 transition-all uppercase tracking-widest shadow-lg active:scale-95 disabled:opacity-70">
                            Initiate Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
