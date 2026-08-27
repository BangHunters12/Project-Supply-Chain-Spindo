<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identitas Pipa Per Blok - {{ $gudang->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 11px;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            border-collapse: collapse;
            table-layout: fixed;
        }
        th, td {
            border: 2px solid #000;
            text-align: center;
            padding: 5px;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .block-header {
            background-color: #e47867 !important; /* Warna merah bata/oranye sesuai foto */
            color: #000;
            font-weight: bold;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .block-content {
            height: 80px; /* Sesuai proporsi di foto agar sel cukup tinggi */
            background-color: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            body {
                margin: 0;
            }
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header-title">IDENTITAS PIPA PER BLOK</div>
    
    <table>
        <tbody>
            @foreach($groups as $letter => $blocks)
                <!-- Header Row untuk Grup Blok (misal: A3, A2, A1) -->
                <tr>
                    @foreach($blocks as $block)
                        <th class="block-header">{{ $block['code'] }}</th>
                    @endforeach
                </tr>
                <!-- Content Row (Daftar Pipa) -->
                <tr>
                    @foreach($blocks as $block)
                        <td class="block-content">{{ $block['content'] }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
