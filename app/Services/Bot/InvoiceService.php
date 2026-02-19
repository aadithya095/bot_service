<?php

namespace App\Services\Bot;

use App\Models\Invoice;

class InvoiceService
{
    public function getLatest(int $limit = 10)
    {
        return Invoice::latest()->take($limit)->get();
    }

    public function findByNumber(string $number): ?Invoice
    {
        return Invoice::where('invoice_number', $number)->first();
    }
}