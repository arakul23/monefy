<script setup lang="ts">
const STATUSES = ['Pending', 'Approved', 'Rejected'] as const
const CURRENCIES = ['UAH', 'EUR', 'USD'] as const
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
    due_date: string,
    updated_at: string
}

const route = useRoute()
const config = useRuntimeConfig()

const { data: response, pending, error } = await useFetch<{ data: Invoice }>(
    `/invoices/${route.params.id}`,
    {
        baseURL: import.meta.server ? config.apiInternalBase : config.public.apiBase,
        key: `invoice-${route.params.id}`,
    }
)

const invoice = computed(() => response.value?.data ?? null)

const invoiceTitle = computed(() => {
    if (!invoice.value) {
        return 'Invoice'
    }

    return invoice.value.number
        ? `Invoice #${invoice.value.number}`
        : `Invoice #${invoice.value.id}`
})

const formatDate = (value?: unknown) => {
    if (!value || typeof value !== 'string') {
        return '—'
    }

    return new Intl.DateTimeFormat('UK', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    }).format(new Date(value))
}

const colors = {
    'Pending': 'neutral',
    'Approved': 'success',
    'Rejected': 'error'
}

const statusColor = computed(() => {
    return colors[invoice.value?.status ?? ''];
})

const details = computed(() => {
    if (!invoice.value) {
        return []
    }

    return [
        {
            label: 'ID',
            value: invoice.value.id ?? '—'
        },
        {
            label: 'Number',
            value: invoice.value.number ?? '—'
        },
        {
            label: 'Supplier name',
            value:  invoice.value.supplier_name ?? '—'
        },
        {
            label: 'Supplier tax id',
            value:  invoice.value.supplier_tax_id ?? '—'
        },
        {
            label: 'Net amount',
            value: invoice.value.net_amount ?? '—'
        },
        {
            label: 'Vat amount',
            value: invoice.value.vat_amount ?? '—'
        },
        {
            label: 'Gross amount',
            value: invoice.value.gross_amount ?? '—'
        },
        {
            label: 'Issue date',
            value: formatDate(invoice.value.issue_date)
        },
        {
            label: 'Due date',
            value: formatDate(invoice.value.due_date)
        },
        {
            label: 'Updated',
            value: formatDate(invoice.value.updated_at)
        }
    ]
})
</script>

<template>
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm text-muted">
                    Просмотр счёта
                </p>
                <h1 class="text-3xl font-semibold tracking-tight">
                    {{ invoiceTitle }}
                </h1>
            </div>

            <UButton
                label="Go back"
                icon="i-lucide-arrow-left"
                variant="soft"
                color="neutral"
                @click="navigateTo('/invoices')"
            />
        </div>

        <UAlert
            v-if="error"
            color="error"
            variant="soft"
            title="Не удалось загрузить счёт"
            :description="error.message"
        />

        <UCard v-else-if="pending">
            <div class="space-y-4">
                <USkeleton class="h-8 w-1/3" />
                <USkeleton class="h-24 w-full" />
                <USkeleton class="h-24 w-full" />
            </div>
        </UCard>

        <template v-else-if="invoice">
            <div class="grid gap-4 md:grid-cols-3">
                <UCard class="md:col-span-2">
                    <div class="flex flex-col gap-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <UBadge
                                :color="statusColor"
                                variant="soft"
                                size="lg"
                            >
                                {{ invoice.status || 'Без статуса' }}
                            </UBadge>
                        </div>

                        <USeparator />

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div
                                v-for="item in details"
                                :key="item.label"
                                class="rounded-lg border border-default bg-muted/30 p-4"
                            >
                                <p class="text-sm text-muted">
                                    {{ item.label }}
                                </p>
                                <p class="mt-1 font-medium">
                                    {{ item.value }}
                                </p>
                            </div>
                        </div>
                    </div>
                </UCard>

                <UCard>
                    <template #header>
                        <div class="flex items-center gap-2">
                            <UIcon name="i-lucide-file-text" class="size-5 text-primary" />
                            <h2 class="font-semibold">
                                Information
                            </h2>
                        </div>
                    </template>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-muted">
                                Invoice ID
                            </p>
                            <p class="font-medium">
                                {{ invoice.id ?? '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted">
                                Currency
                            </p>
                            <p class="font-medium">
                                {{ invoice.currency || '—' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted">
                                Status
                            </p>
                            <UBadge :color="statusColor" variant="subtle">
                                {{ invoice.status || '—' }}
                            </UBadge>
                        </div>
                    </div>
                </UCard>
            </div>
        </template>
        <template v-else>
            <UAlert
                color="warning"
                variant="soft"
                title="Invoice not found"
                description="Check the account ID or try returning to the list."
            />
        </template>
    </div>
</template>
