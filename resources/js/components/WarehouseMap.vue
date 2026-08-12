<template>
  <div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h2 class="text-sm font-display font-bold text-iron-200 uppercase tracking-wider">Layout Rak Gudang</h2>
        <p class="text-xs text-iron-400 font-mono">Kapasitas tonase real-time per zona dan rak</p>
      </div>
      <div class="flex items-center space-x-1 overflow-x-auto">
        <button
          @click="selectedZone = 'ALL'"
          :class="['px-2.5 py-1 rounded text-xs font-display font-medium transition-colors', selectedZone === 'ALL' ? 'bg-iron-800 text-iron-200' : 'text-iron-400 hover:text-iron-200']"
        >Semua</button>
        <button
          v-for="zone in zones" :key="zone.id"
          @click="selectedZone = zone.code"
          :class="['px-2.5 py-1 rounded text-xs font-display font-medium transition-colors', selectedZone === zone.code ? 'bg-iron-800 text-iron-200' : 'text-iron-400 hover:text-iron-200']"
        >{{ zone.code }}</button>
      </div>
    </div>

    <!-- Zone Panels -->
    <div class="space-y-5">
      <div v-for="zone in filteredZones" :key="zone.id" class="bg-iron-900 rounded-lg border border-iron-800 overflow-hidden">
        <!-- Zone header row -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-iron-800 bg-iron-950">
          <div class="flex items-center space-x-3">
            <span class="font-mono text-xs font-bold text-iron-200">{{ zone.code }}</span>
            <span class="text-xs font-display text-iron-300">{{ zone.name }}</span>
          </div>
          <div class="flex items-center space-x-4 text-[11px] font-mono text-iron-400">
            <span>{{ zone.category }}</span>
            <span>Max: <strong class="text-iron-200">{{ zone.total_capacity_tons }}T</strong></span>
          </div>
        </div>

        <!-- Racks grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-px bg-iron-800">
          <div
            v-for="rack in zone.racks" :key="rack.id"
            class="bg-iron-900 p-3 space-y-2 cursor-pointer hover:bg-iron-950 transition-colors"
            @click="selectedRack = rack"
          >
            <div class="flex items-center justify-between">
              <span class="font-mono text-[11px] font-bold text-iron-200">{{ rack.rack_code }}</span>
              <span v-if="rack.status === 'FULL'" class="w-2 h-2 rounded-full bg-spindo-red"></span>
              <span v-else-if="rack.status === 'MAINTENANCE'" class="w-2 h-2 rounded-full bg-amber-500"></span>
              <span v-else class="w-2 h-2 rounded-full bg-emerald-500"></span>
            </div>
            <div class="w-full bg-iron-800 h-1.5 rounded-sm overflow-hidden">
              <div
                class="h-full transition-all duration-300"
                :class="getBarColor(rack)"
                :style="{ width: getPercent(rack) + '%' }"
              ></div>
            </div>
            <div class="flex justify-between text-[10px] font-mono text-iron-400">
              <span>{{ rack.current_weight_tons }}T</span>
              <span>{{ rack.inventories_count ?? 0 }} bdl</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Rack detail modal -->
    <div v-if="selectedRack" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-iron-950/80 backdrop-blur-sm">
      <div class="bg-iron-900 border border-iron-800 rounded-lg max-w-sm w-full p-5 space-y-3 relative">
        <button @click="selectedRack = null" class="absolute top-3 right-3 text-iron-400 hover:text-iron-200 text-xs font-mono">ESC</button>
        <div>
          <span class="font-mono text-sm font-bold text-iron-200">{{ selectedRack.rack_code }}</span>
          <span class="ml-2 text-xs font-mono" :class="selectedRack.status === 'FULL' ? 'text-spindo-red' : selectedRack.status === 'MAINTENANCE' ? 'text-amber-500' : 'text-emerald-500'">{{ selectedRack.status }}</span>
        </div>
        <div class="text-xs font-mono text-iron-400 space-y-1">
          <p>Beban: {{ selectedRack.current_weight_tons }} / {{ selectedRack.max_weight_tons }} Ton</p>
          <p>Bundle tersimpan: {{ selectedRack.inventories_count ?? 0 }}</p>
        </div>
        <p class="text-[11px] text-iron-400">Lihat tab Stok Pipa untuk mengelola atau merelokasi bundle.</p>
        <div class="flex justify-end">
          <button @click="selectedRack = null" class="px-3 py-1.5 bg-iron-800 hover:bg-iron-700 text-iron-300 text-xs font-display font-medium rounded transition">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

const props = defineProps({ zones: { type: Array, default: () => [] } });
const selectedZone = ref('ALL');
const selectedRack = ref(null);

const filteredZones = computed(() => {
  if (selectedZone.value === 'ALL') return props.zones;
  return props.zones.filter(z => z.code === selectedZone.value);
});

function getPercent(rack) {
  return Math.min(100, (rack.current_weight_tons / rack.max_weight_tons) * 100);
}

function getBarColor(rack) {
  const p = getPercent(rack);
  if (p >= 90) return 'bg-spindo-red';
  if (p >= 60) return 'bg-amber-500';
  return 'bg-steel-blue-light';
}
</script>
