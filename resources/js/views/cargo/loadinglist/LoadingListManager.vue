<!-- src/views/LoadingListManager.vue -->
<template>
  <div class="space-y-6">
    <!-- Action Bar Tray -->
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

    <!-- Master Layout Data Grid -->
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
   
    <!-- SPREADSHEET MATRIX PREVIEW MODAL -->
    <BaseModal :show="modal.viewOpen" title="Loading List Matrix" size="lg" @close="modal.viewOpen = false">
      <div v-if="activeManifest" class="space-y-4">
        <div class="flex justify-between items-center bg-slate-50 p-4 rounded-xl border border-slate-200">
          <div><strong>Container Unit:</strong> {{ activeManifest.container?.container_number }}</div>
          <div><strong>Loading Audit Date:</strong> {{ activeManifest.loading_date }}</div>
        </div>

        <!-- Horizontal Matrix Grid Spreadsheet Layout View -->
        <div class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full text-left text-xs border-collapse min-w-[1000px]">
            <thead class="bg-slate-100 font-bold border-b border-slate-200 text-slate-700">
              <tr>
                <th class="p-2 border-r border-slate-200">#</th>
                <th class="p-2 border-r border-slate-200 min-w-[180px]">Item Description</th>
                <th class="p-2 border-r border-slate-200">UOM</th>
                <th class="p-2 border-r border-slate-200">Total Qty</th>
                <!-- Mapped sector headers -->
                <th v-for="n in 12" :key="'a'+n" class="p-1 text-center font-mono bg-blue-50/40 border-r border-slate-200">A{{n}}</th>
                <th v-for="n in 12" :key="'h'+n" class="p-1 text-center font-mono bg-purple-50/40 border-r border-slate-200">H{{n}}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
              <tr v-for="(item, idx) in activeManifest.items" :key="item.id" class="hover:bg-slate-50/60">
                <td class="p-2 border-r border-slate-200 font-mono">{{ idx + 1 }}</td>
                <td class="p-2 border-r border-slate-200 font-semibold text-slate-800">{{ item.declared_item_name }}</td>
                <td class="p-2 border-r border-slate-200 font-mono text-center">{{ item.uom_code }}</td>
                <td class="p-2 border-r border-slate-200 font-bold text-center text-blue-600 bg-blue-50/20">{{ item.total_qty }}</td>
                
                <!-- Loop placeholder cells checking sector allocation distributions -->
                <td v-for="n in 12" :key="'acell'+n" class="p-1 text-center font-mono border-r border-slate-200 text-slate-400">
                  {{ item.sector_allocations?.['A'+n] || '-' }}
                </td>
                <td v-for="n in 12" :key="'hcell'+n" class="p-1 text-center font-mono border-r border-slate-200 text-slate-400">
                  {{ item.sector_allocations?.['H'+n] || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
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
    <!-- Inside src/views/admin/LoadingListManager.vue -->

    <!-- DYNAMIC WORKSHEET MANIFEST CREATION MODAL -->
    <BaseModal :show="modal.formOpen" title="Initialize Container Loading Worksheet Matrix" size="lg" @close="modal.formOpen = false">
      <div v-if="validationErrors.general" class="mb-4 rounded-xl bg-red-50 border border-red-200 p-3.5 text-xs font-semibold text-red-700">{{ validationErrors.general }}</div>
      
      <form @submit.prevent="submitLoadingForm" class="space-y-6">
        <!-- Section 1: Header Routing Details -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200/60">
          <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-slate-600">Ocean Voyage Track</label>
            <select v-model="form.voyage_id" required @change="handleVoyageChange" class="rounded-lg border border-slate-200 p-2 text-xs bg-white">
              <option value="">Select Scheduled Route</option>
              <option v-for="v in lookups.voyages" :key="v.id" :value="v.id">Voyage {{ v.voyage_number }} ({{ v.vessel?.name }})</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-slate-600">Container Asset Unit</label>
            <select v-model="form.container_id" required class="rounded-lg border border-slate-200 p-2 text-xs bg-white">
              <option value="">Select Container Target</option>
              <option v-for="c in lookups.containers" :key="c.id" :value="c.id">{{ c.container_number }} [{{ c.container_size }}]</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-slate-600">Loading Date</label>
            <input v-model="form.loading_date" type="date" required class="rounded-lg border border-slate-200 p-2 text-xs" />
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-bold text-slate-600">Loader Inspector Name</label>
            <input v-model="form.loader_name" type="text" placeholder="e.g. Depot Shift Supervisor" class="rounded-lg border border-slate-200 p-2 text-xs" />
          </div>
        </div>

        <!-- Section 2: Dynamic Row Append Spreadsheet Array Matrix -->
        <div class="space-y-3">
          <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <h4 class="text-sm font-bold text-slate-800">ワークシート Container Loading Items Matrix Log</h4>
            <button type="button" @click="appendBlankItemRow" class="text-xs font-bold text-[var(--brand-primary)] hover:underline">+ Append Cargo Line Item</button>
          </div>

          <div v-if="form.items.length === 0" class="text-center p-6 text-slate-400 italic text-xs border border-dashed border-slate-200 rounded-xl bg-slate-50/40">
            No rows attached yet. Click 'Append Cargo Line Item' above or upload a CSV spreadsheet matrix to generate rows automatically.
          </div>

          <!-- Dynamic Entry Matrix Block Rows -->
          <div v-for="(item, index) in form.items" :key="index" class="relative bg-white p-4 rounded-xl border border-slate-200 shadow-sm grid grid-cols-1 md:grid-cols-4 gap-4">
            <button type="button" @click="removeManifestRow(index)" class="absolute -top-2 -right-2 h-5 w-5 grid place-items-center bg-slate-100 border border-slate-200 rounded-full text-slate-500 hover:bg-red-50 hover:text-red-500 text-xs shadow-sm">&times;</button>
            
            <div class="flex flex-col gap-1 md:col-span-2">
              <label class="text-[10px] font-bold text-slate-400 uppercase">Product Identification Search</label>
              <select v-model="item.product_id" required @change="syncDeclaredName(index)" class="rounded-lg border border-slate-200 p-2 text-xs bg-white">
                <option value="">Select Master Catalog Item Match</option>
                <option v-for="p in lookups.products" :key="p.id" :value="p.id">{{ p.name }} (SKU: {{ p.sku }})</option>
              </select>
            </div>
            
            <div class="flex flex-col gap-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase">UOM Unit</label>
              <select v-model="item.uom_code" required class="rounded-lg border border-slate-200 p-2 text-xs bg-white">
                <option value="CTN">CTN (Carton)</option>
                <option value="BAG">BAG (Sack)</option>
                <option value="PCS">PCS (Pieces)</option>
                <option value="PLT">PLT (Pallet)</option>
              </select>
            </div>

            <div class="flex flex-col gap-1">
              <label class="text-[10px] font-bold text-slate-400 uppercase">Manifest Total Qty</label>
              <input v-model.number="item.total_qty" type="number" min="1" required class="rounded-lg border border-slate-200 p-2 text-xs font-bold text-blue-600 bg-blue-50/10 text-center" />
            </div>

            <!-- Dynamic Sectors Mapping Mini Spreadsheet Entry Block -->
            <div class="md:col-span-4 border-t border-slate-100 pt-3">
              <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1.5">Downstream Sub-Mark Sector Spacial Allocations (A1-A12 / H1-H12)</span>
              <div class="grid grid-cols-6 sm:grid-cols-8 md:grid-cols-12 gap-1.5">
                <div v-for="n in 12" :key="'formA'+n" class="flex flex-col items-center border border-slate-100 rounded bg-slate-50/40 p-1">
                  <span class="text-[9px] font-mono font-bold text-slate-400">A{{n}}</span>
                  <input v-model.number="item.sector_allocations['A'+n]" type="number" min="0" placeholder="0" class="w-full text-center font-mono text-[11px] bg-white border border-slate-200 rounded mt-0.5 p-0.5 focus:outline-none" />
                </div>
                <div v-for="n in 12" :key="'formH'+n" class="flex flex-col items-center border border-slate-100 rounded bg-slate-50/40 p-1">
                  <span class="text-[9px] font-mono font-bold text-slate-400">H{{n}}</span>
                  <input v-model.number="item.sector_allocations['H'+n]" type="number" min="0" placeholder="0" class="w-full text-center font-mono text-[11px] bg-white border border-slate-200 rounded mt-0.5 p-0.5 focus:outline-none" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Footer Bars -->
        <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
          <button type="button" @click="modal.formOpen = false" class="rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-200">Cancel</button>
          <button type="submit" :disabled="submitting" class="rounded-xl bg-[var(--brand-primary)] px-5 py-2 text-sm font-semibold text-white shadow-md">
            {{ submitting ? 'Processing Blueprint...' : 'Initialize Manifest List' }}
          </button>
        </div>
      </form>
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
// Inside src/views/admin/LoadingListManager.vue -> script setup block

const submitting = ref(false);
const validationErrors = ref({});

// Master configuration object managing creation arrays
const form = reactive({
  container_id: '',
  voyage_id: '',
  loading_date: '2026-11-09', // Current date placeholder matching timeline logs
  loader_name: '',
  items: []
});

// Options collections parameters
const lookups = reactive({
  voyages: [],
  containers: [],
  products: []
});

// Load catalog registers to fill out creation choice metrics
const fetchLookupDataPools = async () => {
  const [voyagesRes, containersRes, productsRes] = await Promise.all([
    api.get('/admin/voyages', { params: { paginate: 0 } }),
    api.get('/admin/containers', { params: { paginate: 0 } }),
    api.get('/admin/products', { params: { paginate: 0 } })
  ]);
  lookups.voyages = voyagesRes.data.data || voyagesRes.data;
  lookups.containers = containersRes.data.data || containersRes.data;
  lookups.products = productsRes.data.data || productsRes.data;
};

// Open creation dashboard panel interface clear memory
const openCreateModal = () => {
  validationErrors.value = {};
  modal.formOpen = true;
  Object.assign(form, {
    container_id: '',
    voyage_id: '',
    loading_date: '2026-11-09',
    loader_name: '',
    items: []
  });
  appendBlankItemRow(); // Initial default item row
};

const appendBlankItemRow = () => {
  const allocations = {};
  for(let i = 1; i <= 12; i++) {
    allocations['A' + i] = '';
    allocations['H' + i] = '';
  }
  form.items.push({
    product_id: '',
    declared_item_name: '',
    uom_code: 'CTN',
    total_qty: 1,
    sector_allocations: allocations
  });
};

const removeManifestRow = (index) => {
  form.items.splice(index, 1);
};

const syncDeclaredName = (index) => {
  const selectedProduct = lookups.products.find(p => p.id === form.items[index].product_id);
  if (selectedProduct) {
    form.items[index].declared_item_name = selectedProduct.name;
  }
};

// Handle route filtering criteria constraints upon selecting a voyage
const handleVoyageChange = async () => {
  if (!form.voyage_id) return;
  // Dynamic query pre-filtering available container hulls linked to that exact route selection
  const response = await api.get('/admin/containers', { params: { voyage_id: form.voyage_id, paginate: 0 } });
  lookups.containers = response.data.data || response.data;
};

const submitLoadingForm = async () => {
  submitting.value = true;
  validationErrors.value = {};
  
  // Sanitize empty sector values into numeric format before transmitting payload files
  const cleanItems = form.items.map(item => {
    const cleanedAllocations = {};
    Object.entries(item.sector_allocations).forEach(([key, val]) => {
      if (val !== '' && val !== null) {
        cleanedAllocations[key] = Number(val);
      }
    });
    return { ...item, sector_allocations: cleanedAllocations };
  });

  try {
    await api.post('/admin/loading-lists', {
      ...form,
      items: cleanItems
    });
    modal.formOpen = false;
    fetchLoadingLists(); // Refresh grid layout rows
  } catch (err) {
    if (err.response && err.response.status === 422) {
      validationErrors.value = err.response.data.errors || {};
    } else {
      validationErrors.value = { general: 'Critical error submitting spreadsheet matrix validation fields.' };
    }
  } finally {
    submitting.value = false;
  }
};



const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { fetchLoadingLists(); }, 350);
};


const openViewModal = async (list) => {
  const response = await api.get(`/loading-lists/${list.id}`);
  activeManifest.value = response.data;
  modal.viewOpen = true;
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

onMounted(() => { 
  fetchLoadingLists(); 
  fetchLookupDataPools();
});
</script>
