<template>
  <section class="space-y-4">
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 dark:border-iron-800 sm:flex-row sm:items-end">
      <div>
        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-safety">SC-U7 / architectural blueprint</p>
        <h1 class="mt-1 text-xl font-black tracking-tight text-slate-900 dark:text-iron-100">Denah Gedung Supply Chain</h1>
        <p class="mt-1 max-w-3xl text-xs leading-5 text-slate-500 dark:text-iron-400">Gedung 100 m x 100 m dengan fasilitas depan dan Gudang 1-4. SLOC mengikuti kelompok empat kolom.</p>
      </div>
      <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-wide text-slate-500 dark:text-iron-400">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Live WMS
        <button v-if="activeWarehouse" type="button" @click="closeWarehouse" class="ml-3 rounded border border-slate-300 px-2.5 py-1.5 font-sans font-black text-slate-600 transition hover:border-safety hover:text-safety dark:border-iron-700 dark:text-iron-300">Kembali ke master plan</button>
      </div>
    </div>

    <div v-if="errorMessage" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{{ errorMessage }} <button type="button" @click="loadMap" class="ml-2 font-bold underline">Coba lagi</button></div>
    <div v-else-if="loading" class="rounded-lg border border-slate-200 bg-white px-4 py-16 text-center font-mono text-xs text-slate-500 dark:border-iron-800 dark:bg-iron-900 dark:text-iron-400">Memuat blueprint gudang...</div>

    <div v-else class="overflow-x-auto rounded-lg border border-slate-300 bg-white p-2 shadow-sm dark:border-iron-700 dark:bg-[#07131c] sm:p-4">
      <div class="min-w-[920px]">
        <div class="mb-2 flex items-center justify-between px-2 font-mono text-[9px] uppercase tracking-[0.15em] text-slate-500 dark:text-cyan-200/60"><span>PT SPINDO Tbk - Unit 7 Gresik</span><span>GUDANG 1-4 / FLOOR PLAN</span></div>

        <svg v-if="!activeWarehouse" viewBox="0 0 1200 1240" class="h-auto w-full" role="img" aria-label="Denah gedung 100 meter dengan fasilitas dan empat gudang">
          <defs>
            <marker id="master-yellow-arrow" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10Z" class="fill-yellow-500" /></marker>
            <marker id="master-dimension-arrow" markerWidth="8" markerHeight="8" refX="4" refY="4" orient="auto"><path d="M0 0L8 4L0 8Z" class="fill-slate-500 dark:fill-cyan-200/60" /></marker>
          </defs>
          <rect width="1200" height="1240" class="fill-white dark:fill-[#07131c]" />
          <rect x="100" y="180" width="1000" height="1000" rx="2" class="fill-white stroke-slate-700 dark:fill-[#0b2230] dark:stroke-cyan-100/80" stroke-width="4" />
          <rect x="120" y="200" width="960" height="960" class="fill-none stroke-slate-400 dark:stroke-cyan-200/50" stroke-width="2" />
          <text x="120" y="68" class="fill-slate-700 dark:fill-cyan-100" font-size="16" font-family="IBM Plex Mono" font-weight="700">GEDUNG UTAMA / DENAH GUDANG 1-4</text>
          <text x="1080" y="68" text-anchor="end" class="fill-safety" font-size="10" font-family="IBM Plex Mono" font-weight="700">100 m x 100 m</text>

          <rect x="120" y="200" width="960" height="70" class="fill-slate-100 stroke-slate-400 dark:fill-[#12384a] dark:stroke-cyan-200/50" />
          <path d="M312 200V270M504 200V270M696 200V270M888 200V270" class="stroke-slate-400 dark:stroke-cyan-200/35" />
          <text x="216" y="242" text-anchor="middle" class="fill-slate-600 dark:fill-cyan-100/80" font-size="9" font-family="IBM Plex Mono">PARKIR MANAGEMENT</text>
          <text x="408" y="242" text-anchor="middle" class="fill-slate-600 dark:fill-cyan-100/80" font-size="9" font-family="IBM Plex Mono">RUANG MEETING</text>
          <text x="600" y="242" text-anchor="middle" class="fill-slate-600 dark:fill-cyan-100/80" font-size="9" font-family="IBM Plex Mono">LOKET DELIVERY</text>
          <text x="792" y="242" text-anchor="middle" class="fill-yellow-600 dark:fill-yellow-300" font-size="9" font-family="IBM Plex Mono" font-weight="700">MASUK PINTU SAMPING</text>
          <text x="984" y="242" text-anchor="middle" class="fill-slate-600 dark:fill-cyan-100/80" font-size="9" font-family="IBM Plex Mono">MUSHOLA</text>

          <path d="M92 235H145" class="fill-none stroke-yellow-500" stroke-width="6" marker-end="url(#master-yellow-arrow)" />
          <text x="132" y="220" text-anchor="end" class="fill-yellow-600 dark:fill-yellow-300" font-size="8" font-family="IBM Plex Mono" font-weight="700">JALUR TRUK MUAT</text>
          <path d="M92 280V350" class="fill-none stroke-yellow-500" stroke-width="6" marker-end="url(#master-yellow-arrow)" />
          <text x="78" y="335" text-anchor="middle" transform="rotate(-90 78 335)" class="fill-yellow-600 dark:fill-yellow-300" font-size="8" font-family="IBM Plex Mono" font-weight="700">MASUK PINTU DEPAN</text>
          <path d="M792 155V270H642" class="fill-none stroke-yellow-500" stroke-width="6" marker-end="url(#master-yellow-arrow)" />

          <rect x="590" y="270" width="52" height="890" class="fill-amber-300/20 dark:fill-amber-300/10" aria-label="Jalan utama antar Gudang 1 sampai 4" />

          <g v-for="(warehouse, index) in warehouses" :key="warehouse.id" @click="openWarehouse(warehouse)" @mouseenter="hoveredWarehouseId = warehouse.id" @mouseleave="hoveredWarehouseId = null" @focus="hoveredWarehouseId = warehouse.id" @blur="hoveredWarehouseId = null" :class="['cursor-pointer transition-opacity duration-200', hoveredWarehouseId === warehouse.id ? 'opacity-100' : hoveredWarehouseId ? 'opacity-30' : 'opacity-100']" role="button" tabindex="0" :aria-label="`Buka ${shortName(warehouse)}`" @keydown.enter="openWarehouse(warehouse)">
            <text x="1060" :y="masterHotspot(index).y + 94" text-anchor="middle" :transform="`rotate(-90 1060 ${masterHotspot(index).y + 94})`" class="fill-safety dark:fill-yellow-200" font-size="17" font-family="IBM Plex Mono" font-weight="700">{{ shortName(warehouse).toUpperCase() }}</text>
            <rect :x="masterHotspot(index).x" :y="masterHotspot(index).y" :width="masterHotspot(index).width" :height="masterHotspot(index).height" class="fill-none stroke-blue-600/90 dark:stroke-blue-300/90" stroke-width="3" stroke-dasharray="10 6" aria-label="Batas area gudang" />
            <rect :x="masterHotspot(index).x + 12" :y="masterHotspot(index).y + 12" width="405" height="163" class="fill-none stroke-blue-500/80 dark:stroke-blue-300/80" stroke-width="2" stroke-dasharray="6 5" aria-label="Klaster SLOC kiri" />
            <rect :x="masterHotspot(index).x + 490" :y="masterHotspot(index).y + 12" width="335" height="163" class="fill-none stroke-blue-500/80 dark:stroke-blue-300/80" stroke-width="2" stroke-dasharray="6 5" aria-label="Klaster SLOC kanan" />
            <rect :x="masterHotspot(index).x + 20" :y="masterHotspot(index).y + 76" width="860" height="28" rx="2" class="fill-amber-400/20 dark:fill-amber-300/15" aria-label="Jalan antar blok 1 dan 2" />
            <rect :x="masterHotspot(index).x + 18" :y="masterHotspot(index).y + 20" width="198" height="42" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup A-D atas" />
            <rect :x="masterHotspot(index).x + 18" :y="masterHotspot(index).y + 112" width="198" height="63" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup A-D bawah" />
            <rect :x="masterHotspot(index).x + 214" :y="masterHotspot(index).y + 20" width="198" height="42" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup E-H atas" />
            <rect :x="masterHotspot(index).x + 214" :y="masterHotspot(index).y + 112" width="198" height="63" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup E-H bawah" />
            <rect :x="masterHotspot(index).x + 500" :y="masterHotspot(index).y + 20" width="315" height="42" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup I-L atas" />
            <rect :x="masterHotspot(index).x + 500" :y="masterHotspot(index).y + 112" width="315" height="63" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup I-L bawah" />
            <g v-for="block in warehouse.blocks" :key="block.id">
              <rect :x="masterBlockBox(index, block).x" :y="masterBlockBox(index, block).y" :width="masterBlockBox(index, block).width" :height="masterBlockBox(index, block).height" rx="2" :class="masterBlockClass(block)" />
              <text :x="masterBlockBox(index, block).x + masterBlockBox(index, block).width / 2" :y="masterBlockBox(index, block).y + 14" text-anchor="middle" class="fill-slate-700 dark:fill-cyan-50" font-size="7" font-family="IBM Plex Mono" font-weight="700">{{ block.code }}</text>
              <text v-if="isSlocAnchor(block)" :x="masterBlockBox(index, block).x + masterBlockBox(index, block).width / 2" :y="masterBlockBox(index, block).y - 3" text-anchor="middle" class="fill-safety dark:fill-yellow-200" font-size="6" font-family="IBM Plex Mono" font-weight="700">{{ block.sloc_code }}</text>
            </g>
          </g>
          <path d="M100 1210H1100" class="fill-none stroke-slate-500 dark:stroke-cyan-200/60" marker-start="url(#master-dimension-arrow)" marker-end="url(#master-dimension-arrow)" />
          <text x="600" y="1230" text-anchor="middle" class="fill-slate-500 dark:fill-cyan-200/70" font-size="10" font-family="IBM Plex Mono">DIMENSI LUAR 100 m</text>
          <path d="M1130 180V1180" class="fill-none stroke-slate-500 dark:stroke-cyan-200/60" marker-start="url(#master-dimension-arrow)" marker-end="url(#master-dimension-arrow)" />
          <text x="1120" y="680" text-anchor="middle" transform="rotate(-90 1120 680)" class="fill-slate-500 dark:fill-cyan-200/70" font-size="10" font-family="IBM Plex Mono">DIMENSI LUAR 100 m</text>
          <text x="600" y="88" text-anchor="middle" class="fill-slate-500 dark:fill-cyan-200/70" font-size="8" font-family="IBM Plex Mono">AREA DALAM 96 m x 96 m / SKALA 1 m = 10 UNIT</text>
        </svg>

        <svg v-else viewBox="0 0 1200 800" class="h-auto w-full" role="img" :aria-label="`Blueprint ${shortName(activeWarehouse)}`">
          <defs><marker id="detail-yellow-arrow" markerWidth="10" markerHeight="10" refX="8" refY="5" orient="auto"><path d="M0 0L10 5L0 10Z" class="fill-yellow-500" /></marker></defs>
          <rect width="1200" height="800" class="fill-white dark:fill-[#07131c]" />
          <rect x="58" y="62" width="1084" height="680" class="fill-white stroke-slate-700 dark:fill-[#0b2230] dark:stroke-cyan-100/80" stroke-width="4" />
          <path d="M58 126H1142" class="stroke-slate-500 dark:stroke-cyan-200/40" stroke-width="2" />
          <text x="82" y="96" class="fill-slate-700 dark:fill-cyan-100" font-size="16" font-family="IBM Plex Mono" font-weight="700">{{ activeWarehouse.code }} / {{ shortName(activeWarehouse).toUpperCase() }} - FLOOR PLAN</text>
          <text x="1118" y="96" text-anchor="end" class="fill-safety" font-size="10" font-family="IBM Plex Mono" font-weight="700">BACK TO GUDANG</text>
          <rect x="78" y="160" width="1044" height="500" class="fill-white stroke-cyan-600/90 dark:fill-[#0b2230] dark:stroke-cyan-300/80" stroke-width="3" />
          <rect x="78" y="160" width="1044" height="500" class="fill-none stroke-blue-600/90 dark:stroke-blue-300/90" stroke-width="3" stroke-dasharray="10 6" aria-label="Batas area gudang" />
          <rect x="88" y="300" width="1024" height="54" rx="3" class="fill-amber-400/20 dark:fill-amber-300/15" aria-label="Jalan antar blok 1 dan 2" />
          <rect x="716" y="190" width="76" height="355" class="fill-amber-300/20 dark:fill-amber-300/10" />
          <rect x="88" y="185" width="610" height="365" class="fill-none stroke-blue-500/80 dark:stroke-blue-300/80" stroke-width="2" stroke-dasharray="6 5" aria-label="Klaster SLOC kiri" />
          <rect x="808" y="185" width="300" height="365" class="fill-none stroke-blue-500/80 dark:stroke-blue-300/80" stroke-width="2" stroke-dasharray="6 5" aria-label="Klaster SLOC kanan" />
          <rect x="92" y="195" width="290" height="65" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup A-D atas" />
          <rect x="92" y="360" width="290" height="190" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup A-D bawah" />
          <rect x="390" y="195" width="298" height="65" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup E-H atas" />
          <rect x="390" y="360" width="298" height="190" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup E-H bawah" />
          <rect x="818" y="195" width="278" height="65" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup I-L atas" />
          <rect x="818" y="360" width="278" height="190" class="fill-none stroke-red-500/80 dark:stroke-red-300/80" stroke-width="3" aria-label="SLOC grup I-L bawah" />
          <path d="M90 340H150M1110 340H1050" class="fill-none stroke-yellow-500" stroke-width="6" marker-end="url(#detail-yellow-arrow)" />
          <g v-for="block in activeWarehouse.blocks" :key="block.id" @click="selectBlock(block)" class="cursor-pointer" role="button" tabindex="0" @keydown.enter="selectBlock(block)">
            <rect :x="detailBlockBox(block).x" :y="detailBlockBox(block).y" :width="detailBlockBox(block).width" :height="detailBlockBox(block).height" rx="2" :class="detailBlockClass(block)" stroke-width="2" />
            <text :x="detailBlockBox(block).x + detailBlockBox(block).width / 2" :y="detailBlockBox(block).y + 18" text-anchor="middle" class="fill-slate-800 dark:fill-cyan-50" font-size="10" font-family="IBM Plex Mono" font-weight="700">{{ block.code }}</text>
            <text v-if="isSlocAnchor(block)" :x="detailBlockBox(block).x + detailBlockBox(block).width / 2" :y="detailBlockBox(block).y - 4" text-anchor="middle" class="fill-safety" font-size="7" font-family="IBM Plex Mono" font-weight="700">{{ block.sloc_code }}</text>
            <text :x="detailBlockBox(block).x + detailBlockBox(block).width - 6" :y="detailBlockBox(block).y + 18" text-anchor="end" class="fill-slate-500 dark:fill-cyan-100/60" font-size="7" font-family="IBM Plex Mono">{{ block.inventories.length }}</text>
            <rect :x="detailBlockBox(block).x + 6" :y="detailBlockBox(block).y + 38" :width="detailBlockBox(block).width - 12" height="3" rx="1" class="fill-slate-200 dark:fill-[#07131c]" />
            <rect :x="detailBlockBox(block).x + 6" :y="detailBlockBox(block).y + 38" :width="(detailBlockBox(block).width - 12) * percent(block) / 100" height="3" rx="1" :class="barClass(block)" />
          </g>
        </svg>
      </div>
    </div>

    <div class="rounded border border-slate-200 bg-white px-3 py-2 font-mono text-[10px] text-slate-500 dark:border-iron-800 dark:bg-iron-900 dark:text-iron-400">Tip: pilih Gudang 1-4, lalu tekan blok pada blueprint.</div>
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
function isSlocAnchor(block) { return [0, 4, 8].includes(blockColumn(block)) && [0, 1].includes(blockRow(block)); }
function percent(block) { return Math.min(100, Math.round((Number(block.current_weight_tons || 0) / Number(block.max_weight_tons || 50)) * 100)); }
function barClass(block) { return percent(block) >= 90 ? 'fill-red-500' : percent(block) >= 60 ? 'fill-safety' : block.inventories.length ? 'fill-emerald-500' : 'fill-slate-300 dark:fill-iron-700'; }
function detailBlockClass(block) { return block.inventories.length ? 'fill-emerald-50 stroke-emerald-500/70 dark:fill-emerald-950/30 dark:stroke-emerald-400/60' : 'fill-white stroke-slate-400 dark:fill-[#0d2c3c] dark:stroke-cyan-200/50'; }
function masterBlockClass(block) { return block.inventories.length ? 'fill-emerald-200/60 stroke-emerald-600/70 dark:fill-emerald-950/40 dark:stroke-emerald-300/60' : 'fill-white stroke-slate-500/70 dark:fill-[#0b2230] dark:stroke-cyan-100/40'; }
function masterHotspot(index) { return { x: 150, y: 285 + index * 215, width: 900, height: 195 }; }
function masterBlockBox(index, block) {
  const zone = masterHotspot(index);
  const column = blockColumn(block);
  const row = blockRow(block);
  return column < 8
    ? { x: zone.x + 24 + column * 49, y: zone.y + (row === 0 ? 24 : row === 1 ? 120 : 150), width: 44, height: 25 }
    : { x: zone.x + 510 + (column - 8) * 78, y: zone.y + (row === 0 ? 24 : row === 1 ? 120 : 150), width: 65, height: 25 };
}
function detailBlockBox(block) { const column = blockColumn(block); const row = blockRow(block); return column < 8 ? { x: 98 + column * 75, y: row === 0 ? 205 : row === 1 ? 370 : 485, width: 66, height: 52 } : { x: 824 + (column - 8) * 68, y: row === 0 ? 205 : row === 1 ? 370 : 485, width: 60, height: 52 }; }

onMounted(loadMap);
</script>
