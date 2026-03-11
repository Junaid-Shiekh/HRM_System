<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    warehouses: Array,
});

const showModal = ref(false);
const editingWarehouse = ref(null);

const form = useForm({
    name: '',
    location: '',
    description: '',
    is_active: true,
});

const openCreate = () => {
    editingWarehouse.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (wh) => {
    editingWarehouse.value = wh;
    form.name = wh.name;
    form.location = wh.location;
    form.description = wh.description;
    form.is_active = wh.is_active;
    showModal.value = true;
};

const submit = () => {
    if (editingWarehouse.value) {
        form.put(route('inventory.warehouses.update', editingWarehouse.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('inventory.warehouses.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteWarehouse = (id) => {
    if (confirm('Are you sure you want to delete this warehouse?')) {
        useForm({}).delete(route('inventory.warehouses.destroy', id));
    }
};
</script>

<template>
    <Head title="Warehouses" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1C0D82]">Warehouse Master</h2>
                    <p class="text-sm text-gray-500 font-medium">Manage your storage locations and distribution centers.</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center px-4 py-2 bg-[#1C0D82] text-white rounded-lg hover:bg-blue-900 transition-all font-semibold shadow-md active:scale-95 uppercase tracking-widest text-xs">
                    <i class="pi pi-plus mr-2"></i>
                    Add Warehouse
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="wh in warehouses" :key="wh.id" class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition-all group">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-[#1C0D82] group-hover:bg-[#1C0D82] group-hover:text-white transition-colors">
                                <i class="pi pi-home text-2xl"></i>
                            </div>
                            <span :class="['px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tight', wh.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                                {{ wh.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <h3 class="text-lg font-black text-gray-900 leading-tight uppercase tracking-tight">{{ wh.name }}</h3>
                        <p class="text-xs text-[#EAB308] font-black uppercase tracking-widest mt-1"><i class="pi pi-map-marker mr-1 text-[10px]"></i> {{ wh.location || 'GLOBAL REACH' }}</p>
                        <p class="text-xs text-gray-500 font-medium mt-3 line-clamp-2 min-h-[2.5rem]">{{ wh.description || 'Primary storage location for inventory management operations.' }}</p>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-50 flex justify-between items-center mt-auto">
                        <div class="flex items-center gap-1">
                            <i class="pi pi-box text-xs text-gray-400"></i>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ wh.stocks_count }} Items Stocked</span>
                        </div>
                        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openEdit(wh)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded"><i class="pi pi-pencil"></i></button>
                            <button @click="deleteWarehouse(wh.id)" class="p-2 text-red-500 hover:bg-red-50 rounded"><i class="pi pi-trash"></i></button>
                        </div>
                    </div>
                </div>
                
                <button @click="openCreate" class="border-2 border-dashed border-gray-200 p-6 rounded-2xl flex flex-col items-center justify-center text-gray-400 hover:border-[#EAB308] hover:text-[#EAB308] transition-all group min-h-[250px]">
                    <i class="pi pi-building text-3xl mb-3 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-black uppercase tracking-widest">Register New Facility</span>
                </button>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden" @click.stop>
                 <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-black text-[#1C0D82] uppercase tracking-wider">{{ editingWarehouse ? 'Modify Facility' : 'Register Facility' }}</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Facility Name</label>
                        <input v-model="form.name" type="text" placeholder="e.g., Main Distribution Center" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Location / Address</label>
                        <input v-model="form.location" type="text" placeholder="e.g., Block 42, Port Industrial Zone" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Description</label>
                        <textarea v-model="form.description" rows="3" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm"></textarea>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" v-model="form.is_active" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#EAB308]"></div>
                        </label>
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-widest">Operational Status ({{ form.is_active ? 'Active' : 'Inactive' }})</span>
                    </div>
                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing" class="w-full py-3 rounded-xl bg-[#EAB308] text-white text-sm font-black hover:bg-yellow-600 transition-all uppercase tracking-widest shadow-lg active:scale-95 disabled:opacity-70">
                            {{ editingWarehouse ? 'Update Facility' : 'Register Facility' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
