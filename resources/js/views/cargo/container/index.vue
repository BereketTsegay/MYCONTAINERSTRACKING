<!-- src/views/ContainerManager.vue -->
<template>
  <div class="container-workspace">
    <div class="action-bar">
      <div class="action-bar-extended">
      <!-- Multi-Axis Filter Grid Tray -->
      <div class="filter-tray">
        <input
          v-model="filters.search"
          @input="debounceSearch"
          type="text"
          placeholder="🔍 Search Container #, Booking..."
          class="input-search"
        />

        <select v-model="filters.status" @change="fetchContainers" class="select-filter">
          <option value="">All Statuses</option>
          <option value="empty">Empty</option>
          <option value="loaded">Loaded</option>
          <option value="in_transit">In Transit</option>
          <option value="discharged">Discharged</option>
        </select>

        <select v-model="filters.container_size" @change="fetchContainers" class="select-filter">
          <option value="">All Sizes</option>
          <option value="20ft">20ft</option>
          <option value="40ft">40ft</option>
          <option value="40ft HC">40ft HC</option>
          <option value="45ft">45ft</option>
        </select>

        <select v-model="filters.voyage_id" @change="fetchContainers" class="select-filter">
          <option value="">All Voyages</option>
          <option v-for="v in lookups.voyages" :key="v.id" :value="v.id">Voyage {{ v.voyage_number }}</option>
        </select>

        <select v-model="filters.shipper_id" @change="fetchContainers" class="select-filter">
          <option value="">All Shippers</option>
          <option v-for="s in lookups.shippers" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>

        <select v-model="filters.shipping_line_id" @change="fetchContainers" class="select-filter">
          <option value="">All Shipping Lines</option>
          <option v-for="l in lookups.shippingLines" :key="l.id" :value="l.id">{{ l.name }}</option>
        </select>

        <button @click="resetFilters" class="btn-clear-filters">Reset</button>
      </div>

      <button @click="openCreateModal" class="btn-add">+ Register Container</button>
    </div>

    </div>

    <!-- Data Presentation Layout Table -->
    <div class="table-wrapper">
      <table class="modern-table">
        <thead>
          <tr>
            <th>Container Specs</th>
            <th>Booking & Seal Codes</th>
            <th>Relational Fleet Map</th>
            <th>Status</th>
            <th class="text-right">Operations</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="box in containers" :key="box.id">
            <td>
              <div class="font-bold text-slate">{{ box.container_number || 'UNASSIGNED-UNIT' }}</div>
              <div class="text-sub">{{ box.container_size }} • {{ box.container_type }} • {{ box.container_name || 'N/A' }}</div>
            </td>
            <td>
              <div><strong>B/N:</strong> {{ box.booking_number || 'N/A' }}</div>
              <div class="text-sub"><strong>Seal:</strong> {{ box.seal_number || 'N/A' }}</div>
            </td>
            <td>
              <div class="relation-item">🚢 Voyage: {{ box.voyage ? box.voyage.voyage_number : 'N/A' }}</div>
              <div class="relation-item">📦 Batch: {{ box.batch ? box.batch.name : 'N/A' }}</div>
            </td>
            <td>
              <span :class="['status-pill', `pill-${box.status}`]">{{ box.status.replace('_', ' ') }}</span>
            </td>
            <td class="text-right actions-cell">
              <button @click="openViewModal(box)" class="btn-action view">👁️ View</button>
              <button @click="openEditModal(box)" class="btn-action edit">✏️ Edit</button>
              <button @click="deleteContainer(box.id)" class="btn-action delete">🗑️ Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- CREATE & UPDATE INTERACTIVE FORM MODAL -->
    <BaseModal :show="modal.formOpen" :title="modal.isEdit ? 'Modify Container Manifest' : 'Provision Cargo Unit'" size="lg" @close="modal.formOpen = false">
      <div v-if="validationErrors.general" class="form-error-banner">{{ validationErrors.general }}</div>

      <form @submit.prevent="saveForm" class="grid-form">
        <div class="field" :class="{ 'has-error': validationErrors.container_number }">
          <label>Container Number</label>
          <input v-model="form.container_number" type="text" placeholder="e.g. MSKU1234567" />
          <span v-if="validationErrors.container_number" class="error-msg">{{ validationErrors.container_number }}</span>
        </div>

        <div class="field">
          <label>Container Name/Nickname</label>
          <input v-model="form.container_name" type="text" placeholder="e.g. Chemicals Unit A" />
        </div>

        <div class="field">
          <label>Size Specification</label>
          <select v-model="form.container_size">
            <option value="20ft">20ft</option>
            <option value="40ft">40ft</option>
            <option value="40ft HC">40ft HC</option>
            <option value="45ft">45ft</option>
          </select>
        </div>

        <div class="field">
          <label>Ownership Type</label>
          <select v-model="form.container_type">
            <option value="SOC">SOC (Shipper Owned)</option>
            <option value="COC">COC (Carrier Owned)</option>
          </select>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.booking_number }">
          <label>Booking Number</label>
          <input v-model="form.booking_number" type="text" />
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.seal_number }">
          <label>Seal Number</label>
          <input v-model="form.seal_number" type="text" />
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.voyage_id }">
          <label>Linked Voyage Tracker</label>
          <select v-model="form.voyage_id" required>
            <option value="">Select Associated Voyage</option>
            <option v-for="v in lookups.voyages" :key="v.id" :value="v.id">Voyage {{ v.voyage_number }} ({{ v.vessel?.name }})</option>
          </select>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.batch_id }">
          <label>Linked Production Batch</label>
          <select v-model="form.batch_id" required>
            <option value="">Select Associated Batch</option>
            <option v-for="b in lookups.batches" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.transitor_id }">
          <label>Assigned Transitor Party</label>
          <select v-model="form.transitor_id" required>
            <option value="">Select Transitor</option>
            <option v-for="p in lookups.transitors" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <span v-if="validationErrors.transitor_id" class="error-msg">{{ validationErrors.transitor_id }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.shipper_id }">
          <label>Assigned Shipper Party</label>
          <select v-model="form.shipper_id" required>
            <option value="">Select Shipper</option>
            <option v-for="s in lookups.shippers" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
          <span v-if="validationErrors.shipper_id" class="error-msg">{{ validationErrors.shipper_id }}</span>
        </div>

        <div class="field">
          <label>VGM Weight (KG)</label>
          <input v-model="form.vgm" type="number" step="0.01" />
        </div>

        <div class="field">
          <label>Tare Weight (KG)</label>
          <input v-model="form.tare_weight" type="number" step="0.01" />
        </div>

        <div class="field">
          <label>Max Gross Weight (KG)</label>
          <input v-model="form.max_gross_weight" type="number" step="0.01" />
        </div>

        <div class="field">
          <label>Total Packages (PKG Count)</label>
          <input v-model="form.total_pkg" type="number" />
        </div>

        <div class="field">
          <label>Inventory Workflow Status</label>
          <select v-model="form.status">
            <option value="empty">Empty</option>
            <option value="loaded">Loaded</option>
            <option value="in_transit">In Transit</option>
            <option value="discharged">Discharged</option>
          </select>
        </div>

        <div class="form-actions full-width">
          <button type="button" @click="modal.formOpen = false" class="btn-cancel">Cancel</button>
          <button type="submit" class="btn-submit">Commit Log</button>
        </div>
      </form>
    </BaseModal>

    <!-- FULL DETAILED VIEW BLUEPRINT VIEW -->
    <BaseModal :show="modal.viewOpen" title="Integrated Cargo Container Metrics" size="lg" @close="modal.viewOpen = false">
      <div v-if="selectedContainer" class="detail-card">
        <div class="detail-header">
          <h4>Unit ID: {{ selectedContainer.container_number || 'UNASSIGNED SERIAL' }}</h4>
          <span :class="['status-pill', `pill-${selectedContainer.status}`]">{{ selectedContainer.status }}</span>
        </div>
        <div class="detail-grid">
          <div><strong>Size Configuration:</strong> {{ selectedContainer.container_size }} ({{ selectedContainer.container_type }})</div>
          <div><strong>Container Descriptor Name:</strong> {{ selectedContainer.container_name || 'N/A' }}</div>
          <div><strong>Booking Registration Code:</strong> {{ selectedContainer.booking_number || 'N/A' }}</div>
          <div><strong>Customs Seal Track Signature:</strong> {{ selectedContainer.seal_number || 'N/A' }}</div>
          <div><strong>VGM Verified Weight:</strong> {{ selectedContainer.vgm || '0.00' }} KG</div>
          <div><strong>Tare Structural Mass:</strong> {{ selectedContainer.tare_weight }} KG</div>
          <div><strong>Max Cargo Load Limit:</strong> {{ selectedContainer.max_gross_weight }} KG</div>
          <div><strong>Internal Loaded Item Count:</strong> {{ selectedContainer.total_pkg }} Packages</div>

          <div class="full relational-overview-box">
            <h5>🔗 Connected Operational Mappings</h5>
            <p><strong>Associated Ocean Transit:</strong> Voyage {{ selectedContainer.voyage?.voyage_number }} via Hull Carrier [{{ selectedContainer.voyage?.vessel?.name || 'N/A' }}]</p>
            <p><strong>Parent Cargo Batch Node:</strong> Batch Profile {{ selectedContainer.batch?.name }}</p>
            <p><strong>Accountability Broker (Transitor):</strong> 🏢 {{ selectedContainer.transitor?.name || 'N/A' }}</p>
            <p><strong>Accountability Supplier (Shipper):</strong> 🏢 {{ selectedContainer.shipper?.name || 'N/A' }}</p>
          </div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../../api/axios';
