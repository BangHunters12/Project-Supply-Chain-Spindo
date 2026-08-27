<template>
  <div :data-theme="darkMode ? 'dark' : 'light'" :class="darkMode ? 'bg-iron-950 text-iron-300' : 'bg-slate-50 text-slate-800'" class="min-h-screen font-display antialiased transition-colors">
    
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
        class="fixed bottom-5 right-5 z-50 px-4 py-2.5 rounded bg-iron-900 border border-iron-700 text-xs font-mono text-iron-200 shadow-lg"
      >
        {{ toastMessage }}
      </div>
    </Transition>

    <main class="max-w-screen-2xl mx-auto px-4 sm:px-6 py-6">

      <!-- ======================= DENAH & MAPPING GUDANG LANDING PAGE ======================= -->
      <div>
        <BlueprintWarehouseMap
          ref="warehouseMap"
        />
      </div>

    </main>

    <footer class="mt-16 border-t border-iron-800 py-4 text-center text-[11px] font-mono text-iron-600">
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

function toggleTheme() {
  darkMode.value = !darkMode.value;
  localStorage.setItem('wms-theme', darkMode.value ? 'dark' : 'light');
}

function showToast(msg) {
  toastMessage.value = msg;
  setTimeout(() => { toastMessage.value = ''; }, 3500);
}

// Data loaders
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
  showToast('Sinkronisasi SIKUTA dimulai...');
  try {
    const res = await fetch('/api/wms/sync', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ table: 'all' }) });
    const json = await res.json();
    if (res.ok && json.status === 'success') {
      showToast('✅ Data SIKUTA berhasil disinkronkan!');
      await loadMapData();
      await loadSyncStatus();
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
