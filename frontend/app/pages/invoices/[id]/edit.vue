<script setup lang="ts">
import * as zod from 'zod'
import type {FormSubmitEvent} from '@nuxt/ui'
import {CalendarDate} from '@internationalized/date'

const route = useRoute()
const config = useRuntimeConfig()

const STATUSES = ['Pending', 'Approved', 'Rejected'] as const
const CURRENCIES = ['UAH', 'EUR', 'USD'] as const

const DEFAULT_CALENDAR_DATE = new CalendarDate(2022, 1, 10)

const issueDate = shallowRef(DEFAULT_CALENDAR_DATE)
const dueDate = shallowRef(DEFAULT_CALENDAR_DATE)

type Invoice = {
    id: number
    number: string
    supplier_name: string
    supplier_tax_id: string
    net_amount: number
    vat_amount: number
    gross_amount: number
    status: typeof STATUSES[number]
    currency: typeof CURRENCIES[number]
    issue_date: string
    due_date: string
}

const {data: response, pending, error} = await useFetch<{ data: Invoice }>(`/invoices/${route.params.id}`, {
    baseURL: import.meta.server ? config.apiInternalBase : config.public.apiBase
})

const StatusEnum = zod.enum(STATUSES)
const CurrencyEnum = zod.enum(CURRENCIES)

const minString = (requiredMessage: string, min = 8) =>
    zod.string(requiredMessage).min(min, `Must be at least ${min} characters`)

const money = () =>
    zod
        .coerce
        .number()
        .positive('Must be greater than 0')
        .multipleOf(0.01, 'Must have at most 2 decimal places')

const requiredDate = () =>
    zod.date({
        error: 'Date is required',
    })

const schema = zod
    .object({
        number: minString('Number is required'),
        supplier_name: minString('Supplier name is required'),
        supplier_tax_id: minString('Supplier tax id is required', 3),

        net_amount: money(),
        vat_amount: money(),
        gross_amount: money(),

        status: StatusEnum,
        currency: CurrencyEnum,

        issue_date: requiredDate(),
        due_date: requiredDate(),
    })
    .refine(({due_date, issue_date}) => due_date >= issue_date, {
        message: 'Due date must be after or equal the issue date',
        path: ['due_date'],
    })

type Schema = zod.output<typeof schema>

type SubmitStatus = {
    loading: boolean
    success: string | null
    error: string | null
}

const state = reactive<Partial<Schema>>({})

const submitStatus = ref<SubmitStatus>({
    loading: false,
    success: null,
    error: null,
})

const dateFormat = {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
} as const

const toast = useToast()

const statuses = [...STATUSES]
const currencies = [...CURRENCIES]

watch(response, (value) => {
    const data = value?.data
    if (!data) return

    const {issue_date, due_date, ...rest} = data
    Object.assign(state, rest)

    if (issue_date) {
        const [year, month, day] = issue_date.split('-').map(Number)
        issueDate.value = new CalendarDate(year, month, day)
    }

    if (due_date) {
        const [year, month, day] = due_date.split('-').map(Number)
        dueDate.value = new CalendarDate(year, month, day)
    }
}, {immediate: true})

watch(issueDate, (value) => {
    if (value) {
        state.issue_date = new Date(`${value.year}-${String(value.month).padStart(2, '0')}-${String(value.day).padStart(2, '0')}`)
    }
}, {immediate: true})

watch(dueDate, (value) => {
    if (value) {
        state.due_date = new Date(`${value.year}-${String(value.month).padStart(2, '0')}-${String(value.day).padStart(2, '0')}`)
    }
}, {immediate: true})

watchEffect(() => {
    state.gross_amount = Number(state.net_amount || 0) + Number(state.vat_amount || 0)
})

async function onSubmit(_event: FormSubmitEvent<Schema>) {
    submitStatus.value.loading = true
    submitStatus.value.success = null
    submitStatus.value.error = null

    const payload = {
        ...state,
        issue_date: formatDate(state.issue_date),
        due_date: formatDate(state.due_date),
    }

    try {
        await $fetch(`/invoices/${route.params.id}`, {
            baseURL: config.public.apiBase,
            method: 'PUT',
            body: JSON.stringify(payload),
            headers: {
                'Content-Type': 'application/json',
            },
        })

        submitStatus.value.success = 'Invoice has been updated successfully.'

        toast.add({
            title: 'Invoice updated',
            description: 'The invoice was updated successfully.',
            color: 'success',
            icon: 'i-lucide-circle-check',
        })

    } catch (error) {
        submitStatus.value.error = getSubmitErrorMessage(error)

        toast.add({
            title: 'Error',
            description: submitStatus.value.error ?? 'Something went wrong.',
            color: 'error',
            icon: 'i-lucide-circle-x',
        })
    } finally {
        submitStatus.value.loading = false
    }
}

function formatDate(date?: Date): string | null {
    return date?.toISOString().split('T')[0] ?? null
}

function getSubmitErrorMessage(error: unknown): string {
    if (
        typeof error === 'object' &&
        error !== null &&
        'data' in error &&
        typeof error.data === 'object' &&
        error.data !== null &&
        'message' in error.data &&
        typeof error.data.message === 'string'
    ) {
        return error.data.message
    }

    return 'Something went wrong.'
}
</script>

<template>
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
        <UAlert
            v-if="error"
            color="error"
            variant="soft"
            title="Failed to load invoice"
            :description="error.message"
        />

        <UCard v-else-if="pending">
            <div class="space-y-4">
                <USkeleton class="h-10 w-full" />
                <USkeleton class="h-10 w-full" />
                <USkeleton class="h-10 w-full" />
                <USkeleton class="h-10 w-full" />
                <USkeleton class="h-10 w-full" />
            </div>
        </UCard>

        <InvoiceForm
            v-else
            v-model:issue-date="issueDate"
            v-model:due-date="dueDate"
            title="Edit invoice"
            description="Update supplier, amount and payment details for this invoice."
            submit-label="Save changes"
            submit-icon="i-lucide-save"
            cancel-label="Cancel"
            :schema="schema"
            :state="state"
            :statuses="statuses"
            :currencies="currencies"
            :date-format="dateFormat"
            :loading="submitStatus.loading"
            @submit="onSubmit"
            @cancel="navigateTo('/invoices')"
        />
    </div>
</template>
