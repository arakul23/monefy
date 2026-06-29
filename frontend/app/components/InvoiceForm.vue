<!-- frontend/app/components/InvoiceForm.vue -->
<script setup lang="ts">
import type {FormSubmitEvent} from '@nuxt/ui'
import type {CalendarDate} from '@internationalized/date'

type InvoiceFormState = {
    number?: string
    description?: string
    supplier_name?: string
    supplier_tax_id?: string
    net_amount?: number
    vat_amount?: number
    gross_amount?: number
    status?: string
    currency?: string
    issue_date?: Date
    due_date?: Date
}

const props = withDefaults(defineProps<{
    title: string
    description: string
    submitLabel: string
    submitIcon?: string
    cancelLabel?: string
    showReset?: boolean
    loading?: boolean
    schema: unknown
    state: Partial<InvoiceFormState>
    statuses: string[]
    currencies: string[]
    dateFormat: Intl.DateTimeFormatOptions
}>(), {
    submitIcon: undefined,
    cancelLabel: 'Cancel',
    showReset: false,
    loading: false,
})

const emit = defineEmits<{
    submit: [event: FormSubmitEvent<Partial<InvoiceFormState>>]
    cancel: []
    reset: []
}>()

const issueDate = defineModel<CalendarDate>('issueDate', {required: true})
const dueDate = defineModel<CalendarDate>('dueDate', {required: true})

const issueDateInput = useTemplateRef('issueDateInput')
const dueDateInput = useTemplateRef('dueDateInput')
</script>

<template>
    <ClientOnly>
        <div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">
                        {{ title }}
                    </h1>
                </div>

                <UButton
                    label="Back to invoices"
                    color="neutral"
                    variant="soft"
                    icon="i-lucide-arrow-left"
                    @click="emit('cancel')"
                />
            </div>

            <UCard>
                <UForm :schema="schema" :state="state" class="space-y-8" @submit="emit('submit', $event)">
                    <div class="space-y-4">
                        <div>
                            <h2 class="text-base font-semibold">
                                Invoice details
                            </h2>
                            <p class="text-sm text-muted">
                                Basic invoice and supplier information.
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <UFormField label="Number" name="number" required>
                                <UInput
                                    v-model="state.number"
                                    placeholder="INV-0001"
                                    class="w-full"
                                />
                            </UFormField>

                            <UFormField label="Supplier tax id" name="supplier_tax_id" required>
                                <UInput
                                    v-model="state.supplier_tax_id"
                                    type="text"
                                    placeholder="Tax ID"
                                    class="w-full"
                                />
                            </UFormField>

                            <UFormField label="Supplier name" name="supplier_name" required class="md:col-span-2">
                                <UInput
                                    v-model="state.supplier_name"
                                    type="text"
                                    placeholder="Supplier company name"
                                    class="w-full"
                                />
                            </UFormField>

                            <UFormField label="Status" name="status" required>
                                <USelect
                                    v-model="state.status"
                                    :items="statuses"
                                    class="w-full"
                                />
                            </UFormField>

                            <UFormField label="Currency" name="currency" required>
                                <USelect
                                    v-model="state.currency"
                                    :items="currencies"
                                    class="w-full"
                                />
                            </UFormField>
                        </div>
                    </div>

                    <USeparator/>

                    <div class="space-y-4">
                        <div>
                            <h2 class="text-base font-semibold">
                                Amounts
                            </h2>
                            <p class="text-sm text-muted">
                                Enter net and VAT amounts. Gross amount is calculated automatically.
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <UFormField label="Net amount" name="net_amount" required>
                                <UInput
                                    v-model="state.net_amount"
                                    type="number"
                                    :step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full"
                                />
                            </UFormField>

                            <UFormField label="VAT amount" name="vat_amount" required>
                                <UInput
                                    v-model="state.vat_amount"
                                    type="number"
                                    :step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    class="w-full"
                                />
                            </UFormField>

                            <UFormField label="Gross amount" name="gross_amount" required>
                                <UInput
                                    v-model="state.gross_amount"
                                    disabled
                                    class="w-full"
                                />
                            </UFormField>
                        </div>
                    </div>

                    <USeparator/>

                    <div class="space-y-4">
                        <div>
                            <h2 class="text-base font-semibold">
                                Dates
                            </h2>
                            <p class="text-sm text-muted">
                                Select issue and due dates for the invoice.
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <UFormField label="Issue date" name="issue_date" required>
                                <UInputDate
                                    ref="issueDateInput"
                                    v-model="issueDate"
                                    :date-format="dateFormat"
                                    class="w-full"
                                >
                                    <template #trailing>
                                        <UPopover :reference="issueDateInput?.inputsRef[3]?.$el">
                                            <UButton
                                                color="neutral"
                                                variant="link"
                                                size="sm"
                                                icon="i-lucide-calendar"
                                                aria-label="Select issue date"
                                                class="px-0"
                                            />

                                            <template #content>
                                                <UCalendar v-model="issueDate" class="p-2"/>
                                            </template>
                                        </UPopover>
                                    </template>
                                </UInputDate>
                            </UFormField>

                            <UFormField label="Due date" name="due_date" required>
                                <UInputDate
                                    ref="dueDateInput"
                                    v-model="dueDate"
                                    :date-format="dateFormat"
                                    class="w-full"
                                >
                                    <template #trailing>
                                        <UPopover :reference="dueDateInput?.inputsRef[3]?.$el">
                                            <UButton
                                                color="neutral"
                                                variant="link"
                                                size="sm"
                                                icon="i-lucide-calendar"
                                                aria-label="Select due date"
                                                class="px-0"
                                            />

                                            <template #content>
                                                <UCalendar v-model="dueDate" class="p-2"/>
                                            </template>
                                        </UPopover>
                                    </template>
                                </UInputDate>
                            </UFormField>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-default pt-6 sm:flex-row sm:justify-end">
                        <UButton
                            v-if="showReset"
                            label="Reset"
                            color="neutral"
                            variant="soft"
                            type="button"
                            @click="emit('reset')"
                        />

                        <UButton
                            v-else
                            :label="cancelLabel"
                            color="neutral"
                            variant="soft"
                            type="button"
                            @click="emit('cancel')"
                        />

                        <UButton
                            :label="submitLabel"
                            type="submit"
                            :icon="submitIcon"
                            :loading="loading"
                        />
                    </div>
                </UForm>
            </UCard>
        </div>
    </ClientOnly>
</template>
