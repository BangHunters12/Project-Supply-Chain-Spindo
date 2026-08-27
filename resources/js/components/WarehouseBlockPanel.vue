<template>
  <div class="fixed inset-0 z-50 bg-black/60 p-0 sm:p-5" role="presentation" @click.self="$emit('close')" @keydown.esc="$emit('close')">
    <aside ref="panel" class="ml-auto flex h-full w-full max-w-xl flex-col border-l border-wms-border bg-white shadow-lg dark:border-iron-700 dark:bg-iron-900 sm:border" role="dialog" aria-modal="true" :aria-labelledby="`block-panel-title-${block.id}`" tabindex="-1">
      <div class="flex items-start justify-between gap-4 border-b border-wms-border p-5 dark:border-zinc-800">
        <div>
          <p class="text-[10px] font-black uppercase tracking-[0.18em] text-safety">{{ warehouse.code }} / block detail</p>
          <h2 :id="`block-panel-title-${block.id}`" class="mt-1 text-2xl font-black text-wms-navy dark:text-white">Blok {{ block.code }}</h2>
          <p class="mt-1 font-mono text-xs text-slate-500 dark:text-zinc-400">SLOC <strong class="text-spindo dark:text-cyan-300">{{ block.sloc_code || '-' }}</strong> · Area {{ block.area_code || '-' }}</p>
        </div>
        <button type="button" @click="$emit('close')" class="rounded-md border border-slate-300 px-3 py-2 font-mono text-[10px] font-bold text-slate-500 hover:border-spindo-red hover:text-spindo-red dark:border-iron-700 dark:text-iron-400 dark:hover:border-cyan-300 dark:hover:text-cyan-300" aria-label="Tutup rincian blok">Tutup <span aria-hidden="true">(Esc)</span></button>
      </div>
      <div class="grid grid-cols-4 gap-px border-b border-slate-200 bg-slate-200 dark:border-zinc-800 dark:bg-zinc-800">
        <div class="bg-white p-4 dark:bg-zinc-950"><span class="block text-[9px] font-black uppercase text-slate-500 dark:text-zinc-500">Bundle</span><strong class="mt-1 block font-mono text-lg dark:text-white">{{ totalBundles }}</strong></div>
        <div class="bg-white p-4 dark:bg-zinc-950"><span class="block text-[9px] font-black uppercase text-slate-500 dark:text-zinc-500">Eceran</span><strong class="mt-1 block font-mono text-lg dark:text-white">{{ totalLoosePcs }}</strong></div>
        <div class="bg-white p-4 dark:bg-zinc-950"><span class="block text-[9px] font-black uppercase text-slate-500 dark:text-zinc-500">Total Pcs</span><strong class="mt-1 block font-mono text-lg dark:text-white">{{ totalPcs }}</strong></div>
        <div class="bg-white p-4 dark:bg-zinc-950"><span class="block text-[9px] font-black uppercase text-slate-500 dark:text-zinc-500">Weight</span><strong class="mt-1 block font-mono text-lg dark:text-white">{{ totalTons }}T</strong></div>
      </div>
      <div class="flex-1 overflow-y-auto p-5">
        <div class="mb-3 flex items-center justify-between"><h3 class="text-xs font-black uppercase tracking-wide text-steel-900 dark:text-white">Pipa tersimpan</h3><span class="font-mono text-[10px] text-slate-400">{{ block.inventories.length }} RECORDS</span></div>
        <div v-if="!block.inventories.length" class="rounded-lg border border-dashed border-slate-300 px-4 py-12 text-center text-sm text-slate-500 dark:border-zinc-700 dark:text-zinc-400">Belum ada bundle pipa tersimpan di blok ini.</div>
        <div v-else class="space-y-2">
          <article v-for="inventory in block.inventories" :key="inventory.id" class="rounded-lg border border-slate-200 bg-steel-50 p-3 dark:border-zinc-800 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3"><div><strong class="font-mono text-xs text-spindo dark:text-cyan-300">{{ inventory.bundle_tag }}</strong><p class="mt-1 text-sm font-bold text-steel-900 dark:text-white">{{ inventory.product?.sap_code || '-' }} · {{ inventory.product?.nominal_size || '-' }} · {{ inventory.product?.spec_name || '-' }}</p></div><span :class="inventory.qc_status === 'PASSED' ? 'text-emerald-600 dark:text-emerald-400' : 'text-safety'" class="font-mono text-[9px] font-black">{{ inventory.qc_status }}</span></div>
            <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-200 pt-2 font-mono text-[10px] text-slate-500 dark:border-zinc-800 dark:text-zinc-500"><span>Heat: {{ inventory.heat_number }}</span><span class="text-right"><template v-if="inventory.qty_bundles > 0">{{ inventory.qty_bundles }} Bdl </template><template v-if="inventory.qty_bundles > 0 && loosePcs(inventory) > 0">+ </template><template v-if="loosePcs(inventory) > 0 || inventory.qty_bundles === 0">{{ loosePcs(inventory) }} Pcs </template><span class="ml-1 font-sans text-[9px] tracking-tight text-slate-400 dark:text-zinc-600">({{ inventory.qty_pcs }} total)</span></span><span>{{ inventory.product?.category || '-' }}</span><span class="text-right font-bold text-steel-900 dark:text-zinc-200">{{ (Number(inventory.total_weight_kg || 0) / 1000).toFixed(2) }} Ton</span></div>
          </article>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';

const props = defineProps({
  block: { type: Object, required: true },
  warehouse: { type: Object, required: true },
});

const panel = ref(null);

defineEmits(['close']);

const loosePcs = (item) => {
  const bdl = Number(item.qty_bundles || 0);
  const ppb = Number(item.product?.pcs_per_bundle || 0);
  const total = Number(item.qty_pcs || 0);
  return ppb > 0 ? total - (bdl * ppb) : total;
};

const totalBundles = computed(() => props.block.inventories.reduce((sum, item) => sum + Number(item.qty_bundles || 0), 0));
const totalPcs = computed(() => props.block.inventories.reduce((sum, item) => sum + Number(item.qty_pcs || 0), 0));
const totalLoosePcs = computed(() => props.block.inventories.reduce((sum, item) => sum + loosePcs(item), 0));
const totalTons = computed(() => (props.block.inventories.reduce((sum, item) => sum + Number(item.total_weight_kg || 0), 0) / 1000).toFixed(2));

nextTick(() => panel.value?.focus());
</script>
