<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->string('supplier_name');
            $table->string('supplier_tax_id');
            $table->decimal('net_amount');
            $table->decimal('vat_amount');
            $table->decimal('gross_amount');
            $table->string('currency', 3);
            $table->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $table->date('issue_date');
            $table->date('due_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
