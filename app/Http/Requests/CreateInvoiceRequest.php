<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\InvoiceStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        return [
            'number' => ['required', 'string', 'min:8', 'max:255', 'unique:invoices,number'],
            'supplier_name' => ['required', 'string', 'min:8', 'max:255'],
            'supplier_tax_id' => ['required', 'string', 'min:3', 'max:255'],
            'net_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'vat_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'gross_amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99', function (string $attribute, mixed $value, $fail) {
                $net = $this->json('net_amount', 0);
                $vat = $this->json('vat_amount', 0);

                if (abs($value - ($net + $vat)) > 0.001) {
                    $fail('The gross_amount must equal net_amount + vat_amount.');
                }
            }],
            'currency' => ['required', 'string', 'size:3'],
            'status' => ['required', Rule::enum(InvoiceStatus::class)],
            'issue_date' => ['required', 'date', 'date_format:Y-m-d'],
            'due_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:issue_date'],
        ];
    }
}
