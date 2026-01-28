<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import Avatar from 'primevue/avatar';
import AvatarGroup from 'primevue/avatargroup';
import SweetAlert from '@/Components/SweetAlert.vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Calendar from 'primevue/calendar';
import MultiSelect from 'primevue/multiselect';
import Dropdown from 'primevue/dropdown';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';

const props = defineProps({
    meetings: Array,
    employees: Array,
    meetingTypes: Array,
    meetingRooms: Array,
    isEmployee: Boolean,
});

const meetingToDelete = ref(null);
const viewDialog = ref(false);
const viewData = ref(null);
const meetingDialog = ref(false);
const isEdit = ref(false);

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
    description: '',
    start_time: null,
    end_time: null,
    location: '',
    meeting_link: '',
    status: 'scheduled',
    employees: [],
    meeting_type_id: null,
    meeting_room_id: null,
});

const statuses = [
    { label: 'Scheduled', value: 'scheduled' },
    { label: 'Completed', value: 'completed' },
    { label: 'Cancelled', value: 'cancelled' }
];

// Auto-fill location when room changes
watch(() => form.meeting_room_id, (newRoomId) => {
    if (newRoomId) {
        const room = props.meetingRooms.find(r => r.id === newRoomId);
        if (room) {
            form.location = room.location || room.name;
        }
    } else if (!isEdit.value) {
        // Only clear if not editing, or maybe just leave it?
        // Let's leave it to avoid annoying the user if they typed something custom
        // form.location = '';
    }
});

const openNew = () => {
    form.reset();
    form.clearErrors();
    isEdit.value = false;
    meetingDialog.value = true;
};

const editMeeting = (meeting) => {
    form.clearErrors();
    form.id = meeting.id;
    form.title = meeting.title;
    form.description = meeting.description;
    form.start_time = meeting.start_time ? new Date(meeting.start_time) : null;
    form.end_time = meeting.end_time ? new Date(meeting.end_time) : null;
    form.location = meeting.location;
    form.meeting_link = meeting.meeting_link;
    form.status = meeting.status;
    form.employees = meeting.employees.map(emp => emp.id);
    form.meeting_type_id = meeting.meeting_type_id;
    form.meeting_room_id = meeting.meeting_room_id;

    isEdit.value = true;
    meetingDialog.value = true;
};

const hideDialog = () => {
    meetingDialog.value = false;
    form.reset();
};

const saveMeeting = () => {
    if (isEdit.value) {
        form.put(route('meetings.update', form.id), {
            onSuccess: () => {
                meetingDialog.value = false;
                form.reset();
                showSuccessAlert('Meeting updated successfully');
            }
        });
    } else {
        form.post(route('meetings.store'), {
            onSuccess: () => {
                meetingDialog.value = false;
                form.reset();
                showSuccessAlert('Meeting scheduled successfully');
            }
        });
    }
};

const generateMeetLink = () => {
    window.open('https://meet.google.com/new', '_blank');
};

const confirmDelete = (meeting) => {
    meetingToDelete.value = meeting;
    alertConfig.value = {
        title: 'Are you sure?',
        message: `Do you want to delete this meeting? This action cannot be undone.`,
        type: 'warning',
        showCancel: true,
        confirmText: 'Yes, delete it!',
        cancelText: 'Cancel'
    };
    showAlert.value = true;
};

const deleteMeeting = () => {
    router.delete(route('meetings.destroy', meetingToDelete.value.id), {
        onSuccess: () => {
            meetingToDelete.value = null;
            alertConfig.value = {
                title: 'Deleted!',
                message: 'Meeting has been deleted successfully.',
                type: 'success',
                showCancel: false
            };
            showAlert.value = true;
        }
    });
};

const openView = (meeting) => {
    viewData.value = meeting;
    viewDialog.value = true;
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
        deleteMeeting();
    }
};

