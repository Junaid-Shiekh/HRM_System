<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    categories: Array,
});

const showModal = ref(false);
const editingCategory = ref(null);

const form = useForm({
    name: '',
    description: '',
});

const openCreate = () => {
    editingCategory.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.description = category.description;
    showModal.value = true;
};

const submit = () => {
    if (editingCategory.value) {
        form.put(route('inventory.categories.update', editingCategory.value.id), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('inventory.categories.store'), {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            }
        });
    }
};

const deleteCategory = (id) => {
    if (confirm('Are you sure you want to delete this category?')) {
        useForm({}).delete(route('inventory.categories.destroy', id));
    }
};
</script>

<template>
    <Head title="Categories" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1C0D82]">Inventory Categories</h2>
                    <p class="text-sm text-gray-500 font-medium">Group your items by classification.</p>
                </div>
                <button @click="openCreate" class="inline-flex items-center px-4 py-2 bg-[#1C0D82] text-white rounded-lg hover:bg-blue-900 transition-all font-semibold shadow-md active:scale-95">
                    <i class="pi pi-plus mr-2"></i>
                    New Category
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="cat in categories" :key="cat.id" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-[#1C0D82] group-hover:bg-[#1C0D82] group-hover:text-white transition-colors">
                            <i class="pi pi-tag text-xl"></i>
                        </div>
                        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button @click="openEdit(cat)" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded"><i class="pi pi-pencil"></i></button>
                            <button @click="deleteCategory(cat.id)" class="p-2 text-red-500 hover:bg-red-50 rounded"><i class="pi pi-trash"></i></button>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-black text-gray-900 leading-tight">{{ cat.name }}</h3>
                        <p class="text-xs text-gray-500 font-medium mt-1 min-h-[3rem] line-clamp-2">{{ cat.description || 'No description provided.' }}</p>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-50 flex justify-between items-center">
                        <span class="text-[10px] font-black uppercase text-gray-400 tracking-widest">Total Items linked</span>
                        <span class="text-sm font-black text-[#1C0D82]">{{ cat.items_count }}</span>
                    </div>
                </div>
                
                <button @click="openCreate" class="border-2 border-dashed border-gray-200 p-6 rounded-2xl flex flex-col items-center justify-center text-gray-400 hover:border-[#EAB308] hover:text-[#EAB308] transition-all group">
                    <i class="pi pi-plus text-2xl mb-2 group-hover:scale-110 transition-transform"></i>
                    <span class="text-sm font-bold uppercase tracking-widest">Add New Category</span>
                </button>
            </div>
        </div>

        <!-- Modal Overlay -->
        <div v-if="showModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden" @click.stop>
                 <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="font-black text-[#1C0D82] uppercase tracking-wider">{{ editingCategory ? 'Edit Category' : 'Create Category' }}</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600"><i class="pi pi-times"></i></button>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Category Name</label>
                        <input v-model="form.name" type="text" placeholder="e.g., Electronics, Stationery" class="w-full h-11 px-4 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Description</label>
                        <textarea v-model="form.description" placeholder="Optional description..." rows="4" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#1C0D82] focus:ring-0 text-sm transition-all shadow-sm"></textarea>
                    </div>
                    <div class="pt-4">
                        <button type="submit" :disabled="form.processing" class="w-full py-3 rounded-xl bg-[#EAB308] text-white text-sm font-black hover:bg-yellow-600 transition-all uppercase tracking-widest shadow-lg shadow-yellow-200 active:scale-95 disabled:opacity-70">
                            {{ editingCategory ? 'Update' : 'Save Category' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
