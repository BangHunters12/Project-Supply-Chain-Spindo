<template>
  <section class="space-y-4">
    <div class="flex flex-col justify-between gap-4 border-b border-wms-border pb-5 dark:border-iron-800 sm:flex-row sm:items-end">
      <div>
        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-wms-blue dark:text-sky-400">SC-U7 / DENAH OPERASIONAL</p>
        <h1 class="mt-1 text-2xl font-black tracking-tight text-wms-navy dark:text-iron-100">Denah Gedung Supply Chain</h1>
        <p class="mt-1 max-w-3xl text-sm leading-6 text-wms-muted dark:text-iron-400">Pilih gudang untuk membuka blok, SLOC, dan stok pipa. Pilih blok untuk melihat rincian inventaris.</p>
      </div>
      <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-wide text-slate-500 dark:text-iron-400">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span><span>Data WMS aktif</span>
        <a v-if="activeWarehouse" :href="`/print/gudang/${activeWarehouse.code}`" target="_blank" class="ml-2 rounded border border-slate-300 bg-slate-50 px-2.5 py-1.5 font-sans font-black text-slate-600 transition hover:border-wms-blue hover:bg-sky-50 hover:text-wms-blue dark:border-iron-600 dark:bg-iron-800 dark:text-iron-300 dark:hover:border-cyan-500 dark:hover:bg-cyan-900/40 dark:hover:text-cyan-300">🖨️ Cetak Label</a>
        <a v-if="activeWarehouse" :href="`/print/gudang/${activeWarehouse.code}?excel=1`" target="_blank" download class="ml-2 rounded border border-slate-300 bg-emerald-50 px-2.5 py-1.5 font-sans font-black text-emerald-700 transition hover:border-emerald-500 hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400" title="Export Excel Gudang Ini">📊 Excel (Ini)</a>
        <a href="/print/gudang/all" target="_blank" download class="ml-2 rounded border border-emerald-500 bg-emerald-500 px-2.5 py-1.5 font-sans font-black text-white transition hover:bg-emerald-600 dark:bg-emerald-600 dark:hover:bg-emerald-500 shadow-sm" title="Export Semua Gudang (Multi-Sheet)">📊 Excel (Semua)</a>
        <button v-if="activeWarehouse" type="button" @click="closeWarehouse" class="ml-2 rounded border border-slate-300 px-2.5 py-1.5 font-sans font-black text-slate-600 transition hover:border-safety hover:text-safety dark:border-iron-700 dark:text-iron-300">Kembali ke semua gudang</button>
      </div>
    </div>

    <div v-if="errorMessage" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300" role="alert">{{ errorMessage }} <button type="button" @click="loadMap" class="ml-2 font-bold underline">Coba lagi</button></div>
    <div v-else-if="loading" class="rounded-lg border border-slate-200 bg-white px-4 py-16 text-center font-mono text-xs text-slate-500 dark:border-iron-800 dark:bg-iron-900 dark:text-iron-400" role="status" aria-live="polite">Memuat denah gudang...</div>

    <div v-else class="relative overflow-x-auto border border-wms-border bg-white p-2 dark:border-iron-700 dark:bg-[#07131c] sm:p-4">
      <div class="min-w-[800px] pb-4">
        <div class="mb-2 flex items-center justify-between px-2 font-mono text-[9px] uppercase tracking-[0.15em] text-slate-500 dark:text-cyan-200/60"><span>PT SPINDO Tbk - Unit 7 Gresik</span><span>GUDANG 1-4 / FLOOR PLAN</span></div>

        <svg v-if="!activeWarehouse" viewBox="0 0 1200 1240" class="h-auto w-full" role="img" aria-label="Denah gabungan gedung 100 meter dengan empat gudang">
          <defs>
            <marker id="dimension-arrow" markerWidth="8" markerHeight="8" refX="4" refY="4" orient="auto"><path d="M0 0L8 4L0 8Z" class="fill-slate-500 dark:fill-cyan-200/60" /></marker>
          </defs>
          <rect width="1200" height="1240" class="fill-white dark:fill-[#07131c]" />
          <rect x="100" y="100" width="1000" height="1000" rx="2" class="fill-white stroke-slate-700 dark:fill-[#0b2230] dark:stroke-cyan-100/80" stroke-width="4" />
          <rect x="120" y="120" width="960" height="960" class="fill-none stroke-slate-400 dark:stroke-cyan-200/50" stroke-width="2" />
          <text x="120" y="68" class="fill-slate-700 dark:fill-cyan-100" font-size="16" font-family="IBM Plex Mono" font-weight="700">GEDUNG UTAMA / DENAH GUDANG 1-4</text>
          <text x="1080" y="68" text-anchor="end" class="fill-safety" font-size="10" font-family="IBM Plex Mono" font-weight="700">100 m x 100 m</text>

          <g v-for="(warehouse, index) in warehouses" :key="warehouse.id" @click="openWarehouse(warehouse)" @mouseenter="hoveredWarehouseId = warehouse.id" @mouseleave="hoveredWarehouseId = null" @focus="hoveredWarehouseId = warehouse.id" @blur="hoveredWarehouseId = null" :class="['cursor-pointer transition-opacity duration-200', hoveredWarehouseId === warehouse.id ? 'opacity-100' : hoveredWarehouseId ? 'opacity-30' : 'opacity-100']" role="button" tabindex="0" :aria-label="`Buka ${shortName(warehouse)}`" @keydown.enter="openWarehouse(warehouse)">
            <rect v-if="index > 0" x="120" :y="masterHotspot(index).y - 1" width="960" height="2" class="fill-slate-400 dark:fill-cyan-200/50" />
            <text x="1060" :y="masterHotspot(index).y + 124" text-anchor="middle" :transform="`rotate(-90 1060 ${masterHotspot(index).y + 124})`" class="fill-safety" font-size="11" font-family="IBM Plex Mono" font-weight="700">{{ shortName(warehouse).toUpperCase() }}</text>
            <rect :x="masterHotspot(index).x + 12" :y="masterHotspot(index).y + 28" width="876" height="198" class="fill-white stroke-slate-400/70 dark:fill-[#0b2230] dark:stroke-cyan-200/40" stroke-width="1" />
            <rect :x="masterHotspot(index).x + 20" :y="masterHotspot(index).y + 36" width="397" height="36" rx="1" class="fill-none stroke-red-500/55 dark:stroke-red-300/55" stroke-width="1.5" vector-effect="non-scaling-stroke" />
            <rect :x="masterHotspot(index).x + 20" :y="masterHotspot(index).y + 124" width="397" height="72" rx="1" class="fill-none stroke-red-500/55 dark:stroke-red-300/55" stroke-width="1.5" vector-effect="non-scaling-stroke" />
            <rect :x="masterHotspot(index).x + 505" :y="masterHotspot(index).y + 36" width="367" height="36" rx="1" class="fill-none stroke-red-500/55 dark:stroke-red-300/55" stroke-width="1.5" vector-effect="non-scaling-stroke" />
            <rect :x="masterHotspot(index).x + 505" :y="masterHotspot(index).y + 124" width="367" height="72" rx="1" class="fill-none stroke-red-500/55 dark:stroke-red-300/55" stroke-width="1.5" vector-effect="non-scaling-stroke" />
            <rect :x="masterHotspot(index).x + 20" :y="masterHotspot(index).y + 86" width="860" height="32" rx="2" class="fill-amber-400/15 stroke-amber-500/70 dark:fill-amber-300/10 dark:stroke-amber-200/70" />
            <text :x="masterHotspot(index).x + 450" :y="masterHotspot(index).y + 106" text-anchor="middle" class="fill-amber-700 dark:fill-amber-200" font-size="7" font-family="IBM Plex Mono" font-weight="700">JALUR UTAMA</text>
            <rect :x="masterHotspot(index).x + 440" :y="masterHotspot(index).y + 34" width="52" height="182" class="fill-amber-300/20 stroke-amber-500/60 dark:fill-amber-300/10 dark:stroke-amber-200/60" stroke-width="1.5" vector-effect="non-scaling-stroke" />
            <text :x="masterHotspot(index).x + 466" :y="masterHotspot(index).y + 128" text-anchor="middle" :transform="`rotate(-90 ${masterHotspot(index).x + 466} ${masterHotspot(index).y + 128})`" class="fill-amber-700 dark:fill-amber-200" font-size="6" font-family="IBM Plex Mono" font-weight="700">AKSES</text>
            <g v-for="block in warehouse.blocks" :key="block.id">
              <rect :x="masterBlockBox(index, block).x" :y="masterBlockBox(index, block).y" :width="masterBlockBox(index, block).width" :height="masterBlockBox(index, block).height" rx="2" :class="masterBlockClass(block)" />
              <text :x="masterBlockBox(index, block).x + masterBlockBox(index, block).width / 2" :y="masterBlockBox(index, block).y + 14" text-anchor="middle" class="fill-slate-700 dark:fill-cyan-50" font-size="7" font-family="IBM Plex Mono" font-weight="700">{{ block.code }}</text>
            </g>
          </g>
          <path d="M100 1130H1100" class="fill-none stroke-slate-500 dark:stroke-cyan-200/60" marker-start="url(#dimension-arrow)" marker-end="url(#dimension-arrow)" />
          <text x="600" y="1150" text-anchor="middle" class="fill-slate-500 dark:fill-cyan-200/70" font-size="10" font-family="IBM Plex Mono">DIMENSI LUAR 100 m</text>
          <path d="M1130 100V1100" class="fill-none stroke-slate-500 dark:stroke-cyan-200/60" marker-start="url(#dimension-arrow)" marker-end="url(#dimension-arrow)" />
          <text x="1120" y="600" text-anchor="middle" transform="rotate(-90 1120 600)" class="fill-slate-500 dark:fill-cyan-200/70" font-size="10" font-family="IBM Plex Mono">DIMENSI LUAR 100 m</text>
          <text x="600" y="88" text-anchor="middle" class="fill-slate-500 dark:fill-cyan-200/70" font-size="8" font-family="IBM Plex Mono">AREA DALAM 96 m x 96 m / SKALA 1 m = 10 UNIT</text>
        </svg>

        <svg v-else viewBox="0 0 1200 800" class="h-auto w-full" role="img" :aria-label="`Blueprint ${shortName(activeWarehouse)}`">
          <rect width="1200" height="800" class="fill-white dark:fill-[#07131c]" /><rect x="58" y="62" width="1084" height="680" class="fill-white stroke-slate-700 dark:fill-[#0b2230] dark:stroke-cyan-100/80" stroke-width="4" />
          <path d="M58 126H1142" class="stroke-slate-500 dark:stroke-cyan-200/40" stroke-width="2" /><text x="82" y="96" class="fill-slate-700 dark:fill-cyan-100" font-size="16" font-family="IBM Plex Mono" font-weight="700">{{ activeWarehouse.code }} / {{ shortName(activeWarehouse).toUpperCase() }} - FLOOR PLAN</text><text x="1118" y="96" text-anchor="end" class="fill-safety" font-size="10" font-family="IBM Plex Mono" font-weight="700">BACK TO GUDANG</text>
          <rect x="78" y="160" width="1044" height="500" class="fill-white stroke-cyan-600/90 dark:fill-[#0b2230] dark:stroke-cyan-300/80" stroke-width="3" />
           <rect x="88" y="300" width="1024" height="54" rx="2" class="fill-amber-400/15 stroke-amber-500/80 dark:fill-amber-300/10 dark:stroke-amber-200/70" stroke-width="2" />
           <text x="600" y="333" text-anchor="middle" class="fill-amber-700 dark:fill-amber-200" font-size="11" font-family="IBM Plex Mono" font-weight="700">JALUR UTAMA</text>
           <rect x="716" y="190" width="68" height="351" class="fill-amber-300/20 stroke-amber-500/60 dark:fill-amber-300/10 dark:stroke-amber-200/60" stroke-width="1.5" vector-effect="non-scaling-stroke" />
           <text x="754" y="510" text-anchor="middle" transform="rotate(-90 754 510)" class="fill-amber-700 dark:fill-amber-200" font-size="8" font-family="IBM Plex Mono" font-weight="700">AKSES</text>
           <rect x="96" y="199" width="588" height="53" class="fill-none stroke-red-500/55 dark:stroke-red-300/55" stroke-width="1.5" vector-effect="non-scaling-stroke" />
           <rect x="96" y="364" width="588" height="178" class="fill-none stroke-red-500/55 dark:stroke-red-300/55" stroke-width="1.5" vector-effect="non-scaling-stroke" />
           <rect x="822" y="199" width="262" height="53" class="fill-none stroke-red-500/55 dark:stroke-red-300/55" stroke-width="1.5" vector-effect="non-scaling-stroke" />
           <rect x="822" y="364" width="262" height="178" class="fill-none stroke-red-500/55 dark:stroke-red-300/55" stroke-width="1.5" vector-effect="non-scaling-stroke" />
          <g v-for="block in activeWarehouse.blocks" :key="block.id" @click="selectBlock(block)" class="cursor-pointer" role="button" tabindex="0" @keydown.enter="selectBlock(block)"><rect :x="detailBlockBox(block).x" :y="detailBlockBox(block).y" :width="detailBlockBox(block).width" :height="detailBlockBox(block).height" rx="2" :class="detailBlockClass(block)" stroke-width="2" /><text :x="detailBlockBox(block).x + detailBlockBox(block).width / 2" :y="detailBlockBox(block).y + 18" text-anchor="middle" class="fill-slate-800 dark:fill-cyan-50" font-size="10" font-family="IBM Plex Mono" font-weight="700">{{ block.code }}</text><text :x="detailBlockBox(block).x + detailBlockBox(block).width / 2" :y="detailBlockBox(block).y + 32" text-anchor="middle" class="fill-safety" font-size="7" font-family="IBM Plex Mono" font-weight="700">{{ block.sloc_code }}</text><text :x="detailBlockBox(block).x + detailBlockBox(block).width - 6" :y="detailBlockBox(block).y + 18" text-anchor="end" class="fill-slate-500 dark:fill-cyan-100/60" font-size="7" font-family="IBM Plex Mono">{{ block.inventories.length }}</text><rect :x="detailBlockBox(block).x + 6" :y="detailBlockBox(block).y + 38" :width="detailBlockBox(block).width - 12" height="3" rx="1" class="fill-slate-200 dark:fill-[#07131c]" /><rect :x="detailBlockBox(block).x + 6" :y="detailBlockBox(block).y + 38" :width="(detailBlockBox(block).width - 12) * percent(block) / 100" height="3" rx="1" :class="barClass(block)" /></g>
          <text x="82" y="700" class="fill-safety" font-size="9" font-family="IBM Plex Mono" font-weight="700">LAYOUT PENYIMPANAN / JALUR UTAMA</text>
        </svg>
      </div>
    </div>

    <div class="grid gap-3 rounded-lg border border-wms-border bg-white p-3 text-xs text-wms-muted dark:border-iron-800 dark:bg-iron-900 dark:text-iron-300 sm:grid-cols-[1fr_auto] sm:items-center">
      <p><span class="font-semibold text-wms-ink dark:text-white">Cara menggunakan:</span> pilih gudang, lalu pilih blok untuk melihat stok. <span class="md:hidden">Geser ke samping untuk melihat seluruh denah.</span></p>
      <div class="flex flex-wrap gap-x-4 gap-y-2 font-mono text-[10px] uppercase tracking-wide" aria-label="Legenda denah">
        <span class="inline-flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-sm border border-emerald-500 bg-emerald-100 dark:bg-emerald-950/40"></i>Terisi</span>
        <span class="inline-flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-sm border border-slate-400 bg-white dark:bg-[#0d2c3c]"></i>Kosong</span>
        <span class="inline-flex items-center gap-1.5"><i class="h-2.5 w-2.5 rounded-sm border border-yellow-600 bg-yellow-100 dark:bg-yellow-950/40"></i>Akses</span>
      </div>
    </div>
    <WarehouseBlockPanel v-if="selectedBlock && activeWarehouse" :block="selectedBlock" :warehouse="activeWarehouse" @close="selectedBlock = null" />
  </section>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import WarehouseBlockPanel from './WarehouseBlockPanel.vue';

