<!-- src/views/LoadingListManager.vue (Fully Extended Blueprint Wrapper) -->
<template>
  <div class="space-y-6">
    <!-- Updated Filter Tray supporting Raw CSV Upload Stream Handlers -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
      <div class="flex items-center gap-3">
        <input v-model="filters.search" @input="debounceSearch" type="text" placeholder="🔍 Search Loading Lists..." class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:outline-none" />
        
        <!-- Inline Upload Control Module Hook -->
        <label class="cursor-pointer rounded-xl bg-slate-100 border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-200 transition">
          📂 Import Sheet (CSV Matrix)
          <input type="file" ref="fileInput" class="hidden" accept=".csv" @change="handleCSVUploadStream" />
        </label>
      </div>
      <button @click="openCreateModal" class="rounded-xl bg-[var(--brand-primary)] px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-500/10 hover:opacity-95 transition">+ New Loading Manifest</button>
    </div>

    <!-- Master Layout Data Grid Table -->
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
          <tr>
            <th class="px-6 py-4">Container Reference</th>
            <th class="px-6 py-4">Loading Logistics Parameters</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4 text-right">Operations & Automations</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="list in loadingLists" :key="list.id" class="hover:bg-slate-50/45 transition">
            <td class="px-6 py-4 font-mono font-bold text-slate-900">📥 {{ list.container?.container_number || 'TBD' }}</td>
            <td class="px-6 py-4">
              <div class="font-medium text-slate-700">Loader: {{ list.loader_name || 'System Guest' }}</div>
              <div class="text-xs text-slate-400 mt-0.5">Date: {{ list.loading_date }} • Voyage: {{ list.voyage?.voyage_number }}</div>
            </td>
            <td class="px-6 py-4">
              <span :class="list.document_status === 'compiled' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100'" class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border">
                {{ list.document_status.toUpperCase() }}
              </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button v-if="list.document_status !== 'compiled'" @click="compileManifestDocuments(list.id)" class="rounded-lg bg-indigo-50 border border-indigo-100 px-3 py-1.5 text-xs font-bold text-indigo-600 hover:bg-indigo-100 transition">⚙️ Compile Downstream HBL & Invoices</button>
              <button v-else @click="triggerPrintInvoiceOverlay(list)" class="rounded-lg bg-emerald-50 border border-emerald-100 px-3 py-1.5 text-xs font-bold text-emerald-600 hover:bg-emerald-100 transition">🖨️ Print House Bill (HBL)</button>
              <button @click="openViewModal(list)" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">Review Grid</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- RESIDUAL VIEW MODAL ACCORDION SPREADSHEET (MAPPED IN PREVIOUS STEPS) -->
    <BaseModal :show="modal.viewOpen" title="Loading List Matrix" size="lg" @close="modal.viewOpen = false">
      <!-- spreadsheet table layouts stay contextually active here -->
    </BaseModal>

    <!-- PRINT LAYOUT OVERLAY MODAL WINDOW -->
    <BaseModal :show="modal.printOpen" title="Corporate Bill of Lading Manifest Voucher" size="lg" @close="modal.printOpen = false">
      <div class="flex justify-end mb-4 no-print">
        <button @click="executeBrowserPrintCommand" class="bg-blue-600 text-white font-semibold text-xs rounded-lg px-4 py-2 shadow hover:bg-blue-700">⚡ Send directly to System Printer</button>
      </div>

      <!-- Printable Corporate Document Card Envelope -->
      <div id="printable-hbl-sheet" class="print-preview-container p-8 bg-white border border-slate-300 font-serif text-slate-900 leading-normal max-w-[800px] mx-auto text-xs">
        <div class="flex justify-between items-start border-b-2 border-slate-900 pb-4">
          <div>
            <h2 class="text-xl font-black tracking-tight uppercase text-blue-900">HANSOOM GENERAL TRADING FZE LLC</h2>
            <p class="mt-1 font-sans text-[10px] text-slate-500">FLAMINGO VILLAS AJMAN, UAE • TEL +971588537846</p>
          </div>
          <div class="text-right font-sans">
            <div class="text-sm font-bold bg-slate-900 text-white px-2 py-0.5 rounded font-mono">HOUSE BILL OF LADING</div>
            <div class="mt-2">HBL NO: <span class="font-mono font-bold text-red-600">HS24982</span></div>
          </div>
        </div>

        <div class="grid grid-cols-2 border-b border-slate-400 font-sans">
          <div class="p-3 border-r border-slate-400">
            <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-wide">Shipper / Exporter</span>
            <strong class="block mt-1 text-slate-800">HANSOOM GENERAL TRADING FZE LLC</strong>
          </div>
          <div class="p-3">
            <span class="block text-[9px] font-bold text-slate-500 uppercase tracking-wide">Consignee Identity</span>
            <strong class="block mt-1 text-slate-800">ASMARA, ERITREA (C/O M1)</strong>
          </div>
        </div>

        <div class="grid grid-cols-4 bg-slate-50 border-b border-slate-400 font-sans text-center text-[10px] py-2 font-semibold">
          <div>Vessel Carrier: <p class="font-bold text-slate-800">AFRICA SUN</p></div>
          <div>Port of Loading: <p class="font-bold text-slate-800">JEBEL ALI</p></div>
          <div>Port of Discharge: <p class="font-bold text-slate-800">MASSAWA CY</p></div>
          <div>Container Reg: <p class="font-bold text-slate-800 font-mono">CMAU4467256</p></div>
        </div>

        <div class="my-6">
          <h4 class="font-sans font-bold text-[11px] border-b border-slate-900 pb-1 mb-2 uppercase tracking-wide text-slate-700">Cargo Particulars Breakdown Manifest</h4>
          <div class="space-y-1 font-mono text-[11px] text-slate-800 pl-2">
            <div>• 52 PKG FOOD STUFFS INVENTORY ASSIGNED SECTORS</div>
            <div>• 56 PKG PERSONAL EFFECTS AND HOUSEHOLD APPLIANCES</div>
            <div>• 1 PCS TELEVISION STORAGE UNIT BLOCKS</div>
            <div>• 1 PCS REFRIGERATOR SYSTEM CARRIER</div>
          </div>
        </div>

        <div class="mt-8 pt-4 border-t border-dashed border-slate-400 grid grid-cols-2 font-sans items-center text-[10px]">
          <div>
            <div class="inline-block bg-emerald-100 text-emerald-800 font-bold px-3 py-1 rounded text-xs tracking-wider uppercase">FREIGHT PRE-PAID</div>
          </div>
          <div class="text-right text-slate-400 italic">Issued under authorized clearance seals as single master logistics operator manifest voucher logs.</div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../../api/axios';
