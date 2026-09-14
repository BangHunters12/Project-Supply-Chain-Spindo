<template>
  <div class="space-y-6">
    
    <!-- Hero Header Landing Page Banner -->
    <div class="bg-iron-900 rounded-lg border border-iron-800 p-6 space-y-4 shadow-xl">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
          <div class="flex items-center space-x-3">
            <span class="px-2.5 py-0.5 rounded bg-spindo-red text-white font-mono text-[10px] font-bold uppercase tracking-wider">
              SPINDO SC-U7
            </span>
            <span class="text-xs font-mono text-iron-400">Gresik Unit 7 &middot; Plant Floor Plan Mapping</span>
          </div>
          <h1 class="text-xl sm:text-2xl font-display font-extrabold text-iron-100 tracking-tight mt-1">
            Denah Layout & Mapping Pipa Gudang
          </h1>
          <p class="text-xs text-iron-400 font-mono mt-1 max-w-3xl">
            Visualisasi cetak biru 2D real-time posisi bundle pipa per blok (A1-L3) untuk Gudang 1, Gudang 2, dan Gudang 3. Skala area fisik 96m &times; 96m (Gedung 100m &times; 100m) &middot; Clearance Overhead Crane 7.0 Meter.
          </p>
        </div>

        <!-- Real-time Search Box in Landing Page -->
        <div class="w-full lg:w-72">
          <label class="block text-[10px] font-mono text-iron-400 uppercase mb-1">Cari Pipa / Blok:</label>
          <div class="relative">
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Ketik ukuran, spec, atau blok..."
              class="w-full bg-iron-950 border border-iron-700 rounded px-3 py-2 text-xs font-mono text-iron-200 placeholder-iron-600 focus:outline-none focus:border-steel-blue"
            />
            <button v-if="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-2 text-iron-500 hover:text-iron-300 text-xs font-mono">
              ESC
            </button>
          </div>
        </div>
      </div>

      <!-- Warehouse Selector Tabs & Stats -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-3 border-t border-iron-800">
        
        <!-- Warehouse Selector -->
        <div class="flex items-center space-x-2 overflow-x-auto bg-iron-950 p-1 rounded-md border border-iron-800">
          <button
            v-for="wh in warehouses"
            :key="wh.id"
            @click="activeWarehouseCode = wh.code"
            :class="[
              'px-4 py-2 rounded text-xs font-display font-bold transition-all whitespace-nowrap flex items-center space-x-2',
              activeWarehouseCode === wh.code
                ? 'bg-steel-blue text-white shadow-md'
                : 'text-iron-400 hover:text-iron-200 hover:bg-iron-800'
            ]"
          >
            <span>{{ wh.name }}</span>
            <span class="text-[10px] font-mono px-1.5 py-0.2 bg-black/30 rounded">24 Blok</span>
          </button>
        </div>

        <!-- Key Metrics Badges -->
        <div class="flex items-center space-x-4 text-xs font-mono">
          <div class="bg-iron-950 px-3 py-1.5 rounded border border-iron-800">
            <span class="text-iron-400">Okupansi: </span>
            <span class="text-emerald-400 font-bold tabular-nums">{{ currentWarehouseTons.toFixed(1) }} / {{ currentWarehouseMaxTons.toFixed(0) }} Ton</span>
          </div>
          <div class="bg-iron-950 px-3 py-1.5 rounded border border-iron-800">
            <span class="text-iron-400">Total Bundle: </span>
            <span class="text-steel-blue-light font-bold tabular-nums">{{ currentWarehouseBundles }} Bundle</span>
          </div>
          <div class="bg-iron-950 px-3 py-1.5 rounded border border-iron-800 hidden sm:block">
            <span class="text-iron-400">Crane: </span>
            <span class="text-amber-400 font-bold">7.0 m</span>
          </div>
        </div>

      </div>
    </div>

    <!-- 2D Interactive Blueprint Canvas Layout -->
    <div class="bg-iron-900 rounded-lg border border-iron-800 p-5 space-y-4 shadow-2xl relative">
      
      <!-- Blueprint Top Header Bar -->
      <div class="flex items-center justify-between border-b border-iron-800 pb-2 text-[10px] font-mono text-iron-400">
        <div class="flex items-center space-x-3">
          <span class="font-bold text-iron-200 uppercase">{{ currentWarehouseName }}</span>
          <span class="text-iron-600">&bull;</span>
          <span>SPINDO UNIT 7 GRESIK</span>
        </div>

        <!-- Masuk Pintu Samping Marker -->
        <div class="flex items-center space-x-1.5 bg-amber-500/20 text-amber-400 border border-amber-500/40 px-3 py-1 rounded font-bold">
          <svg class="w-3.5 h-3.5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7 7m7 7V3"/></svg>
          <span>MASUK PINTU SAMPING</span>
        </div>

        <div class="flex items-center space-x-3">
          <span class="bg-iron-950 border border-iron-800 px-2 py-0.5 rounded text-iron-300">Parkir Management</span>
          <span class="bg-iron-950 border border-iron-800 px-2 py-0.5 rounded text-iron-300">Loket Delivery</span>
          <span class="bg-iron-950 border border-iron-800 px-2 py-0.5 rounded text-iron-300">Mushola</span>
        </div>
      </div>

      <!-- Main Layout Grid Body -->
      <div class="grid grid-cols-12 gap-3 min-w-[1000px] bg-iron-950 p-4 rounded-lg border border-iron-800 relative">
        
        <!-- Left Wing: Columns A, B, C, D, E, F, G, H (8 Columns x 3 Rows) -->
        <div class="col-span-8 space-y-3">
          
          <!-- Row 1: Blocks A1..H1 -->
          <div class="grid grid-cols-8 gap-2">
            <div
              v-for="col in leftColumns"
              :key="col + '1'"
              @click="selectBlock(col + '1')"
              :class="getBlockCardClasses(col + '1')"
            >
              <div class="flex items-center justify-between">
                <span class="font-mono text-xs font-bold text-iron-100">{{ col }}1</span>
                <span class="w-2.5 h-2.5 rounded-full" :class="getBlockDotStatus(col + '1')"></span>
              </div>
              <div class="text-[9px] font-display font-medium text-iron-300 truncate" :title="getBlockPipeType(col + '1')">
                {{ getBlockPipeType(col + '1') }}
              </div>
              <div class="w-full bg-iron-950 h-1.5 rounded-sm overflow-hidden border border-iron-800/50">
                <div class="h-full transition-all" :class="getBlockBarColor(col + '1')" :style="{ width: getBlockPercent(col + '1') + '%' }"></div>
              </div>
              <div class="flex justify-between text-[8px] font-mono text-iron-400">
                <span>{{ getBlockTons(col + '1') }}T</span>
                <span>{{ getBlockBundlesCount(col + '1') }} bdl</span>
              </div>
            </div>
          </div>

          <!-- Central Driveway Pathway Arrow (Horizontal) -->
          <div class="bg-amber-500/10 border border-amber-500/30 rounded py-1.5 px-3 flex items-center justify-between text-[10px] font-mono text-amber-400">
            <div class="flex items-center space-x-2">
              <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              <span class="font-bold">JALUR BONGKAR MUAT / TRUK MUAT</span>
            </div>
            <div class="flex items-center space-x-2">
              <span class="font-bold">MASUK PINTU DEPAN</span>
              <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </div>
          </div>

          <!-- Row 2: Blocks A2..H2 -->
          <div class="grid grid-cols-8 gap-2">
            <div
              v-for="col in leftColumns"
              :key="col + '2'"
              @click="selectBlock(col + '2')"
              :class="getBlockCardClasses(col + '2')"
            >
              <div class="flex items-center justify-between">
                <span class="font-mono text-xs font-bold text-iron-100">{{ col }}2</span>
                <span class="w-2.5 h-2.5 rounded-full" :class="getBlockDotStatus(col + '2')"></span>
              </div>
              <div class="text-[9px] font-display font-medium text-iron-300 truncate" :title="getBlockPipeType(col + '2')">
                {{ getBlockPipeType(col + '2') }}
              </div>
              <div class="w-full bg-iron-950 h-1.5 rounded-sm overflow-hidden border border-iron-800/50">
                <div class="h-full transition-all" :class="getBlockBarColor(col + '2')" :style="{ width: getBlockPercent(col + '2') + '%' }"></div>
              </div>
              <div class="flex justify-between text-[8px] font-mono text-iron-400">
                <span>{{ getBlockTons(col + '2') }}T</span>
                <span>{{ getBlockBundlesCount(col + '2') }} bdl</span>
              </div>
            </div>
          </div>

          <!-- Row 3: Blocks A3..H3 -->
          <div class="grid grid-cols-8 gap-2">
            <div
              v-for="col in leftColumns"
              :key="col + '3'"
              @click="selectBlock(col + '3')"
              :class="getBlockCardClasses(col + '3')"
            >
              <div class="flex items-center justify-between">
                <span class="font-mono text-xs font-bold text-iron-100">{{ col }}3</span>
                <span class="w-2.5 h-2.5 rounded-full" :class="getBlockDotStatus(col + '3')"></span>
              </div>
              <div class="text-[9px] font-display font-medium text-iron-300 truncate" :title="getBlockPipeType(col + '3')">
                {{ getBlockPipeType(col + '3') }}
              </div>
              <div class="w-full bg-iron-950 h-1.5 rounded-sm overflow-hidden border border-iron-800/50">
                <div class="h-full transition-all" :class="getBlockBarColor(col + '3')" :style="{ width: getBlockPercent(col + '3') + '%' }"></div>
              </div>
              <div class="flex justify-between text-[8px] font-mono text-iron-400">
                <span>{{ getBlockTons(col + '3') }}T</span>
                <span>{{ getBlockBundlesCount(col + '3') }} bdl</span>
              </div>
            </div>
          </div>

        </div>

        <!-- Right Wing: Columns I, J, K, L (4 Columns x 3 Rows) -->
        <div class="col-span-4 space-y-3 border-l border-iron-800 pl-3">
          
          <!-- Row 1: Blocks I1..L1 -->
          <div class="grid grid-cols-4 gap-2">
            <div
              v-for="col in rightColumns"
              :key="col + '1'"
              @click="selectBlock(col + '1')"
              :class="getBlockCardClasses(col + '1')"
            >
              <div class="flex items-center justify-between">
                <span class="font-mono text-xs font-bold text-iron-100">{{ col }}1</span>
                <span class="w-2.5 h-2.5 rounded-full" :class="getBlockDotStatus(col + '1')"></span>
              </div>
              <div class="text-[9px] font-display font-medium text-iron-300 truncate" :title="getBlockPipeType(col + '1')">
                {{ getBlockPipeType(col + '1') }}
              </div>
              <div class="w-full bg-iron-950 h-1.5 rounded-sm overflow-hidden border border-iron-800/50">
                <div class="h-full transition-all" :class="getBlockBarColor(col + '1')" :style="{ width: getBlockPercent(col + '1') + '%' }"></div>
              </div>
              <div class="flex justify-between text-[8px] font-mono text-iron-400">
                <span>{{ getBlockTons(col + '1') }}T</span>
                <span>{{ getBlockBundlesCount(col + '1') }} bdl</span>
              </div>
            </div>
          </div>

          <!-- Central Driveway Pathway Right (Horizontal) -->
          <div class="bg-amber-500/10 border border-amber-500/30 rounded py-1.5 px-3 flex items-center justify-between text-[10px] font-mono text-amber-400">
            <div class="flex items-center space-x-1.5">
              <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
              <span class="font-bold">AKSES CRANE & CADDY</span>
            </div>
            <span class="text-[9px] bg-iron-800 text-iron-300 px-2 py-0.5 rounded font-bold">TANGGA MUAT</span>
          </div>

          <!-- Row 2: Blocks I2..L2 -->
          <div class="grid grid-cols-4 gap-2">
            <div
              v-for="col in rightColumns"
              :key="col + '2'"
              @click="selectBlock(col + '2')"
              :class="getBlockCardClasses(col + '2')"
            >
              <div class="flex items-center justify-between">
                <span class="font-mono text-xs font-bold text-iron-100">{{ col }}2</span>
                <span class="w-2.5 h-2.5 rounded-full" :class="getBlockDotStatus(col + '2')"></span>
              </div>
              <div class="text-[9px] font-display font-medium text-iron-300 truncate" :title="getBlockPipeType(col + '2')">
                {{ getBlockPipeType(col + '2') }}
              </div>
              <div class="w-full bg-iron-950 h-1.5 rounded-sm overflow-hidden border border-iron-800/50">
                <div class="h-full transition-all" :class="getBlockBarColor(col + '2')" :style="{ width: getBlockPercent(col + '2') + '%' }"></div>
              </div>
              <div class="flex justify-between text-[8px] font-mono text-iron-400">
                <span>{{ getBlockTons(col + '2') }}T</span>
                <span>{{ getBlockBundlesCount(col + '2') }} bdl</span>
              </div>
            </div>
          </div>

          <!-- Row 3: Blocks I3..L3 -->
          <div class="grid grid-cols-4 gap-2">
            <div
              v-for="col in rightColumns"
              :key="col + '3'"
              @click="selectBlock(col + '3')"
              :class="getBlockCardClasses(col + '3')"
            >
              <div class="flex items-center justify-between">
                <span class="font-mono text-xs font-bold text-iron-100">{{ col }}3</span>
                <span class="w-2.5 h-2.5 rounded-full" :class="getBlockDotStatus(col + '3')"></span>
              </div>
              <div class="text-[9px] font-display font-medium text-iron-300 truncate" :title="getBlockPipeType(col + '3')">
                {{ getBlockPipeType(col + '3') }}
              </div>
              <div class="w-full bg-iron-950 h-1.5 rounded-sm overflow-hidden border border-iron-800/50">
                <div class="h-full transition-all" :class="getBlockBarColor(col + '3')" :style="{ width: getBlockPercent(col + '3') + '%' }"></div>
              </div>
              <div class="flex justify-between text-[8px] font-mono text-iron-400">
                <span>{{ getBlockTons(col + '3') }}T</span>
                <span>{{ getBlockBundlesCount(col + '3') }} bdl</span>
              </div>
            </div>
          </div>

        </div>

      </div>

      <!-- Blueprint Footer Legend -->
      <div class="pt-3 border-t border-iron-800 flex flex-wrap items-center justify-between gap-4 text-[10px] font-mono text-iron-400">
        <div class="flex items-center space-x-5">
          <div class="flex items-center space-x-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
            <span>Available / QC Passed</span>
          </div>
          <div class="flex items-center space-x-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
            <span>QC Pending / Pre-Loading</span>
          </div>
          <div class="flex items-center space-x-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-spindo-red"></span>
            <span>Kapasitas Penuh (&ge;90%)</span>
          </div>
          <div class="flex items-center space-x-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-iron-700"></span>
            <span>Kosong / Doff</span>
          </div>
        </div>

        <div class="flex items-center space-x-4 text-[9px]">
          <span class="text-iron-500">Lokasi Sampah:</span>
          <span class="flex items-center space-x-1"><span class="w-2 h-2 rounded-full bg-red-500"></span><span>B3</span></span>
          <span class="flex items-center space-x-1"><span class="w-2 h-2 rounded-full bg-orange-500"></span><span>Plastik/Kertas</span></span>
          <span class="flex items-center space-x-1"><span class="w-2 h-2 rounded-full bg-green-500"></span><span>Organik</span></span>
          <span class="flex items-center space-x-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span><span>Logam</span></span>
        </div>
      </div>

    </div>

    <!-- Interactive Block Detail & Pipe Stock Modal -->
    <div v-if="selectedBlockData" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-iron-950/80 backdrop-blur-sm">
      <div class="bg-iron-900 border border-iron-800 rounded-lg max-w-xl w-full p-6 space-y-4 relative shadow-2xl">
        
        <button @click="selectedBlockData = null" class="absolute top-4 right-4 text-iron-400 hover:text-iron-200 text-xs font-mono">
          ESC
        </button>

        <!-- Header Modal -->
        <div class="border-b border-iron-800 pb-3">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <span class="font-mono text-sm font-bold text-steel-blue-light">
                {{ currentWarehouseName }} &mdash; Blok {{ selectedBlockId }}
              </span>
              <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-iron-800 text-iron-300">
                Kapasitas Max: 50.0 Ton
              </span>
            </div>
            <span class="text-xs font-mono font-bold" :class="selectedBlockTons >= 45 ? 'text-spindo-red' : 'text-emerald-400'">
              {{ selectedBlockTons.toFixed(2) }} Ton ({{ Math.round((selectedBlockTons / 50.0) * 100) }}%)
            </span>
          </div>
          <p class="text-xs text-iron-300 font-mono mt-1">
            Identitas Alokasi Pipa: <strong class="text-amber-400">{{ selectedBlockIdentity }}</strong>
          </p>
        </div>

        <!-- Inventory Pipe List inside Block -->
        <div class="space-y-2">
          <div class="flex items-center justify-between">
            <h4 class="text-xs font-display font-bold text-iron-200 uppercase tracking-wider">
              Pipa Tersimpan Di Blok Ini ({{ selectedBlockInventories.length }} Bundle)
            </h4>
            <span class="text-[10px] font-mono text-iron-500">Real-time WMS Ledger</span>
          </div>

          <div class="bg-iron-950 rounded border border-iron-800 p-2.5 max-h-56 overflow-y-auto space-y-2">
            <div
              v-for="bdl in selectedBlockInventories"
              :key="bdl.id"
              @click="selectedInventoryDetail = bdl"
              class="p-3 rounded bg-iron-900 border border-iron-800 flex items-center justify-between text-xs font-mono cursor-pointer hover:border-steel-blue-light/70 hover:bg-iron-850 transition group"
              title="Klik untuk melihat detail lengkap pipa ini"
            >
              <div class="space-y-1 min-w-0 flex-1 pr-3">
                <div class="text-xs font-bold text-white group-hover:text-steel-blue-light transition-colors flex items-center gap-2 flex-wrap">
                  <span>{{ pipeEasyName(bdl) }}</span>
                  <span class="text-[9px] px-1.5 py-0.5 rounded bg-iron-800 text-iron-300 font-normal">
                    {{ bdl.product?.category?.code || bdl.product?.category }}
                  </span>
                </div>
                <div class="text-[11px] text-cyan-400 font-mono font-semibold">
                  {{ pipeDescription(bdl) }}
                </div>
                <div class="text-[10px] text-iron-400">
                  Heat: {{ bdl.heat_number }} &middot; {{ bdl.qty_pcs }} Pcs ({{ bdl.qty_bundles }} Bendel)
                </div>
              </div>

              <div class="text-right space-y-1 shrink-0">
                <div class="font-bold text-iron-100">{{ (bdl.total_weight_kg / 1000).toFixed(2) }} Ton</div>
                <div class="flex items-center justify-end space-x-1">
                  <span class="w-2 h-2 rounded-full" :class="bdl.qc_status === 'PASSED' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                  <span class="text-[10px] text-iron-400 uppercase">{{ bdl.qc_status }}</span>
                </div>
                <span class="text-[9px] text-iron-500 group-hover:text-steel-blue-light transition-colors block">
                  Detail &rarr;
                </span>
              </div>
            </div>

            <div v-if="selectedBlockInventories.length === 0" class="text-xs font-display text-iron-500 text-center py-6">
              Belum ada bundle pipa tersimpan di blok ini.
            </div>
          </div>
        </div>

        <!-- Quick Transaction Buttons -->
        <div class="space-y-2 pt-2 border-t border-iron-800">
          <label class="block text-[11px] font-display font-semibold text-iron-400 uppercase tracking-wider">
            Aksi Transaksi Pipa Langsung:
          </label>
          <div class="grid grid-cols-3 gap-2">
            <button
              @click="triggerInboundToBlock"
              class="px-3 py-2 rounded bg-steel-blue hover:bg-steel-blue-light text-white text-xs font-display font-bold transition text-center"
            >
              Inbound Ke Blok
            </button>
            <button
              @click="triggerOutboundFromBlock"
              :disabled="selectedBlockInventories.length === 0"
              class="px-3 py-2 rounded bg-iron-800 hover:bg-iron-700 disabled:opacity-40 text-iron-200 text-xs font-display font-bold transition text-center"
            >
              Muat Outbound
            </button>
            <button
              @click="triggerRelocateFromBlock"
              :disabled="selectedBlockInventories.length === 0"
              class="px-3 py-2 rounded bg-iron-800 hover:bg-iron-700 disabled:opacity-40 text-iron-200 text-xs font-display font-bold transition text-center"
            >
              Relokasi Pipa
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- Pipe Inventory Detail Modal in 2D Interactive Map -->
    <div
      v-if="selectedInventoryDetail"
      class="fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-6 bg-black/80 backdrop-blur-sm"
      role="dialog"
      aria-modal="true"
      @click.self="selectedInventoryDetail = null"
    >
      <div class="relative w-full max-w-lg rounded-2xl border border-iron-700 bg-iron-900 p-5 sm:p-6 shadow-2xl max-h-[92vh] overflow-y-auto space-y-4 text-iron-100">
        <!-- Header Modal -->
        <div class="flex items-start justify-between gap-3 border-b border-iron-800 pb-3">
          <div>
            <span class="inline-block font-mono text-[10px] font-black uppercase tracking-wider text-steel-blue-light">
              SIKUTA &middot; DETAIL RINCIAN PIPA
            </span>
            <h3 class="mt-1 text-base sm:text-lg font-black text-white leading-tight">
              {{ pipeEasyName(selectedInventoryDetail) }}
            </h3>
          </div>
          <button
            type="button"
            @click="selectedInventoryDetail = null"
            class="rounded-lg border border-iron-700 p-1.5 text-iron-400 hover:bg-iron-800 hover:text-white transition-colors"
            title="Tutup (Esc)"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Deskripsi Asli SIKUTA Card -->
        <div class="rounded-xl border border-iron-800 bg-iron-950/80 p-3.5">
          <div class="flex items-center justify-between text-[10px] font-mono text-iron-400 mb-1">
            <span>DESKRIPSI SIKUTA</span>
            <span class="font-bold text-steel-blue-light">{{ selectedInventoryDetail.product?.sap_code || '-' }}</span>
          </div>
          <p class="font-mono text-sm font-bold text-cyan-300 break-words leading-relaxed">
            {{ pipeDescription(selectedInventoryDetail) }}
          </p>
        </div>

        <!-- Badges / Status Bar -->
        <div class="flex flex-wrap items-center gap-2">
          <span
            class="rounded-md px-2.5 py-1 text-[11px] font-bold font-mono border"
            :class="selectedInventoryDetail.product?.category_code === 'PG' || (selectedInventoryDetail.product?.category || '').includes('Galva') ? 'bg-amber-950/60 text-amber-300 border-amber-800' : 'bg-iron-800 text-iron-300 border-iron-700'"
          >
            {{ selectedInventoryDetail.product?.category || 'Pipa Hitam' }}
          </span>
          <span
            class="rounded-md px-2.5 py-1 text-[11px] font-bold font-mono border"
            :class="isPipeDrat(selectedInventoryDetail) ? 'bg-indigo-950/60 text-indigo-300 border-indigo-800' : 'bg-emerald-950/60 text-emerald-300 border-emerald-800'"
          >
            {{ isPipeDrat(selectedInventoryDetail) ? 'DRAT (Threaded)' : 'PLAIN-END (Tanpa Drat)' }}
          </span>
          <span
            class="rounded-md px-2.5 py-1 text-[11px] font-bold font-mono border"
            :class="selectedInventoryDetail.qc_status === 'PASSED' ? 'bg-emerald-950/60 text-emerald-300 border-emerald-800' : 'bg-rose-950/60 text-rose-300 border-rose-800'"
          >
            QC: {{ selectedInventoryDetail.qc_status }}
          </span>
          <span class="rounded-md px-2.5 py-1 text-[11px] font-bold font-mono bg-blue-950/60 text-blue-300 border border-blue-800">
            {{ selectedInventoryDetail.status || 'AVAILABLE' }}
          </span>
        </div>

        <!-- Spesifikasi Teknis Grid -->
        <div class="space-y-2">
          <h4 class="text-xs font-black uppercase tracking-wider text-iron-400">
            Spesifikasi Produk & Material
          </h4>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Kode SAP / Material</span>
              <div class="mt-0.5 flex items-center justify-between">
                <strong class="font-mono text-xs text-iron-200">{{ selectedInventoryDetail.product?.sap_code || '-' }}</strong>
                <button
                  v-if="selectedInventoryDetail.product?.sap_code"
                  type="button"
                  @click="copySapCode(selectedInventoryDetail.product.sap_code)"
                  class="text-[10px] font-bold text-steel-blue-light hover:underline ml-1"
                >
                  {{ copiedCode ? 'Tersalin!' : 'Salin' }}
                </button>
              </div>
            </div>

            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Ukuran Nominal</span>
              <strong class="mt-0.5 block font-mono text-xs text-iron-200">
                {{ selectedInventoryDetail.product?.nominal_size || '-' }}
              </strong>
            </div>

            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Class / Spesifikasi</span>
              <strong class="mt-0.5 block font-mono text-xs text-iron-200">
                {{ selectedInventoryDetail.product?.spec_name || '-' }}
              </strong>
            </div>

            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Panjang Batang</span>
              <strong class="mt-0.5 block font-mono text-xs text-iron-200">
                {{ selectedInventoryDetail.product?.length_meters || 6.00 }} Meter
              </strong>
            </div>

            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Isi Standar per Bundle</span>
              <strong class="mt-0.5 block font-mono text-xs text-iron-200">
                {{ selectedInventoryDetail.product?.pcs_per_bundle || 0 }} Pcs / Bdl
              </strong>
            </div>

            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Pabrik Asal / Mill</span>
              <strong class="mt-0.5 block font-mono text-xs text-iron-200 truncate" :title="selectedInventoryDetail.mill_source">
                {{ selectedInventoryDetail.mill_source || 'Unit 7 Gresik' }}
              </strong>
            </div>
          </div>
        </div>

        <!-- Posisi & Volume Stok Grid -->
        <div class="space-y-2">
          <h4 class="text-xs font-black uppercase tracking-wider text-iron-400">
            Posisi & Volume Stok
          </h4>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Lokasi Blok & Gudang</span>
              <strong class="mt-0.5 block font-mono text-xs text-cyan-300">
                Blok {{ selectedBlockId }} &middot; {{ currentWarehouseName }}
              </strong>
            </div>

            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Heat Number / Lot</span>
              <strong class="mt-0.5 block font-mono text-xs text-iron-200">
                {{ selectedInventoryDetail.heat_number || '-' }}
              </strong>
            </div>

            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Bundle Tag SIKUTA</span>
              <strong class="mt-0.5 block font-mono text-xs text-iron-200 truncate" :title="selectedInventoryDetail.bundle_tag">
                {{ selectedInventoryDetail.bundle_tag || '-' }}
              </strong>
            </div>

            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Total Bundel</span>
              <strong class="mt-0.5 block font-mono text-xs text-iron-200">
                {{ selectedInventoryDetail.qty_bundles }} Bundle
              </strong>
            </div>

            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Total Batang (Pcs)</span>
              <strong class="mt-0.5 block font-mono text-xs text-iron-200">
                {{ selectedInventoryDetail.qty_pcs }} Pcs
              </strong>
            </div>

            <div class="rounded-lg border border-iron-800 bg-iron-950/40 p-2.5">
              <span class="block text-[10px] text-iron-500">Total Berat</span>
              <strong class="mt-0.5 block font-mono text-xs text-iron-200">
                {{ (Number(selectedInventoryDetail.total_weight_kg || 0) / 1000).toFixed(2) }} Ton
              </strong>
            </div>
          </div>
        </div>

        <!-- Tombol Tutup Footer -->
        <div class="pt-2 border-t border-iron-800 flex justify-end">
          <button
            type="button"
            @click="selectedInventoryDetail = null"
            class="w-full sm:w-auto rounded-lg bg-steel-blue hover:bg-steel-blue-light px-5 py-2.5 text-xs font-bold text-white transition shadow-sm"
          >
            Tutup Rincian
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  zones: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['open-inbound-with-rack', 'open-outbound', 'open-relocate']);

