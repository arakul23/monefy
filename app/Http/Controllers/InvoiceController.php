<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return InvoiceResource::collection(Invoice::all()->sortByDesc('created_at'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateInvoiceRequest $request)
    {
        Invoice::create($request->validated());

        return response()->json(status: 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        return InvoiceResource::make($invoice);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Invoice $invoice, UpdateInvoiceRequest $request): JsonResponse
    {
        try {
            $invoice->update($request->validated());
        } catch (QueryException $e) {
            return response()->json(['message' => 'Failed to update invoice'], 500);
        }

        return response()->json(InvoiceResource::make($invoice), 200);
    }
}
