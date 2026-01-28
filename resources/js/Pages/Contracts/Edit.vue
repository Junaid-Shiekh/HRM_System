<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import { ref } from 'vue';

const props = defineProps({
    contract: Object,
    employees: Array,
    contractTypes: Array,
});

const form = useForm({
    employee_id: props.contract.employee_id,
    contract_type_id: props.contract.contract_type_id,
    start_date: props.contract.start_date ? new Date(props.contract.start_date) : null,
    end_date: props.contract.end_date ? new Date(props.contract.end_date) : null,
    salary: parseFloat(props.contract.salary),
    status: props.contract.status,
    document: null,
});

const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Expired', value: 'expired' },
    { label: 'Terminated', value: 'terminated' }
];

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'put',
    })).post(route('contracts.update', props.contract.id));
};
</script>

<template>
    <Head title="Aimanova - Edit Contract" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Contract</h2>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="employee" class="font-bold">Employee</label>
                        <Dropdown id="employee" v-model="form.employee_id" :options="employees" optionLabel="first_name" optionValue="id" placeholder="Select Employee" filter class="w-full" :invalid="!!form.errors.employee_id">
                             <template #option="slotProps">
                                {{ slotProps.option.first_name }} {{ slotProps.option.last_name }} ({{ slotProps.option.employee_id }})
                            </template>
                             <template #value="slotProps">
                                <div v-if="slotProps.value">
                                     {{ employees.find(e => e.id === slotProps.value)?.first_name }} {{ employees.find(e => e.id === slotProps.value)?.last_name }}
                                </div>
                                <span v-else>
                                    {{ slotProps.placeholder }}
                                </span>
                            </template>
                        </Dropdown>
                        <small class="text-red-500" v-if="form.errors.employee_id">{{ form.errors.employee_id }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="contract_type" class="font-bold">Contract Type</label>
                        <Dropdown id="contract_type" v-model="form.contract_type_id" :options="contractTypes" optionLabel="name" optionValue="id" placeholder="Select Type" class="w-full" :invalid="!!form.errors.contract_type_id" />
                         <small class="text-red-500" v-if="form.errors.contract_type_id">{{ form.errors.contract_type_id }}</small>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div class="flex flex-col gap-2">
                        <label for="start_date" class="font-bold">Start Date</label>
                        <Calendar id="start_date" v-model="form.start_date" dateFormat="yy-mm-dd" showIcon :invalid="!!form.errors.start_date" />
                        <small class="text-red-500" v-if="form.errors.start_date">{{ form.errors.start_date }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="end_date" class="font-bold">End Date</label>
                        <Calendar id="end_date" v-model="form.end_date" dateFormat="yy-mm-dd" showIcon :invalid="!!form.errors.end_date" />
                        <small class="text-red-500" v-if="form.errors.end_date">{{ form.errors.end_date }}</small>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                     <div class="flex flex-col gap-2">
                        <label for="salary" class="font-bold">Salary</label>
                        <InputNumber id="salary" v-model="form.salary" mode="currency" currency="PKR" locale="en-PK" class="w-full" :invalid="!!form.errors.salary" />
                        <small class="text-red-500" v-if="form.errors.salary">{{ form.errors.salary }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="status" class="font-bold">Status</label>
                        <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label" optionValue="value" placeholder="Select Status" class="w-full" :invalid="!!form.errors.status" />
                         <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="document" class="font-bold">Document (PDF/DOC)</label>
                    <div v-if="contract.document" class="mb-2">
                        <a :href="'/storage/' + contract.document" target="_blank" class="text-blue-500 underline">Current Document</a>
                    </div>
                    <input type="file" @input="form.document = $event.target.files[0]" class="block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-violet-50 file:text-violet-700
                        hover:file:bg-violet-100
                    "/>
                     <small class="text-red-500" v-if="form.errors.document">{{ form.errors.document }}</small>
                     <progress v-if="form.progress" :value="form.progress.percentage" max="100">
                        {{ form.progress.percentage }}%
                    </progress>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                     <Link :href="route('contracts.index')">
                        <Button label="Cancel" severity="secondary" text />
                    </Link>
                    <Button type="submit" label="Update Contract" :loading="form.processing" />
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