const selectedInventoryDetail = ref(null);
const copiedCode = ref(false);

const copySapCode = (code) => {
  if (!code) return;
  navigator.clipboard.writeText(code).then(() => {
    copiedCode.value = true;
    setTimeout(() => { copiedCode.value = false; }, 2000);
  });
};

const activeWarehouseCode = ref('GUDANG-1');
const selectedBlockId = ref(null);
const selectedBlockData = ref(null);
const searchQuery = ref('');

const leftColumns = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
const rightColumns = ['I', 'J', 'K', 'L'];

const warehouses = [
  { id: 1, code: 'GUDANG-1', name: 'Gudang 1' },
  { id: 2, code: 'GUDANG-2', name: 'Gudang 2' },
  { id: 3, code: 'GUDANG-3', name: 'Gudang 3' },
];

const currentZone = computed(() => {
  return props.zones.find(z => z.code === activeWarehouseCode.value) || props.zones[0] || {};
});

const currentWarehouseName = computed(() => currentZone.value.name || 'Gudang SPINDO');

const currentWarehouseTons = computed(() => {
  if (!currentZone.value.racks) return 0;
  return currentZone.value.racks.reduce((sum, r) => sum + Number(r.current_weight_tons || 0), 0);
});

const currentWarehouseMaxTons = computed(() => {
  if (!currentZone.value.racks) return 1200;
  return currentZone.value.racks.reduce((sum, r) => sum + Number(r.max_weight_tons || 50), 0);
});

