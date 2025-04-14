<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Sale::with(['customer', 'staff', 'detailSales.product'])
            ->get()
            ->map(function ($sale) {
                $products = $sale->detailSales->map(function ($detail) {
                    return $detail->product->name . ' (x' . $detail->amount . ') - Rp ' . number_format($detail->product->price, 0, ',', '.');
                })->join(', ');

                return [
                    'Date' => $sale->sale_date,
                    'Customer Name' => $sale->customer->name ?? 'Non-Member',
                    'Customer Phone' => $sale->customer->no_telp ?? 'N/A',
                    'Customer Points' => $sale->customer->poin ?? 0,
                    'Staff' => $sale->staff->name,
                    'Products' => $products,
                    'Total Price' => $sale->total_price,
                    'Total Pay' => $sale->total_pay,
                    'Total Return' => $sale->total_return,
                    'Total Discount Points' => $sale->poin,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Customer Name',
            'Customer Phone',
            'Customer Points',
            'Staff',
            'Products',
            'Total Price',
            'Total Pay',
            'Total Return',
            'Total Discount Points',
        ];
    }
}
