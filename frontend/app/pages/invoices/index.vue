<script setup lang="ts">
import type {TableColumn, TableRow} from "@nuxt/ui/components/Table.vue";
import {UButton, UDropdownMenu} from "#components";
import {getPaginationRowModel} from '@tanstack/vue-table'


const STATUSES = ['Pending', 'Approved', 'Rejected'] as const
const CURRENCIES = ['UAH', 'EUR', 'USD'] as const

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
const UBadge = resolveComponent('UBadge')
const config = useRuntimeConfig()
const table = useTemplateRef('table')

const {data: response, pending, error, refresh} = await useFetch<{ data: Invoice[] }>('/invoices', {
    baseURL: import.meta.server ? config.apiInternalBase : config.public.apiBase
})

const invoices = computed(() => response.value?.data ?? [])

const columns: TableColumn<Invoice>[] = [
    {
        accessorKey: 'number',
        header: 'Number',
    },
    {
        accessorKey: 'supplier_name',
        header: 'Supplier name',
    },
    {
        accessorKey: 'gross_amount',
        header: 'Gross amount',
    },
    {
        accessorKey: 'status',
        header: 'Status',
        cell: ({row}) => {
            const color = {
                Approved: 'success' as const,
                Rejected: 'error' as const,
                Pending: 'neutral' as const
            }[row.getValue('status') as string]

            return h(UBadge, {class: 'capitalize', variant: 'subtle', color}, () =>
                row.getValue('status')
            )
        }
    },
    {
        accessorKey: 'due_date',
        header: 'Due date',
        cell: ({row}) => {
            return new Date(row.getValue('due_date')).toLocaleString('UK', {
                day: 'numeric',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            })
        },

    },

    {
        id: 'actions',
        meta: {
            class: {td: 'text-right'}
        },
        cell: ({row}) => {
            const items = getRowItems(row)

            if (!items || items.length === 0) {
                return h('div', {class: 'w-9 h-9'})
            }

            return h(
                UDropdownMenu,
                {
                    content: {align: 'end'},
                    items: items, // передаем правильный двумерный массив
                    'aria-label': 'Actions dropdown'
                },
                () =>
                    h(UButton, {
                        icon: 'i-lucide-ellipsis-vertical',
                        color: 'neutral',
                        variant: 'ghost',
                        'aria-label': 'Actions dropdown'
                    })
            )
        }
    }
]

const pagination = ref({
    pageIndex: 0,
    pageSize: 2
})

function getRowItems(row: any) {


    return [
        [
            {
                label: 'Actions',
                type: 'label' as const
            },
            {
                label: 'Edit',
                icon: 'i-lucide-pencil', // по желанию
                onSelect() {
                    navigateTo(`/invoices/${row.original.id}/edit`)
                }
            }
        ]
    ]
}


function onSelect(e: Event, row: TableRow<Invoice>) {
    navigateTo(`/invoices/${row.original.id}`)

    row.toggleSelected(!row.getIsSelected())
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <div class="flex justify-end">
            <UButton label="Create invoice" @click="navigateTo('/invoices/create')"/>
        </div>

        <UAlert
            v-if="error"
            color="error"
            variant="soft"
            title="Не удалось загрузить счета"
            :description="error.message"
        >
            <template #actions>
                <UButton label="Try again" color="error" variant="soft" @click="refresh()"/>
            </template>
        </UAlert>

        <div v-else>
            <UCard v-if="pending">
                <div class="space-y-4">
                    <USkeleton class="h-10 w-full"/>
                    <USkeleton class="h-10 w-full"/>
                    <USkeleton class="h-10 w-full"/>
                </div>
            </UCard>

            <template v-else>
                <div v-if="invoices.length" class="flex flex-col gap-4">
                    <UTable
                        ref="table"
                        v-model:pagination="pagination"
                        :data="invoices"
                        class="flex-1 cursor-pointer"
                        :onSelect="onSelect"
                        :columns="columns"
                        :pagination-options="{
                    getPaginationRowModel: getPaginationRowModel()
                }"
                    />

                    <div class="flex justify-end border-t border-default pt-4 px-4">
                        <UPagination
                            :page="(table?.tableApi?.getState().pagination.pageIndex || 0) + 1"
                            :items-per-page="table?.tableApi?.getState().pagination.pageSize"
                            :total="table?.tableApi?.getFilteredRowModel().rows.length"
                            @update:page="(p) => table?.tableApi?.setPageIndex(p - 1)"
                        />
                    </div>
                </div>

                <UAlert
                    v-else
                    color="warning"
                    variant="soft"
                    title="Счета не найдены"
                    description="Создайте первый счет, чтобы он появился в списке."
                />
            </template>
        </div>
    </div>

</template>