import BaseModal from '../../../componets/shared/BaseModal.vue';

const containers = ref([]);
const selectedContainer = ref(null);
let searchTimeout = null;

const filters = reactive({
  search: '',
  status: '',
  container_size: '',
  voyage_id: '',
  shipper_id: '',
  shipping_line_id: ''
});

const validationErrors = ref({});
const modal = reactive({ formOpen: false, viewOpen: false, isEdit: false, currentId: null });
const form = reactive({
container_number: '', container_name: '', container_size: '40ft HC', container_type: 'SOC',
booking_number: '', seal_number: '', voyage_id: '', transitor_id: '', shipper_id: '',
batch_id: '', vgm: '', tare_weight: 3900, max_gross_weight: 32500, total_pkg: 950, status: 'empty'
});
const lookups = reactive({
  voyages: [],
  batches: [],
  transitors: [],
  shippers: [],
  shippingLines: []
});
const fetchContainers = async () => {
  const response = await api.get('/containers', { params: filters });
  containers.value = response.data.data;
};
const debounceSearch = () => {
clearTimeout(searchTimeout);
searchTimeout = setTimeout(() => { fetchContainers(); }, 350);
};
const fetchLookupDatasets = async () => {
  const [vRes, bRes, tRes, sRes, lRes] = await Promise.all([
    api.get('/voyages', { params: { paginate: 0 } }),
    api.get('/batches', { params: { paginate: 0 } }),
    api.get('/parties', { params: { type: 'transitor', paginate: 0 } }),
    api.get('/parties', { params: { type: 'shipper', paginate: 0 } }),
    api.get('/parties', { params: { type: 'shipping_line', paginate: 0 } }) // New lookup fetch
  ]);
  lookups.voyages = vRes.data.data || vRes.data;
  lookups.batches = bRes.data.data || bRes.data;
  lookups.transitors = tRes.data.data || tRes.data;
  lookups.shippers = sRes.data.data || sRes.data;
  lookups.shippingLines = lRes.data.data || lRes.data;
};


