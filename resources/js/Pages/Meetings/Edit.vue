<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import MultiSelect from 'primevue/multiselect';
import Dropdown from 'primevue/dropdown';

const props = defineProps({
    meeting: Object,
    employees: Array,
});

const form = useForm({
    title: props.meeting.title,
    description: props.meeting.description,
    start_time: props.meeting.start_time ? new Date(props.meeting.start_time) : null,
    end_time: props.meeting.end_time ? new Date(props.meeting.end_time) : null,
    location: props.meeting.location,
    meeting_link: props.meeting.meeting_link,
    status: props.meeting.status,
    employees: props.meeting.employees.map(emp => emp.id)
});

const statuses = [
    { label: 'Scheduled', value: 'scheduled' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' }
];

const generateMeetLink = () => {
    window.open('https://meet.google.com/new', '_blank');
};

const submit = () => {
    form.put(route('meetings.update', props.meeting.id));
};
</script>

<template>

    <Head title="Aimanova - Edit Meeting" />

    <AuthenticatedLayout>
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Meeting</h2>

            <form @submit.prevent="submit" class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="title" class="font-bold">Title</label>
                    <InputText id="title" v-model="form.title" placeholder="Meeting Title"
                        :invalid="!!form.errors.title" />
                    <small class="text-red-500" v-if="form.errors.title">{{ form.errors.title }}</small>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="description" class="font-bold">Description (Agenda)</label>
                    <Textarea id="description" v-model="form.description" rows="4" />
                    <small class="text-red-500" v-if="form.errors.description">{{ form.errors.description }}</small>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="start_time" class="font-bold">Start Time</label>
                        <Calendar id="start_time" v-model="form.start_time" showTime hourFormat="12" showIcon
                            :invalid="!!form.errors.start_time" />
                        <small class="text-red-500" v-if="form.errors.start_time">{{ form.errors.start_time }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="end_time" class="font-bold">End Time</label>
                        <Calendar id="end_time" v-model="form.end_time" showTime hourFormat="12" showIcon
                            :invalid="!!form.errors.end_time" />
                        <small class="text-red-500" v-if="form.errors.end_time">{{ form.errors.end_time }}</small>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex flex-col gap-2">
                        <label for="location" class="font-bold">Location</label>
                        <InputText id="location" v-model="form.location" placeholder="Conference Room A / Zoom Link" />
                        <small class="text-red-500" v-if="form.errors.location">{{ form.errors.location }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="meeting_link" class="font-bold">Google Meet Link</label>
                        <div class="flex gap-2">
                            <InputText id="meeting_link" v-model="form.meeting_link"
                                placeholder="https://meet.google.com/..." class="flex-1"
                                :invalid="!!form.errors.meeting_link" />
                            <Button icon="pi pi-google" label="Generate" severity="secondary" @click="generateMeetLink"
                                v-tooltip="'Opens Google Meet to create new link'" type="button" />
                        </div>
                        <small class="text-gray-500 text-xs">Click "Generate" to create a meeting, then copy the link
                            here.</small>
                        <small class="text-red-500" v-if="form.errors.meeting_link">{{ form.errors.meeting_link
                            }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="status" class="font-bold">Status</label>
                        <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label"
                            optionValue="value" placeholder="Select Status" class="w-full"
                            :invalid="!!form.errors.status" />
                        <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    <label for="employees" class="font-bold">Participants</label>
                    <MultiSelect id="employees" v-model="form.employees" :options="employees" optionLabel="first_name"
                        optionValue="id" placeholder="Select Participants" filter display="chip" class="w-full"
                        :invalid="!!form.errors.employees">
                        <template #option="slotProps">
                            <div class="flex flex-col">
                                <span>{{ slotProps.option.first_name }} {{ slotProps.option.last_name }}</span>
                                <span class="text-xs text-gray-500">{{ slotProps.option.employee_id }}</span>
                            </div>
                        </template>
                        <template #value="slotProps">
                            <div v-if="!slotProps.value || slotProps.value.length === 0">
                                {{ slotProps.placeholder }}
                            </div>
                        </template>
                    </MultiSelect>
                    <small class="text-red-500" v-if="form.errors.employees">{{ form.errors.employees }}</small>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <Link :href="route('meetings.index')">
                        <Button label="Cancel" severity="secondary" text />
                    </Link>
                    <Button type="submit" label="Update Meeting" :loading="form.processing" />
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
