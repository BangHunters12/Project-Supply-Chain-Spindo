<template>
  <header :class="darkMode ? 'bg-iron-950 border-iron-800' : 'bg-white border-slate-200'" class="sticky top-0 z-50 border-b">
    <div class="max-w-screen-2xl mx-auto px-4 sm:px-6">
      <div class="flex items-center justify-between h-14">
        
        <!-- Brand Wordmark -->
        <div class="flex items-center space-x-3">
          <span class="font-display text-lg font-extrabold tracking-tight text-spindo-red">SPINDO</span>
          <div :class="darkMode ? 'bg-iron-700' : 'bg-slate-200'" class="h-5 w-px"></div>
          <span :class="darkMode ? 'text-iron-400' : 'text-slate-500'" class="font-mono text-[11px] tracking-wider">WMS SC-U7 PIPE</span>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-1">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="$emit('change-tab', tab.id)"
            :class="[
               'rounded px-3 py-1.5 text-xs font-display font-medium transition-colors',
               activeTab === tab.id
                 ? (darkMode ? 'bg-iron-800 text-iron-200' : 'bg-slate-100 text-slate-900')
                 : (darkMode ? 'text-iron-400 hover:text-iron-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900')
            ]"
          >
            {{ tab.label }}
          </button>
        </nav>

        <!-- Right Controls -->
        <div class="flex items-center space-x-2">
          <button
            @click="$emit('toggle-theme')"
            :class="darkMode ? 'text-iron-400 hover:bg-iron-800 hover:text-iron-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'"
            class="rounded p-1.5 transition"
            :title="darkMode ? 'Gunakan light mode' : 'Gunakan dark mode'"
          >
            <svg v-if="darkMode" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" stroke-width="2" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z"/></svg>
          </button>
          <button
            @click="$emit('refresh')"
            :class="darkMode ? 'text-iron-400 hover:bg-iron-800 hover:text-iron-200' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900'"
            class="rounded p-1.5 transition"
            title="Muat Ulang Data"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
          </button>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <div class="md:hidden flex space-x-1 overflow-x-auto pb-2 -mx-1">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="$emit('change-tab', tab.id)"
          :class="[
               'rounded px-3 py-1.5 text-xs font-display font-medium whitespace-nowrap transition-colors',
               activeTab === tab.id
                 ? (darkMode ? 'bg-iron-800 text-iron-200' : 'bg-slate-100 text-slate-900')
                 : (darkMode ? 'text-iron-400' : 'text-slate-500')
          ]"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
defineProps({
  activeTab: { type: String, required: true },
  darkMode: { type: Boolean, default: false },
});

defineEmits(['change-tab', 'refresh', 'toggle-theme']);

const tabs = [
  { id: 'map', label: 'Denah & Mapping Gudang' },
  { id: 'dashboard', label: 'Dashboard Analitik' },
  { id: 'inventory', label: 'Daftar Stok Pipa' },
  { id: 'inbound', label: 'Penerimaan Mill' },
  { id: 'outbound', label: 'Surat Jalan' },
];
</script>
