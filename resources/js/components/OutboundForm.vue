<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-sm font-display font-bold text-iron-200 uppercase tracking-wider">Surat Jalan Pengiriman</h2>
        <p class="text-xs text-iron-400 font-mono">Pengeluaran pipa, pembuatan DO, dan dispatch armada truk</p>
      </div>
      <button @click="showCreateModal = true" class="px-3.5 py-2 rounded bg-steel-blue hover:bg-steel-blue-light text-white text-xs font-display font-semibold transition">Buat Surat Jalan Baru</button>
    </div>

    <!-- Shipments table -->
    <div class="bg-iron-900 rounded-lg border border-iron-800 overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="bg-iron-950 border-b border-iron-800">
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">No. DO</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">Customer / Tujuan</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">Truk & Driver</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">Muatan</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">Status</th>
              <th class="py-2.5 px-3 text-[10px] font-mono font-semibold text-iron-400 uppercase tracking-wider">Tanggal</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-iron-800/60">
            <tr v-for="s in shipments" :key="s.id" class="hover:bg-iron-950/50 transition-colors">
              <td class="py-2.5 px-3 font-mono text-xs font-bold text-iron-200">{{ s.do_number }}</td>
              <td class="py-2.5 px-3">
                <div class="text-xs font-display font-semibold text-iron-200">{{ s.customer_name }}</div>
                <div class="text-[11px] font-mono text-iron-400">{{ s.destination }}</div>
              </td>
              <td class="py-2.5 px-3">
                <div class="font-mono text-xs text-iron-200">{{ s.truck_number }}</div>
                <div class="text-[11px] text-iron-400">{{ s.driver_name }}</div>
              </td>
              <td class="py-2.5 px-3 font-mono text-xs text-iron-300">{{ s.total_bundles }} bdl / {{ s.total_weight_tons }}T</td>
              <td class="py-2.5 px-3">
                <span class="inline-block w-2 h-2 rounded-full mr-1" :class="s.status === 'DISPATCHED' ? 'bg-emerald-500' : s.status === 'LOADING' ? 'bg-amber-500' : 'bg-iron-600'"></span>
                <span class="font-mono text-[11px] text-iron-300">{{ s.status }}</span>
              </td>
              <td class="py-2.5 px-3 font-mono text-[11px] text-iron-400">{{ s.shipment_date }}</td>
            </tr>
            <tr v-if="shipments.length === 0">
              <td colspan="6" class="py-8 text-center text-iron-600 text-xs font-display">Belum ada surat jalan.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create modal -->
    <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-iron-950/80 backdrop-blur-sm">
      <div class="bg-iron-900 border border-iron-800 rounded-lg max-w-2xl w-full p-5 space-y-4 relative max-h-[90vh] overflow-y-auto">
        <button @click="showCreateModal = false" class="absolute top-3 right-3 text-iron-400 hover:text-iron-200 text-xs font-mono">ESC</button>
        <h3 class="text-sm font-display font-bold text-iron-200 uppercase tracking-wide">Buat Surat Jalan Outbound</h3>

        <form @submit.prevent="submitOutbound" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-display font-semibold text-iron-400 mb-1">Customer / Kontraktor</label>
              <input v-model="form.customer_name" required placeholder="PT Wijaya Karya Tbk" class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs text-iron-200 focus:outline-none focus:border-iron-600" />
            </div>
            <div>
              <label class="block text-[11px] font-display font-semibold text-iron-400 mb-1">Tujuan Proyek</label>
              <input v-model="form.destination" required placeholder="Proyek Tol IKN, Kaltim" class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs text-iron-200 focus:outline-none focus:border-iron-600" />
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-[11px] font-display font-semibold text-iron-400 mb-1">Plat Nomor Truk</label>
              <input v-model="form.truck_number" required placeholder="B 9284 UXT" class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 focus:outline-none focus:border-iron-600" />
            </div>
            <div>
              <label class="block text-[11px] font-display font-semibold text-iron-400 mb-1">Nama Supir</label>
              <input v-model="form.driver_name" required placeholder="Budi Santoso" class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs text-iron-200 focus:outline-none focus:border-iron-600" />
            </div>
          </div>

          <!-- Bundle selection -->
          <div>
            <label class="block text-[11px] font-display font-semibold text-iron-400 mb-1.5">Bundle Pipa (QC Passed, Available)</label>
            <div class="bg-iron-950 border border-iron-800 rounded p-2 max-h-48 overflow-y-auto space-y-1">
              <label v-for="bundle in availableBundles" :key="bundle.id" class="flex items-center justify-between p-2 rounded bg-iron-900 hover:bg-iron-800 cursor-pointer text-xs">
                <div class="flex items-center space-x-2">
                  <input type="checkbox" :value="bundle.id" v-model="form.bundle_ids" class="w-3.5 h-3.5 rounded border-iron-700 bg-iron-950 text-steel-blue" />
                  <span class="font-mono text-steel-blue-light font-bold">{{ bundle.bundle_tag }}</span>
                  <span class="text-iron-300 font-display">{{ bundle.product?.sap_code + ' ' + bundle.product?.nominal_size + '" ' + bundle.product?.spec_name }}</span>
                </div>
                <span class="font-mono text-iron-400">{{ (bundle.total_weight_kg/1000).toFixed(2) }}T</span>
              </label>
              <div v-if="availableBundles.length === 0" class="text-xs text-iron-600 p-2 text-center font-display">Tidak ada bundle yang tersedia.</div>
            </div>
          </div>

          <div class="flex justify-end space-x-2 pt-3 border-t border-iron-800">
            <button type="button" @click="showCreateModal = false" class="px-3.5 py-2 rounded bg-iron-800 hover:bg-iron-700 text-iron-300 text-xs font-display font-medium transition">Batal</button>
            <button type="submit" :disabled="form.bundle_ids.length === 0" class="px-4 py-2 rounded bg-steel-blue hover:bg-steel-blue-light disabled:opacity-40 text-white text-xs font-display font-bold transition">Terbitkan Surat Jalan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  shipments: { type: Array, default: () => [] },
  inventories: { type: Array, default: () => [] }
});

const emit = defineEmits(['submit-outbound']);
const showCreateModal = ref(false);

const form = ref({ customer_name: '', destination: '', truck_number: '', driver_name: '', bundle_ids: [], notes: '' });

const availableBundles = computed(() => props.inventories.filter(i => i.status === 'AVAILABLE' && i.qc_status === 'PASSED'));

function submitOutbound() {
  emit('submit-outbound', { ...form.value });
  showCreateModal.value = false;
  form.value = { customer_name: '', destination: '', truck_number: '', driver_name: '', bundle_ids: [], notes: '' };
}
</script>
