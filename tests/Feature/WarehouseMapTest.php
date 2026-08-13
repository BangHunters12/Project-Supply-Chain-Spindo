<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseMapTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_map_returns_four_warehouses_with_thirty_six_blocks_each(): void
    {
        $this->seed();

        $response = $this->getJson('/api/wms/warehouse-map');

        $response->assertOk()->assertJsonPath('status', 'success');

        $warehouses = $response->json('data.warehouses');

        $this->assertCount(4, $warehouses);
        $this->assertSame(
            ['GUDANG-1', 'GUDANG-2', 'GUDANG-3', 'GUDANG-4'],
            array_column($warehouses, 'code'),
        );

        foreach ($warehouses as $warehouse) {
            $this->assertCount(36, $warehouse['blocks']);
            $this->assertCount(6, array_unique(array_column($warehouse['blocks'], 'sloc_code')));
            $this->assertSame(
                collect(range('A', 'L'))->flatMap(fn ($column) => collect([1, 2, 3])->map(fn ($row) => $column . $row))->all(),
                array_column($warehouse['blocks'], 'code'),
            );
            $this->assertSame(
                match ($warehouse['code']) {
                    'GUDANG-1' => ['7AA1', '7AA2', '7AB1', '7AB2', '7AC1', '7AC2'],
                    'GUDANG-2' => ['7BA1', '7BA2', '7BB1', '7BB2', '7BC1', '7BC2'],
                    'GUDANG-3' => ['7CA1', '7CA2', '7CB1', '7CB2', '7CC1', '7CC2'],
                    'GUDANG-4' => ['7DA1', '7DA2', '7DB1', '7DB2', '7DC1', '7DC2'],
                },
                array_values(array_unique(array_column($warehouse['blocks'], 'sloc_code'))),
            );

            $blocks = collect($warehouse['blocks'])->keyBy('code');
            $prefix = match ($warehouse['code']) {
                'GUDANG-1' => '7A',
                'GUDANG-2' => '7B',
                'GUDANG-3' => '7C',
                'GUDANG-4' => '7D',
            };
            $this->assertSame($prefix . 'A1', $blocks['A1']['sloc_code']);
            $this->assertSame($prefix . 'A2', $blocks['A2']['sloc_code']);
            $this->assertSame($prefix . 'A2', $blocks['A3']['sloc_code']);
            $this->assertSame($prefix . 'C1', $blocks['L1']['sloc_code']);
            $this->assertSame($prefix . 'C2', $blocks['L2']['sloc_code']);
            $this->assertSame($prefix . 'C2', $blocks['L3']['sloc_code']);
            $this->assertSame('L2', $blocks['L2']['code']);
            $this->assertArrayHasKey('inventories', $blocks['A1']);
        }
    }
}