const currentWarehouseBundles = computed(() => {
  if (!currentZone.value.racks) return 0;
  return currentZone.value.racks.reduce((sum, r) => sum + (r.inventories_count ?? r.inventories?.length ?? 0), 0);
});

const rackPrefix = computed(() => {
  if (activeWarehouseCode.value === 'GUDANG-1') return 'G1';
  if (activeWarehouseCode.value === 'GUDANG-2') return 'G2';
  return 'G3';
});

// Assigned pipe spec labels from physical floor plan signs
const blockSpecMappings = {
  'GUDANG-3': {
    'A1': 'PIPA KOTAK GI', 'A2': 'EMPTY', 'A3': 'PIPA KOTAK GI',
    'B1': 'PIPA KOTAK GI', 'B2': 'EMPTY', 'B3': 'PIPA KOTAK GI',
    'C1': 'PIPA KOTAK GI', 'C2': 'PIPA KOTAK GI', 'C3': 'PIPA KOTAK GI',
    'D1': 'PIPA KOTAK GI', 'D2': 'PIPA KOTAK GI', 'D3': 'PIPA KOTAK GI',
    'E1': 'PIPA KOTAK GI', 'E2': 'PIPA KOTAK GI', 'E3': '8" SCH/PH',
    'F1': 'EMPTY', 'F2': 'EMPTY', 'F3': '6" SCH/PH',
    'G1': '4" MED/PH', 'G2': '6" MED/PAD', 'G3': '2" MED/PH',
    'H1': '1" MED/PH', 'H2': '3" LGH/PAD', 'H3': '8" SCH/PH',
    'I1': '3/4" SCH/PH', 'I2': 'PRE LOADING', 'I3': '1" MED/PAD',
    'J1': '1-1/2" MED/PAD', 'J2': '8" MED/PAD', 'J3': '5" SCH/PH',
    'K1': '3" MED/PH', 'K2': '4" MED/PH', 'K3': '1/2" MED/PAD',
    'L1': 'EMPTY', 'L2': '8" MED/PH', 'L3': '8" LGH/PH',
  },
  'GUDANG-2': {
    'A1': '1/2" MED/PAD', 'A2': '3/4" MED/PAD', 'A3': '1" MED/PAD',
    'B1': '1-1/4" MED/PAD', 'B2': '1-1/2" MED/PAD', 'B3': '2" MED/PAD',
    'C1': '2-1/2" MED/PAD', 'C2': '3" MED/PAD', 'C3': '4" MED/PAD',
    'D1': 'PIPA KOTAK GI', 'D2': 'PIPA KOTAK GI', 'D3': 'PIPA KOTAK GI',
    'E1': '5" SCH/PH', 'E2': '6" SCH/PH', 'E3': '8" SCH/PH',
    'F1': '1" SCH/PH', 'F2': '2" SCH/PH', 'F3': '3" SCH/PH',
    'G1': '2" MED/PH', 'G2': '3" MED/PH', 'G3': '4" MED/PH',
    'H1': '6" MED/PH', 'H2': '8" MED/PH', 'H3': '10" SCH/PH',
    'I1': '3/4" LGH/PAD', 'I2': 'PRE LOADING', 'I3': '1" LGH/PAD',
    'J1': '1-1/2" LGH/PAD', 'J2': '2" LGH/PAD', 'J3': '3" LGH/PAD',
    'K1': '4" LGH/PAD', 'K2': '6" LGH/PAD', 'K3': '8" LGH/PAD',
    'L1': 'STOCK READY', 'L2': 'STOCK READY', 'L3': 'STOCK READY',
  },
  'GUDANG-1': {
    'A1': 'SCH-40 1"', 'A2': 'SCH-40 1-1/4"', 'A3': 'SCH-40 1-1/2"',
    'B1': 'SCH-40 2"', 'B2': 'SCH-40 2-1/2"', 'B3': 'SCH-40 3"',
    'C1': 'SCH-40 4"', 'C2': 'SCH-40 5"', 'C3': 'SCH-40 6"',
    'D1': 'MEDIUM 1"', 'D2': 'MEDIUM 1-1/4"', 'D3': 'MEDIUM 1-1/2"',
    'E1': 'MEDIUM 2"', 'E2': 'MEDIUM 2-1/2"', 'E3': 'MEDIUM 3"',
    'F1': 'MEDIUM 4"', 'F2': 'MEDIUM 6"', 'F3': 'MEDIUM 8"',
    'G1': 'LGH/PH 1"', 'G2': 'LGH/PH 2"', 'G3': 'LGH/PH 3"',
    'H1': 'LGH/PH 4"', 'H2': 'LGH/PH 6"', 'H3': 'LGH/PH 8"',
    'I1': 'PRE LOADING A', 'I2': 'PRE LOADING B', 'I3': 'PRE LOADING C',
    'J1': 'DISPATCH BAY 1', 'J2': 'DISPATCH BAY 2', 'J3': 'DISPATCH BAY 3',
    'K1': 'QC HOLD BAY 1', 'K2': 'QC HOLD BAY 2', 'K3': 'QC HOLD BAY 3',
    'L1': 'TEMPAT CADDY', 'L2': 'TANGGA MUAT', 'L3': 'AREA BUFFER',
  }
};

