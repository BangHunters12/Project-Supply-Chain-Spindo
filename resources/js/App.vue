<template>
  <div :data-theme="darkMode ? 'dark' : 'light'" :class="darkMode ? 'bg-iron-950 text-iron-300' : 'bg-slate-50 text-slate-800'" class="min-h-screen font-display antialiased transition-colors">
    
    <Navbar
      :active-tab="currentTab"
      :dark-mode="darkMode"
      @change-tab="setTab"
      @refresh="loadAllData"
      @toggle-theme="toggleTheme"
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
      <div v-if="currentTab === 'map'">
        <BlueprintWarehouseMap
          @open-inbound-with-rack="handleOpenInboundWithRack"
          @open-outbound="currentTab = 'outbound'"
          @open-relocate="currentTab = 'inventory'"
        />
      </div>

      <!-- ======================= DASHBOARD ANALITIK ======================= -->
      <div v-if="currentTab === 'dashboard'" class="space-y-6">

        <!-- Signature Element: Tonnage Capacity Gauge -->
        <section class="bg-iron-900 rounded-lg border border-iron-800 p-5">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
            <div>
              <h1 class="text-base font-bold text-iron-200 tracking-tight">
                Kapasitas Gudang SC-U7
              </h1>
              <p class="text-xs font-mono text-iron-400 mt-0.5">
                {{ m.used_rack_tons ?? 0 }} Ton terpakai dari {{ m.total_rack_capacity_tons ?? 0 }} Ton kapasitas total rak
              </p>
            </div>
            <div class="flex items-center space-x-6 text-right">
              <div>
                <div class="text-2xl font-mono font-bold text-iron-200 tabular-nums">{{ m.rack_occupancy_percent ?? 0 }}<span class="text-sm text-iron-400">%</span></div>
                <div class="text-[10px] font-mono text-iron-400 uppercase tracking-widest">Okupansi</div>
              </div>
              <div class="h-10 w-px bg-iron-800"></div>
              <div>
                <div class="text-2xl font-mono font-bold text-iron-200 tabular-nums">{{ m.total_stock_tons ?? 0 }}<span class="text-sm text-iron-400 ml-0.5">T</span></div>
                <div class="text-[10px] font-mono text-iron-400 uppercase tracking-widest">Total Stok</div>
              </div>
              <div class="h-10 w-px bg-iron-800"></div>
              <div>
                <div class="text-2xl font-mono font-bold text-iron-200 tabular-nums">{{ m.total_bundles ?? 0 }}</div>
                <div class="text-[10px] font-mono text-iron-400 uppercase tracking-widest">Bundle</div>
              </div>
            </div>
          </div>

          <!-- Segmented capacity bar -->
          <div class="w-full bg-iron-800 h-3 rounded-sm overflow-hidden flex">
            <div
              v-for="zone in dashboardData.zones || []"
              :key="zone.id"
              class="h-full transition-all duration-500 relative group"
              :class="zoneBarColor(zone.code)"
              :style="{ width: getZoneBarWidth(zone) + '%' }"
            >
              <div class="absolute -top-7 left-1/2 -translate-x-1/2 bg-iron-950 border border-iron-700 rounded px-1.5 py-0.5 text-[9px] font-mono text-iron-300 whitespace-nowrap opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                {{ zone.code }}: {{ getZoneTotalTons(zone).toFixed(1) }}T
              </div>
            </div>
          </div>
          <!-- Zone legend -->
          <div class="flex flex-wrap gap-x-4 gap-y-1 mt-3">
            <div v-for="zone in dashboardData.zones || []" :key="zone.id" class="flex items-center space-x-1.5 text-[10px] font-mono text-iron-400">
              <span class="w-2.5 h-2.5 rounded-sm" :class="zoneBarColor(zone.code)"></span>
              <span>{{ zone.code }} &mdash; {{ zone.category }}</span>
            </div>
          </div>
        </section>

        <!-- Secondary metrics row: asymmetric, NOT identical cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <div class="bg-iron-900 rounded-lg border border-iron-800 px-4 py-3">
            <div class="text-[10px] font-mono text-iron-400 uppercase tracking-widest">Total Pcs</div>
            <div class="text-lg font-mono font-bold text-iron-200 mt-1 tabular-nums">{{ m.total_pcs ?? 0 }}</div>
          </div>
          <div class="bg-iron-900 rounded-lg border border-iron-800 px-4 py-3">
            <div class="text-[10px] font-mono text-iron-400 uppercase tracking-widest">Inbound Hari Ini</div>
            <div class="text-lg font-mono font-bold text-iron-200 mt-1 tabular-nums">{{ m.today_inbound_tons ?? 0 }} <span class="text-xs text-iron-400">T</span></div>
          </div>
          <div class="bg-iron-900 rounded-lg border border-iron-800 px-4 py-3">
            <div class="text-[10px] font-mono text-iron-400 uppercase tracking-widest">QC Pending</div>
            <div class="text-lg font-mono font-bold tabular-nums mt-1" :class="(m.qc_pending_count ?? 0) > 0 ? 'text-amber-500' : 'text-iron-200'">{{ m.qc_pending_count ?? 0 }}</div>
          </div>
          <div class="bg-iron-900 rounded-lg border border-iron-800 px-4 py-3">
            <div class="text-[10px] font-mono text-iron-400 uppercase tracking-widest">Pengiriman Aktif</div>
            <div class="text-lg font-mono font-bold text-iron-200 mt-1 tabular-nums">{{ m.active_shipments ?? 0 }}</div>
          </div>
        </div>

        <!-- Two column: Zone overview + Activity log -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
          <!-- Zone / Rack visual (wider) -->
          <div class="lg:col-span-3">
            <WarehouseMap :zones="dashboardData.zones || []" />
          </div>

          <!-- Activity timeline (narrower) -->
          <div class="lg:col-span-2 bg-iron-900 rounded-lg border border-iron-800 overflow-hidden">
            <div class="px-4 py-3 border-b border-iron-800">
              <h3 class="text-xs font-display font-bold text-iron-200 uppercase tracking-wider">Riwayat Pergerakan</h3>
            </div>
            <div class="divide-y divide-iron-800/50 max-h-[480px] overflow-y-auto">
              <div
                v-for="mov in dashboardData.recent_movements || []"
                :key="mov.id"
                class="px-4 py-3 text-xs"
              >
                <div class="flex items-center justify-between mb-1">
                  <span class="font-mono text-[11px] font-bold text-iron-200">{{ mov.movement_code }}</span>
                  <span class="font-mono text-[10px]" :class="movTypeColor(mov.movement_type)">{{ mov.movement_type }}</span>
                </div>
                <div class="font-display text-iron-300 text-[11px]">{{ mov.inventory?.product?.sap_code + ' ' + mov.inventory?.product?.nominal_size + '"' }}</div>
                <div class="font-mono text-[10px] text-iron-400 mt-0.5">
                  {{ mov.qty_pcs }} pcs &middot; {{ (mov.total_weight_kg / 1000).toFixed(2) }}T &middot; {{ mov.operator_name }}
                </div>
              </div>
              <div v-if="!dashboardData.recent_movements?.length" class="px-4 py-8 text-center text-iron-600 text-xs font-display">
                Belum ada log pergerakan.
              </div>
            </div>
          </div>
        </div>

        <!-- Quick action links (not big hero buttons) -->
        <div class="flex items-center space-x-3 text-xs font-display">
          <button @click="currentTab = 'inbound'" class="px-3 py-1.5 rounded bg-steel-blue hover:bg-steel-blue-light text-white font-semibold transition">Input Penerimaan Mill</button>
          <button @click="currentTab = 'outbound'" class="px-3 py-1.5 rounded bg-iron-800 hover:bg-iron-700 text-iron-300 font-medium transition">Buat Surat Jalan</button>
          <button @click="currentTab = 'inventory'" class="px-3 py-1.5 rounded bg-iron-800 hover:bg-iron-700 text-iron-300 font-medium transition">Lihat Seluruh Stok</button>
        </div>
      </div>

      <!-- ======================= INVENTORY ======================= -->
      <div v-if="currentTab === 'inventory'">
        <InventoryList
          :inventories="inventories"
          :racks="masterData.racks || []"
          @open-inbound-modal="currentTab = 'inbound'"
          @update-qc="handleUpdateQc"
          @relocate-bundle="handleRelocate"
        />
      </div>

      <!-- ======================= INBOUND ======================= -->
      <div v-if="currentTab === 'inbound'">
        <InboundForm
          :products="masterData.products || []"
          :racks="masterData.racks || []"
          :preselected-rack-id="selectedRackForInbound"
          @submit-inbound="handleInboundSubmit"
          @cancel="currentTab = 'inventory'"
        />
      </div>

      <!-- ======================= OUTBOUND ======================= -->
      <div v-if="currentTab === 'outbound'">
        <OutboundForm
          :shipments="shipments"
          :inventories="inventories"
          @submit-outbound="handleOutboundSubmit"
        />
      </div>
    </main>

    <footer class="mt-16 border-t border-iron-800 py-4 text-center text-[11px] font-mono text-iron-600">
      PT Steel Pipe Industry of Indonesia Tbk (SPINDO) &mdash; WMS SC-U7 &copy; 2026
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import Navbar from './components/Navbar.vue';
import BlueprintWarehouseMap from './components/BlueprintWarehouseMap.vue';
import WarehouseMap from './components/WarehouseMap.vue';
import InventoryList from './components/InventoryList.vue';
import InboundForm from './components/InboundForm.vue';
import OutboundForm from './components/OutboundForm.vue';

