<template>
  <div class="fixed inset-0 z-50 bg-black/60 p-0 sm:p-5" role="presentation" @click.self="handleBackdropClick" @keydown.esc="handleEsc">
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
        <div class="mb-3 flex items-center justify-between">
          <h3 class="text-xs font-black uppercase tracking-wide text-steel-900 dark:text-white">Pipa tersimpan</h3>
          <span class="font-mono text-[10px] text-slate-400">{{ block.inventories.length }} RECORDS &middot; KLIK UNTUK DETAIL</span>
        </div>

        <div v-if="!block.inventories.length" class="rounded-lg border border-dashed border-slate-300 px-4 py-12 text-center text-sm text-slate-500 dark:border-zinc-700 dark:text-zinc-400">
          Belum ada bundle pipa tersimpan di blok ini.
        </div>

        <div v-else class="space-y-2.5">
          <article
            v-for="inventory in block.inventories"
            :key="inventory.id"
            @click="selectedInventory = inventory"
            class="group cursor-pointer rounded-lg border border-slate-200 bg-steel-50 p-3.5 transition-all duration-200 hover:border-spindo hover:bg-white hover:shadow-md dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-cyan-400/60 dark:hover:bg-zinc-850 active:scale-[0.99] relative"
            title="Klik untuk melihat detail lengkap pipa ini"
          >
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0 flex-1">
                <!-- NAMA MUDAH DI ATAS DENGAN FONT LEBIH BESAR -->
                <h4 class="text-sm font-bold text-steel-900 group-hover:text-spindo dark:text-white dark:group-hover:text-cyan-300 break-words leading-snug transition-colors">
                  {{ pipeEasyName(inventory) }}
                </h4>
                <!-- DESKRIPSI DI BAWAHNYA DENGAN FONT LEBIH KECIL -->
                <p class="mt-1 font-mono text-xs font-semibold text-spindo dark:text-cyan-400 break-words leading-relaxed">
                  {{ pipeDescription(inventory) }}
                </p>
              </div>

              <div class="flex flex-col items-end gap-1 shrink-0">
                <span :class="inventory.qc_status === 'PASSED' ? 'text-emerald-600 dark:text-emerald-400' : 'text-safety'" class="font-mono text-[9px] font-black">
                  {{ inventory.qc_status }}
                </span>
                <span class="text-[9px] font-mono text-slate-400 group-hover:text-spindo dark:text-zinc-500 dark:group-hover:text-cyan-300 transition-colors flex items-center gap-0.5">
                  Detail &rarr;
                </span>
              </div>
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2 border-t border-slate-200 pt-2 font-mono text-[10px] text-slate-500 dark:border-zinc-800 dark:text-zinc-500">
              <span>Heat: {{ inventory.heat_number }}</span>
              <span class="text-right">
                <template v-if="inventory.qty_bundles > 0">{{ inventory.qty_bundles }} Bdl </template>
                <template v-if="inventory.qty_bundles > 0 && loosePcs(inventory) > 0">+ </template>
                <template v-if="loosePcs(inventory) > 0 || inventory.qty_bundles === 0">{{ loosePcs(inventory) }} Pcs </template>
                <span class="ml-1 font-sans text-[9px] tracking-tight text-slate-400 dark:text-zinc-600">({{ inventory.qty_pcs }} total)</span>
              </span>
              <span>{{ inventory.product?.category || '-' }}</span>
              <span class="text-right font-bold text-steel-900 dark:text-zinc-200">{{ (Number(inventory.total_weight_kg || 0) / 1000).toFixed(2) }} Ton</span>
            </div>
          </article>
        </div>
      </div>
    </aside>

    <!-- Modal Popup Detail Pipa & Stok Inventaris -->
    <div
      v-if="selectedInventory"
      class="fixed inset-0 z-[60] flex items-center justify-center p-3 sm:p-6 bg-black/75 backdrop-blur-sm animate-fadeIn"
      role="dialog"
      aria-modal="true"
      @click.self="selectedInventory = null"
    >
      <div class="relative w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-2xl dark:border-zinc-700 dark:bg-zinc-900 max-h-[92vh] overflow-y-auto space-y-4">
        <!-- Header Modal -->
        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3 dark:border-zinc-800">
          <div>
            <span class="inline-block font-mono text-[10px] font-black uppercase tracking-wider text-spindo dark:text-cyan-400">
              SIKUTA &middot; DETAIL RINCIAN PIPA
            </span>
            <h3 class="mt-1 text-base sm:text-lg font-black text-steel-900 dark:text-white leading-tight">
              {{ pipeEasyName(selectedInventory) }}
            </h3>
          </div>
          <button
            type="button"
            @click="selectedInventory = null"
            class="rounded-lg border border-slate-200 p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:border-zinc-700 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white transition-colors"
            title="Tutup (Esc)"
          >
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Deskripsi Asli SIKUTA Card -->
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5 dark:border-zinc-800/80 dark:bg-zinc-950/60">
          <div class="flex items-center justify-between text-[10px] font-mono text-slate-500 dark:text-zinc-400 mb-1">
            <span>DESKRIPSI SIKUTA</span>
            <span class="font-bold text-spindo dark:text-cyan-400">{{ selectedInventory.product?.sap_code || '-' }}</span>
          </div>
          <p class="font-mono text-sm font-bold text-steel-900 dark:text-cyan-300 break-words leading-relaxed">
            {{ pipeDescription(selectedInventory) }}
          </p>
        </div>

        <!-- Badges / Status Bar -->
        <div class="flex flex-wrap items-center gap-2">
          <span
            class="rounded-md px-2.5 py-1 text-[11px] font-bold font-mono"
            :class="selectedInventory.product?.category_code === 'PG' || (selectedInventory.product?.category || '').includes('Galva') ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-800' : 'bg-slate-100 text-slate-800 dark:bg-zinc-800 dark:text-zinc-300 border border-slate-300 dark:border-zinc-700'"
          >
            {{ selectedInventory.product?.category || 'Pipa Hitam' }}
          </span>
          <span
            class="rounded-md px-2.5 py-1 text-[11px] font-bold font-mono"
            :class="isPipeDrat(selectedInventory) ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300 border border-indigo-300 dark:border-indigo-800' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800'"
          >
            {{ isPipeDrat(selectedInventory) ? 'DRAT (Threaded)' : 'PLAIN-END (Tanpa Drat)' }}
          </span>
          <span
            class="rounded-md px-2.5 py-1 text-[11px] font-bold font-mono"
            :class="selectedInventory.qc_status === 'PASSED' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-300 dark:border-rose-800'"
          >
            QC: {{ selectedInventory.qc_status }}
          </span>
          <span class="rounded-md px-2.5 py-1 text-[11px] font-bold font-mono bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
            {{ selectedInventory.status || 'AVAILABLE' }}
          </span>
        </div>

        <!-- Spesifikasi Teknis Grid -->
        <div class="space-y-2">
          <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-zinc-400">
            Spesifikasi Produk & Material
          </h4>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Kode SAP / Material</span>
              <div class="mt-0.5 flex items-center justify-between">
                <strong class="font-mono text-xs text-steel-900 dark:text-zinc-200">{{ selectedInventory.product?.sap_code || '-' }}</strong>
                <button
                  v-if="selectedInventory.product?.sap_code"
                  type="button"
                  @click="copyText(selectedInventory.product.sap_code)"
                  class="text-[10px] font-bold text-spindo hover:underline dark:text-cyan-400 ml-1"
                >
                  {{ copied ? 'Tersalin!' : 'Salin' }}
                </button>
              </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Ukuran Nominal</span>
              <strong class="mt-0.5 block font-mono text-xs text-steel-900 dark:text-zinc-200">
                {{ selectedInventory.product?.nominal_size || '-' }}
              </strong>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Class / Spesifikasi</span>
              <strong class="mt-0.5 block font-mono text-xs text-steel-900 dark:text-zinc-200">
                {{ selectedInventory.product?.spec_name || '-' }}
              </strong>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Panjang Batang</span>
              <strong class="mt-0.5 block font-mono text-xs text-steel-900 dark:text-zinc-200">
                {{ selectedInventory.product?.length_meters || 6.00 }} Meter
              </strong>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Isi Standar per Bundle</span>
              <strong class="mt-0.5 block font-mono text-xs text-steel-900 dark:text-zinc-200">
                {{ selectedInventory.product?.pcs_per_bundle || 0 }} Pcs / Bdl
              </strong>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Pabrik Asal / Mill</span>
              <strong class="mt-0.5 block font-mono text-xs text-steel-900 dark:text-zinc-200 truncate" :title="selectedInventory.mill_source">
                {{ selectedInventory.mill_source || 'Unit 7 Gresik' }}
              </strong>
            </div>
          </div>
        </div>

        <!-- Posisi & Volume Stok Grid -->
        <div class="space-y-2">
          <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-zinc-400">
            Posisi & Volume Stok
          </h4>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Lokasi Blok & Gudang</span>
              <strong class="mt-0.5 block font-mono text-xs text-spindo dark:text-cyan-300">
                Blok {{ block.code }} &middot; {{ warehouse.code }}
              </strong>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">SLOC & Area</span>
              <strong class="mt-0.5 block font-mono text-xs text-steel-900 dark:text-zinc-200">
                {{ block.sloc_code || '-' }} &middot; Area {{ block.area_code || '-' }}
              </strong>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Heat Number / Lot</span>
              <strong class="mt-0.5 block font-mono text-xs text-steel-900 dark:text-zinc-200">
                {{ selectedInventory.heat_number || '-' }}
              </strong>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Bundle Tag SIKUTA</span>
              <strong class="mt-0.5 block font-mono text-xs text-steel-900 dark:text-zinc-200 truncate" :title="selectedInventory.bundle_tag">
                {{ selectedInventory.bundle_tag || '-' }}
              </strong>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Rincian Fisik</span>
              <strong class="mt-0.5 block font-mono text-xs text-steel-900 dark:text-zinc-200">
                {{ selectedInventory.qty_bundles }} Bdl + {{ loosePcs(selectedInventory) }} Pcs Eceran
              </strong>
            </div>

            <div class="rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-zinc-800 dark:bg-zinc-950/40">
              <span class="block text-[10px] text-slate-400 dark:text-zinc-500">Total Batang & Berat</span>
              <strong class="mt-0.5 block font-mono text-xs text-steel-900 dark:text-zinc-200">
                {{ selectedInventory.qty_pcs }} Pcs &middot; {{ (Number(selectedInventory.total_weight_kg || 0) / 1000).toFixed(2) }} Ton
              </strong>
            </div>
          </div>
        </div>

        <!-- Tombol Tutup Footer -->
        <div class="pt-2 border-t border-slate-100 dark:border-zinc-800 flex justify-end">
          <button
            type="button"
            @click="selectedInventory = null"
            class="w-full sm:w-auto rounded-lg bg-spindo px-5 py-2.5 text-xs font-bold text-white hover:bg-spindo-light dark:bg-cyan-500 dark:text-zinc-950 dark:hover:bg-cyan-400 transition-colors shadow-sm"
          >
            Tutup Rincian
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, ref } from 'vue';

