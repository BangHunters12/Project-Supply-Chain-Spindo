<template>
  <section class="space-y-4">
    <div class="flex flex-col justify-between gap-4 border-b border-slate-200 pb-4 dark:border-iron-800 sm:flex-row sm:items-end">
      <div>
        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-safety">SC-U7 / architectural blueprint</p>
        <h1 class="mt-1 text-xl font-black tracking-tight text-slate-900 dark:text-iron-100">Denah Gedung Supply Chain</h1>
        <p class="mt-1 max-w-3xl text-xs leading-5 text-slate-500 dark:text-iron-400">Satu background gedung untuk Gudang 1-4. Hover atau klik overlay storage untuk membuka denah detail, SLOC, dan stok pipa.</p>
      </div>
      <div class="flex items-center gap-2 font-mono text-[10px] uppercase tracking-wide text-slate-500 dark:text-iron-400">
        <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Live WMS
        <button v-if="activeWarehouse" type="button" @click="closeWarehouse" class="ml-3 rounded border border-slate-300 px-2.5 py-1.5 font-sans font-black text-slate-600 transition hover:border-safety hover:text-safety dark:border-iron-700 dark:text-iron-300">Kembali ke master plan</button>
      </div>
    </div>

    <div v-if="errorMessage" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-xs text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-300">{{ errorMessage }} <button type="button" @click="loadMap" class="ml-2 font-bold underline">Coba lagi</button></div>
    <div v-else-if="loading" class="rounded-lg border border-slate-200 bg-white px-4 py-16 text-center font-mono text-xs text-slate-500 dark:border-iron-800 dark:bg-iron-900 dark:text-iron-400">Memuat blueprint gudang...</div>

    <div v-else class="overflow-x-auto rounded-lg border border-slate-300 bg-[#eaf2f4] p-2 shadow-sm dark:border-iron-700 dark:bg-[#07131c] sm:p-4">
      <div class="min-w-[920px]">
        <div class="mb-2 flex items-center justify-between px-2 font-mono text-[9px] uppercase tracking-[0.15em] text-slate-500 dark:text-cyan-200/60"><span>PT SPINDO Tbk - Unit 7 Gresik</span><span>GUDANG 1-4 / FLOOR PLAN</span></div>

        <svg v-if="!activeWarehouse" viewBox="0 0 1200 980" class="h-auto w-full" role="img" aria-label="Denah Gudang 1 sampai Gudang 4">
          <defs>
            <pattern id="master-grid" width="24" height="24" patternUnits="userSpaceOnUse"><path d="M24 0H0V24" fill="none" class="stroke-slate-300/70 dark:stroke-cyan-200/10" /></pattern>
          </defs>
          <rect width="1200" height="980" class="fill-[#eaf2f4] dark:fill-[#07131c]" />
          <rect x="24" y="24" width="1152" height="932" fill="url(#master-grid)" class="stroke-slate-300 dark:stroke-cyan-200/20" />
          <text x="60" y="68" class="fill-slate-700 dark:fill-cyan-100" font-size="18" font-family="IBM Plex Mono" font-weight="700">DENAH AREA GUDANG</text>
          <text x="1140" y="68" text-anchor="end" class="fill-slate-500 dark:fill-cyan-200/60" font-size="10" font-family="IBM Plex Mono">GUDANG 1-4</text>

          <g v-for="(warehouse, index) in warehouses" :key="warehouse.id" @click="openWarehouse(warehouse)" @mouseenter="hoveredWarehouseId = warehouse.id" @mouseleave="hoveredWarehouseId = null" @focus="hoveredWarehouseId = warehouse.id" @blur="hoveredWarehouseId = null" :class="['cursor-pointer transition-opacity duration-200', hoveredWarehouseId === warehouse.id ? 'opacity-100' : hoveredWarehouseId ? 'opacity-30' : 'opacity-90']" role="button" tabindex="0" :aria-label="`Buka ${shortName(warehouse)}`" @keydown.enter="openWarehouse(warehouse)">
            <rect :x="masterHotspot(index).x" :y="masterHotspot(index).y" :width="masterHotspot(index).width" :height="masterHotspot(index).height" rx="4" class="fill-white/70 stroke-slate-500 dark:fill-[#0b2230]/80 dark:stroke-cyan-100/70" stroke-width="2" />
            <text :x="masterHotspot(index).x + 20" :y="masterHotspot(index).y + 28" class="fill-safety" font-size="11" font-family="IBM Plex Mono" font-weight="700">{{ warehouse.code }}</text>
            <text :x="masterHotspot(index).x + 20" :y="masterHotspot(index).y + 51" class="fill-slate-700 dark:fill-cyan-100" font-size="17" font-family="DM Sans" font-weight="800">{{ shortName(warehouse) }}</text>
            <text :x="masterHotspot(index).x + 20" :y="masterHotspot(index).y + 70" class="fill-slate-500 dark:fill-cyan-100/60" font-size="8" font-family="IBM Plex Mono">36 BLOK / KLIK UNTUK DETAIL</text>
            <rect :x="masterHotspot(index).x + 20" :y="masterHotspot(index).y + 153" :width="masterHotspot(index).width - 40" height="12" rx="3" class="fill-amber-400/15 stroke-amber-500/70 dark:fill-amber-300/10 dark:stroke-amber-200/70" />
            <text :x="masterHotspot(index).x + masterHotspot(index).width / 2" :y="masterHotspot(index).y + 162" text-anchor="middle" class="fill-amber-700 dark:fill-amber-200" font-size="6" font-family="IBM Plex Mono" font-weight="700">JALAN ANTARA BLOK 1 DAN 2</text>
            <g v-for="block in warehouse.blocks" :key="block.id">
              <rect :x="masterBlockBox(index, block).x" :y="masterBlockBox(index, block).y" :width="masterBlockBox(index, block).width" :height="masterBlockBox(index, block).height" rx="2" :class="masterBlockClass(block)" />
              <text :x="masterBlockBox(index, block).x + masterBlockBox(index, block).width / 2" :y="masterBlockBox(index, block).y + 14" text-anchor="middle" class="fill-slate-700 dark:fill-cyan-50" font-size="7" font-family="IBM Plex Mono" font-weight="700">{{ block.code }}</text>
            </g>
          </g>
        </svg>

        <svg v-else viewBox="0 0 1200 800" class="h-auto w-full" role="img" :aria-label="`Blueprint ${shortName(activeWarehouse)}`">
          <defs><pattern id="detail-grid" width="24" height="24" patternUnits="userSpaceOnUse"><path d="M24 0H0V24" fill="none" class="stroke-slate-300/70 dark:stroke-cyan-200/10" /></pattern></defs>
          <rect width="1200" height="800" class="fill-[#eaf2f4] dark:fill-[#07131c]" /><rect x="24" y="24" width="1152" height="752" fill="url(#detail-grid)" class="stroke-slate-300 dark:stroke-cyan-200/20" /><rect x="58" y="62" width="1084" height="680" class="fill-white/70 stroke-slate-700 dark:fill-[#0b2230]/80 dark:stroke-cyan-100/80" stroke-width="4" />
          <path d="M58 126H1142" class="stroke-slate-500 dark:stroke-cyan-200/40" stroke-width="2" /><text x="82" y="96" class="fill-slate-700 dark:fill-cyan-100" font-size="16" font-family="IBM Plex Mono" font-weight="700">{{ activeWarehouse.code }} / {{ shortName(activeWarehouse).toUpperCase() }} - FLOOR PLAN</text><text x="1118" y="96" text-anchor="end" class="fill-safety" font-size="10" font-family="IBM Plex Mono" font-weight="700">BACK TO GUDANG</text>
          <rect x="78" y="160" width="1044" height="500" class="fill-slate-100/50 stroke-cyan-600/90 dark:fill-[#0b2230]/35 dark:stroke-cyan-300/80" stroke-width="3" />
          <rect x="88" y="365" width="1024" height="54" rx="3" class="fill-amber-400/15 stroke-amber-500/80 dark:fill-amber-300/10 dark:stroke-amber-200/70" stroke-width="2" />
          <text x="600" y="398" text-anchor="middle" class="fill-amber-700 dark:fill-amber-200" font-size="11" font-family="IBM Plex Mono" font-weight="700">JALAN ANTARA BLOK 1 DAN 2</text>
          <text x="600" y="185" text-anchor="middle" class="fill-slate-500 dark:fill-cyan-100/60" font-size="10" font-family="IBM Plex Mono">BLOK 1</text><text x="600" y="450" text-anchor="middle" class="fill-slate-500 dark:fill-cyan-100/60" font-size="10" font-family="IBM Plex Mono">BLOK 2</text><text x="600" y="620" text-anchor="middle" class="fill-slate-500 dark:fill-cyan-100/60" font-size="10" font-family="IBM Plex Mono">BLOK 3</text>
          <g v-for="block in activeWarehouse.blocks" :key="block.id" @click="selectBlock(block)" class="cursor-pointer" role="button" tabindex="0" @keydown.enter="selectBlock(block)"><rect :x="detailBlockBox(block).x" :y="detailBlockBox(block).y" :width="detailBlockBox(block).width" :height="detailBlockBox(block).height" rx="2" :class="detailBlockClass(block)" stroke-width="2" /><text :x="detailBlockBox(block).x + detailBlockBox(block).width / 2" :y="detailBlockBox(block).y + 18" text-anchor="middle" class="fill-slate-800 dark:fill-cyan-50" font-size="10" font-family="IBM Plex Mono" font-weight="700">{{ block.code }}</text><text :x="detailBlockBox(block).x + detailBlockBox(block).width / 2" :y="detailBlockBox(block).y + 32" text-anchor="middle" class="fill-safety" font-size="7" font-family="IBM Plex Mono" font-weight="700">{{ block.sloc_code }}</text><text :x="detailBlockBox(block).x + detailBlockBox(block).width - 6" :y="detailBlockBox(block).y + 18" text-anchor="end" class="fill-slate-500 dark:fill-cyan-100/60" font-size="7" font-family="IBM Plex Mono">{{ block.inventories.length }}</text><rect :x="detailBlockBox(block).x + 6" :y="detailBlockBox(block).y + 38" :width="detailBlockBox(block).width - 12" height="3" rx="1" class="fill-slate-200 dark:fill-[#07131c]" /><rect :x="detailBlockBox(block).x + 6" :y="detailBlockBox(block).y + 38" :width="(detailBlockBox(block).width - 12) * percent(block) / 100" height="3" rx="1" :class="barClass(block)" /></g>
          <text x="82" y="700" class="fill-safety" font-size="9" font-family="IBM Plex Mono" font-weight="700">LAYOUT PENYIMPANAN / JALAN ANTARA BLOK 1 DAN 2</text>
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
function isAccessBlock(block) { return block.code === 'L2'; }
function percent(block) { return Math.min(100, Math.round((Number(block.current_weight_tons || 0) / Number(block.max_weight_tons || 50)) * 100)); }
function barClass(block) { return percent(block) >= 90 ? 'fill-red-500' : percent(block) >= 60 ? 'fill-safety' : block.inventories.length ? 'fill-emerald-500' : 'fill-slate-300 dark:fill-iron-700'; }
function detailBlockClass(block) { return isAccessBlock(block) ? 'fill-yellow-100 stroke-yellow-600 dark:fill-yellow-950/40 dark:stroke-yellow-300' : block.inventories.length ? 'fill-emerald-50 stroke-emerald-500/70 dark:fill-emerald-950/30 dark:stroke-emerald-400/60' : 'fill-slate-50 stroke-slate-400 dark:fill-[#0d2c3c] dark:stroke-cyan-200/50'; }
function masterBlockClass(block) { return block.inventories.length ? 'fill-emerald-200/60 stroke-emerald-600/70 dark:fill-emerald-950/40 dark:stroke-emerald-300/60' : 'fill-slate-200/50 stroke-slate-500/50 dark:fill-cyan-950/20 dark:stroke-cyan-100/30'; }
function masterHotspot(index) { return { x: index % 2 ? 620 : 60, y: index < 2 ? 100 : 510, width: 520, height: 350 }; }
function masterBlockBox(index, block) {
  const zone = masterHotspot(index);
  const column = blockColumn(block);
  const row = blockRow(block);
  return {
    x: zone.x + 20 + column * 40,
    y: zone.y + (row === 0 ? 115 : row === 1 ? 170 : 225),
    width: 34,
    height: 34,
  };
}
function detailBlockBox(block) { const column = blockColumn(block); const row = blockRow(block); return { x: 92 + column * 84, y: row === 0 ? 205 : row === 1 ? 465 : 530, width: 74, height: 48 }; }

onMounted(loadMap);
</script>