function getRackObject(blockId) {
  const targetCode = rackPrefix.value + '-' + blockId;
  return (currentZone.value.racks || []).find(r => r.rack_code === targetCode);
}

function getBlockTons(blockId) {
  const rack = getRackObject(blockId);
  return rack ? Number(rack.current_weight_tons || 0).toFixed(1) : '0.0';
}

function getBlockBundlesCount(blockId) {
  const rack = getRackObject(blockId);
  return rack ? (rack.inventories_count ?? rack.inventories?.length ?? 0) : 0;
}

function getBlockPercent(blockId) {
  const rack = getRackObject(blockId);
  if (!rack) return 0;
  return Math.min(100, (rack.current_weight_tons / (rack.max_weight_tons || 50)) * 100);
}

function getBlockPipeType(blockId) {
  return blockSpecMappings[activeWarehouseCode.value]?.[blockId] || 'BAY PIPA';
}

function getBlockDotStatus(blockId) {
  const percent = getBlockPercent(blockId);
  if (percent >= 90) return 'bg-spindo-red';
  if (percent > 0) return 'bg-emerald-500';
  return 'bg-iron-700';
}

function getBlockBarColor(blockId) {
  const percent = getBlockPercent(blockId);
  if (percent >= 90) return 'bg-spindo-red';
  if (percent >= 50) return 'bg-amber-500';
  return 'bg-steel-blue-light';
}