const currentTab = ref('map');
const dashboardData = ref({});
const inventories = ref([]);
const masterData = ref({});
const shipments = ref([]);
const toastMessage = ref('');
const selectedRackForInbound = ref(null);
const darkMode = ref(false);

const m = computed(() => dashboardData.value.metrics || {});

function setTab(tab) { currentTab.value = tab; }

function toggleTheme() {
  darkMode.value = !darkMode.value;
  localStorage.setItem('wms-theme', darkMode.value ? 'dark' : 'light');
}

function handleOpenInboundWithRack(rackId) {
  selectedRackForInbound.value = rackId;
  currentTab.value = 'inbound';
}

function showToast(msg) {
  toastMessage.value = msg;
  setTimeout(() => { toastMessage.value = ''; }, 3500);
}

// Zone capacity bar helpers
function getZoneTotalTons(zone) {
  if (!zone.racks) return 0;
  return zone.racks.reduce((sum, r) => sum + Number(r.current_weight_tons || 0), 0);
}

function getZoneBarWidth(zone) {
  const totalCap = m.value.total_rack_capacity_tons || 1;
  const zoneCap = (zone.racks || []).reduce((s, r) => s + Number(r.max_weight_tons || 0), 0);
  return (zoneCap / totalCap) * 100;
}

