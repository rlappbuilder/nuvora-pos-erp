<script setup>
import FormSection from '@/Components/Form/FormSection.vue'
import FormField from '@/Components/Form/FormField.vue'
import FormInput from '@/Components/Form/FormInput.vue'
import FormTextarea from '@/Components/Form/FormTextarea.vue'
import FormCheckbox from '@/Components/Form/FormCheckbox.vue'
import BaseButton from '@/Components/Button/BaseButton.vue'
import SearchableSelect from '@/Components/Form/SearchableSelect.vue'
import AutoGenerateInput from '@/Components/Form/AutoGenerateInput.vue'
import CheckBoxGroup from '@/Components/Form/CheckBoxGroup.vue'
import axios from 'axios'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
const props = defineProps({

    form: {
        type: Object,
        required: true,
    },

    mode: {
        type: String,
        default: 'create',
    },

    productId: {
        type: [Number, String],
        default: null,
    },

    categories: {
        type: Array,
        default: () => [],
    },

    brands: {
        type: Array,
        default: () => [],
    },

    units: {
        type: Array,
        default: () => [],
    },

    attributes: {
        type: Array,
        default: () => [],
    },

    previewCode: {
        type: String,
        default: '',
    },

    images: {
        type: Array,
        default: () => [],
    },

})

const emit = defineEmits([
    'submit',
    'submitAndNew',
    'cancel',
])
const uploadingImages = ref(false)
const imageActionId = ref(null)

async function uploadImages(event) {

    const files = Array.from(
        event.target.files || []
    )

    console.log('PRODUCT ID:', props.productId)
    console.log('FILES:', files)

    if (!files.length) {
        return
    }

    if (!props.productId) {
        alert('Product belum memiliki ID.')
        return
    }

    uploadingImages.value = true

    try {

        for (const file of files) {

            const formData = new FormData()

            formData.append(
                'image',
                file
            )

            await axios.post(
                route(
                    'products.images.store',
                    props.productId
                ),
                formData
            )
        }

        await router.reload({
            only: ['product'],
            preserveScroll: true,
            preserveState: true,
        })

    } catch (error) {

        console.error(
            'PRODUCT IMAGE UPLOAD ERROR:',
            error
        )

        console.error(
            'RESPONSE:',
            error.response?.data
        )

        alert(
            error.response?.data?.message ??
            'Gagal mengupload product image.'
        )

    } finally {

        uploadingImages.value = false

        event.target.value = ''
    }
}

async function reloadProductImages() {
    await router.reload({
        only: ['product'],
        preserveScroll: true,
        preserveState: true,
    })
}

async function setPrimary(image) {
    if (!props.productId) {
        return
    }

    imageActionId.value = image.id

    try {
        await axios.put(
            route(
                'products.images.primary',
                [
                    props.productId,
                    image.id,
                ]
            )
        )

        await reloadProductImages()

    } catch (error) {
        console.error(
            'Set primary image failed:',
            error
        )

        alert(
            'Gagal mengubah primary image.'
        )
    } finally {
        imageActionId.value = null
    }
}

async function deleteImage(image) {
    if (!props.productId) {
        return
    }

    if (
        !confirm(
            'Yakin ingin menghapus product image ini?'
        )
    ) {
        return
    }

    imageActionId.value = image.id

    try {
        const deleteUrl = route(
            'products.images.destroy',
            [
                props.productId,
                image.id,
            ]
        )

        console.log(
            'DELETE URL:',
            deleteUrl
        )

        console.log(
            'PRODUCT ID:',
            props.productId
        )

        console.log(
            'IMAGE ID:',
            image.id
        )

        await axios.delete(deleteUrl)

        await reloadProductImages()

    } catch (error) {

        console.error(
            'Delete product image failed:',
            error
        )

        console.error(
            'STATUS:',
            error.response?.status
        )

        console.error(
            'URL:',
            error.config?.url
        )

        console.error(
            'METHOD:',
            error.config?.method
        )

        console.error(
            'RESPONSE:',
            error.response?.data
        )

        alert(
            'Gagal menghapus product image.'
        )

    } finally {

        imageActionId.value = null

    }
}

async function moveImage(image, direction) {
    if (!props.productId) {
        return
    }

    const currentIndex =
        props.images.findIndex(
            item => item.id === image.id
        )

    if (currentIndex === -1) {
        return
    }

    const newIndex =
        currentIndex + direction

    if (
        newIndex < 0 ||
        newIndex >= props.images.length
    ) {
        return
    }

    const imageIds =
        props.images.map(item => item.id)

    const temp =
        imageIds[currentIndex]

    imageIds[currentIndex] =
        imageIds[newIndex]

    imageIds[newIndex] =
        temp

    imageActionId.value = image.id

    try {
        await axios.post(
            route(
                'products.images.reorder',
                props.productId
            ),
            {
                image_ids: imageIds,
            }
        )

        await reloadProductImages()

    } catch (error) {
        console.error(
            'Reorder product images failed:',
            error
        )

        alert(
            'Gagal mengubah urutan product image.'
        )
    } finally {
        imageActionId.value = null
    }
}

