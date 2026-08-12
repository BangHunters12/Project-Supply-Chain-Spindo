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
            $this->assertCount(12, array_unique(array_column($warehouse['blocks'], 'sloc_code')));
            $this->assertSame(
                collect(range('A', 'L'))->flatMap(fn ($column) => collect([1, 2, 3])->map(fn ($row) => $column . $row))->all(),
                array_column($warehouse['blocks'], 'code'),
            );
            $this->assertSame(
                ['7AA1', '7AA2', '7AA3', '7AA4', '7AA5', '7AA6', '7AA7', '7AA8', '7AA9', '7AA10', '7AA11', '7AA12'],
                array_values(array_unique(array_column($warehouse['blocks'], 'sloc_code'))),
            );

            $blocks = collect($warehouse['blocks'])->keyBy('code');
            $this->assertSame('7AA1', $blocks['A1']['sloc_code']);
            $this->assertSame('7AA1', $blocks['A2']['sloc_code']);
            $this->assertSame('7AA1', $blocks['A3']['sloc_code']);
            $this->assertSame('7AA12', $blocks['L1']['sloc_code']);
            $this->assertSame('7AA12', $blocks['L2']['sloc_code']);
            $this->assertSame('7AA12', $blocks['L3']['sloc_code']);
            $this->assertSame('L2', $blocks['L2']['code']);
            $this->assertArrayHasKey('inventories', $blocks['A1']);
        }
    }
}