const resetFilters = () => {
  filters.search = '';
  filters.status = '';
  filters.container_size = '';
  filters.voyage_id = '';
  filters.shipper_id = '';
  filters.shipping_line_id = '';
  fetchContainers();
};
const resetValidation = () => { validationErrors.value = {}; };
const openCreateModal = () => {
resetValidation();
modal.isEdit = false; modal.currentId = null; modal.formOpen = true;
Object.assign(form, { container_number: '', container_name: '', container_size: '40ft HC', container_type: 'SOC', booking_number: '', seal_number: '', voyage_id: '', transitor_id: '', shipper_id: '', batch_id: '', vgm: '', tare_weight: 3900, max_gross_weight: 32500, total_pkg: 950, status: 'empty' });
};
const openEditModal = (box) => {
resetValidation();
modal.isEdit = true; modal.currentId = box.id; modal.formOpen = true;
Object.assign(form, box);
};
const openViewModal = (box) => { selectedContainer.value = box; modal.viewOpen = true; };
const saveForm = async () => {
resetValidation();
try {
if (modal.isEdit) {
await api.put(`/containers/${modal.currentId}`, form);
} else {
await api.post('/containers', form);
}
modal.formOpen = false;
fetchContainers();
} catch (err) {
if (err.response && err.response.status === 422) {
validationErrors.value = err.response.data.errors || {};
} else {
validationErrors.value = { general: 'Critical systemic exception encountered writing entity logs.' };
}
}
};
const deleteContainer = async (id) => {
if (confirm('Permanently de-provision this shipping container matrix asset?')) {
await api.delete(`/containers/${id}`);
fetchContainers();
}
};
onMounted(() => {
fetchContainers();
fetchLookupDatasets();
});
</script>
<style scoped>