const getSeverity = (status) => {
    switch (status) {
        case 'scheduled':
            return 'info';
        case 'completed':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
};

const formatDateTime = (dateString, timeOnly = false) => {
    if (!dateString) return '-';
    if (timeOnly) {
        return new Date(dateString).toLocaleString(undefined, {
            timeStyle: 'short'
        });
    }
    return new Date(dateString).toLocaleString(undefined, {
        dateStyle: 'medium',
        timeStyle: 'short'
    });
};
</script>

<template>

    <Head title="Aimanova - Meetings" />

    <AuthenticatedLayout>
        <div class="mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-800">Meetings</h2>
            <Button v-if="!isEmployee" label="Schedule Meeting" icon="pi pi-plus" @click="openNew"
                class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
        </div>

        <div class="card bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <DataTable :value="meetings" removableSort paginator :rows="10" tableStyle="min-width: 50rem">
                <Column header="Type" style="width: 10%">
                    <template #body="slotProps">
                        <div v-if="slotProps.data.type" class="flex items-center gap-2">
                            <div
                                :style="{ backgroundColor: slotProps.data.type.color, width: '10px', height: '10px', borderRadius: '50%' }">
                            </div>
                            <span>{{ slotProps.data.type.name }}</span>
                        </div>
                        <span v-else>-</span>
                    </template>
                </Column>
                <Column field="title" header="Title" sortable class="font-bold"></Column>
                <Column header="Time" sortable sortField="start_time">
                    <template #body="slotProps">
                        <div class="flex flex-col text-sm">
                            <span class="font-semibold">{{ formatDateTime(slotProps.data.start_time) }}</span>
                            <span class="text-gray-500">to {{ formatDateTime(slotProps.data.end_time) }}</span>
                        </div>
                    </template>
                </Column>
                <Column header="Location/Room" style="width: 15%">
                    <template #body="slotProps">
                        <div v-if="slotProps.data.room" class="flex flex-col">
                            <span class="font-semibold">{{ slotProps.data.room.name }}</span>
                            <span class="text-xs text-gray-500">{{ slotProps.data.room.location }}</span>
                        </div>
                        <span v-else>{{ slotProps.data.location || 'Online' }}</span>
                    </template>
                </Column>
                <Column header="Participants" style="width: 20%">
                    <template #body="slotProps">
                        <AvatarGroup>
                            <Avatar v-for="emp in slotProps.data.employees.slice(0, 3)" :key="emp.id"
                                :label="emp.first_name[0] + emp.last_name[0]" shape="circle"
                                class="bg-blue-100 text-blue-700 font-bold" />
                            <Avatar v-if="slotProps.data.employees.length > 3"
                                :label="'+' + (slotProps.data.employees.length - 3)" shape="circle"
                                class="bg-gray-200 text-gray-600 font-bold" />
                        </AvatarGroup>
                    </template>
                </Column>
                <Column header="Status" style="width: 10%">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getSeverity(slotProps.data.status)"
                            class="capitalize" />
                    </template>
                </Column>
                <Column header="Actions" style="width: 15%">
                    <template #body="slotProps">
                        <div class="flex gap-2">
                            <Button
                                class="!bg-blue-100 !text-blue-600 !border-blue-100 hover:!bg-blue-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                rounded aria-label="View" @click="openView(slotProps.data)">
                                <i class="pi pi-eye text-lg"></i>
                            </Button>
                            <template v-if="!isEmployee">
                                <Button
                                    class="!bg-green-100 !text-green-600 !border-green-100 hover:!bg-green-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                    rounded aria-label="Edit" @click="editMeeting(slotProps.data)">
                                    <i class="pi pi-pencil text-lg"></i>
                                </Button>
                                <Button
                                    class="!bg-red-100 !text-red-600 !border-red-100 hover:!bg-red-200 !rounded-full !w-10 !h-10 !p-0 !min-w-[40px] flex items-center justify-center p-button-icon-only"
                                    rounded aria-label="Delete" @click="confirmDelete(slotProps.data)">
                                    <i class="pi pi-trash text-lg"></i>
                                </Button>
                            </template>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- Create/Edit Dialog -->
        <Dialog v-model:visible="meetingDialog" :style="{ width: '850px' }"
            :header="isEdit ? 'Edit Meeting Details' : 'Schedule New Meeting'" :modal="true" class="p-fluid">

            <div class="flex flex-col gap-6 p-1">
                <!-- Header Section -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-4 flex flex-col gap-2">
                        <label for="meeting_type_id" class="font-semibold text-gray-700">Meeting Type</label>
                        <Dropdown id="meeting_type_id" v-model="form.meeting_type_id" :options="meetingTypes"
                            optionLabel="name" optionValue="id" placeholder="Select Type"
                            :invalid="!!form.errors.meeting_type_id"
                            class="!w-full !border !border-gray-300 !rounded-md" showClear>
                            <template #option="slotProps">
                                <div class="flex items-center gap-2">
                                    <div
                                        :style="{ backgroundColor: slotProps.option.color, width: '10px', height: '10px', borderRadius: '50%' }">
                                    </div>
                                    <div>{{ slotProps.option.name }}</div>
                                </div>
                            </template>
                            <template #value="slotProps">
                                <div v-if="slotProps.value" class="flex items-center gap-2">
                                    <div
                                        :style="{ backgroundColor: meetingTypes.find(t => t.id === slotProps.value)?.color, width: '10px', height: '10px', borderRadius: '50%' }">
                                    </div>
                                    <div>{{meetingTypes.find(t => t.id === slotProps.value)?.name}}</div>
                                </div>
                                <span v-else>{{ slotProps.placeholder }}</span>
                            </template>
                        </Dropdown>
                        <small class="text-red-500" v-if="form.errors.meeting_type_id">{{ form.errors.meeting_type_id
                            }}</small>
                    </div>

                    <div class="md:col-span-8 flex flex-col gap-2">
                        <label for="title" class="font-semibold text-gray-700">Meeting Title</label>
                        <InputGroup>
                            <InputGroupAddon>
                                <i class="pi pi-tag"></i>
                            </InputGroupAddon>
                            <InputText id="title" v-model="form.title" placeholder="e.g. Weekly Sync, Project Kickoff"
                                :invalid="!!form.errors.title" />
                        </InputGroup>
                        <small class="text-red-500" v-if="form.errors.title">{{ form.errors.title }}</small>
                    </div>
                </div>

                <!-- Agenda Section -->
                <div class="flex flex-col gap-2">
                    <label for="description" class="font-semibold text-gray-700">Agenda / Description</label>
                    <Textarea id="description" v-model="form.description" rows="4"
                        placeholder="Briefly describe the purpose of the meeting..." class="!resize-none" />
                    <small class="text-red-500" v-if="form.errors.description">{{ form.errors.description }}</small>
                </div>

                <!-- Timing Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="flex flex-col gap-2">
                        <label for="start_time" class="font-semibold text-gray-700">Start Time</label>
                        <Calendar id="start_time" v-model="form.start_time" showTime hourFormat="12" showIcon
                            iconDisplay="input" :invalid="!!form.errors.start_time" class="w-full" />
                        <small class="text-red-500" v-if="form.errors.start_time">{{ form.errors.start_time }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="end_time" class="font-semibold text-gray-700">End Time</label>
                        <Calendar id="end_time" v-model="form.end_time" showTime hourFormat="12" showIcon
                            iconDisplay="input" :invalid="!!form.errors.end_time" class="w-full" />
                        <small class="text-red-500" v-if="form.errors.end_time">{{ form.errors.end_time }}</small>
                    </div>
                </div>

                <!-- Location Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="meeting_room_id" class="font-semibold text-gray-700">Meeting Room</label>
                        <Dropdown id="meeting_room_id" v-model="form.meeting_room_id" :options="meetingRooms"
                            optionLabel="name" optionValue="id" placeholder="Select a Room"
                            :invalid="!!form.errors.meeting_room_id" showClear filter
                            class="!w-full !border !border-gray-300 !rounded-md">
                            <template #option="slotProps">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ slotProps.option.name }}</span>
                                    <span class="text-xs text-gray-500">Capacity: {{ slotProps.option.capacity }} | {{
                                        slotProps.option.location }}</span>
                                </div>
                            </template>
                        </Dropdown>
                        <small class="text-gray-500 text-xs" v-if="!form.meeting_room_id">Select a room to auto-fill
                            location.</small>
                        <small class="text-red-500" v-if="form.errors.meeting_room_id">{{ form.errors.meeting_room_id
                            }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="location" class="font-semibold text-gray-700">Location Details</label>
                        <InputGroup>
                            <InputGroupAddon>
                                <i class="pi pi-map-marker"></i>
                            </InputGroupAddon>
                            <InputText id="location" v-model="form.location" placeholder="Room Location or Online Link"
                                :disabled="!!form.meeting_room_id" :class="{ 'bg-gray-100': !!form.meeting_room_id }" />
                        </InputGroup>
                        <small class="text-red-500" v-if="form.errors.location">{{ form.errors.location }}</small>
                    </div>
                </div>

                <!-- Online & Status Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex flex-col gap-2">
                        <label for="meeting_link" class="font-semibold text-gray-700">Online Meeting Link</label>
                        <InputGroup>
                            <InputGroupAddon>
                                <i class="pi pi-video"></i>
                            </InputGroupAddon>
                            <InputText id="meeting_link" v-model="form.meeting_link"
                                placeholder="https://meet.google.com/..." :invalid="!!form.errors.meeting_link" />
                            <Button icon="pi pi-external-link" severity="secondary" @click="generateMeetLink"
                                v-tooltip.top="'Generate New Meet Link'" />
                        </InputGroup>
                        <small class="text-red-500" v-if="form.errors.meeting_link">{{ form.errors.meeting_link
                            }}</small>
                    </div>

                    <div class="flex flex-col gap-2">
                        <label for="status" class="font-semibold text-gray-700">Status</label>
                        <Dropdown id="status" v-model="form.status" :options="statuses" optionLabel="label"
                            optionValue="value" placeholder="Select Status"
                            class="w-full !border !border-gray-300 !rounded-md" :invalid="!!form.errors.status" />
                        <small class="text-red-500" v-if="form.errors.status">{{ form.errors.status }}</small>
                    </div>
                </div>

                <!-- Participants Section -->
                <div class="flex flex-col gap-2">
                    <label for="employees" class="font-semibold text-gray-700">Participants</label>
                    <MultiSelect id="employees" v-model="form.employees" :options="employees" optionLabel="first_name"
                        optionValue="id" placeholder="Select Participants" filter display="chip"
                        class="w-full !border !border-gray-300 !rounded-md" :invalid="!!form.errors.employees">
                        <template #option="slotProps">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ slotProps.option.first_name }} {{
                                    slotProps.option.last_name }}</span>
                                <span class="text-xs text-gray-500">{{ slotProps.option.employee_id }}</span>
                            </div>
                        </template>
                    </MultiSelect>
                    <small class="text-red-500" v-if="form.errors.employees">{{ form.errors.employees }}</small>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times"
                    class="bg-gray-200 text-gray-700 hover:bg-gray-300 !px-6 !py-2.5 !border-gray-200"
                    @click="hideDialog" />
                <Button :label="isEdit ? 'Update Meeting' : 'Schedule Meeting'" icon="pi pi-check" @click="saveMeeting"
                    :loading="form.processing"
                    class="!bg-[#1C0D82] !border-[#1C0D82] hover:!bg-[#150a61] text-white !px-6 !py-2.5" />
            </template>
        </Dialog>

        <!-- View Dialog -->
        <Dialog v-model:visible="viewDialog" :style="{ width: '500px' }" header="Meeting Details" :modal="true"
            class="p-fluid">
            <div class="flex flex-col gap-4" v-if="viewData">
                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Title</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100 text-lg font-semibold text-[#1C0D82]">{{
                        viewData.title }}</div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Date</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{
                            formatDateTime(viewData.start_time).split(',')[0] }}</div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-bold text-gray-500">Time</label>
                        <div class="p-2 bg-gray-50 rounded border border-gray-100">{{
                            formatDateTime(viewData.start_time, true)
                            }} - {{ formatDateTime(viewData.end_time, true) }}</div>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Status</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100 capitalize">
                        <Tag :value="viewData.status" :severity="getSeverity(viewData.status)" />
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Location</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <div v-if="viewData.room">
                            <span class="font-semibold">{{ viewData.room.name }}</span>
                            <span class="text-gray-500 ml-2">({{ viewData.room.location }})</span>
                        </div>
                        <span v-else>{{ viewData.location || 'Online' }}</span>
                    </div>
                </div>

                <div class="flex flex-col gap-1" v-if="viewData.meeting_link">
                    <label class="font-bold text-gray-500">Meeting Link</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <a :href="viewData.meeting_link" target="_blank"
                            class="text-blue-600 underline hover:text-blue-800 flex items-center gap-2">
                            <i class="pi pi-video"></i> Join Meeting
                        </a>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Description</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100 min-h-[60px] whitespace-pre-wrap">{{
                        viewData.description || '-' }}</div>
                </div>

                <div class="flex flex-col gap-1">
                    <label class="font-bold text-gray-500">Participants</label>
                    <div class="p-2 bg-gray-50 rounded border border-gray-100">
                        <div class="flex flex-wrap gap-2">
                            <Tag v-for="emp in viewData.employees" :key="emp.id"
                                :value="emp.first_name + ' ' + emp.last_name" severity="secondary" />
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