function isBlockMatchingSearch(blockId) {
  if (!searchQuery.value.trim()) return false;
  const q = searchQuery.value.toLowerCase();
  const specText = getBlockPipeType(blockId).toLowerCase();
  const codeText = blockId.toLowerCase();
  return specText.includes(q) || codeText.includes(q);
}

function getBlockCardClasses(blockId) {
  const isSelected = selectedBlockId.value === blockId;
  const isMatch = isBlockMatchingSearch(blockId);

  return [
    'p-2 rounded bg-iron-900 border transition-all cursor-pointer space-y-1 hover:bg-iron-800',
    isSelected ? 'border-steel-blue ring-2 ring-steel-blue bg-iron-850' : isMatch ? 'border-amber-400 ring-2 ring-amber-400/50 bg-amber-500/10' : 'border-iron-800'
  ];
}

const selectedBlockIdentity = computed(() => {
  if (!selectedBlockId.value) return '';
  return getBlockPipeType(selectedBlockId.value);
});

const selectedBlockTons = computed(() => {
  if (!selectedBlockId.value) return 0;
  const rack = getRackObject(selectedBlockId.value);
  return rack ? Number(rack.current_weight_tons || 0) : 0;
});

const selectedBlockInventories = computed(() => {
  if (!selectedBlockId.value) return [];
  const rack = getRackObject(selectedBlockId.value);
  return rack?.inventories || [];
});

