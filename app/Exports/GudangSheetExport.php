<?php

namespace App\Exports;

use App\Models\WarehouseZone;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class GudangSheetExport implements FromView, WithTitle, WithStyles
{
    protected $gudangs;

    public function __construct($gudangs)
    {
        $this->gudangs = $gudangs;
    }

    public function view(): View
    {
        $allGroups = [];
        $letters = [];

        foreach ($this->gudangs as $index => $gudang) {
            $groups = [];
            foreach ($gudang->racks as $rack) {
                $blockCode = $rack->block_code;
                if (preg_match('/^([A-Z])(\d+)$/', $blockCode, $matches)) {
                    $letter = $matches[1];
                    $number = (int)$matches[2];

                    if (!isset($groups[$letter])) {
                        $groups[$letter] = [];
                        if (!in_array($letter, $letters)) {
                            $letters[] = $letter;
                        }
                    }
                    
                    $pipesByCategory = [];
                    foreach ($rack->inventories as $inv) {
                        if ($inv->product) {
                            $catName = strtoupper(trim($inv->product->category ? $inv->product->category->name : 'PIPA'));
                            $size = rtrim($inv->product->nominal_size, '"') . '"';
                            $spec = strtoupper(trim($inv->product->spec_name));
                            
                            $desc = trim("{$size} {$spec}");
                            if (!isset($pipesByCategory[$catName])) {
                                $pipesByCategory[$catName] = [];
                            }
                            if (!in_array($desc, $pipesByCategory[$catName])) {
                                $pipesByCategory[$catName][] = $desc;
                            }
                        }
                    }
                    
                    $finalPipes = [];
                    foreach ($pipesByCategory as $cat => $items) {
                        $finalPipes[] = $cat . ' ' . implode(' + ', $items);
                    }
                    $content = empty($finalPipes) ? '-' : implode(" \n", $finalPipes);

                    $groups[$letter][$number] = [
                        'code' => $blockCode,
                        'content' => $content
                    ];
                }
            }
            $allGroups[$index] = $groups;
        }

        sort($letters);

        $mergedGroups = [];
        foreach ($letters as $letter) {
            $mergedGroups[$letter] = [];
            foreach ($this->gudangs as $index => $gudang) {
                $formattedRow = [];
                for ($i = 3; $i >= 1; $i--) {
                    $formattedRow[] = $allGroups[$index][$letter][$i] ?? [
                        'code' => "{$letter}{$i}",
                        'content' => '-'
                    ];
                }
                $mergedGroups[$letter][$index] = $formattedRow;
            }
        }

        return view('print.excel-gudang-side-by-side', [
            'gudangs' => $this->gudangs,
            'mergedGroups' => $mergedGroups
        ]);
    }

    public function title(): string
    {
        $names = [];
        foreach ($this->gudangs as $g) {
            $parts = explode(' / ', $g->name);
            $names[] = $parts[0];
        }
        return implode(' & ', $names);
    }

    public function styles(Worksheet $sheet): ?array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        // Define base alignment
        $alignment = [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
            'wrapText' => true,
        ];
        
        $border = [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['argb' => 'FF000000'],
            ],
            'outline' => [
                'borderStyle' => Border::BORDER_MEDIUM,
                'color' => ['argb' => 'FF000000'],
            ],
        ];

        // Apply borders and alignment only to data columns (A-C and E-G), excluding Title and Footer
        $gridEndRow = $highestRow - 2;
        
        $sheet->getStyle("A4:C{$gridEndRow}")->applyFromArray([
            'borders' => $border,
            'alignment' => $alignment,
        ]);
        
        if ($highestColumn >= 'E') {
            $sheet->getStyle("E4:G{$gridEndRow}")->applyFromArray([
                'borders' => $border,
                'alignment' => $alignment,
            ]);
        }

        // Apply Header styling (Orange) for even rows in the grid
        for ($i = 4; $i <= $gridEndRow; $i += 2) {
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => 'FFE47867'],
                ],
            ];
            
            $sheet->getStyle("A{$i}:C{$i}")->applyFromArray($headerStyle);
            if ($highestColumn >= 'E') {
                $sheet->getStyle("E{$i}:G{$i}")->applyFromArray($headerStyle);
            }
            $sheet->getRowDimension($i)->setRowHeight(25);
        }

        // Apply Content styling for odd rows in the grid
        for ($i = 5; $i <= $gridEndRow; $i += 2) {
            $sheet->getRowDimension($i)->setRowHeight(90);
        }
        
        // Setup Title Row height
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(10); // spacer

        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(30);
        
        // Gap column
        $sheet->getColumnDimension('D')->setWidth(5);
        
        // Second warehouse columns
        $sheet->getColumnDimension('E')->setWidth(30);
        $sheet->getColumnDimension('F')->setWidth(30);
        $sheet->getColumnDimension('G')->setWidth(30);

        // Setup Print Configuration
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        
        $sheet->getPageMargins()->setTop(0.5);
        $sheet->getPageMargins()->setRight(0.25);
        $sheet->getPageMargins()->setLeft(0.25);
        $sheet->getPageMargins()->setBottom(0.5);

        return null;
    }
}
