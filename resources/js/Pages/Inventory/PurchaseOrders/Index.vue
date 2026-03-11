<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    purchaseOrders: Array,
    suppliers: Array,
    warehouses: Array,
    items: Array,
});

const showCreateModal = ref(false);
const showReceiveModal = ref(false);
const selectedPO = ref(null);

const form = useForm({
    supplier_id: '',
    warehouse_id: '',
    order_date: new Date().toISOString().substr(0, 10),
    expected_delivery_date: '',
    notes: '',
    items: [
        { inventory_item_id: '', quantity: 1, unit_cost: 0 }
    ],
});

const receiveForm = useForm({
    items: {}
});

const addItem = () => {
    form.items.push({ inventory_item_id: '', quantity: 1, unit_cost: 0 });
};

const removeItem = (index) => {
    form.items.splice(index, 1);
};

const submitCreate = () => {
    form.post(route('inventory.purchase-orders.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            form.reset();
        }
    });
};

const openReceive = (po) => {
    selectedPO.value = po;
    const itemsData = {};
    po.items.forEach(item => {
        itemsData[item.id] = { received_quantity: item.quantity - item.received_quantity };
    });
    receiveForm.items = itemsData;
    showReceiveModal.value = true;
};

const submitReceive = () => {
    receiveForm.post(route('inventory.purchase-orders.receive', selectedPO.value.id), {
        onSuccess: () => {
            showReceiveModal.value = false;
        }
    });
};

const approvePO = (id) => {
    if (confirm('Approve this purchase order?')) {
        router.post(route('inventory.purchase-orders.approve', id));
    }
};

