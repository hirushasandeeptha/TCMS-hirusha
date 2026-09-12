<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    student: { type: Object, required: true },
});

const cf = computed(() => props.student.custom_fields || {});
const confirmingDeletion = ref(false);
const deleteForm = useForm({});

const deleteStudent = () => {
    deleteForm.delete(route('students.destroy', props.student.id), { onFinish: () => { confirmingDeletion.value = false; } });
};
</script>

<template>
    <Head title="Student Profile" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Student Profile</h2>
                <div class="flex items-center gap-3">
                    <Link :href="route('students.edit', student.id)" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">Edit</Link>
                    <Link :href="route('students.index')" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50">Back</Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6">
                        <div class="flex items-center space-x-4">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-indigo-100 text-2xl font-bold text-indigo-600">
                                {{ student.full_name?.[0] }}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ student.full_name }}</h3>
                                <p class="text-sm text-gray-500 font-mono">{{ student.student_code }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ student.full_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Student Code</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ student.student_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ student.phone || '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">QR Token</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-mono text-xs break-all">{{ student.qr_code_token || '-' }}</dd>
                            </div>
                        </dl>

                        <div v-if="Object.keys(cf).length > 0" class="mt-8">
                            <h4 class="mb-4 text-lg font-medium text-gray-900">Additional Details</h4>
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div v-for="(value, key) in cf" :key="key">
                                    <dt class="text-sm font-medium text-gray-500 capitalize">{{ key.replace(/_/g, ' ') }}</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ value || '-' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="mt-8 border-t border-gray-200 pt-4">
                            <dl class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                <div>
                                    <dt class="text-xs text-gray-500">Created</dt>
                                    <dd class="text-sm text-gray-700">{{ new Date(student.created_at).toLocaleDateString() }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs text-gray-500">Last Updated</dt>
                                    <dd class="text-sm text-gray-700">{{ new Date(student.updated_at).toLocaleDateString() }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h4 class="text-lg font-medium text-red-600">Danger Zone</h4>
                        <p class="mt-1 text-sm text-gray-600">This will permanently delete this student record.</p>
                        <div class="mt-4">
                            <DangerButton @click="confirmingDeletion = true">Delete Student</DangerButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Student</h2>
                <p class="mt-1 text-sm text-gray-600">Are you sure you want to delete <strong>{{ student.full_name }}</strong>?</p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="confirmingDeletion = false">Cancel</SecondaryButton>
                    <DangerButton class="ms-3" :disabled="deleteForm.processing" @click="deleteStudent">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