const warehouses = ref([]);
const activeWarehouse = ref(null);
const selectedBlock = ref(null);
const hoveredWarehouseId = ref(null);
const loading = ref(true);
const errorMessage = ref('');

function loadMap() {
  loading.value = true;
  errorMessage.value = '';
  fetch('/api/wms/warehouse-map', { headers: { Accept: 'application/json' } })
    .then((response) => { if (!response.ok) throw new Error('Data denah tidak dapat dimuat.'); return response.json(); })
    .then((json) => { if (json.status !== 'success') throw new Error('Data denah tidak tersedia.'); warehouses.value = json.data.warehouses || []; })
    .catch((error) => { errorMessage.value = error.message; })
    .finally(() => { loading.value = false; });
}

function shortName(warehouse) { return warehouse.name?.split(' / ')[0] || warehouse.code; }
function openWarehouse(warehouse) { activeWarehouse.value = warehouse; selectedBlock.value = null; }
function closeWarehouse() { activeWarehouse.value = null; selectedBlock.value = null; }
function selectBlock(block) { selectedBlock.value = block; }
function blockColumn(block) { return Math.max(0, (block.code?.charCodeAt(0) || 65) - 65); }
function blockRow(block) { return Math.max(0, Number(block.code?.slice(1) || 1) - 1); }
function isAccessBlock(block) { return block.code === 'L2'; }
function percent(block) { 
  if (block.status === 'FULL') return 100;
  
  // 1. Calculate physical fullness via Area Geometry (Smart System)
  if (block.max_area_m2 > 0 && block.utilized_area_m2 > 0) {
      const areaPercent = Math.round((Number(block.utilized_area_m2) / Number(block.max_area_m2)) * 100);
      if (areaPercent > 0) return Math.min(110, areaPercent); // cap at 110 for overcapacity visual
  }
  
  // 2. Fallback to SIKUTA pieces limit if area calculation is 0
  if (block.max_stock_pcs > 0) return Math.min(100, Math.round((Number(block.current_stock_pcs || 0) / Number(block.max_stock_pcs)) * 100));
  
  // 3. Last fallback to weight (mostly inaccurate)
  return Math.min(100, Math.round((Number(block.current_weight_tons || 0) / Number(block.max_weight_tons || 50)) * 100)); 
}
function barClass(block) { 
  const p = percent(block);
  if (p > 100) return 'fill-red-600 animate-pulse'; // OVERCAPACITY
  return p >= 90 ? 'fill-red-500' : p >= 60 ? 'fill-safety' : block.inventories.length ? 'fill-emerald-500' : 'fill-slate-300 dark:fill-iron-700'; 
}
function detailBlockClass(block) { return isAccessBlock(block) ? 'fill-yellow-100 stroke-yellow-600 dark:fill-yellow-950/40 dark:stroke-yellow-300' : block.inventories.length ? 'fill-emerald-50 stroke-emerald-500/70 dark:fill-emerald-950/30 dark:stroke-emerald-400/60' : 'fill-white stroke-slate-400 dark:fill-[#0d2c3c] dark:stroke-cyan-200/50'; }
function masterBlockClass(block) { return block.inventories.length ? 'fill-emerald-200/60 stroke-emerald-600/70 dark:fill-emerald-950/40 dark:stroke-emerald-300/60' : 'fill-white stroke-slate-500/70 dark:fill-[#0b2230] dark:stroke-cyan-100/40'; }
function masterHotspot(index) { return { x: 150, y: 130 + index * 235, width: 900, height: 235 }; }
function masterBlockBox(index, block) {
  const zone = masterHotspot(index);
  const column = blockColumn(block);
  const row = blockRow(block);
  const left = column < 8;
  return left
    ? { x: zone.x + 24 + column * 49, y: zone.y + (row === 0 ? 38 : row === 1 ? 128 : 174), width: 44, height: 25 }
    : { x: zone.x + 510 + (column - 8) * 78, y: zone.y + (row === 0 ? 38 : row === 1 ? 128 : 174), width: 65, height: 25 };
}
function detailBlockBox(block) { const column = blockColumn(block); const row = blockRow(block); return column < 8 ? { x: 98 + column * 75, y: row === 0 ? 205 : row === 1 ? 370 : 485, width: 66, height: 52 } : { x: 824 + (column - 8) * 68, y: row === 0 ? 205 : row === 1 ? 370 : 485, width: 60, height: 52 }; }

onMounted(loadMap);

defineExpose({ loadMap });
</script>
