<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromCollection, WithTitle, WithStyles
{
    public function collection()
    {
        $rows = [
            [],
            [],
            [],
        ];

        $sales = Sale::with(['customer', 'staff', 'detailSales.product'])->get();
        $previousCustomerId = null;

        foreach ($sales as $sale) {
            $isFirstRow = true;
            $sameCustomer = $previousCustomerId === $sale->customer_id && $sale->customer_id !== null;
            $previousCustomerId = $sale->customer_id;

            $discount = 0;
            $finalPrice = $sale->total_price;

            if ($sale->customer && $sale->customer->status === 'old') {
                $discount = $sale->poin;
                $finalPrice = $sale->total_price - $discount;
            }

            foreach ($sale->detailSales as $detail) {
                $rows[] = [
                    'Transaction ID' => '#' . $sale->id . ($sameCustomer ? ' (Repeat)' : ''),
                    'Date' => $sale->sale_date,
                    'Customer Name' => $sale->customer->name ?? 'Non-Member',
                    'Customer Phone' => $sale->customer->no_telp ?? 'N/A',
                    'Customer Points' => $sale->customer->poin ?? 0,
                    'Staff' => $sale->staff->name,
                    'Product Name' => $detail->product->name,
                    'Quantity' => $detail->amount,
                    'Product Price' => 'Rp ' . number_format($detail->product->price, 0, ',', '.'),
                    'Subtotal' => 'Rp ' . number_format($detail->sub_total, 0, ',', '.'),
                    'Total Price' => $isFirstRow ? 'Rp ' . number_format($sale->total_price, 0, ',', '.') : '',
                    'Points Discount' => $isFirstRow ? ($discount > 0 ? 'Rp ' . number_format($discount, 0, ',', '.') : '') : '',
                    'Final Price' => $isFirstRow ? 'Rp ' . number_format($finalPrice, 0, ',', '.') : '',
                    'Total Pay' => $isFirstRow ? 'Rp ' . number_format($sale->total_pay, 0, ',', '.') : '',
                    'Total Return' => $isFirstRow ? 'Rp ' . number_format($sale->total_return, 0, ',', '.') : '',
                    'Points Earned' => $isFirstRow ? $sale->poin : '',
                ];
                $isFirstRow = false;
            }
        }

        return collect($rows);
    }

    public function title(): string
    {
        return 'Transaction Report';
    }

    public function styles(Worksheet $sheet)
    {

        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', 'IndoApril Transaction Report');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue('A2', 'Generated on: ' . now()->format('d F Y'));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'size' => 12
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        $sheet->getStyle('A3:O3')->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'rgb' => 'E2E8F0'
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ]);

        $sheet->setCellValue('A3', 'Transaction ID');
        $sheet->setCellValue('B3', 'Date');
        $sheet->setCellValue('C3', 'Customer Name');
        $sheet->setCellValue('D3', 'Phone Number');
        $sheet->setCellValue('E3', 'Points');
        $sheet->setCellValue('F3', 'Staff Name');
        $sheet->setCellValue('G3', 'Product Name');
        $sheet->setCellValue('H3', 'Quantity');
        $sheet->setCellValue('I3', 'Unit Price');
        $sheet->setCellValue('J3', 'Subtotal');
        $sheet->setCellValue('K3', 'Total Price');
        $sheet->setCellValue('L3', 'Points Discount');
        $sheet->setCellValue('M3', 'Final Price');
        $sheet->setCellValue('N3', 'Amount Paid');
        $sheet->setCellValue('O3', 'Change');
        $sheet->setCellValue('P3', 'Points Earned');

        foreach(range('A','P') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // align
        $lastRow = $sheet->getHighestRow();
        $lastColumn = 'P';

        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);

        // autoSize
        foreach(range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            3 => ['font' => ['bold' => true]],
        ];
    }
}
