<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    suppliers: Array,
});

const showModal = ref(false);
const editingSupplier = ref(null);

const form = useForm({
    name: '',
    contact_person: '',
    email: '',
    phone: '',
    address: '',
    supply_details: '',
    rating: 0,
});

const openCreate = () => {
    editingSupplier.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (supplier) => {
    editingSupplier.value = supplier;
    form.name = supplier.name;
    form.contact_person = supplier.contact_person;
    form.email = supplier.email;
    form.phone = supplier.phone;
    form.address = supplier.address;
    form.supply_details = supplier.supply_details;
    form.rating = supplier.rating;
    showModal.value = true;
};

const submit = () => {
    if (editingSupplier.value) {
        form.put(route('inventory.suppliers.update', editingSupplier.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('inventory.suppliers.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteSupplier = (id) => {
    if (confirm('Are you sure you want to delete this supplier?')) {
        useForm({}).delete(route('inventory.suppliers.destroy', id));
    }
};
</script>

<template>

    <Head title="Suppliers" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1C0D82]">Supplier Management</h2>
                    <p class="text-sm text-gray-500 font-medium">Manage your vendors and external partners.</p>
                </div>
                <button @click="openCreate"
                    class="inline-flex items-center px-4 py-2 bg-[#1C0D82] text-white rounded-lg hover:bg-blue-900 transition-all font-semibold shadow-md active:scale-95">
                    <i class="pi pi-plus mr-2"></i>
                    Add Supplier
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div v-for="supplier in suppliers" :key="supplier.id"
                    class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm flex gap-6 group hover:border-[#1C0D82]/30 transition-all">
                    <div
                        class="w-20 h-20 bg-gray-50 rounded-2xl flex items-center justify-center text-[#1C0D82] group-hover:bg-[#1C0D82] group-hover:text-white transition-all shrink-0 border border-gray-100">
                        <i class="pi pi-truck text-3xl"></i>
                    </div>
                    <div class="flex-1 space-y-2">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-black text-gray-900 leading-tight">{{ supplier.name }}</h3>
                                <p class="text-xs text-[#EAB308] font-black uppercase tracking-widest mt-0.5">{{
                                    supplier.contact_person || 'No Contact Person' }}</p>
                            </div>
                            <div class="flex gap-1">
                                <i v-for="i in 5" :key="i" class="pi"
                                    :class="[i <= supplier.rating ? 'pi-star-fill text-[#EAB308]' : 'pi-star text-gray-200']"
                                    style="font-size: 10px;"></i>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1">
                            <div class="flex items-center gap-2 text-[10px] font-bold text-gray-500">
                                <i class="pi pi-envelope text-[10px]"></i> {{ supplier.email || 'N/A' }}
                            </div>
                            <div class="flex items-center gap-2 text-[10px] font-bold text-gray-500">
                                <i class="pi pi-phone text-[10px]"></i> {{ supplier.phone || 'N/A' }}
                            </div>
                            <div class="col-span-2 flex items-center gap-2 text-[10px] font-bold text-gray-400 mt-1">
                                <i class="pi pi-map-marker text-[10px]"></i> {{ supplier.address || 'No address provided.' }}
                            </div>
                            <div v-if="supplier.supply_details"
                                class="col-span-2 flex items-center gap-2 text-[10px] font-bold text-[#EAB308] uppercase tracking-tighter mt-1">
                                <i class="pi pi-box text-[10px]"></i> {{ supplier.supply_details }}
                            </div>
                        </div>
                        <div class="pt-3 flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openEdit(supplier)"
                                class="text-xs font-black text-indigo-600 hover:underline uppercase tracking-widest">Edit</button>
                            <button @click="deleteSupplier(supplier.id)"
                                class="text-xs font-black text-red-500 hover:underline uppercase tracking-widest">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Overlay -->
        <div v-if="showModal"
            class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden" @click.stop>
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-black text-[#1C0D82] uppercase tracking-wider">{{ editingSupplier ? 'Edit Supplier'
                        : 'New Supplier' }}</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i
                            class="pi pi-times"></i></button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Company
                                Name</label>
                            <input v-model="form.name" type="text"
                                class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Contact
                                Person</label>
                            <input v-model="form.contact_person" type="text"
                                class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Email
                                Address</label>
                            <input v-model="form.email" type="email"
                                class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Phone
                                Number</label>
                            <input v-model="form.phone" type="text"
                                class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Rating
                            (1-5)</label>
                        <div class="flex gap-4">
                            <button v-for="i in 5" :key="i" type="button" @click="form.rating = i"
                                class="text-xl transition-all active:scale-75"
                                :class="i <= form.rating ? 'text-[#EAB308]' : 'text-gray-200'">
                                <i :class="i <= form.rating ? 'pi pi-star-fill' : 'pi pi-star'"></i>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Supply Area (What
                            he supplies?)</label>
                        <input v-model="form.supply_details" type="text"
                            placeholder="e.g., Raw Materials, Office Supplies"
                            class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Full
                            Address</label>
                        <textarea v-model="form.address" rows="2"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm"></textarea>
                    </div>
                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing"
                            class="w-full py-3 rounded-xl bg-[#1C0D82] text-white text-sm font-black hover:bg-blue-900 transition-all uppercase tracking-widest shadow-lg shadow-indigo-200 active:scale-95 disabled:opacity-70">
                            {{ editingSupplier ? 'Update Profile' : 'Register Supplier' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