/* Inside src/views/admin/ContainerManager.vue -> style section */
.action-bar-extended { display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; }
@media(min-width: 1200px) {
  .action-bar-extended { flex-direction: row; justify-content: space-between; align-items: start; }
}
.filter-tray { display: flex; flex-wrap: wrap; gap: 0.5rem; flex: 1; max-width: 1000px; }
.input-search { flex: 2; min-width: 220px; padding: 0.65rem 1rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; }
.select-filter { flex: 1; min-width: 145px; padding: 0.65rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; background-color: #fff; color: #334155; }
.btn-clear-filters { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 0.65rem 1rem; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 0.9rem; }
.btn-clear-filters:hover { background: #e2e8f0; }
.btn-add { background: #2563eb; color: white; border: none; padding: 0.65rem 1.25rem; border-radius: 8px; cursor: pointer; font-weight: 500; white-space: nowrap; }

.container-workspace {
  color: var(--brand-text);
  font-family: var(--font-sans);
  padding: 0.5rem 0 1rem;
}

.action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  gap: 1rem;
  background: linear-gradient(135deg, rgba(212, 232, 107, 0.12), rgba(255, 255, 255, 0.96));
  border: 1px solid rgba(23, 61, 50, 0.08);
  border-radius: 18px;
  padding: 1rem 1.25rem;
  box-shadow: 0 12px 28px var(--brand-shadow);
}

.search-filters {
  display: flex;
  gap: 0.75rem;
  flex: 1;
  max-width: 620px;
}

.input-search,
.select-filter,
.field input,
.field select {
  padding: 0.8rem 0.95rem;
  border: 1px solid var(--brand-border);
  border-radius: 12px;
  font-size: 0.92rem;
  background: var(--brand-surface);
  color: var(--brand-text);
  box-sizing: border-box;
  transition: all 0.2s ease;
  box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
}

.input-search {
  flex: 2;
}

.select-filter {
  flex: 1;
  background-color: var(--brand-surface);
}

.input-search:focus,
.select-filter:focus,
.field input:focus,
.field select:focus {
  outline: none;
  border-color: var(--brand-primary-strong);
  box-shadow: 0 0 0 4px rgba(23, 61, 50, 0.08);
}

.btn-add,
.btn-submit {
  background: linear-gradient(135deg, var(--brand-primary), var(--brand-primary-strong));
  color: white;
  border: none;
  padding: 0.72rem 1.25rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
  box-shadow: 0 10px 18px rgba(23, 61, 50, 0.12);
}

.btn-add:hover,
.btn-submit:hover {
  transform: translateY(-1px);
}

.table-wrapper {
  background: var(--brand-surface);
  border: 1px solid var(--brand-border);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 16px 30px rgba(23, 61, 50, 0.08);
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.95rem;
}

.modern-table th {
  background: rgba(23, 61, 50, 0.03);
  padding: 1rem 1.25rem;
  font-weight: 700;
  color: var(--brand-primary-strong);
  border-bottom: 1px solid var(--brand-border);
}

.modern-table td {
  padding: 1.2rem 1.25rem;
  border-bottom: 1px solid #edf2ed;
  vertical-align: middle;
}

.modern-table tbody tr:hover {
  background: rgba(23, 61, 50, 0.02);
}

.text-sub {
  font-size: 0.8rem;
  color: var(--brand-muted);
  margin-top: 0.25rem;
}

.font-bold {
  font-weight: 700;
  color: var(--brand-text);
}

.relation-item {
  font-size: 0.78rem;
  background: rgba(23, 61, 50, 0.03);
  padding: 0.26rem 0.55rem;
  border: 1px solid var(--brand-border);
  border-radius: 999px;
  margin-top: 0.3rem;
  font-weight: 600;
  color: var(--brand-primary-strong);
  display: inline-block;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.38rem 0.72rem;
  border-radius: 999px;
  font-size: 0.75rem;
  font-weight: 700;
  border: 1px solid transparent;
  text-transform: capitalize;
}

.pill-empty {
  background: rgba(148, 163, 184, 0.16);
  color: #475569;
  border-color: rgba(148, 163, 184, 0.25);
}

.pill-loaded {
  background: rgba(59, 130, 246, 0.12);
  color: #1d4ed8;
  border-color: rgba(59, 130, 246, 0.16);
}

.pill-in_transit {
  background: rgba(212, 232, 107, 0.2);
  color: var(--brand-primary-strong);
  border-color: rgba(212, 232, 107, 0.32);
}

.pill-discharged {
  background: rgba(31, 122, 88, 0.12);
  color: var(--brand-success);
  border-color: rgba(31, 122, 88, 0.2);
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
  align-items: center;
}

.btn-action {
  border: 1px solid var(--brand-border);
  background: var(--brand-surface-soft);
  color: var(--brand-text);
  padding: 0.45rem 0.8rem;
  border-radius: 9px;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 600;
  transition: all 0.15s ease;
}

.btn-action:hover {
  transform: translateY(-1px);
}

.btn-action.view:hover {
  background: rgba(23, 61, 50, 0.04);
}

.btn-action.edit:hover {
  background: rgba(212, 232, 107, 0.18);
}

.btn-action.delete:hover {
  background: rgba(239, 68, 68, 0.05);
  color: #b91c1c;
  border-color: rgba(239, 68, 68, 0.2);
}

.grid-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.2rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
  position: relative;
  padding-bottom: 1.1rem;
}

.field.full-width {
  grid-column: span 2;
}

.field label {
  font-size: 0.82rem;
  letter-spacing: 0.01em;
  font-weight: 700;
  color: var(--brand-primary-strong);
}

.field.has-error input,
.field.has-error select {
  border-color: rgba(220, 38, 38, 0.5);
  background-color: rgba(254, 242, 242, 0.7);
}

.error-msg {
  font-size: 0.75rem;
  color: #dc2626;
  font-weight: 600;
  position: absolute;
  bottom: -3px;
  left: 0;
}

.form-error-banner {
  background: rgba(239, 68, 68, 0.08);
  border: 1px solid rgba(239, 68, 68, 0.15);
  padding: 0.8rem 1rem;
  border-radius: 10px;
  color: #b91c1c;
  margin-bottom: 1rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  grid-column: span 2;
  margin-top: 0.2rem;
}

.btn-cancel {
  background: var(--brand-surface-soft);
  color: var(--brand-text);
  border: 1px solid var(--brand-border);
  padding: 0.72rem 1.15rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
}

.detail-card {
  background: var(--brand-surface-soft);
  border: 1px solid var(--brand-border);
  border-radius: 14px;
  padding: 1rem;
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.5rem;
}

.detail-header h4 {
  margin: 0;
  font-size: 1.2rem;
  color: var(--brand-primary-strong);
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  background: rgba(23, 61, 50, 0.02);
  padding: 1.1rem 1rem;
  border-radius: 12px;
  border: 1px solid var(--brand-border);
  margin-top: 1rem;
  font-size: 0.95rem;
}

.relational-overview-box {
  grid-column: span 2;
  border-top: 1px dashed rgba(23, 61, 50, 0.18);
  padding-top: 1rem;
  margin-top: 0.5rem;
}

.relational-overview-box h5 {
  margin: 0 0 0.5rem 0;
  font-size: 0.96rem;
  font-weight: 700;
  color: var(--brand-primary-strong);
}

.relational-overview-box p {
  margin: 0.35rem 0;
  color: var(--brand-text);
}

@media (max-width: 768px) {
  .action-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-filters,
  .grid-form,
  .detail-grid {
    grid-template-columns: 1fr;
    flex-direction: column;
  }

  .form-actions,
  .relational-overview-box {
    grid-column: span 1;
  }

  .actions-cell {
    justify-content: flex-start;
    flex-wrap: wrap;
  }
}
</style>
