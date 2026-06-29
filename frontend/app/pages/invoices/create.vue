<script setup lang="ts">
import * as zod from 'zod'
import type {FormSubmitEvent} from '@nuxt/ui'
import {CalendarDate} from '@internationalized/date'

const STATUSES = ['Pending', 'Approved', 'Rejected'] as const
const CURRENCIES = ['UAH', 'EUR', 'USD'] as const

const DEFAULT_CALENDAR_DATE = new CalendarDate(2022, 1, 10)

const dateFormat = {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
} as const

const toast = useToast()

const issueDate = shallowRef(DEFAULT_CALENDAR_DATE)
const dueDate = shallowRef(DEFAULT_CALENDAR_DATE)

const config = useRuntimeConfig()

const statuses = [...STATUSES]
const currencies = [...CURRENCIES]

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
        description: zod.string().max(1000, 'Must be at most 1000 characters').optional(),
        supplier_name: minString('Supplier name is required'),
        supplier_tax_id: minString('Supplier tax id is required', 1),

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

const createInitialState = (): Partial<Schema> => ({
    number: '',
    description: '',
    supplier_name: '',
    supplier_tax_id: '',
    net_amount: 0,
    vat_amount: 0,
    gross_amount: 0,
    status: STATUSES[0],
    currency: CURRENCIES[0],
    issue_date: DEFAULT_CALENDAR_DATE.toDate('UTC'),
    due_date: DEFAULT_CALENDAR_DATE.toDate('UTC'),
})

const state = reactive<Partial<Schema>>(createInitialState())

const submitStatus = ref<SubmitStatus>({
    loading: false,
    success: null,
    error: null,
})

watchEffect(() => {
    state.gross_amount = Number(state.net_amount || 0) + Number(state.vat_amount || 0)
})

watch(issueDate, (value) => {
    state.issue_date = value?.toDate('UTC')
}, {immediate: true})

watch(dueDate, (value) => {
    state.due_date = value?.toDate('UTC')
}, {immediate: true})


async function onSubmit(_event: FormSubmitEvent<Schema>) {
    submitStatus.value.loading = true
    submitStatus.value.success = null
    submitStatus.value.error = null

    const payload = {
        ...state,
        issue_date: formatDate(state.issue_date),
        due_date: formatDate(state.due_date),
    }

    type Invoice = {
        id: number
        number: string
        description?: string | null
        supplier_name: string
        supplier_tax_id: string
        net_amount: number
        vat_amount: number
        gross_amount: number
        status: typeof STATUSES[number]
        currency: typeof CURRENCIES[number]
        issue_date: string
        due_date: string
        created_at?: string
        updated_at?: string
    }

    try {
        await $fetch<{ data: Invoice[] }>('/invoices', {
            baseURL: config.public.apiBase,
            method: 'POST',
            body: payload,
        })

        submitStatus.value.success = 'Invoice has been created successfully.'

        toast.add({
            title: 'Invoice created',
            description: 'The invoice was added successfully.',
            color: 'success',
            icon: 'i-lucide-circle-check',
        })

        resetState();
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

function resetState() {
    Object.assign(state, createInitialState())

    issueDate.value = DEFAULT_CALENDAR_DATE
    dueDate.value = DEFAULT_CALENDAR_DATE
}
</script>

<template>
    <InvoiceForm
        v-model:issue-date="issueDate"
        v-model:due-date="dueDate"
        title="Create invoice"
        description="Fill in supplier, amount and payment details to create a new invoice."
        submit-label="Create invoice"
        submit-icon="i-lucide-plus"
        :show-reset="true"
        :schema="schema"
        :state="state"
        :statuses="statuses"
        :currencies="currencies"
        :date-format="dateFormat"
        :loading="submitStatus.loading"
        @submit="onSubmit"
        @reset="resetState"
        @cancel="navigateTo('/invoices')"
    />
</template>