function zoneBarColor(code) {
  const colors = {
    'ZONE-A': 'bg-steel-blue-light',
    'ZONE-B': 'bg-amber-500',
    'ZONE-C': 'bg-emerald-500',
    'ZONE-D': 'bg-purple-500',
    'ZONE-E': 'bg-spindo-red',
  };
  return colors[code] || 'bg-iron-600';
}

function movTypeColor(type) {
  if (type === 'INBOUND') return 'text-emerald-500';
  if (type === 'OUTBOUND') return 'text-amber-500';
  return 'text-steel-blue-light';
}

// Data loaders
async function loadDashboard() {
  try {
    const res = await fetch('/api/wms/dashboard');
    const json = await res.json();
    if (json.status === 'success') dashboardData.value = json.data;
  } catch (err) { console.error('Dashboard fetch error:', err); }
}

async function loadInventories() {
  try {
    const res = await fetch('/api/wms/inventories');
    const json = await res.json();
    if (json.status === 'success') inventories.value = json.data;
  } catch (err) { console.error('Inventories fetch error:', err); }
}

async function loadMasterData() {
  try {
    const res = await fetch('/api/wms/master-data');
    const json = await res.json();
    if (json.status === 'success') masterData.value = json.data;
  } catch (err) { console.error('Master data fetch error:', err); }
}

async function loadShipments() {
  try {
    const res = await fetch('/api/wms/shipments');
    const json = await res.json();
    if (json.status === 'success') shipments.value = json.data;
  } catch (err) { console.error('Shipments fetch error:', err); }
}

async function loadAllData() {
  await Promise.all([loadDashboard(), loadInventories(), loadMasterData(), loadShipments()]);
}

// Event handlers
async function handleInboundSubmit(formData) {
  try {
    const res = await fetch('/api/wms/inbound', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(formData) });
    const json = await res.json();
    if (res.ok && json.status === 'success') { showToast(json.message); await loadAllData(); currentTab.value = 'inventory'; }
    else { alert(json.message || 'Gagal menyimpan inbound.'); }
  } catch { alert('Koneksi server bermasalah.'); }
}

async function handleUpdateQc({ id, qc_status }) {
  try {
    const res = await fetch(`/api/wms/inventories/${id}/qc`, { method: 'PATCH', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ qc_status }) });
    const json = await res.json();
    if (res.ok && json.status === 'success') { showToast(json.message); await loadAllData(); }
  } catch { alert('Gagal memperbarui status QC.'); }
}

async function handleRelocate(payload) {
  try {
    const res = await fetch('/api/wms/relocate', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(payload) });
    const json = await res.json();
    if (res.ok && json.status === 'success') { showToast(json.message); await loadAllData(); }
    else { alert(json.message || 'Gagal merelokasi.'); }
  } catch { alert('Koneksi server bermasalah.'); }
}

async function handleOutboundSubmit(formData) {
  try {
    const res = await fetch('/api/wms/outbound', { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify(formData) });
    const json = await res.json();
    if (res.ok && json.status === 'success') { showToast(json.message); await loadAllData(); currentTab.value = 'outbound'; }
    else { alert(json.message || 'Gagal membuat surat jalan.'); }
  } catch { alert('Koneksi server bermasalah.'); }
}

onMounted(() => {
  darkMode.value = localStorage.getItem('wms-theme') === 'dark';
  loadAllData();
});
</script>
