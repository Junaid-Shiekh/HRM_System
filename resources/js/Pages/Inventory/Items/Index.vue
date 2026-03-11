<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import MultiSelect from 'primevue/multiselect';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    items: Array,
    categories: Array,
    suppliers: Array,
});

const showModal = ref(false);
const editingItem = ref(null);

const form = useForm({
    category_id: '',
    item_code: '',
    name: '',
    uom: '',
    min_stock_level: 0,
    reorder_point: 0,
    description: '',
    supplier_ids: [],
});

const openCreate = () => {
    editingItem.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (item) => {
    editingItem.value = item;
    form.category_id = item.category_id;
    form.item_code = item.item_code;
    form.name = item.name;
    form.uom = item.uom;
    form.min_stock_level = item.min_stock_level;
    form.reorder_point = item.reorder_point;
    form.description = item.description;
    form.supplier_ids = item.suppliers?.map(s => s.id) || [];
    showModal.value = true;
};

const submit = () => {
    if (editingItem.value) {
        form.put(route('inventory.items.update', editingItem.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('inventory.items.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteItem = (id) => {
    if (confirm('Are you sure you want to delete this item?')) {
        useForm({}).delete(route('inventory.items.destroy', id));
    }
};
const units = [
    { label: 'Pieces', value: 'Pieces' },
    { label: 'Dozen', value: 'Dozen' },
    { label: 'Box', value: 'Box' },
    { label: 'Kg', value: 'Kg' },
    { label: 'Liter', value: 'Liter' },
    { label: 'Meter', value: 'Meter' },
    { label: 'Packet', value: 'Packet' },
];
</script>

<template>

    <Head title="Item Master" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1C0D82]">Item & Product Master</h2>
                    <p class="text-sm text-gray-500 font-medium">Manage your inventory stock definitions here.</p>
                </div>
                <button @click="openCreate"
                    class="inline-flex items-center px-4 py-2 bg-[#1C0D82] text-white rounded-lg hover:bg-blue-900 transition-all font-semibold shadow-md active:scale-95">
                    <i class="pi pi-plus mr-2"></i>
                    Add New Item
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Code
                                </th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Name &
                                    Category</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest">Unit
                                </th>
                                <th
                                    class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">
                                    Min Stock</th>
                                <th
                                    class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-center">
                                    Reorder Point</th>
                                <th
                                    class="px-6 py-4 text-xs font-black text-gray-400 uppercase tracking-widest text-right">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-black text-[#1C0D82] bg-indigo-50 px-2 py-0.5 rounded">{{
                                        item.item_code }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-black text-gray-900 leading-none">{{ item.name }}</p>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase mt-1">{{ item.category.name
                                        }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-xs font-bold text-gray-600 uppercase">{{ item.uom }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-black text-red-500">{{ item.min_stock_level }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-sm font-black text-[#EAB308]">{{ item.reorder_point }}</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="openEdit(item)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3 transition-colors">
                                        <i class="pi pi-pencil"></i>
                                    </button>
                                    <button @click="deleteItem(item.id)"
                                        class="text-red-500 hover:text-red-700 transition-colors">
                                        <i class="pi pi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Overlay -->
        <div v-if="showModal"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden my-auto" @click.stop>
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-black text-[#1C0D82] uppercase tracking-wider">{{ editingItem ? 'Edit Item' :
                        'Create New Item' }}</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i
                            class="pi pi-times"></i></button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Item
                                Name</label>
                            <input v-model="form.name" type="text" placeholder="e.g., MacBook Pro M3"
                                class="w-full h-10 px-4 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Item
                                Code</label>
                            <input v-model="form.item_code" type="text" placeholder="ITM-001"
                                class="w-full h-10 px-4 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label
                                class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Category</label>
                            <Dropdown v-model="form.category_id" :options="categories" optionLabel="name"
                                optionValue="id" placeholder="Select Category"
                                class="w-full h-10 border border-gray-200 rounded-lg flex items-center px-1 text-sm shadow-sm" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Unit</label>
                            <Dropdown v-model="form.uom" :options="units" optionLabel="label" optionValue="value"
                                placeholder="Select Unit"
                                class="w-full h-10 border border-gray-200 rounded-lg flex items-center px-1 text-sm shadow-sm" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Min Stock
                                Level</label>
                            <input v-model="form.min_stock_level" type="number"
                                class="w-full h-10 px-4 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Reorder
                                Point</label>
                            <input v-model="form.reorder_point" type="number"
                                class="w-full h-10 px-4 rounded-lg border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm text-yellow-600 font-bold">
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Suppliers</label>
                        <MultiSelect v-model="form.supplier_ids" :options="suppliers" optionLabel="name"
                            optionValue="id" placeholder="Select Suppliers" filter display="chip"
                            class="w-full h-10 border border-gray-200 rounded-lg flex items-center px-1" />
                    </div>

                    <div class="space-y-1">
                        <label
                            class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Description</label>
                        <textarea v-model="form.description" rows="3"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm"></textarea>
                    </div>

                    <div class="pt-4 flex justify-end gap-3 border-t border-gray-100">
                        <button type="button" @click="showModal = false"
                            class="px-6 py-2 rounded-lg border border-gray-200 text-gray-600 text-sm font-bold hover:bg-gray-50 transition-colors uppercase tracking-widest">Cancel</button>
                        <button type="submit" :disabled="form.processing"
                            class="px-8 py-2 rounded-lg bg-[#EAB308] text-white text-sm font-black hover:bg-yellow-600 transition-all uppercase tracking-widest shadow-lg shadow-yellow-200 active:scale-95 disabled:opacity-70">
                            {{ editingItem ? 'Update' : 'Save Item' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