function selectBlock(blockId) {
  selectedBlockId.value = blockId;
  const rack = getRackObject(blockId);
  selectedBlockData.value = rack || { rack_code: rackPrefix.value + '-' + blockId };
}

function triggerInboundToBlock() {
  const rack = getRackObject(selectedBlockId.value);
  selectedBlockData.value = null;
  emit('open-inbound-with-rack', rack?.id);
}

function triggerOutboundFromBlock() {
  selectedBlockData.value = null;
  emit('open-outbound');
}

function triggerRelocateFromBlock() {
  selectedBlockData.value = null;
  emit('open-relocate');
}

const isPipeDrat = (item) => {
  const p = item.product;
  if (p?.is_threaded) return true;
  const desc = (item.description || p?.description || '').toUpperCase();
  if (desc.includes('THRD') || desc.includes('THREAD') || desc.includes('DRAT')) {
    if (!desc.includes('NON-DRAT') && !desc.includes('NON DRAT')) return true;
  }
  const code = (p?.sap_code || '').toUpperCase();
  if (/^[GH][12]B10/i.test(code)) return true;
  const jenis = (p?.jenis || '').toUpperCase();
  if (jenis.includes('DRAT') && !jenis.includes('NON-DRAT') && !jenis.includes('NON DRAT')) return true;
  return false;
};

