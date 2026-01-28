<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';
import SweetAlert from '@/Components/SweetAlert.vue';
import SearchFilter from '@/Components/SearchFilter.vue';
import FilterPanel from '@/Components/FilterPanel.vue';

const props = defineProps({
    postings: Object,
    filters: Object,
    categories: Array,
    branches: Array,
    jobTypes: Array,
});

const searchTerm = ref(props.filters.search || '');
const perPage = ref(10);
const filterVisible = ref(false);
const localFilters = ref({
    status: props.filters.status || null,
    branch_id: props.filters.branch_id || null,
    job_category_id: props.filters.job_category_id || null,
});

const handleSearch = () => {
    router.get(route('job-postings.index'), {
        search: searchTerm.value,
        perPage: perPage.value,
        ...localFilters.value
    }, {
        preserveState: true,
        replace: true
    });
};

const filterConfig = [
    {
        name: 'status', label: 'Status', type: 'dropdown', options: [
            { label: 'Draft', value: 'draft' },
            { label: 'Published', value: 'published' },
            { label: 'Closed', value: 'closed' }
        ]
    },
    { name: 'branch_id', label: 'Branch', type: 'dropdown', options: props.branches, optionLabel: 'name', optionValue: 'id' },
    { name: 'job_category_id', label: 'Category', type: 'dropdown', options: props.categories, optionLabel: 'name', optionValue: 'id' }
];

// Modal State
const postingDialog = ref(false);
const submitted = ref(false);
const isEdit = ref(false);
const postingToDelete = ref(null);
const viewDialog = ref(false);
const viewData = ref(null);

// SweetAlert State
const showAlert = ref(false);
const alertConfig = ref({
    title: '',
    message: '',
    type: 'success',
    showCancel: false
});

const form = useForm({
    id: null,
    title: '',
    job_category_id: null,
    branch_id: null,
    job_type: null,
    description: '',
    requirements: '',
    salary_min: null,
    salary_max: null,
    closing_date: null,
    status: 'draft'
});

const statuses = [
    { label: 'Draft', value: 'draft' },
    { label: 'Published', value: 'published' },
    { label: 'Closed', value: 'closed' }
];

const openNew = () => {
    form.reset();
    form.clearErrors();
    submitted.value = false;
    isEdit.value = false;
    postingDialog.value = true;
};

const editPosting = (posting) => {
    form.clearErrors();
    form.id = posting.id;
    form.title = posting.title;
    form.job_category_id = posting.job_category_id;
    form.branch_id = posting.branch_id;
    form.job_type = posting.job_type;
    form.description = posting.description;
    form.requirements = posting.requirements;
    form.salary_min = posting.salary_min;
    form.salary_max = posting.salary_max;
    form.closing_date = posting.closing_date ? new Date(posting.closing_date) : null;
    form.status = posting.status;

    isEdit.value = true;
    postingDialog.value = true;
};

const hideDialog = () => {
    postingDialog.value = false;
    submitted.value = false;
};

const savePosting = () => {
    submitted.value = true;

    if (isEdit.value) {
        form.put(route('job-postings.update', form.id), {
            onSuccess: () => {
                postingDialog.value = false;
                form.reset();
                showSuccessAlert('Job Posting updated successfully');
            },
            onError: () => {
                // Errors handled by Inertia
            }
        });
    } else {
        form.post(route('job-postings.store'), {
            onSuccess: () => {
                postingDialog.value = false;
                form.reset();
                showSuccessAlert('Job Posting created successfully');
            },
            onError: () => {
                // Errors handled by Inertia
            }
        });
    }
};

const openView = (posting) => {
    viewData.value = posting;
    viewDialog.value = true;
};