const getStatusClass = (status) => {
    switch (status) {
        case 'Pending': return 'bg-orange-100 text-orange-700';
        case 'Approved': return 'bg-blue-100 text-blue-700';
        case 'Received': return 'bg-green-100 text-green-700';
        case 'Cancelled': return 'bg-gray-100 text-gray-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>

<template>
    <Head title="Purchase Orders" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1C0D82]">Purchase Order Management</h2>
                    <p class="text-sm text-gray-500 font-medium">Create and track orders with suppliers.</p>
                </div>
                <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 bg-[#1C0D82] text-white rounded-lg hover:bg-blue-900 transition-all font-semibold shadow-md active:scale-95 uppercase tracking-widest text-xs">
                    <i class="pi pi-plus mr-2"></i>
                    Raise New PO
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">PO Number</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Supplier</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Order Date</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Total Amount</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">Status</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="po in purchaseOrders" :key="po.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-black text-[#1C0D82]">{{ po.po_number }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-bold text-gray-900 leading-none">{{ po.supplier.name }}</p>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase mt-1">Wh: {{ po.warehouse.name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs font-medium text-gray-700">{{ po.order_date }}</p>
                                    <p v-if="po.expected_delivery_date" class="text-[9px] text-orange-500 font-black tracking-tighter uppercase mt-0.5">Exp: {{ po.expected_delivery_date }}</p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="text-sm font-black text-gray-900">${{ po.total_amount }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="['px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tight', getStatusClass(po.status)]">
                                        {{ po.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button v-if="po.status === 'Pending'" @click="approvePO(po.id)" class="text-xs font-black text-blue-600 hover:underline uppercase tracking-widest mr-3">Approve</button>
                                    <button v-if="po.status === 'Approved'" @click="openReceive(po)" class="text-xs font-black text-green-600 hover:underline uppercase tracking-widest mr-3">Receive Items</button>
                                    <button class="text-xs font-black text-gray-400 hover:text-gray-600 uppercase tracking-widest">Details</button>
                                </td>
                            </tr>
                            <tr v-if="purchaseOrders.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">No purchase orders raised yet.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create PO Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-4xl shadow-2xl overflow-hidden" @click.stop>
                 <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-black text-[#1C0D82] uppercase tracking-wider">Raise New Purchase Order</h3>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                </div>
                <form @submit.prevent="submitCreate" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Supplier</label>
                            <select v-model="form.supplier_id" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs transition-all bg-white">
                                <option value="" disabled>Choose Supplier</option>
                                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Target Warehouse</label>
                            <select v-model="form.warehouse_id" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs transition-all bg-white">
                                <option value="" disabled>Choose Warehouse</option>
                                <option v-for="wh in warehouses" :key="wh.id" :value="wh.id">{{ wh.name }}</option>
                            </select>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Order Date</label>
                            <input v-model="form.order_date" type="date" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs transition-all">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Expected Delivery</label>
                            <input v-model="form.expected_delivery_date" type="date" class="w-full h-10 px-3 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs transition-all">
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="mt-6 border border-gray-100 rounded-xl overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase">Item</th>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase w-32">Quantity</th>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase w-32">Unit Cost</th>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase w-32 text-right">Total</th>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase w-16"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="(item, index) in form.items" :key="index">
                                    <td class="px-4 py-3">
                                        <select v-model="item.inventory_item_id" class="w-full h-9 px-2 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs bg-white">
                                            <option value="" disabled>Select internal item...</option>
                                            <option v-for="itm in items" :key="itm.id" :value="itm.id">[{{ itm.item_code }}] {{ itm.name }}</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-3">
                                        <input v-model="item.quantity" type="number" step="0.01" class="w-full h-9 px-2 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input v-model="item.unit_cost" type="number" step="0.01" class="w-full h-9 px-2 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs">
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="text-xs font-black text-gray-700">${{ (item.quantity * item.unit_cost).toFixed(2) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <button type="button" @click="removeItem(index)" v-if="form.items.length > 1" class="text-red-400 hover:text-red-600"><i class="pi pi-trash"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="px-4 py-3 bg-gray-50/50 flex justify-between items-center">
                            <button type="button" @click="addItem" class="text-xs font-black text-[#1C0D82] hover:underline uppercase tracking-widest"><i class="pi pi-plus mr-1"></i> Add Row</button>
                            <div class="text-right">
                                <p class="text-[10px] text-gray-400 uppercase font-black">Estimated Subtotal</p>
                                <p class="text-xl font-black text-[#1C0D82]">${{ form.items.reduce((acc, item) => acc + (item.quantity * item.unit_cost), 0).toFixed(2) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Internal Remarks / Notes</label>
                        <textarea v-model="form.notes" rows="2" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-xs transition-all shadow-sm"></textarea>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-6 py-2 rounded-lg border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-50 transition-colors uppercase tracking-widest">Discard</button>
                        <button type="submit" :disabled="form.processing" class="px-8 py-2 rounded-lg bg-[#EAB308] text-white text-xs font-black hover:bg-yellow-600 transition-all uppercase tracking-widest shadow-lg shadow-yellow-200 active:scale-95 disabled:opacity-70">
                            Confirm PO
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Receive Modal -->
        <div v-if="showReceiveModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden" @click.stop>
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-black text-[#1C0D82] uppercase tracking-wider">Receive Inventory: {{ selectedPO.po_number }}</h3>
                    <button @click="showReceiveModal = false" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                </div>
                <form @submit.prevent="submitReceive" class="p-6 space-y-4">
                    <p class="text-xs text-gray-500 font-medium bg-blue-50 p-3 rounded-lg border border-blue-100 italic">Verify the actual physical quantity received before confirming. This will update real-time stock and create movement logs.</p>
                    
                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase">Item</th>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase text-center w-24">Ordered</th>
                                    <th class="px-4 py-2 text-[10px] font-black text-gray-400 uppercase text-center w-24">Received Prev.</th>
                                    <th class="px-4 py-2 text-[10px] font-black text-[#1C0D82] uppercase text-center w-32 border-l border-indigo-100 bg-indigo-50/30">Now Receiving</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <tr v-for="item in selectedPO.items" :key="item.id">
                                    <td class="px-4 py-3">
                                        <p class="text-xs font-black text-gray-900 leading-none">{{ item.item.name }}</p>
                                        <p class="text-[9px] text-gray-500 font-bold uppercase mt-1">{{ item.item.item_code }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-center text-xs font-bold text-gray-600">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 text-center text-xs font-bold text-gray-400">{{ item.received_quantity }}</td>
                                    <td class="px-4 py-3 border-l border-indigo-100 bg-indigo-50/30">
                                        <input v-model="receiveForm.items[item.id].received_quantity" type="number" step="0.01" class="w-full h-9 px-2 rounded-lg border border-indigo-200 focus:border-[#1C0D82] focus:ring-0 text-xs text-center font-black">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-gray-100">
                        <button type="button" @click="showReceiveModal = false" class="px-6 py-2 rounded-lg border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-50 transition-colors uppercase tracking-widest">Cancel</button>
                        <button type="submit" :disabled="receiveForm.processing" class="px-8 py-2 rounded-lg bg-[#1C0D82] text-white text-xs font-black hover:bg-blue-900 transition-all uppercase tracking-widest shadow-lg active:scale-95 disabled:opacity-70">
                            Confirm Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
