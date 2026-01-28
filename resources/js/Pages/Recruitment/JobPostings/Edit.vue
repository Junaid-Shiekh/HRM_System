<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import DatePicker from 'primevue/datepicker';
import Textarea from 'primevue/textarea';

const props = defineProps({
    posting: Object,
    categories: Array,
    branches: Array,
    jobTypes: Array,
});

const form = useForm({
    title: props.posting.title,
    job_category_id: props.posting.job_category_id,
    branch_id: props.posting.branch_id,
    job_type: props.posting.job_type,
    description: props.posting.description,
    requirements: props.posting.requirements,
    salary_min: props.posting.salary_min,
    salary_max: props.posting.salary_max,
    closing_date: props.posting.closing_date ? new Date(props.posting.closing_date) : null,
    status: props.posting.status
});

const statuses = [
    { label: 'Draft', value: 'draft' },
    { label: 'Published', value: 'published' },
    { label: 'Closed', value: 'closed' }
];

const submit = () => {
    form.put(route('job-postings.update', props.posting.id));
};
</script>

<template>
    <Head title="Edit Job Posting" />

    <AuthenticatedLayout>
        <div class="max-w-4xl mx-auto">
             <div class="mb-6">
                <Link :href="route('job-postings.index')" class="text-gray-500 hover:text-gray-700 mb-2 inline-block">
                    <i class="pi pi-arrow-left mr-1"></i> Back to Postings
                </Link>
                <h2 class="text-2xl font-bold text-gray-800">Edit Job Posting</h2>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
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
                        <Dropdown id="category" v-model="form.job_category_id" :options="categories" optionLabel="name" optionValue="id" placeholder="Select Category" :invalid="form.errors.job_category_id" filter />
                         <small class="text-red-500" v-if="form.errors.job_category_id">{{ form.errors.job_category_id }}</small>
                    </div>

                     <div class="flex flex-col gap-2">
                        <label for="type" class="font-bold text-sm">Job Type</label>
                        <Dropdown id="type" v-model="form.job_type" :options="jobTypes" optionLabel="label" optionValue="value" placeholder="Select Type" :invalid="form.errors.job_type" />
                         <small class="text-red-500" v-if="form.errors.job_type">{{ form.errors.job_type }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="branch" class="font-bold text-sm">Branch (Optional)</label>
                        <Dropdown id="branch" v-model="form.branch_id" :options="branches" optionLabel="name" optionValue="id" placeholder="All Branches" showClear filter />
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
                        <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label" optionValue="value" />
                         <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                    </div>

                    <!-- Actions -->
                    <div class="col-span-full flex justify-end gap-3 mt-4">
                        <Link :href="route('job-postings.index')">
                            <Button label="Cancel" severity="secondary" text />
                        </Link>
                        <Button type="submit" label="Save Changes" :loading="form.processing" />
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