function imageUrl(image) {
    if (!image?.image) {
        return ''
    }

    return `/storage/${image.image}`
}
</script>
<template>
<form @submit.prevent="emit('submit')">
        <!-- ========================================================= -->
    <!-- Product Information -->
    <!-- ========================================================= -->

        <FormSection
            icon="📦"
            title="Product Information"
            description="Basic information about this product."
            :columns="2"
        >
             <FormField
                label="Product Name"
                required
                :error="form.errors.name"
            >

                <FormInput
                    v-model="form.name"
                    placeholder="Product Name"
                />

            </FormField>

            
            <FormField label="Product Code">

                <FormInput
                   :model-value="
                        mode === 'edit'
                            ? form.code
                            : props.previewCode
                    "
                    readonly
                />

            </FormField>
             <FormField
                label="SKU"
                :error="form.errors.sku"
            >

               <AutoGenerateInput
                v-model="form.sku"
                :generate-route="route('products.generate-sku')"
                response-key="sku"
                placeholder="Generate or input SKU"
                :error="form.errors.sku"
            />

            </FormField>
            <FormField
                label="Brand"
                :error="form.errors.brand_id"
            >
                <SearchableSelect
                    v-model="form.brand_id"
                    :options="props.brands"
                    label="name"
                    value-key="id"
                    placeholder="Select Brand"
                />
            </FormField>

            <FormField
                label="Unit"
                required
                :error="form.errors.unit_id"
            >
                <SearchableSelect
                    v-model="form.unit_id"
                    :options="props.units"
                    label="name"
                    value-key="id"
                    placeholder="Select Unit"
                />
            </FormField>

            
           
            <FormField
                label="Category"
                required
                :error="form.errors.category_id"
            >
                <SearchableSelect
                    v-model="form.category_id"
                    :options="props.categories"
                    label="name"
                    value-key="id"
                    placeholder="Select Category"
                />
            </FormField>
            
            <FormField
                label="Product Type"
                required
                :error="form.errors.product_type"
            >

                <SearchableSelect
                    v-model="form.product_type"
                    :options="[
                        { label: 'Product', value: 'PRODUCT' },
                        { label: 'Service', value: 'SERVICE' },
                    ]"
                    label="label"
                    value-key="value"
                    placeholder="Select Product Type"
                />

            </FormField>
                <FormField
                    label="Minimum Stock"
                    :error="form.errors.minimum_stock"
                >
                    <FormInput
                        v-model="form.minimum_stock"
                        type="number"
                        min="0"
                        placeholder="0"
                    />
                    </FormField>
        </FormSection>
        <FormSection icon="📦"
            title="variant Attributes"
            description="Pilih attribute yang digunakan untuk membentuk Product Variant."
            :columns="1" 
        >                 

                <CheckBoxGroup
                    v-model="form.attribute_ids"
                    :options="attributes"
                    label-key="display_name"
                    value-key="id"
                    :error="form.errors.attribute_ids"
                />

        </FormSection>
        <!-- inventory setting-->
         <FormSection
            icon="📦"
            title="Inventory Settings"
            description="Inventory configuration."
            :columns="1"
        >

           

            <div class="flex flex-col gap-4">

                <FormCheckbox
                    v-model="form.track_stock"
                    label="Track Stock"
                    description="Enable stock tracking."
                    variant="switch"
                />

                <FormCheckbox
                    v-model="form.is_sellable"
                    label="Sellable"
                    description="Allow this product to be sold."
                    variant="switch"
                />

                <FormCheckbox
                    v-model="form.is_purchasable"
                    label="Purchasable"
                    description="Allow this product to be purchased."
                    variant="switch"
                />

                <FormCheckbox
                    v-model="form.is_active"
                    label="Active"
                    description="Enable or disable this product."
                    variant="switch"
                />

            </div>

        </FormSection>
        <FormSection
    icon="🖼️"
    title="Product Images"
    description="Manage product images and select the primary image."
    :columns="1"
>
    <!-- CREATE / DUPLICATE -->
    <div
        v-if="!props.productId"
        class="
            rounded-lg
            border
            border-dashed
            border-gray-300
            bg-gray-50
            p-6
            text-center
        "
    >
        <div class="text-3xl mb-2">
            📂
        </div>

        <p class="text-sm font-medium text-gray-700">
            Save the product first
        </p>

        <p class="mt-1 text-sm text-gray-500">
            Product images can be uploaded after
            the product has been created.
        </p>
    </div>

    <!-- EDIT -->
    <div v-else>
        <div
            class="
                flex
                items-center
                justify-between
                gap-4
                mb-5
            "
        >
            <div>
                <p class="text-sm font-medium text-gray-700">
                    Product Gallery
                </p>

                <p class="text-xs text-gray-500 mt-1">
                    JPG, JPEG, PNG, or WEBP. Maximum 5 MB per image.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <label
    class="
        inline-flex
        cursor-pointer
        items-center
        rounded-lg
        bg-blue-600
        px-4
        py-2
        text-sm
        font-medium
        text-white
        transition
        hover:bg-blue-700
        disabled:opacity-50
    "
    :class="{
        'pointer-events-none opacity-50':
            uploadingImages
    }"
