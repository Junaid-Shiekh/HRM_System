<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';
import SweetAlert from '@/Components/SweetAlert.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    candidates: Object,
    filters: Object,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({});

// SweetAlert State
const showAlert = ref(false);
const alertConfig = ref({
    title: '',
    message: '',
    type: 'success',
    showCancel: false
});

const viewDialog = ref(false);
const viewData = ref(null);

const handleSearch = () => {
    router.get(route('candidates.index'), {
        search: searchTerm.value,
        perPage: perPage.value,
        ...localFilters.value
    }, {
        preserveState: true,
        replace: true
    });
};

const openView = (candidate) => {
    viewData.value = candidate;
    viewDialog.value = true;
};

const openNew = () => {
    form.reset();
    form.clearErrors();
    submitted.value = false;
    isEdit.value = false;
    candidateDialog.value = true;
};

const editCandidate = (candidate) => {
    form.clearErrors();
    form.id = candidate.id;
    form.first_name = candidate.first_name;
    form.last_name = candidate.last_name;
    form.email = candidate.email;
    form.phone = candidate.phone;
    form.cover_letter = candidate.cover_letter;

    isEdit.value = true;
    candidateDialog.value = true;
};

const hideDialog = () => {
    candidateDialog.value = false;
    submitted.value = false;
};

const saveCandidate = () => {
    submitted.value = true;

    if (isEdit.value) {
        form.put(route('candidates.update', form.id), {
            onSuccess: () => {
                candidateDialog.value = false;
                form.reset();
                showSuccessAlert('Candidate updated successfully');
            }
        });
    } else {
        form.post(route('candidates.store'), {
            onSuccess: () => {
                candidateDialog.value = false;
                form.reset();
                showSuccessAlert('Candidate created successfully');
            }
        });
    }
};

const showSuccessAlert = (message) => {
    alertConfig.value = {
        title: 'Success!',
        message: message,
        type: 'success',
        showCancel: false
    };
    showAlert.value = true;
};

const handleAlertConfirm = () => {
    if (alertConfig.value.showCancel && alertConfig.value.type === 'warning') {
        deleteCandidate();
    }
};

const confirmDeleteCandidate = (candidate) => {
    form.id = candidate.id;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${candidate.first_name} ${candidate.last_name}? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteCandidate = () => {
    form.delete(route('candidates.destroy', form.id), {
        onSuccess: () => {
            form.reset();
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Candidate has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};
</script>

<template>

    <Head title="Recruitment - Candidates" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Candidates</h2>
            <Button label="Add Candidate" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search candidates..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="[]" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="candidates.data" removableSort paginator :rows="perPage" tableStyle="min-width: 50rem">
                <Column field="first_name" header="Name" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.first_name }} {{ slotProps.data.last_name }}
                    </template>
                </Column>
                <Column field="email" header="Email" sortable></Column>
                <Column field="phone" header="Phone"></Column>
                <Column field="applications_count" header="Applications" sortable></Column>
                <Column header="Actions" style="width: 15%">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button
                                class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="View" @click="openView(slotProps.data)">
                                <i class="pi pi-eye"></i>
                            </Button>
                            <Button
                                class="!bg-orange-100 !text-orange-600 !border-orange-100 hover:!bg-orange-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Edit" @click="editCandidate(slotProps.data)">
                                <i class="pi pi-pencil"></i>
                            </Button>
                            <Button
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete" @click="confirmDeleteCandidate(slotProps.data)">
                                <i class="pi pi-trash"></i>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:visible="candidateDialog" :style="{ width: '450px' }"
            :header="isEdit ? 'Edit Candidate' : 'New Candidate'" :modal="true" class="p-fluid">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="first_name" class="font-bold">First Name</label>
                    <InputText id="first_name" v-model.trim="form.first_name" required="true" autofocus
                        :invalid="submitted && !form.first_name" />
                    <small class="text-red-500" v-if="form.errors.first_name">{{ form.errors.first_name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="last_name" class="font-bold">Last Name</label>
                    <InputText id="last_name" v-model.trim="form.last_name" required="true"
                        :invalid="submitted && !form.last_name" />
                    <small class="text-red-500" v-if="form.errors.last_name">{{ form.errors.last_name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="email" class="font-bold">Email</label>
                    <InputText id="email" v-model.trim="form.email" required="true"
                        :invalid="submitted && !form.email" />
                    <small class="text-red-500" v-if="form.errors.email">{{ form.errors.email }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="phone" class="font-bold">Phone</label>
                    <InputText id="phone" v-model.trim="form.phone" />
                </div>
                <!-- Resume Upload would go here, keep simple for now -->
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveCandidate" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '500px' }" header="Candidate Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Name</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100 font-semibold">{{ viewData.first_name }}
                        {{
                            viewData.last_name }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Email</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.email }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Phone</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.phone || '-' }}</div>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Cover Letter</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100 min-h-[100px] whitespace-pre-line">{{
                        viewData.cover_letter || '-' }}</div>
                </div>
            </div>
            <template #footer>
                <Button label="Close" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="viewDialog = false" />
            </template>
        </Dialog>


        <!-- SweetAlert -->
        <SweetAlert v-model:visible="showAlert" :title="alertConfig.title" :message="alertConfig.message"
            :type="alertConfig.type" @confirm="handleAlertConfirm" />
    </AuthenticatedLayout>
</template>
