<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    student: { type: Object, required: true },
});

const cf = computed(() => props.student.custom_fields || {});

const form = useForm({
    full_name: props.student.full_name ?? '',
    phone: props.student.phone ?? '',
    custom_fields: {},
});

const customFields = ref({
    school: cf.value.school ?? '',
    guardian_name: cf.value.guardian_name ?? '',
    guardian_phone: cf.value.guardian_phone ?? '',
    address: cf.value.address ?? '',
    notes: cf.value.notes ?? '',
});

const submit = () => {
    form.custom_fields = { ...customFields.value };
    form.put(route('students.update', props.student.id));
};
</script>

<template>
    <Head title="Edit Student" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Edit Student</h2>
                <Link :href="route('students.index')" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50">Back to Students</Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <h3 class="mb-6 text-lg font-medium text-gray-900">Student Information</h3>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel for="full_name" value="Full Name *" />
                                <TextInput id="full_name" v-model="form.full_name" type="text" class="mt-1 block w-full" required />
                                <InputError :message="form.errors.full_name" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="phone" value="Phone" />
                                <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                                <InputError :message="form.errors.phone" class="mt-2" />
                            </div>
                        </div>

                        <div class="mt-4 text-sm text-gray-500">
                            <span class="font-medium">Student Code:</span> {{ student.student_code }}
                        </div>

                        <h3 class="mb-6 mt-8 text-lg font-medium text-gray-900">Additional Details</h3>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel for="school" value="School" />
                                <TextInput id="school" v-model="customFields.school" type="text" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <InputLabel for="address" value="Address" />
                                <TextInput id="address" v-model="customFields.address" type="text" class="mt-1 block w-full" />
                            </div>
                        </div>

                        <h3 class="mb-6 mt-8 text-lg font-medium text-gray-900">Guardian Information</h3>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel for="guardian_name" value="Guardian Name" />
                                <TextInput id="guardian_name" v-model="customFields.guardian_name" type="text" class="mt-1 block w-full" />
                            </div>
                            <div>
                                <InputLabel for="guardian_phone" value="Guardian Phone" />
                                <TextInput id="guardian_phone" v-model="customFields.guardian_phone" type="text" class="mt-1 block w-full" />
                            </div>
                        </div>

                        <div class="mt-6">
                            <InputLabel for="notes" value="Notes" />
                            <textarea id="notes" v-model="customFields.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </div>

                        <div class="mt-8 flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Update Student</PrimaryButton>
                            <Link :href="route('students.index')" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm hover:bg-gray-50">Cancel</Link>
                            <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