const pipeEasyName = (item) => {
  const p = item.product;
  if (!p) return item.bundle_tag;

  const isDrat = isPipeDrat(item);

  if (p.nama_mudah) {
    let name = p.nama_mudah.replace(/\bNON[-\s]?DRAT\b/gi, '').replace(/\s+/g, ' ').trim();
    if (isDrat && !name.toUpperCase().includes('DRAT')) {
      name = name.replace(/^(PIPA\s+(?:GALVA|HITAM|GALVANIS))/i, '$1 DRAT');
    }
    return name;
  }

  let jenis = p.jenis || (p.category?.toUpperCase().includes('GALVA') ? 'PIPA GALVA' : 'PIPA HITAM');
  jenis = jenis.replace(/\bNON[-\s]?DRAT\b/gi, '').replace(/\s+/g, ' ').trim();

  if (isDrat && !jenis.toUpperCase().includes('DRAT')) {
    jenis += ' DRAT';
  }

  return [jenis, p.nominal_size, p.spec_name, p.sap_code].filter(Boolean).join(' ');
};

const pipeDescription = (item) => {
  if (item.description) return item.description;
  if (item.product?.description) return item.product.description;
  const p = item.product;
  if (!p) return '-';
  return [p.sap_code, p.nominal_size, p.spec_name].filter(Boolean).join(' · ');
};
</script>
