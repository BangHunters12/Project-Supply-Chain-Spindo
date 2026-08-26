<?php

namespace App\Http\Controllers;

use App\Models\WarehouseZone;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    public function identitasBlok(Request $request, string $code)
    {
        $gudang = WarehouseZone::where('code', $code)->with(['racks.inventories.product.category'])->firstOrFail();

        // Mengelompokkan blok berdasarkan prefix huruf pertamanya (A-L)
        // Format rak: G1-A1 -> prefix A. 
        $groups = [];

        foreach ($gudang->racks as $rack) {
            $blockCode = $rack->block_code; // contoh: A1, B3
            if (preg_match('/^([A-Z])(\d+)$/', $blockCode, $matches)) {
                $letter = $matches[1];
                $number = (int)$matches[2];

                if (!isset($groups[$letter])) {
                    $groups[$letter] = [];
                }
                
                // Get unique pipe descriptions
                $pipes = [];
                foreach ($rack->inventories as $inv) {
                    if ($inv->product) {
                        $catName = $inv->product->category ? $inv->product->category->name : 'PIPA';
                        $size = $inv->product->nominal_size . '"';
                        $spec = $inv->product->spec_name;
                        
                        $desc = trim("{$catName} {$size} {$spec}");
                        $pipes[] = strtoupper($desc);
                    }
                }
                
                $pipes = array_values(array_unique($pipes));
                $content = empty($pipes) ? '-' : implode(' + ', $pipes);

                $groups[$letter][$number] = [
                    'code' => $blockCode,
                    'content' => $content
                ];
            }
        }

        // Urutkan grup secara alfabetikal (A, B, C...)
        ksort($groups);

        // Pastikan setiap grup memiliki kolom 3, 2, 1 (karena formatnya terbalik: A3, A2, A1)
        foreach ($groups as $letter => &$blocks) {
            $formattedRow = [];
            for ($i = 3; $i >= 1; $i--) {
                $formattedRow[] = $blocks[$i] ?? [
                    'code' => "{$letter}{$i}",
                    'content' => '-'
                ];
            }
            $blocks = $formattedRow;
        }

        if ($request->has('excel')) {
            $fileName = "Identitas_Pipa_{$code}.xls";
            $headers = [
                'Content-Type' => 'application/vnd.ms-excel',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ];
            $html = view('print.identitas-blok', compact('gudang', 'groups'))->render();
            return response($html, 200, $headers);
        }

        return view('print.identitas-blok', compact('gudang', 'groups'));
    }
}
