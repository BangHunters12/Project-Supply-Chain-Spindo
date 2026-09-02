<template>
  <div :data-theme="darkMode ? 'dark' : 'light'" :class="darkMode ? 'bg-iron-950 text-iron-300' : 'bg-wms-bg text-wms-ink'" class="min-h-screen font-display antialiased transition-colors">
    
    <Navbar
      :dark-mode="darkMode"
      :sync-info="syncInfo"
      :syncing="syncing"
      @refresh="loadMapData"
      @toggle-theme="toggleTheme"
      @sync-sikuta="handleSyncSikuta"
    />

    <!-- Toast notification -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="translate-y-2 opacity-0"
      enter-to-class="translate-y-0 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="translate-y-0 opacity-100"
      leave-to-class="translate-y-2 opacity-0"
    >
      <div
        v-if="toastMessage"
        class="fixed bottom-5 right-5 z-50 max-w-[min(24rem,calc(100vw-2rem))] border-l-4 border-wms-blue bg-wms-navy px-4 py-3 text-xs font-medium text-white shadow-md"
      >
        {{ toastMessage }}
      </div>
    </Transition>

    <!-- Sync Detail Modal -->
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="syncDetail" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm" @click.self="syncDetail = null">
        <div class="relative w-full max-w-lg mx-4 border border-iron-700 bg-iron-900 text-iron-200 shadow-2xl">
          <!-- Header -->
          <div class="flex items-center justify-between border-b border-iron-700 px-5 py-3">
            <h3 class="font-display text-sm font-bold tracking-wide text-white">
              {{ syncDetail.success ? '✅ LAPORAN SINKRONISASI' : '⚠️ SINKRONISASI' }}
            </h3>
            <button @click="syncDetail = null" class="text-iron-400 hover:text-white transition">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          <!-- Content -->
          <div class="px-5 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
            <!-- Sync Counts -->
            <div>
              <p class="text-[10px] font-mono uppercase tracking-widest text-iron-500 mb-2">Data yang Disinkronkan</p>
              <div class="grid grid-cols-2 gap-2">
                <div class="border border-iron-700/50 bg-iron-800/50 px-3 py-2">
                  <span class="text-[10px] text-iron-500">Gudang</span>
                  <p class="text-lg font-bold text-emerald-400">{{ syncDetail.d?.gudang_synced ?? 0 }}</p>
                </div>
                <div class="border border-iron-700/50 bg-iron-800/50 px-3 py-2">
                  <span class="text-[10px] text-iron-500">Blok</span>
                  <p class="text-lg font-bold text-emerald-400">{{ syncDetail.d?.blok_synced ?? 0 }}</p>
                </div>
                <div class="border border-iron-700/50 bg-iron-800/50 px-3 py-2">
                  <span class="text-[10px] text-iron-500">Produk/Material</span>
                  <p class="text-lg font-bold text-sky-400">{{ syncDetail.d?.produk_synced ?? 0 }}</p>
                </div>
                <div class="border border-iron-700/50 bg-iron-800/50 px-3 py-2">
                  <span class="text-[10px] text-iron-500">Status Stok</span>
                  <p class="text-lg font-bold text-sky-400">{{ syncDetail.d?.stok_synced ?? 0 }}</p>
                </div>
              </div>
            </div>

            <!-- Database Stats -->
            <div v-if="syncDetail.d?.total_stok_pcs > 0">
              <p class="text-[10px] font-mono uppercase tracking-widest text-iron-500 mb-2">Kondisi Database Sekarang</p>
              <div class="grid grid-cols-2 gap-2">
                <div class="border border-iron-700/50 bg-iron-800/50 px-3 py-2">
                  <span class="text-[10px] text-iron-500">Blok Terisi / Total</span>
                  <p class="text-base font-bold text-amber-400">{{ syncDetail.d.total_blok_terisi }} / {{ syncDetail.d.total_blok }}</p>
                </div>
                <div class="border border-iron-700/50 bg-iron-800/50 px-3 py-2">
                  <span class="text-[10px] text-iron-500">Jenis Material</span>
                  <p class="text-base font-bold text-amber-400">{{ syncDetail.d.total_jenis_material }}</p>
                </div>
                <div class="border border-iron-700/50 bg-iron-800/50 px-3 py-2">
                  <span class="text-[10px] text-iron-500">Total Stok (pcs)</span>
                  <p class="text-base font-bold text-white">{{ Number(syncDetail.d.total_stok_pcs).toLocaleString('id-ID') }}</p>
                </div>
                <div class="border border-iron-700/50 bg-iron-800/50 px-3 py-2">
                  <span class="text-[10px] text-iron-500">Total Tonase</span>
                  <p class="text-base font-bold text-white">{{ Number(syncDetail.d.total_tonase_ton).toLocaleString('id-ID', {minimumFractionDigits:2}) }} ton</p>
                </div>
              </div>
            </div>

            <!-- Top Materials -->
            <div v-if="syncDetail.d?.top_materials?.length > 0">
              <p class="text-[10px] font-mono uppercase tracking-widest text-iron-500 mb-2">Top 5 Material (Stok Terbanyak)</p>
              <div class="border border-iron-700/50 overflow-hidden">
                <table class="w-full text-xs">
                  <thead>
                    <tr class="bg-iron-800 text-iron-400">
                      <th class="px-3 py-1.5 text-left font-mono">#</th>
                      <th class="px-3 py-1.5 text-left font-mono">Kode Material</th>
                      <th class="px-3 py-1.5 text-right font-mono">Pcs</th>
                      <th class="px-3 py-1.5 text-right font-mono">Ton</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(mat, i) in syncDetail.d.top_materials" :key="i" class="border-t border-iron-800">
                      <td class="px-3 py-1.5 text-iron-500">{{ i + 1 }}</td>
                      <td class="px-3 py-1.5 font-mono text-iron-300 text-[10px]">{{ mat.material }}</td>
                      <td class="px-3 py-1.5 text-right font-mono text-emerald-400">{{ Number(mat.pcs).toLocaleString('id-ID') }}</td>
                      <td class="px-3 py-1.5 text-right font-mono text-sky-400">{{ mat.ton }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Warning if no data -->
            <div v-if="!syncDetail.success" class="bg-amber-900/30 border border-amber-700/50 px-3 py-2 text-xs text-amber-300">
              ⚠️ {{ syncDetail.message }}
            </div>
          </div>

          <!-- Footer -->
          <div class="border-t border-iron-700 px-5 py-3 flex justify-end">
            <button @click="syncDetail = null" class="bg-wms-blue px-4 py-1.5 text-xs font-semibold text-white hover:bg-blue-600 transition">
              Tutup
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <main class="mx-auto max-w-screen-2xl px-4 py-5 sm:px-6 sm:py-7">
      <div>
        <BlueprintWarehouseMap ref="warehouseMap" />
      </div>
    </main>

    <footer class="mt-12 border-t border-wms-border py-5 text-center text-[11px] font-mono text-wms-muted dark:border-iron-800 dark:text-iron-600">
      PT Steel Pipe Industry of Indonesia Tbk (SPINDO) &mdash; WMS SC-U7 &copy; 2026
    </footer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Navbar from './components/Navbar.vue';
import BlueprintWarehouseMap from './components/BlueprintWarehouseMap.vue';

const toastMessage = ref('');
const darkMode = ref(false);
const syncInfo = ref(null);
const syncing = ref(false);
const warehouseMap = ref(null);
const syncDetail = ref(null);

function toggleTheme() {
  darkMode.value = !darkMode.value;
  localStorage.setItem('wms-theme', darkMode.value ? 'dark' : 'light');
}

function showToast(msg) {
  toastMessage.value = msg;
  setTimeout(() => { toastMessage.value = ''; }, 3500);
}

async function loadMapData() {
  if (warehouseMap.value) {
    await warehouseMap.value.loadMap();
  }
}

async function loadSyncStatus() {
  try {
    const res = await fetch('/api/wms/sync-status');
    const json = await res.json();
    if (json.status === 'success') syncInfo.value = json.data;
  } catch (err) { console.error('Sync status error:', err); }
}

async function handleSyncSikuta() {
  syncing.value = true;
  showToast('⏳ Sinkronisasi SIKUTA dimulai... mohon tunggu.');
  try {
    const res = await fetch('/api/wms/sync', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ table: 'all' }) });
    const json = await res.json();
    if (res.ok && json.status === 'success') {
      syncDetail.value = { success: true, message: json.message, d: json.data?.detail || {} };
      await loadMapData();
      await loadSyncStatus();
    } else if (json.status === 'warning') {
      syncDetail.value = { success: false, message: json.message, d: json.data?.detail || {} };
    } else {
      showToast('⚠️ ' + (json.message || 'Gagal sync'));
    }
  } catch {
    showToast('❌ Koneksi server bermasalah.');
  } finally {
    syncing.value = false;
  }
}

onMounted(() => {
  darkMode.value = localStorage.getItem('wms-theme') === 'dark';
  loadSyncStatus();
});
</script>
