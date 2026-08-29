<table>
    <tbody>
        <!-- Title Row 1 (Gudang Name) -->
        <tr>
            @php $name1 = explode(' / ', $gudangs[0]->name); $title1 = $name1[0]; @endphp
            <th colspan="3" style="font-size: 14px; text-align: center;">{{ strtoupper($title1) }}</th>
            <th></th>
            @if(isset($gudangs[1]))
                @php $name2 = explode(' / ', $gudangs[1]->name); $title2 = $name2[0]; @endphp
                <th colspan="3" style="font-size: 14px; text-align: center;">{{ strtoupper($title2) }}</th>
            @else
                <th colspan="3"></th>
            @endif
        </tr>
        <!-- Title Row 2 (Identitas) -->
        <tr>
            <th colspan="3" style="font-weight: bold; font-size: 16px; text-align: center;">IDENTITAS PIPA PER BLOK</th>
            <th></th>
            @if(isset($gudangs[1]))
                <th colspan="3" style="font-weight: bold; font-size: 16px; text-align: center;">IDENTITAS PIPA PER BLOK</th>
            @else
                <th colspan="3"></th>
            @endif
        </tr>
        <tr>
            <td colspan="7"></td>
        </tr>
        @foreach($mergedGroups as $letter => $gudangBlocksArray)
            <!-- Header Row -->
            <tr>
                @foreach($gudangBlocksArray as $index => $blocks)
                    @foreach($blocks as $block)
                        <th>{{ $block['code'] }}</th>
                    @endforeach
                    @if(!$loop->last)
                        <th></th> <!-- Spacer -->
                    @endif
                @endforeach
            </tr>
            <!-- Content Row -->
            <tr>
                @foreach($gudangBlocksArray as $index => $blocks)
                    @foreach($blocks as $block)
                        <td>{{ $block['content'] }}</td>
                    @endforeach
                    @if(!$loop->last)
                        <td></td> <!-- Spacer -->
                    @endif
                @endforeach
            </tr>
        @endforeach
        <tr>
            <td colspan="7"></td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: right; font-weight: bold; font-size: 11px; color: #555555;">PRINTED ON {{ now('Asia/Jakarta')->format('d/m/Y') }}</td>
            <td></td>
            @if(isset($gudangs[1]))
                <td colspan="3" style="text-align: right; font-weight: bold; font-size: 11px; color: #555555;">PRINTED ON {{ now('Asia/Jakarta')->format('d/m/Y') }}</td>
            @else
                <td colspan="3"></td>
            @endif
        </tr>
    </tbody>
</table>