import BaseModal from '../../../componets/shared/BaseModal.vue';

const loadingLists = ref([]);
const activeManifest = ref(null);
const fileInput = ref(null);
let searchTimeout = null;

const filters = reactive({ search: '' });
const modal = reactive({ viewOpen: false, printOpen: false });

const fetchLoadingLists = async () => {
  const response = await api.get('/loading-lists', { params: filters });
  loadingLists.value = response.data.data;
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { fetchLoadingLists(); }, 350);
};

// Handle Stream parsing action trigger
const handleCSVUploadStream = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('csv_file', file);
  // Default connection parameters to hook files into system database frameworks
  formData.append('container_id', 1); 
  formData.append('voyage_id', 1);

  try {
    await api.post('/loading-lists/upload-csv', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    alert('Spreadsheet matrix log parsed successfully.');
    fetchLoadingLists();
  } catch (err) {
    alert('Parser configuration failed reading columns.');
  } finally {
    fileInput.value.value = ''; // Flush input file buffer tracks
  }
};

const triggerPrintInvoiceOverlay = (list) => {
  activeManifest.value = list;
  modal.printOpen = true;
};

const executeBrowserPrintCommand = () => {
  const printContent = document.getElementById('printable-hbl-sheet').innerHTML;
  const originalContent = document.body.innerHTML;

  document.body.innerHTML = printContent;
  window.print();
  document.body.innerHTML = originalContent;
  window.location.reload(); // Re-trigger hydration pools cleanly post routing shift
};

const compileManifestDocuments = async (id) => {
  try {
    await api.post(`/loading-lists/${id}/generate`);
    alert('Downstream documents safely compiled.');
    fetchLoadingLists();
  } catch (err) {
    alert('Compilation collision.');
  }
};

onMounted(() => { fetchLoadingLists(); });
</script>

<style scoped>
@media print {
  .no-print { display: none !important; }
  body { background: white; color: black; }
.print-preview-container { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; max-width: 100% !important; }
}
</style>