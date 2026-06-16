<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import BaseModal from '@/Components/UI/BaseModal.vue'
import BaseToast from '@/Components/UI/BaseToast.vue'
import { router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
const isEdit = ref(false)

const selectedId = ref(null)
const showDeleteModal = ref(false)
const selectedUnit = ref(null)
const showToast = ref(false)
const toastMessage = ref('')





const page = usePage()

const props = defineProps({

    units: Object,

    filters: Object

})

const search = ref(
    props.filters?.search ?? ''
)

const doSearch = () => {

    router.get(

        route('units.index'),

        {
            search: search.value
        },

        {
            preserveState: true,
            replace: true
        }

    )

}

const showModal = ref(false)

const form = useForm({
   
    name: '',
    description: '',
    status: true
})

const openModal = () => {
    showModal.value = true
}
const editUnit = (unit) => {

    isEdit.value = true

    selectedId.value = unit.id

    form.name = unit.name

    form.description = unit.description

    form.status = unit.status

    showModal.value = true

}
const deleteUnit = (unit) => {

    selectedUnit.value = unit

    selectedId.value = unit.id

    showDeleteModal.value = true

}
const closeModal = () => {

    showModal.value = false

    isEdit.value = false

    selectedId.value = null

    form.reset()

}
const showNotification = (message) => {

    toastMessage.value = message

    showToast.value = true

    setTimeout(() => {

        showToast.value = false

    }, 3000)

}
const saveUnit = () => {

    if (isEdit.value) {

        form.put(

            route(
                'units.update',
                selectedId.value
            ),

            {

               onSuccess: () => {

    closeModal()

    showNotification(

        isEdit.value

            ? 'Unit updated successfully'

            : 'Unit created successfully'

    )

}

            }

        )

    } else {

        form.post(

            route(
                'units.store'
            ),

            {

                onSuccess: () => {

                    closeModal()

                }

            }

        )

    }

}
const confirmDelete = () => {

    form.delete(

        route(
            'units.destroy',
            selectedId.value
        ),

        {

           onSuccess: () => {

    showDeleteModal.value = false

    selectedId.value = null

    selectedUnit.value = null

    showNotification(

        'Unit deleted successfully'

    )

}

        }

    )

}

</script>

<template>

    <Head title="Units" />

    <AuthenticatedLayout>

        <template #header>

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="text-2xl font-bold text-gray-800">
                    Units 
                     </h2>
                    
                    <p class="text-sm text-gray-500">
                        Master Data Unit
                    </p>

                </div>

                <button
                    @click="openModal"
                    class="rounded-xl bg-blue-600 px-5 py-3 text-white hover:bg-blue-700"
                >
                    + Add Unit
                </button>

            </div>

        </template>

        <div class="space-y-6">
<div
    class="rounded-2xl bg-white p-5 shadow-sm"
>

    <input

        v-model="search"

        @keyup="doSearch"

        type="text"

        placeholder="Search unit..."

        class="w-full rounded-xl border border-gray-300 px-4 py-3"

    />

</div>
            <!-- Table -->

            <div
                class="overflow-hidden rounded-2xl bg-white shadow-sm"
            >

                <table
                    class="min-w-full"
                >

                    <thead
                        class="bg-gray-50"
                    >

                        <tr>

                            <th class="px-6 py-4 text-left">
                                Code
                            </th>

                            <th class="px-6 py-4 text-left">
                                Name
                            </th>

                            <th class="px-6 py-4 text-left">
                                Status
                                                        </th>
                            <th class="px-6 py-4 text-center">
                                Action
                            </th>
                        </tr>

                    </thead>

                    <tbody>

                        <tr
                            v-for="unit in units.data"
                            :key="unit.id"
                            class="border-t"
                        >

                            <td class="px-6 py-4">
                                {{ unit.code }}
                            </td>

                            <td class="px-6 py-4">
                                {{ unit.name }}
                            </td>

                            <td class="px-6 py-4">

                                <span
                                    v-if="unit.status"
                                    class="rounded-full bg-green-100 px-3 py-1 text-sm text-green-700"
                                >
                                    Active
                                </span>

                                <span
                                    v-else
                                    class="rounded-full bg-red-100 px-3 py-1 text-sm text-red-700"
                                >
                                    Inactive
                                </span>

                            </td>
                                <td class="px-6 py-4">

                                    <div
                                        class="flex justify-center gap-2"
                                    >

                                        <button

                                                @click="editUnit(unit)"

                                                class="rounded-lg bg-amber-500 px-3 py-1 text-white hover:bg-amber-600"

                                            >

                                                Edit

                                            </button>

                                       <button

                                            @click="deleteUnit(unit)"

                                            class="rounded-lg bg-red-500 px-3 py-1 text-white hover:bg-red-600"

                                        >

                                            Delete

                                        </button>

                                    </div>

                                </td>
                        </tr>

                    </tbody>

                </table>
                    <div
                        class="mt-5 flex justify-center gap-2"
                    >

                        <template
                            v-for="link in units.links"
                            :key="link.label"
                        >

                            <button

                                v-if="link.url"

                                @click="router.visit(link.url)"

                                v-html="link.label"

                                class="rounded-lg border px-3 py-2 hover:bg-blue-50"

                            />

                        </template>

                    </div>
            </div>

        </div>

        <!-- Modal -->

        <BaseModal
            :show="showModal"
            @close="closeModal"
        >

           <template #title>

                {{ isEdit ? 'Edit Unit' : 'Add Unit' }}

            </template>

            <div class="space-y-4">

                <div>

                   

                </div>

                <div>

                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Name
                    </label>

                    <input
                        v-model="form.name"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3"
                    />

                </div>

                <div>

                    <label
                        class="mb-2 block text-sm font-medium"
                    >
                        Description
                    </label>

                    <textarea
                        v-model="form.description"
                        class="w-full rounded-xl border border-gray-300 px-4 py-3"
                    ></textarea>

                </div>

                <div
                    class="flex items-center gap-3"
                >

                    <input
                        type="checkbox"
                        v-model="form.status"
                    />

                    <span>
                        Active
                    </span>

                </div>

            </div>

            <template #footer>

                <button
                    @click="closeModal"
                    class="rounded-xl border px-5 py-2"
                >
                    Cancel
                </button>

                <button
                    @click="saveUnit"
                    class="rounded-xl bg-blue-600 px-5 py-2 text-white"
                >
                   {{ isEdit ? 'Update' : 'Save' }}
                </button>

            </template>

        </BaseModal>
<BaseModal

    :show="showDeleteModal"

    @close="showDeleteModal = false"

>

    <template #title>

        Delete Unit

    </template>

    <div>

        <p class="text-gray-600">

            Are you sure want to delete

            <strong>

                {{ selectedUnit?.name }}

            </strong>

            ?

        </p>

    </div>

    <template #footer>

        <button

            @click="showDeleteModal = false"

            class="rounded-xl border px-5 py-2"

        >

            Cancel

        </button>

        <button

            @click="confirmDelete"

            class="rounded-xl bg-red-600 px-5 py-2 text-white"

        >

            Delete

        </button>

    </template>

</BaseModal>

<BaseToast

    :show="showToast"

    :message="toastMessage"

/>

<BaseToast

    :show="page.props.flash.success"

    :message="page.props.flash.success"

/>
    </AuthenticatedLayout>

</template>