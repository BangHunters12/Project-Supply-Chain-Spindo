<template>
  <div class="max-w-2xl mx-auto bg-iron-900 rounded-lg border border-iron-800 p-6 space-y-5">
    <div class="border-b border-iron-800 pb-3">
      <h2 class="text-sm font-display font-bold text-iron-200 uppercase tracking-wider">Penerimaan Inbound dari Mill</h2>
      <p class="text-xs text-iron-400 font-mono mt-0.5">Pencatatan bundle pipa hasil produksi masuk ke alokasi rak gudang</p>
    </div>

    <form @submit.prevent="submitForm" class="space-y-4">
      <div>
        <label class="block text-[11px] font-display font-semibold text-iron-400 uppercase tracking-wider mb-1">Spesifikasi Pipa</label>
        <select v-model="form.pipe_product_id" required class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 focus:outline-none focus:border-iron-600">
          <option value="">Pilih spesifikasi...</option>
          <option v-for="spec in products" :key="spec.id" :value="spec.id">{{ spec.sap_code }} &mdash; {{ spec.nominal_size }}" {{ spec.spec_name }} (OD {{ spec.outer_diameter_mm }}mm)</option>
        </select>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[11px] font-display font-semibold text-iron-400 uppercase tracking-wider mb-1">Heat Number</label>
          <input v-model="form.heat_number" type="text" required placeholder="HT-SP-XXXXX" class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 placeholder-iron-600 focus:outline-none focus:border-iron-600" />
        </div>
        <div>
          <label class="block text-[11px] font-display font-semibold text-iron-400 uppercase tracking-wider mb-1">Jumlah Bundle</label>
          <input v-model.number="form.qty_bundles" type="number" min="1" required placeholder="1" class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 placeholder-iron-600 focus:outline-none focus:border-iron-600" />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[11px] font-display font-semibold text-iron-400 uppercase tracking-wider mb-1">Alokasi Rak</label>
          <select v-model="form.warehouse_rack_id" required class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 focus:outline-none focus:border-iron-600">
            <option value="">Pilih rak...</option>
            <option v-for="rack in racks" :key="rack.id" :value="rack.id">{{ rack.rack_code }} ({{ rack.zone?.code }}) — {{ rack.current_weight_tons }}/{{ rack.max_weight_tons }}T</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-display font-semibold text-iron-400 uppercase tracking-wider mb-1">Mill Asal</label>
          <input v-model="form.mill_source" type="text" required placeholder="Unit Spindo Mill #1" class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 placeholder-iron-600 focus:outline-none focus:border-iron-600" />
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-[11px] font-display font-semibold text-iron-400 uppercase tracking-wider mb-1">Status QC</label>
          <select v-model="form.qc_status" class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 focus:outline-none focus:border-iron-600">
            <option value="PASSED">PASSED — Lolos Uji</option>
            <option value="PENDING">PENDING — Menunggu Lab</option>
            <option value="REJECTED">REJECTED — Tahan</option>
          </select>
        </div>
        <div>
          <label class="block text-[11px] font-display font-semibold text-iron-400 uppercase tracking-wider mb-1">Operator</label>
          <input v-model="form.operator_name" type="text" placeholder="Operator WMS" class="w-full bg-iron-950 border border-iron-800 rounded px-3 py-2 text-xs font-mono text-iron-200 placeholder-iron-600 focus:outline-none focus:border-iron-600" />
        </div>
      </div>

      <!-- Weight and Pcs estimate -->
      <div v-if="selectedSpec" class="bg-iron-950 rounded border border-iron-800 px-3 py-2.5 font-mono text-xs text-iron-400 flex items-center justify-between">
        <span>Estimasi Pcs: <strong class="text-iron-200">{{ estimatedPcs }} Pcs</strong></span>
        <span>Estimasi Berat Total: <strong class="text-steel-blue-light">{{ weightEstimateText }}</strong></span>
      </div>

      <div class="flex justify-end space-x-2 pt-3 border-t border-iron-800">
        <button type="button" @click="$emit('cancel')" class="px-3.5 py-2 rounded bg-iron-800 hover:bg-iron-700 text-iron-300 text-xs font-display font-medium transition">Batal</button>
        <button type="submit" :disabled="isSubmitting" class="px-4 py-2 rounded bg-steel-blue hover:bg-steel-blue-light disabled:opacity-40 text-white text-xs font-display font-bold transition">{{ isSubmitting ? 'Menyimpan...' : 'Simpan Penerimaan' }}</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  products: { type: Array, default: () => [] },
  racks: { type: Array, default: () => [] },
  preselectedRackId: { type: [Number, String], default: null }
});

const emit = defineEmits(['submit-inbound', 'cancel']);

const form = ref({
  pipe_product_id: '',
  warehouse_rack_id: props.preselectedRackId || '',
  heat_number: '',
  mill_source: 'Unit Spindo Karawang Mill #1',
  qty_bundles: 1,
  qc_status: 'PASSED',
  operator_name: 'Operator WMS Spindo',
});

const isSubmitting = ref(false);

const selectedSpec = computed(() => props.products.find(s => s.id === form.value.pipe_product_id));

const weightEstimateText = computed(() => {
  if (!selectedSpec.value || !selectedSpec.value.weight_per_bundle_kg || !form.value.qty_bundles) {
    return 'N/A';
  }
  const totalKg = selectedSpec.value.weight_per_bundle_kg * form.value.qty_bundles;
  return `${totalKg} kg (${(totalKg / 1000).toFixed(2)} Ton)`;
});

const estimatedPcs = computed(() => {
  if (!selectedSpec.value || !selectedSpec.value.pcs_per_bundle || !form.value.qty_bundles) {
    return 0;
  }
  return selectedSpec.value.pcs_per_bundle * form.value.qty_bundles;
});

async function submitForm() {
  isSubmitting.value = true;
  try { emit('submit-inbound', { ...form.value }); }
  finally { isSubmitting.value = false; }
}
</script>
