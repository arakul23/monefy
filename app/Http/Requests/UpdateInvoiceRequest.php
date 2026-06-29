<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\InvoiceStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->route('invoice')->status !== InvoiceStatus::Pending->value) {
            abort(403, 'Invoice is not editable.');
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'number' => ['required', 'string', 'min:8', 'max:255'],
        ];

        return array_merge((new CreateInvoiceRequest())->rules(), $rules);
    }
}
