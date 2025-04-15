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

        foreach ($sales as $sale) {
            $isFirstRow = true;

            $discount = 0;
            $finalPrice = $sale->total_price;

            if ($sale->customer && $sale->customer->status === 'old') {
                $discount = $sale->poin;
                $finalPrice = $sale->total_price - $discount;
            }

            foreach ($sale->detailSales as $detail) {
                $rows[] = [
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

        $sheet->setCellValue('A3', 'Date');
        $sheet->setCellValue('B3', 'Customer Name');
        $sheet->setCellValue('C3', 'Phone Number');
        $sheet->setCellValue('D3', 'Points');
        $sheet->setCellValue('E3', 'Staff Name');
        $sheet->setCellValue('F3', 'Product Name');
        $sheet->setCellValue('G3', 'Quantity');
        $sheet->setCellValue('H3', 'Unit Price');
        $sheet->setCellValue('I3', 'Subtotal');
        $sheet->setCellValue('J3', 'Total Price');
        $sheet->setCellValue('K3', 'Points Discount');
        $sheet->setCellValue('L3', 'Final Price');
        $sheet->setCellValue('M3', 'Amount Paid');
        $sheet->setCellValue('N3', 'Change');
        $sheet->setCellValue('O3', 'Points Earned');

        foreach(range('A','O') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Center align all cells in the worksheet
        $lastRow = $sheet->getHighestRow();
        $lastColumn = 'O';

        $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ]);

        // AutoSize columns
        foreach(range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return [
            3 => ['font' => ['bold' => true]],
        ];
    }
}
