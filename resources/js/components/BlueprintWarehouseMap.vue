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
        <div class="mb-2 flex items-center justify-between px-2 font-mono text-[9px] uppercase tracking-[0.15em] text-slate-500 dark:text-cyan-200/60"><span>PT SPINDO Tbk - Unit 7 Gresik</span><span>Scale 1:100 - Rev. 01</span></div>

        <svg v-if="!activeWarehouse" viewBox="0 0 1200 1500" class="h-auto w-full" role="img" aria-label="Master blueprint satu gedung dengan empat overlay gudang">
          <defs>
            <pattern id="master-grid" width="24" height="24" patternUnits="userSpaceOnUse"><path d="M24 0H0V24" fill="none" class="stroke-slate-300/70 dark:stroke-cyan-200/10" /></pattern>
            <marker id="master-arrow" markerWidth="8" markerHeight="8" refX="4" refY="4" orient="auto"><path d="M0 0L8 4L0 8Z" class="fill-safety" /></marker>
          </defs>
          <rect width="1200" height="1500" class="fill-[#eaf2f4] dark:fill-[#07131c]" />
          <rect x="24" y="24" width="1152" height="1452" fill="url(#master-grid)" class="stroke-slate-300 dark:stroke-cyan-200/20" />
          <rect x="58" y="62" width="1084" height="1370" rx="3" class="fill-white/70 stroke-slate-700 dark:fill-[#0b2230]/80 dark:stroke-cyan-100/80" stroke-width="4" />
          <path d="M58 126H1142" class="stroke-slate-500 dark:stroke-cyan-200/40" stroke-width="2" />
          <text x="82" y="96" class="fill-slate-700 dark:fill-cyan-100" font-size="16" font-family="IBM Plex Mono" font-weight="700">SUPPLY CHAIN WAREHOUSE / MAIN BUILDING</text>
          <text x="1118" y="96" text-anchor="end" class="fill-slate-500 dark:fill-cyan-200/60" font-size="10" font-family="IBM Plex Mono">N UP / 100 m x 100 m</text>

          <rect x="78" y="148" width="1044" height="86" class="fill-slate-100 stroke-slate-400 dark:fill-[#12384a] dark:stroke-cyan-200/50" />
          <path d="M300 148V234M525 148V234M755 148V234M970 148V234" class="stroke-slate-400 dark:stroke-cyan-200/35" />
          <text x="188" y="201" text-anchor="middle" class="fill-slate-600 dark:fill-cyan-100/80" font-size="10" font-family="IBM Plex Mono">PARKIR MANAGEMENT</text>
          <text x="412" y="201" text-anchor="middle" class="fill-slate-600 dark:fill-cyan-100/80" font-size="10" font-family="IBM Plex Mono">RUANG MEETING</text>
          <text x="640" y="201" text-anchor="middle" class="fill-slate-600 dark:fill-cyan-100/80" font-size="10" font-family="IBM Plex Mono">LOKET DELIVERY</text>
          <text x="862" y="201" text-anchor="middle" class="fill-safety" font-size="10" font-family="IBM Plex Mono" font-weight="700">MASUK PINTU SAMPING</text>
          <text x="1050" y="201" text-anchor="middle" class="fill-slate-600 dark:fill-cyan-100/80" font-size="10" font-family="IBM Plex Mono">MUSHOLA</text>

          <rect x="78" y="260" width="1044" height="350" class="fill-slate-100/60 stroke-slate-400/70 dark:fill-[#12384a]/35 dark:stroke-cyan-200/30" stroke-dasharray="9 6" />
          <path d="M78 345H1122M78 435H1122M78 525H1122M270 260V610M510 260V610M750 260V610M990 260V610" class="stroke-slate-400/40 dark:stroke-cyan-200/20" />
          <text x="600" y="370" text-anchor="middle" class="fill-slate-500 dark:fill-cyan-100/60" font-size="15" font-family="IBM Plex Mono" font-weight="700">AREA LAPANG GEDUNG / CRANE CLEARANCE</text>
          <text x="600" y="405" text-anchor="middle" class="fill-slate-400 dark:fill-cyan-100/40" font-size="11" font-family="IBM Plex Mono">PARKIR - MEETING - DELIVERY - MATERIAL BUFFER</text>
          <text x="600" y="450" text-anchor="middle" class="fill-safety" font-size="11" font-family="IBM Plex Mono" font-weight="700">CENTRAL TRANSFER AISLE / OVERHEAD CRANE</text>
          <path d="M78 620H1122" class="stroke-safety" stroke-width="4" stroke-dasharray="12 8" />
          <text x="92" y="645" class="fill-safety" font-size="9" font-family="IBM Plex Mono" font-weight="700">MASUK PINTU DEPAN</text>
          <text x="1090" y="645" text-anchor="end" class="fill-safety" font-size="9" font-family="IBM Plex Mono" font-weight="700">JALUR TRUK MUAT</text>
          <text x="600" y="645" text-anchor="middle" class="fill-safety" font-size="10" font-family="IBM Plex Mono" font-weight="700">JALUR TRANSFER / OVERHEAD CRANE</text>

          <rect x="78" y="665" width="1044" height="735" class="fill-slate-100/35 stroke-slate-500/70 dark:fill-[#0b2230]/25 dark:stroke-cyan-200/35" stroke-width="2" />
          <path d="M78 848H1122M78 1031H1122M78 1214H1122M78 1400H1122M180 665V1400M360 665V1400M540 665V1400M720 665V1400M900 665V1400" class="stroke-slate-400/35 dark:stroke-cyan-200/15" stroke-width="1" />
          <text x="92" y="688" class="fill-slate-500 dark:fill-cyan-100/50" font-size="9" font-family="IBM Plex Mono">COMMON BUILDING FLOOR GRID / STORAGE OVERLAY POSITIONS</text>

          <g v-for="(warehouse, index) in warehouses" :key="warehouse.id" @click="openWarehouse(warehouse)" @mouseenter="hoveredWarehouseId = warehouse.id" @mouseleave="hoveredWarehouseId = null" @focus="hoveredWarehouseId = warehouse.id" @blur="hoveredWarehouseId = null" :class="['cursor-pointer transition-opacity duration-200', hoveredWarehouseId === warehouse.id ? 'opacity-100' : hoveredWarehouseId ? 'opacity-30' : 'opacity-70']" role="button" tabindex="0" :aria-label="`Buka ${shortName(warehouse)}`" @keydown.enter="openWarehouse(warehouse)">
            <rect :x="masterHotspot(index).x" :y="masterHotspot(index).y" :width="masterHotspot(index).width" :height="masterHotspot(index).height" class="fill-white/5 stroke-cyan-600/40 transition hover:fill-safety/10 hover:stroke-safety dark:fill-cyan-300/5 dark:stroke-cyan-300/35" stroke-width="2" stroke-dasharray="8 5" />
            <path :d="masterHotspotStorage(index)" :class="hoveredWarehouseId === warehouse.id ? 'opacity-100' : 'opacity-35'" class="fill-red-500/5 stroke-red-500/80 transition-opacity duration-200 dark:fill-red-950/20 dark:stroke-red-300/70" stroke-width="2" />
            <rect :x="masterHotspot(index).x + 500" :y="masterHotspot(index).y" width="36" :height="masterHotspot(index).height" class="fill-yellow-300/30 stroke-yellow-500 dark:fill-yellow-300/10 dark:stroke-yellow-200" stroke-width="2" />
            <text :x="masterHotspot(index).x + 518" :y="masterHotspot(index).y + masterHotspot(index).height / 2" text-anchor="middle" :transform="`rotate(-90 ${masterHotspot(index).x + 518} ${masterHotspot(index).y + masterHotspot(index).height / 2})`" class="fill-safety" font-size="7" font-family="IBM Plex Mono">AISLE / CRANE</text>
            <g v-for="block in warehouse.blocks" :key="block.id">
              <rect :x="masterBlockBox(index, block).x" :y="masterBlockBox(index, block).y" :width="masterBlockBox(index, block).width" :height="masterBlockBox(index, block).height" :class="masterBlockClass(block)" />
              <text :x="masterBlockBox(index, block).x + 2" :y="masterBlockBox(index, block).y + 9" class="fill-slate-700 dark:fill-cyan-50" font-size="6" font-family="IBM Plex Mono" font-weight="700">{{ block.code }}</text>
            </g>
            <text :x="masterHotspot(index).x + 260" :y="masterHotspot(index).y + 14" text-anchor="middle" class="fill-red-500/80 dark:fill-red-300/80" font-size="7" font-family="IBM Plex Mono" font-weight="700">A-H / STORAGE</text>
            <text :x="masterHotspot(index).x + 700" :y="masterHotspot(index).y + 14" text-anchor="middle" class="fill-red-500/80 dark:fill-red-300/80" font-size="7" font-family="IBM Plex Mono" font-weight="700">I-L / STORAGE</text>
            <text :x="masterHotspot(index).x + 15" :y="masterHotspot(index).y + 28" class="fill-safety" font-size="10" font-family="IBM Plex Mono" font-weight="700">{{ warehouse.code }}</text>
            <text :x="masterHotspot(index).x + 15" :y="masterHotspot(index).y + 52" class="fill-slate-700 dark:fill-cyan-100" font-size="15" font-family="DM Sans" font-weight="800">{{ shortName(warehouse) }}</text>
            <text :x="masterHotspot(index).x + 300" :y="masterHotspot(index).y + 43" class="fill-slate-500 dark:fill-cyan-100/60" font-size="8" font-family="IBM Plex Mono">36 BLOCKS / 12 SLOC / CLICK TO DETAIL</text>
            <text :x="masterHotspot(index).x + masterHotspot(index).width - 18" :y="masterHotspot(index).y + masterHotspot(index).height / 2" text-anchor="middle" :transform="`rotate(90 ${masterHotspot(index).x + masterHotspot(index).width - 18} ${masterHotspot(index).y + masterHotspot(index).height / 2})`" class="fill-slate-700 dark:fill-cyan-100" font-size="12" font-family="IBM Plex Mono" font-weight="700">{{ shortName(warehouse).toUpperCase() }}</text>
            <text :x="masterHotspot(index).x + masterHotspot(index).width - 42" :y="masterHotspot(index).y + masterHotspot(index).height / 2" text-anchor="middle" :transform="`rotate(90 ${masterHotspot(index).x + masterHotspot(index).width - 42} ${masterHotspot(index).y + masterHotspot(index).height / 2})`" class="fill-slate-500 dark:fill-cyan-100/70" font-size="8" font-family="IBM Plex Mono">WAREHOUSE ZONE</text>
            <path :d="masterLoadingPath(index)" class="fill-none stroke-emerald-600 dark:stroke-emerald-300" stroke-width="3" marker-end="url(#master-arrow)" />
            <text :x="masterHotspot(index).x + 18" :y="masterHotspot(index).y + 108" class="fill-emerald-600 dark:fill-emerald-300" font-size="8" font-family="IBM Plex Mono" font-weight="700">JALUR BONGKAR MUAT</text>
            <text :x="masterHotspot(index).x + masterHotspot(index).width - 18" :y="masterHotspot(index).y + 108" text-anchor="end" class="fill-yellow-700 dark:fill-yellow-200" font-size="8" font-family="IBM Plex Mono" font-weight="700">L2 / CADDY / TANGGA</text>
            <circle :cx="masterHotspot(index).x + 28" :cy="masterHotspot(index).y + 84" r="6" class="fill-red-600" /><circle :cx="masterHotspot(index).x + 44" :cy="masterHotspot(index).y + 84" r="6" class="fill-orange-500" /><circle :cx="masterHotspot(index).x + 60" :cy="masterHotspot(index).y + 84" r="6" class="fill-emerald-600" /><circle :cx="masterHotspot(index).x + 76" :cy="masterHotspot(index).y + 84" r="6" class="fill-blue-600" />
            <rect :x="masterHotspot(index).x + 700" :y="masterHotspot(index).y + 88" width="10" height="22" class="fill-yellow-400 stroke-yellow-600 dark:fill-yellow-300 dark:stroke-yellow-100" />
            <rect :x="masterHotspot(index).x + 716" :y="masterHotspot(index).y + 88" width="30" height="22" class="fill-yellow-300/60 stroke-yellow-600 dark:fill-yellow-300/20 dark:stroke-yellow-100" />
          </g>

          <path d="M78 1415H1122" class="stroke-safety" stroke-width="2" marker-start="url(#master-arrow)" marker-end="url(#master-arrow)" />
          <text x="600" y="1445" text-anchor="middle" class="fill-safety" font-size="11" font-family="IBM Plex Mono" font-weight="700">MAIN LOADING APRON / TRUCK ROUTE</text>
          <g transform="translate(120 1470)"><circle cx="0" cy="0" r="6" class="fill-red-600" /><circle cx="18" cy="0" r="6" class="fill-orange-500" /><circle cx="36" cy="0" r="6" class="fill-emerald-600" /><circle cx="54" cy="0" r="6" class="fill-blue-600" /><text x="70" y="4" class="fill-slate-500 dark:fill-cyan-100/60" font-size="9" font-family="IBM Plex Mono">WASTE POINTS</text></g>
          <g transform="translate(850 1455)"><rect width="14" height="26" class="fill-yellow-400 stroke-yellow-600" /><text x="22" y="17" class="fill-slate-500 dark:fill-cyan-100/60" font-size="9" font-family="IBM Plex Mono">CADDY / TANGGA MUAT</text></g>
        </svg>

        <svg v-else viewBox="0 0 1200 800" class="h-auto w-full" role="img" :aria-label="`Blueprint ${shortName(activeWarehouse)}`">
          <defs><pattern id="detail-grid" width="24" height="24" patternUnits="userSpaceOnUse"><path d="M24 0H0V24" fill="none" class="stroke-slate-300/70 dark:stroke-cyan-200/10" /></pattern></defs>
          <rect width="1200" height="800" class="fill-[#eaf2f4] dark:fill-[#07131c]" /><rect x="24" y="24" width="1152" height="752" fill="url(#detail-grid)" class="stroke-slate-300 dark:stroke-cyan-200/20" /><rect x="58" y="62" width="1084" height="680" class="fill-white/70 stroke-slate-700 dark:fill-[#0b2230]/80 dark:stroke-cyan-100/80" stroke-width="4" />
          <path d="M58 126H1142" class="stroke-slate-500 dark:stroke-cyan-200/40" stroke-width="2" /><text x="82" y="96" class="fill-slate-700 dark:fill-cyan-100" font-size="16" font-family="IBM Plex Mono" font-weight="700">{{ activeWarehouse.code }} / {{ shortName(activeWarehouse).toUpperCase() }} - FLOOR PLAN</text><text x="1118" y="96" text-anchor="end" class="fill-safety" font-size="10" font-family="IBM Plex Mono" font-weight="700">BACK TO BUILDING</text>
          <rect x="78" y="148" width="1044" height="150" class="fill-slate-100 stroke-slate-400 dark:fill-[#12384a] dark:stroke-cyan-200/50" stroke-dasharray="7 5" /><text x="350" y="230" text-anchor="middle" class="fill-slate-500 dark:fill-cyan-100/60" font-size="11" font-family="IBM Plex Mono">AREA LAPANG / CRANE CLEARANCE</text><text x="850" y="230" text-anchor="middle" class="fill-slate-500 dark:fill-cyan-100/60" font-size="11" font-family="IBM Plex Mono">PARKIR / MEETING / DELIVERY</text>
          <rect x="78" y="320" width="1044" height="330" class="fill-none stroke-cyan-600/90 dark:stroke-cyan-300/80" stroke-width="3" /><path :d="detailStorageOutline" class="fill-red-500/5 stroke-red-500/90 dark:fill-red-950/20 dark:stroke-red-300/80" stroke-width="3" /><rect x="716" y="320" width="76" height="330" class="fill-yellow-300/30 stroke-yellow-500 dark:fill-yellow-300/10 dark:stroke-yellow-200" stroke-width="2" />
           <text x="390" y="386" text-anchor="middle" class="fill-red-500/80 dark:fill-red-300/80" font-size="10" font-family="IBM Plex Mono" font-weight="700">A-H / STORAGE PIPA</text><text x="950" y="386" text-anchor="middle" class="fill-red-500/80 dark:fill-red-300/80" font-size="10" font-family="IBM Plex Mono" font-weight="700">I-L / STORAGE PIPA</text><text x="754" y="500" text-anchor="middle" transform="rotate(-90 754 500)" class="fill-safety" font-size="10" font-family="IBM Plex Mono" font-weight="700">AISLE / CRANE ACCESS</text><path d="M100 500H178M1100 500H1022" class="stroke-emerald-600 dark:stroke-emerald-300" stroke-width="4" />
          <g v-for="block in activeWarehouse.blocks" :key="block.id" @click="selectBlock(block)" class="cursor-pointer" role="button" tabindex="0" @keydown.enter="selectBlock(block)"><rect :x="detailBlockBox(block).x" :y="detailBlockBox(block).y" :width="detailBlockBox(block).width" :height="detailBlockBox(block).height" rx="2" :class="detailBlockClass(block)" stroke-width="2" /><text :x="detailBlockBox(block).x + 7" :y="detailBlockBox(block).y + 17" class="fill-slate-800 dark:fill-cyan-50" font-size="11" font-family="IBM Plex Mono" font-weight="700">{{ block.code }}</text><text :x="detailBlockBox(block).x + 7" :y="detailBlockBox(block).y + 31" class="fill-safety" font-size="8" font-family="IBM Plex Mono" font-weight="700">{{ block.sloc_code }}</text><text :x="detailBlockBox(block).x + detailBlockBox(block).width - 7" :y="detailBlockBox(block).y + 17" text-anchor="end" class="fill-slate-500 dark:fill-cyan-100/60" font-size="8" font-family="IBM Plex Mono">{{ block.inventories.length }}</text><text v-if="isAccessBlock(block)" :x="detailBlockBox(block).x + 7" :y="detailBlockBox(block).y + 43" class="fill-yellow-700 dark:fill-yellow-200" font-size="7" font-family="IBM Plex Mono" font-weight="700">CADDY</text><rect :x="detailBlockBox(block).x + 7" :y="detailBlockBox(block).y + 35" :width="detailBlockBox(block).width - 14" height="3" rx="1" class="fill-slate-200 dark:fill-[#07131c]" /><rect :x="detailBlockBox(block).x + 7" :y="detailBlockBox(block).y + 35" :width="(detailBlockBox(block).width - 14) * percent(block) / 100" height="3" rx="1" :class="barClass(block)" /></g>
          <text x="82" y="700" class="fill-safety" font-size="9" font-family="IBM Plex Mono" font-weight="700">STORAGE LAYOUT / A-H | AISLE | I-L / L2 ACCESS CADDY</text>
        </svg>
      </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 rounded border border-slate-200 bg-white px-3 py-2 font-mono text-[10px] text-slate-500 dark:border-iron-800 dark:bg-iron-900 dark:text-iron-400"><span>Tip: pilih Gudang 1-4, lalu tekan blok pada blueprint.</span><span class="flex gap-3"><span><i class="mr-1 inline-block h-2 w-2 rounded-full bg-emerald-500"></i>Occupied</span><span><i class="mr-1 inline-block h-2 w-2 rounded-full bg-safety"></i>High load</span><span><i class="mr-1 inline-block h-2 w-2 rounded-full bg-slate-300 dark:bg-iron-700"></i>Empty</span></span></div>
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
const detailStorageOutline = 'M92 390H690V440H138V495H690V550H92Z M818 390H1108V440H818V495H1018V550H818Z';

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
function masterHotspot(index) { return { x: 130, y: 700 + index * 170, width: 860, height: 150 }; }
function masterHotspotStorage(index) { const zone = masterHotspot(index); return `M${zone.x + 20} ${zone.y + 18}H${zone.x + 500}V${zone.y + 48}H${zone.x + 20}Z M${zone.x + 78} ${zone.y + 60}H${zone.x + 500}V${zone.y + 90}H${zone.x + 78}Z M${zone.x + 20} ${zone.y + 102}H${zone.x + 500}V${zone.y + 132}H${zone.x + 20}Z M${zone.x + 540} ${zone.y + 18}H${zone.x + 830}V${zone.y + 48}H${zone.x + 540}Z M${zone.x + 540} ${zone.y + 60}H${zone.x + 790}V${zone.y + 90}H${zone.x + 540}Z M${zone.x + 540} ${zone.y + 102}H${zone.x + 790}V${zone.y + 132}H${zone.x + 540}Z`; }
function masterLoadingPath(index) {
  const zone = masterHotspot(index);
  return `M${zone.x + 88} ${zone.y + 84}H${zone.x + 178}M${zone.x + 178} ${zone.y + 84}L${zone.x + 164} ${zone.y + 76}M${zone.x + 178} ${zone.y + 84}L${zone.x + 164} ${zone.y + 92}`;
}
function masterBlockBox(index, block) {
  const zone = masterHotspot(index);
  const column = blockColumn(block);
  const row = blockRow(block);
  const left = column < 8;
  return {
    x: zone.x + (left ? 24 + column * 57 : 545 + (column - 8) * 67),
    y: zone.y + 20 + row * 40,
    width: left ? 50 : 60,
    height: 28,
  };
}
function detailBlockBox(block) { const column = blockColumn(block); const row = blockRow(block); if (isAccessBlock(block)) return { x: 1038, y: 454, width: 58, height: 42 }; return column < 8 ? { x: 98 + column * 74, y: 407 + row * 47, width: 66, height: 36 } : { x: 824 + (column - 8) * 68, y: 407 + row * 47, width: 60, height: 36 }; }

onMounted(loadMap);
</script>