const props = defineProps({
  block: { type: Object, required: true },
  warehouse: { type: Object, required: true },
});

const emit = defineEmits(['close']);

const panel = ref(null);
const selectedInventory = ref(null);
const copied = ref(false);

const handleEsc = () => {
  if (selectedInventory.value) {
    selectedInventory.value = null;
  } else {
    emit('close');
  }
};

const handleBackdropClick = () => {
  if (selectedInventory.value) {
    selectedInventory.value = null;
  } else {
    emit('close');
  }
};

const copyText = (text) => {
  if (!text) return;
  navigator.clipboard.writeText(text).then(() => {
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
  });
};

const loosePcs = (item) => {
  const bdl = Number(item.qty_bundles || 0);
  const ppb = Number(item.product?.pcs_per_bundle || 0);
  const total = Number(item.qty_pcs || 0);
  return ppb > 0 ? total - (bdl * ppb) : total;
};

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

const totalBundles = computed(() => props.block.inventories.reduce((sum, item) => sum + Number(item.qty_bundles || 0), 0));
const totalPcs = computed(() => props.block.inventories.reduce((sum, item) => sum + Number(item.qty_pcs || 0), 0));
const totalLoosePcs = computed(() => props.block.inventories.reduce((sum, item) => sum + loosePcs(item), 0));
const totalTons = computed(() => (props.block.inventories.reduce((sum, item) => sum + Number(item.total_weight_kg || 0), 0) / 1000).toFixed(2));

nextTick(() => panel.value?.focus());
</script>
