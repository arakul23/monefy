<?php

namespace Database\Seeders;

use App\Models\Invoice;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Seed invoice records.
     */
    public function run(): void
    {
        $invoices = [
            [
                'number' => 'INV-2026-0001',
                'supplier_name' => 'Northwind Supplies LLC',
                'supplier_tax_id' => 'US123456789',
                'net_amount' => 500.00,
                'vat_amount' => 100.00,
                'gross_amount' => 600.00,
                'currency' => 'USD',
                'status' => 'Approved',
                'issue_date' => '2026-06-01',
                'due_date' => '2026-06-15',
            ],
            [
                'number' => 'INV-2026-0002',
                'supplier_name' => 'Acme Office Goods',
                'supplier_tax_id' => 'US987654321',
                'net_amount' => 275.50,
                'vat_amount' => 55.10,
                'gross_amount' => 330.60,
                'currency' => 'USD',
                'status' => 'Pending',
                'issue_date' => '2026-06-05',
                'due_date' => '2026-06-20',
            ],
            [
                'number' => 'INV-2026-0003',
                'supplier_name' => 'Kyiv Tech Services',
                'supplier_tax_id' => 'UA4012345678',
                'net_amount' => 1200.00,
                'vat_amount' => 240.00,
                'gross_amount' => 1440.00,
                'currency' => 'UAH',
                'status' => 'Approved',
                'issue_date' => '2026-06-10',
                'due_date' => '2026-06-30',
            ],
            [
                'number' => 'INV-2026-0004',
                'supplier_name' => 'Bright Cloud Hosting',
                'supplier_tax_id' => 'GB112233445',
                'net_amount' => 89.99,
                'vat_amount' => 18.00,
                'gross_amount' => 107.99,
                'currency' => 'EUR',
                'status' => 'Pending',
                'issue_date' => '2026-06-18',
                'due_date' => '2026-07-02',
            ],
            [
                'number' => 'INV-2026-0005',
                'supplier_name' => 'Rejected Vendor Ltd',
                'supplier_tax_id' => 'DE998877665',
                'net_amount' => 760.00,
                'vat_amount' => 152.00,
                'gross_amount' => 912.00,
                'currency' => 'EUR',
                'status' => 'Rejected',
                'issue_date' => '2026-06-22',
                'due_date' => '2026-07-06',
            ],
            [
                'number' => 'INV-2026-0006',
                'supplier_name' => 'Lviv Digital Agency',
                'supplier_tax_id' => 'UA3098765432',
                'net_amount' => 3400.00,
                'vat_amount' => 680.00,
                'gross_amount' => 4080.00,
                'currency' => 'UAH',
                'status' => 'Pending',
                'issue_date' => '2026-06-20',
                'due_date' => '2026-07-10',
            ],
            [
                'number' => 'INV-2026-0007',
                'supplier_name' => 'Baltic Freight Partners',
                'supplier_tax_id' => 'EE445566778',
                'net_amount' => 1850.00,
                'vat_amount' => 370.00,
                'gross_amount' => 2220.00,
                'currency' => 'EUR',
                'status' => 'Approved',
                'issue_date' => '2026-06-12',
                'due_date' => '2026-07-12',
            ],
            [
                'number' => 'INV-2026-0008',
                'supplier_name' => 'SoftServe Solutions',
                'supplier_tax_id' => 'UA2011223344',
                'net_amount' => 9200.00,
                'vat_amount' => 1840.00,
                'gross_amount' => 11040.00,
                'currency' => 'UAH',
                'status' => 'Rejected',
                'issue_date' => '2026-05-28',
                'due_date' => '2026-06-28',
            ],
            [
                'number' => 'INV-2026-0009',
                'supplier_name' => 'Global Print Media Inc',
                'supplier_tax_id' => 'US556677889',
                'net_amount' => 430.00,
                'vat_amount' => 86.00,
                'gross_amount' => 516.00,
                'currency' => 'USD',
                'status' => 'Pending',
                'issue_date' => '2026-06-25',
                'due_date' => '2026-07-25',
            ],
            [
                'number' => 'INV-2026-0010',
                'supplier_name' => 'Alpine Hardware GmbH',
                'supplier_tax_id' => 'AT334455667',
                'net_amount' => 2100.00,
                'vat_amount' => 420.00,
                'gross_amount' => 2520.00,
                'currency' => 'EUR',
                'status' => 'Approved',
                'issue_date' => '2026-06-15',
                'due_date' => '2026-07-15',
            ],
        ];

        foreach ($invoices as $invoice) {
            Invoice::updateOrCreate(
                ['number' => $invoice['number']],
                $invoice
            );
        }
    }
}
