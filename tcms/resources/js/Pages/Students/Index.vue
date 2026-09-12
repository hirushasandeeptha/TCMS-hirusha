<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    students: { type: Object, required: true },
});

const search = ref('');
const confirmingDeletion = ref(false);
const studentToDelete = ref(null);
const deleteForm = useForm({});

const searchStudents = () => {
    router.get(route('students.index'), { search: search.value }, { preserveState: true, replace: true });
};

let timeout = null;
watch(search, (value) => {
    clearTimeout(timeout);
    timeout = setTimeout(searchStudents, 300);
});

const confirmDeletion = (student) => { studentToDelete.value = student; confirmingDeletion.value = true; };

const deleteStudent = () => {
    if (!studentToDelete.value) return;
    deleteForm.delete(route('students.destroy', studentToDelete.value.id), {
        preserveState: true,
        onFinish: () => { confirmingDeletion.value = false; studentToDelete.value = null; },
    });
};

const goToPage = (url) => { if (url) router.get(url, { search: search.value }, { preserveState: true, replace: true }); };
</script>

<template>
    <Head title="Students" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Students</h2>
                <Link :href="route('students.create')" class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-gray-700">Add Student</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="mb-6">
                    <TextInput v-model="search" type="text" placeholder="Search by name, code, or phone..." class="w-full" />
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Code</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-if="students.data.length === 0">
                                    <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No students found.</td>
                                </tr>
                                <tr v-for="student in students.data" :key="student.id" class="hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-500">{{ student.student_code }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <Link :href="route('students.show', student.id)" class="text-sm font-medium text-gray-900 hover:text-indigo-600">{{ student.full_name }}</Link>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ student.phone || '-' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                        <div class="flex items-center space-x-3">
                                            <Link :href="route('students.edit', student.id)" class="text-indigo-600 hover:text-indigo-900">Edit</Link>
                                            <button @click="confirmDeletion(student)" class="text-red-600 hover:text-red-900">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-if="students.last_page > 1" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">Showing <span class="font-medium">{{ students.from }}</span> to <span class="font-medium">{{ students.to }}</span> of <span class="font-medium">{{ students.total }}</span> results</p>
                            </div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <button @click="goToPage(students.prev_page_url)" :disabled="!students.prev_page_url" class="relative inline-flex items-center rounded-l-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30">Previous</button>
                                <button @click="goToPage(students.next_page_url)" :disabled="!students.next_page_url" class="relative inline-flex items-center rounded-r-md border border-gray-300 bg-white px-2 py-2 text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-30">Next</button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Modal :show="confirmingDeletion" @close="confirmingDeletion = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Delete Student</h2>
                <p class="mt-1 text-sm text-gray-600">Are you sure you want to delete <strong>{{ studentToDelete?.full_name }}</strong>?</p>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="confirmingDeletion = false">Cancel</SecondaryButton>
                    <DangerButton class="ms-3" :disabled="deleteForm.processing" @click="deleteStudent">Delete</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
