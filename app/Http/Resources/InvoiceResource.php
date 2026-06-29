<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'supplier_name' => $this->supplier_name,
            'supplier_tax_id' => $this->supplier_tax_id,
            'net_amount' => $this->net_amount,
            'vat_amount' => $this->vat_amount,
            'gross_amount' => $this->gross_amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'issue_date' => $this->issue_date,
            'due_date' => $this->due_date,
            'updated_at' => $this->updated_at
        ];
    }
}
