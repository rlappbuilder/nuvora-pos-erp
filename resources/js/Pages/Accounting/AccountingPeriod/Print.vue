<script setup>

import { onMounted } from 'vue'

import DocumentLayout from '@/Components/Document/DocumentLayout.vue'
import DocumentHeader from '@/Components/Document/DocumentHeader.vue'
import DocumentSection from '@/Components/Document/DocumentSection.vue'
import DocumentField from '@/Components/Document/DocumentField.vue'
import DocumentDivider from '@/Components/Document/DocumentDivider.vue'
import DocumentFooter from '@/Components/Document/DocumentFooter.vue'
import { usePage } from '@inertiajs/vue3'
import DocumentInfo from '@/Components/Document/DocumentInfo.vue'
const page = usePage()
const props = defineProps({

    cashBank: Object,

})

onMounted(() => {

    setTimeout(() => {

        window.print()

    }, 500)

})

window.onafterprint = () => {

    window.history.back()

}

</script>

<template>


<DocumentLayout>

<DocumentHeader

    title="Cash Bank Information"

/>
<DocumentInfo

    :document-id="cashBank.code"

    :printed-by="page.props.auth?.user?.name ?? 'System'"

    :printed-at="new Date().toLocaleString('id-ID')"

    :version="page.props.app.version"

/>
<!-- ====================================== -->
<!-- General Information -->
<!-- ====================================== -->

<DocumentSection

    title="General Information"

>

    <DocumentField

        label="Code"

        :value="cashBank.code"

    />

    <DocumentField

        label="Name"

        :value="cashBank.name"

    />

    <DocumentField

        label="Type"

        :value="cashBank.type"

    />

    <DocumentField

        label="Status"

        :value="cashBank.status

            ? 'Active'

            : 'Inactive'"

    />

</DocumentSection>

<DocumentDivider />

<!-- ====================================== -->
<!-- Bank Information -->
<!-- ====================================== -->

<DocumentSection

    title="Bank Information"

>

    <DocumentField

        label="Bank Name"

        :value="cashBank.bank_name"

    />

    <DocumentField

        label="Branch"

        :value="cashBank.bank_branch"

    />

    <DocumentField

        label="Account Number"

        :value="cashBank.account_number"

    />

    <DocumentField

        label="Account Holder"

        :value="cashBank.account_holder"

    />

</DocumentSection>

<DocumentDivider />

<!-- ====================================== -->
<!-- Accounting -->
<!-- ====================================== -->

<DocumentSection

    title="Accounting"

>

    <DocumentField

        label="Opening Balance"

        :value="cashBank.opening_balance"

    />

    <DocumentField

        label="Current Balance"

        :value="cashBank.current_balance"

    />

    <DocumentField

        label="COA"

        :value="cashBank.coa?.name"

    />

</DocumentSection>

<DocumentDivider />

<!-- ====================================== -->
<!-- Description -->
<!-- ====================================== -->

<DocumentSection

    title="Description"

>

    <p

        class="
            leading-7
            text-gray-700
        "

    >

        {{

            cashBank.description ||

            '-'

        }}

    </p>

</DocumentSection>

<DocumentFooter

    :printed-by="page.props.auth.user.name"

    :printed-at="new Date().toLocaleString('id-ID')"

    :document-id="cashBank.code"

/>

</DocumentLayout>

</template>

<style>

@media print {

    body {

        background: white;

    }

}

</style>