const confirmDelete = (posting) => {
    postingToDelete.value = posting;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete ${posting.title}? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deletePosting = () => {
    router.delete(route('job-postings.destroy', postingToDelete.value.id), {
        onSuccess: () => {
            postingToDelete.value = null;
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Job Posting has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
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
        deletePosting();
    }
};

const getSeverity = (status) => {
    switch (status) {
        case 'published':
            return 'success';
        case 'draft':
            return 'info';
        case 'closed':
            return 'danger';
        default:
            return 'secondary';
    }
};

</script>

<template>

    <Head title="Recruitment - Job Postings" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Job Postings</h2>
            <Button label="Create Posting" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <SearchFilter v-model="searchTerm" v-model:perPage="perPage" placeholder="Search job postings..."
            @search="handleSearch" @filter="filterVisible = true" />

        <FilterPanel v-model:visible="filterVisible" :config="filterConfig" v-model="localFilters" @apply="handleSearch"
            @reset="handleSearch" />

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="postings.data" removableSort paginator :rows="perPage" tableStyle="min-width: 50rem">
                <Column field="title" header="Title" sortable></Column>
                <Column field="category.name" header="Category" sortable></Column>
                <Column field="branch.name" header="Branch" sortable>
                    <template #body="slotProps">
                        {{ slotProps.data.branch ? slotProps.data.branch.name : 'All Branches' }}
                    </template>
                </Column>
                <Column field="job_type" header="Type" sortable>
                    <template #body="slotProps">
                        {{jobTypes.find(type => type.value === slotProps.data.job_type)?.label ||
                            slotProps.data.job_type}}
                    </template>
                </Column>
                <Column header="Status" style="width: 10%">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getSeverity(slotProps.data.status)" />
                    </template>
                </Column>
                <Column header="Actions" style="width: 15%">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button
                                class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="View" @click="openView(slotProps.data)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-eye">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </Button>
                            <Button
                                class="!bg-green-100 !text-green-600 !border-green-100 hover:!bg-green-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Edit" @click="editPosting(slotProps.data)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-edit">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                            </Button>
                            <Button
                                class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="Delete" @click="confirmDelete(slotProps.data)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="feather feather-trash-2">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path
                                        d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
                                    </path>
                                    <line x1="10" y1="11" x2="10" y2="17"></line>
                                    <line x1="14" y1="11" x2="14" y2="17"></line>
                                </svg>
                            </Button>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create/Edit Modal -->
        <Dialog v-model:visible="postingDialog" :style="{ width: '800px' }"
            :header="isEdit ? 'Edit Job Posting' : 'Create Job Posting'" :modal="true" class="p-fluid">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-full border-b pb-2 mb-2">
                    <h3 class="font-semibold text-gray-600">Job Details</h3>
                </div>

                <div class="col-span-full flex flex-col gap-2">
                    <label for="title" class="font-bold text-sm">Job Title</label>
                    <InputText id="title" v-model="form.title" :invalid="form.errors.title" />
                    <small class="text-red-500" v-if="form.errors.title">{{ form.errors.title }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="category" class="font-bold text-sm">Category</label>
                    <Dropdown id="category" v-model="form.job_category_id" :options="categories" optionLabel="name"
                        optionValue="id" placeholder="Select Category" :invalid="form.errors.job_category_id" filter />
                    <small class="text-red-500" v-if="form.errors.job_category_id">{{ form.errors.job_category_id
                        }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="type" class="font-bold text-sm">Job Type</label>
                    <Dropdown id="type" v-model="form.job_type" :options="jobTypes" optionLabel="label"
                        optionValue="value" placeholder="Select Type" :invalid="form.errors.job_type" />
                    <small class="text-red-500" v-if="form.errors.job_type">{{ form.errors.job_type }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="branch" class="font-bold text-sm">Branch (Optional)</label>
                    <Dropdown id="branch" v-model="form.branch_id" :options="branches" optionLabel="name"
                        optionValue="id" placeholder="All Branches" showClear filter />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="closing_date" class="font-bold text-sm">Closing Date</label>
                    <DatePicker id="closing_date" v-model="form.closing_date" showIcon fluid />
                    <small class="text-red-500" v-if="form.errors.closing_date">{{ form.errors.closing_date }}</small>
                </div>

                <div class="col-span-full flex flex-col gap-2">
                    <label for="description" class="font-bold text-sm">Description</label>
                    <Textarea id="description" v-model="form.description" rows="5" :invalid="form.errors.description" />
                    <small class="text-red-500" v-if="form.errors.description">{{ form.errors.description }}</small>
                </div>

                <div class="col-span-full flex flex-col gap-2">
                    <label for="requirements" class="font-bold text-sm">Requirements</label>
                    <Textarea id="requirements" v-model="form.requirements" rows="5" />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="salary_min" class="font-bold text-sm">Min Salary</label>
                    <InputText id="salary_min" v-model="form.salary_min" type="number" />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="salary_max" class="font-bold text-sm">Max Salary</label>
                    <InputText id="salary_max" v-model="form.salary_max" type="number" />
                </div>

                <div class="flex flex-col gap-2">
                    <label for="status" class="font-bold text-sm">Status</label>
                    <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label"
                        optionValue="value" />
                    <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="savePosting" :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '800px' }" header="Job Posting Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Title</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.title }}</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Category</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.category?.name }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Branch</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.branch ?
                            viewData.branch.name :
                            'All Branches' }}</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Type</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{jobTypes.find(type => type.value
                            ===
                            viewData.job_type)?.label || viewData.job_type}}</div>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Description</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100 whitespace-pre-line">{{
                        viewData.description }}
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Requirements</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100 whitespace-pre-line">{{
                        viewData.requirements }}
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Salary</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{ viewData.salary_min }} - {{
                            viewData.salary_max }}</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Status</label>
                        <div>
                            <Tag :value="viewData.status" :severity="getSeverity(viewData.status)" />
                        </div>
                    </div>
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
