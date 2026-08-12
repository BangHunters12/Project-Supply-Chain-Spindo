<template>
  <div class="space-y-5">
    <!-- Header with filters -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-sm font-display font-bold text-iron-200 uppercase tracking-wider">Inventaris Bundle Pipa</h2>
        <p class="text-xs text-iron-400 font-mono">Ledger stok, spesifikasi teknis, heat number, dan status QC</p>
      </div>
      <button
        @click="$emit('open-inbound-modal')"
        class="px-3.5 py-2 rounded bg-steel-blue hover:bg-steel-blue-light text-white text-xs font-display font-semibold transition"
      >Terima Inbound Baru</button>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
      <input
        v-model="searchQuery" type="text" placeholder="Cari tag, heat no, spesifikasi..."
        class="bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 placeholder-iron-600 focus:outline-none focus:border-iron-600"
      />
      <select v-model="selectedType" class="bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 focus:outline-none focus:border-iron-600">
        <option value="">Semua Jenis</option>
        <option value="PH">Pipa Hitam</option>
        <option value="PG">Pipa Galvanis</option>
      </select>
      <select v-model="selectedQc" class="bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 focus:outline-none focus:border-iron-600">
        <option value="">Semua QC</option>
        <option value="PASSED">PASSED</option>
        <option value="PENDING">PENDING</option>
        <option value="REJECTED">REJECTED</option>
      </select>
      <button @click="resetFilters" class="px-3 py-2 rounded bg-iron-800 hover:bg-iron-700 text-iron-300 text-xs font-display font-medium transition">Reset</button>
    </div>

    <!-- Data Table -->
    <div class="bg-iron-900 rounded-lg border border-iron-800 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-iron-950 border-b border-iron-800">
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">Bundle Tag</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">Spesifikasi</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">Rak</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">Heat No</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">Qty / Berat</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">QC</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider text-right">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-iron-800/60">
            <tr v-for="item in filteredInventories" :key="item.id" class="hover:bg-iron-950/50 transition-colors">
              <td class="py-2.5 px-3">
                <div class="font-mono text-xs font-bold text-steel-blue-light">{{ item.bundle_tag }}</div>
                <div class="font-mono text-[10px] text-iron-600">{{ item.inbound_date }}</div>
              </td>
              <td class="py-2.5 px-3">
                <div class="text-xs font-display font-semibold text-iron-200">{{ item.product?.sap_code + ' ' + item.product?.nominal_size + '"' }}</div>
                <div class="font-mono text-[10px] text-iron-400">{{ item.product?.outer_diameter_mm + 'mm OD' }} | {{ item.product?.wall_thickness_min + '-' + item.product?.wall_thickness_max + 'mm' }} | {{ item.product?.spec_name }}</div>
              </td>
              <td class="py-2.5 px-3">
                <span class="font-mono text-xs font-bold text-amber-500">{{ item.rack?.rack_code || '—' }}</span>
                <div class="font-mono text-[10px] text-iron-600">{{ item.rack?.zone?.code }}</div>
              </td>
              <td class="py-2.5 px-3 font-mono text-xs text-iron-300">{{ item.heat_number }}</td>
              <td class="py-2.5 px-3">
                <div class="font-mono text-xs font-semibold text-iron-200">{{ item.qty_pcs }} pcs</div>
                <div class="font-mono text-[10px] text-iron-400">{{ (item.total_weight_kg / 1000).toFixed(2) }} T</div>
              </td>
              <td class="py-2.5 px-3">
                <span class="inline-block w-2 h-2 rounded-full mr-1.5" :class="item.qc_status === 'PASSED' ? 'bg-emerald-500' : item.qc_status === 'REJECTED' ? 'bg-spindo-red' : 'bg-amber-500'"></span>
                <span class="font-mono text-[11px] text-iron-300">{{ item.qc_status }}</span>
              </td>
              <td class="py-2.5 px-3 text-right space-x-1">
                <button @click="openRelocateModal(item)" class="px-2 py-1 rounded bg-iron-800 hover:bg-iron-700 text-iron-300 text-[11px] font-display font-medium transition">Relokasi</button>
                <button v-if="item.qc_status !== 'PASSED'" @click="updateQc(item.id, 'PASSED')" class="px-2 py-1 rounded bg-emerald-950 text-emerald-400 hover:bg-emerald-900 text-[11px] font-display font-medium transition">Pass QC</button>
              </td>
            </tr>
            <tr v-if="filteredInventories.length === 0">
              <td colspan="7" class="py-8 text-center text-iron-600 text-xs font-display">Tidak ada data sesuai filter.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Relocate Modal -->
    <div v-if="relocateTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-iron-950/80 backdrop-blur-sm">
      <div class="bg-iron-900 border border-iron-800 rounded-lg max-w-md w-full p-5 space-y-4 relative">
        <button @click="relocateTarget = null" class="absolute top-3 right-3 text-iron-400 hover:text-iron-200 text-xs font-mono">ESC</button>
        <h3 class="text-sm font-display font-bold text-iron-200 uppercase tracking-wide">Relokasi Rak</h3>
        <div class="bg-iron-950 p-3 rounded border border-iron-800 font-mono text-xs space-y-0.5">
          <div class="text-steel-blue-light font-bold">{{ relocateTarget.bundle_tag }}</div>
          <div class="text-iron-300">{{ relocateTarget.product?.sap_code + ' ' + relocateTarget.product?.nominal_size + '"' }}</div>
          <div class="text-iron-400">Saat ini: <span class="text-amber-500 font-bold">{{ relocateTarget.rack?.rack_code }}</span></div>
        </div>
        <div class="space-y-3">
          <div>
            <label class="block text-[11px] font-display font-semibold text-iron-400 mb-1">Rak Tujuan</label>
            <select v-model="newRackId" class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 focus:outline-none focus:border-iron-600">
              <option value="">-- Pilih rak --</option>
              <option v-for="r in availableRacks" :key="r.id" :value="r.id" :disabled="r.id === relocateTarget.warehouse_rack_id">{{ r.rack_code }} ({{ r.zone?.code }}) — {{ r.current_weight_tons }}/{{ r.max_weight_tons }}T</option>
            </select>
          </div>
          <div>
            <label class="block text-[11px] font-display font-semibold text-iron-400 mb-1">Catatan</label>
            <input v-model="relocateNotes" type="text" placeholder="Alasan pemindahan..." class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs text-iron-200 focus:outline-none focus:border-iron-600" />
          </div>
        </div>
        <div class="flex justify-end space-x-2 pt-2 border-t border-iron-800">
          <button @click="relocateTarget = null" class="px-3 py-1.5 bg-iron-800 hover:bg-iron-700 text-iron-300 text-xs font-display font-medium rounded transition">Batal</button>
          <button @click="submitRelocate" :disabled="!newRackId" class="px-3 py-1.5 bg-steel-blue hover:bg-steel-blue-light disabled:opacity-40 text-white text-xs font-display font-bold rounded transition">Simpan</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  inventories: { type: Array, default: () => [] },
  racks: { type: Array, default: () => [] }
});