>
    <input
        type="file"
        accept="image/jpeg,image/png,image/webp"
        multiple
        class="hidden"
        :disabled="uploadingImages"
        @change="uploadImages"
    />

    {{
        uploadingImages
            ? 'Uploading...'
            : '+ Add Images'
    }}
</label>
            </div>
        </div>

        <!-- EMPTY -->
        <div
            v-if="!props.images.length"
            class="
                rounded-lg
                border
                border-dashed
                border-gray-300
                bg-gray-50
                p-8
                text-center
            "
        >
            <div class="text-4xl mb-3">
                📂
            </div>

            <p class="text-sm font-medium text-gray-700">
                No product images
            </p>

            <p class="text-sm text-gray-500 mt-1">
                Add images to make this product easier
                to identify across the system.
            </p>
        </div>

        <!-- GALLERY -->
        <div
            v-else
            class="
                grid
                grid-cols-2
                md:grid-cols-3
                lg:grid-cols-4
                gap-4
            "
        >
            <div
                v-for="(image, index) in props.images"
                :key="image.id"
                class="
                    overflow-hidden
                    rounded-xl
                    border
                    border-gray-200
                    bg-white
                "
            >
                <!-- IMAGE -->
                <div
                    class="
                        relative
                        aspect-square
                        bg-gray-100
                        overflow-hidden
                    "
                >
                    <img
                        :src="imageUrl(image)"
                        alt="Product image"
                        class="
                            h-full
                            w-full
                            object-cover
                        "
                    />

                    <!-- PRIMARY -->
                    <div
                        v-if="image.is_primary"
                        class="
                            absolute
                            top-2
                            left-2
                            rounded-full
                            bg-blue-600
                            px-2.5
                            py-1
                            text-xs
                            font-medium
                            text-white
                        "
                    >
                        Primary
                    </div>

                    <!-- ORDER -->
                    <div
                        class="
                            absolute
                            bottom-2
                            left-2
                            rounded-md
                            bg-black/60
                            px-2
                            py-1
                            text-xs
                            text-white
                        "
                    >
                        {{ index + 1 }}
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="p-3">
                    <div
                        class="
                            flex
                            items-center
                            justify-between
                            gap-2
                        "
                    >
                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                class="
                                    rounded-md
                                    border
                                    border-gray-200
                                    px-2
                                    py-1
                                    text-xs
                                    text-gray-600
                                    hover:bg-gray-50
                                    disabled:opacity-50
                                "
                                :disabled="
                                    index === 0 ||
                                    imageActionId === image.id
                                "
                                @click="
                                    moveImage(image, -1)
                                "
                            >
                                ←
                            </button>

                            <button
                                type="button"
                                class="
                                    rounded-md
                                    border
                                    border-gray-200
                                    px-2
                                    py-1
                                    text-xs
                                    text-gray-600
                                    hover:bg-gray-50
                                    disabled:opacity-50
                                "
                                :disabled="
                                    index === props.images.length - 1 ||
                                    imageActionId === image.id
                                "
                                @click="
                                    moveImage(image, 1)
                                "
                            >
                                →
                            </button>
                        </div>

                        <button
                            v-if="!image.is_primary"
                            type="button"
                            class="
                                text-xs
                                font-medium
                                text-blue-600
                                hover:text-blue-700
                                disabled:opacity-50
                            "
                            :disabled="
                                imageActionId === image.id
                            "
                            @click="
                                setPrimary(image)
                            "
                        >
                            Set Primary
                        </button>
                    </div>

                    <button
                        type="button"
                        class="
                            mt-3
                            w-full
                            rounded-md
                            border
                            border-red-200
                            px-3
                            py-1.5
                            text-xs
                            font-medium
                            text-red-600
                            hover:bg-red-50
                            disabled:opacity-50
                        "
                        :disabled="
                            imageActionId === image.id
                        "
                        @click="
                            deleteImage(image)
                        "
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</FormSection>
        <FormSection
            icon="📝"
            title="Description"
            description="Additional information about this product."
            :columns="1"
        >

            <FormField
                label="Description"
                :error="form.errors.description"
            >

                <FormTextarea
                    v-model="form.description"
                    :rows="4"
                    placeholder="Write additional notes..."
                />

            </FormField>

        </FormSection>
           <!-- ========================================================= -->
        <!-- Action -->
        <!-- ========================================================= -->

     <div
    class="
        flex
        items-center
        justify-end
        gap-3
        mt-8
        pt-6
        border-t
    "
>

    <BaseButton
        type="button"
        variant="secondary"
        @click="emit('cancel')"
    >
        Cancel
    </BaseButton>

    <BaseButton
        type="submit"
        :loading="form.processing"
    >
        {{ mode === 'edit' ? 'Update' : 'Save' }}
    </BaseButton>

    <BaseButton
        v-if="mode !== 'edit'"
        type="button"
        variant="success"
        :loading="form.processing"
        @click="emit('submitAndNew')"
    >
        Save &amp; New
    </BaseButton>

</div>
</form>
</template>