const emit = defineEmits(['open-inbound-modal', 'update-qc', 'relocate-bundle']);

const searchQuery = ref('');
const selectedType = ref('');
const selectedQc = ref('');
const relocateTarget = ref(null);
const newRackId = ref('');
const relocateNotes = ref('');

const availableRacks = computed(() => props.racks);

const filteredInventories = computed(() => {
  return props.inventories.filter(item => {
    const s = searchQuery.value.toLowerCase();
    const productName = (item.product?.sap_code ? item.product.sap_code + ' ' + item.product.nominal_size + '"' : '').toLowerCase();
    const matchSearch = !s || item.bundle_tag.toLowerCase().includes(s) || item.heat_number.toLowerCase().includes(s) || productName.includes(s);
    const matchType = !selectedType.value || item.product?.category?.code === selectedType.value;
    const matchQc = !selectedQc.value || item.qc_status === selectedQc.value;
    return matchSearch && matchType && matchQc;
  });
});

function resetFilters() { searchQuery.value = ''; selectedType.value = ''; selectedQc.value = ''; }
function updateQc(id, newStatus) { emit('update-qc', { id, qc_status: newStatus }); }
function openRelocateModal(item) { relocateTarget.value = item; newRackId.value = ''; relocateNotes.value = ''; }
function submitRelocate() {
  if (!relocateTarget.value || !newRackId.value) return;
  emit('relocate-bundle', { pipe_inventory_id: relocateTarget.value.id, target_rack_id: newRackId.value, notes: relocateNotes.value, operator_name: 'Operator WMS Spindo' });
  relocateTarget.value = null;
}
</script>
