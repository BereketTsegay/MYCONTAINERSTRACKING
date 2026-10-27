<!-- src/views/VoyageManager.vue -->
<template>
  <div class="voyage-container">
  <div class="action-bar-extended">
      <!-- Grid Filter Block Container -->
      <div class="filter-tray">
        <input
          v-model="filters.search"
          @input="debounceSearch"
          type="text"
          placeholder="🔍 Search Voyage #, Ports..."
          class="input-search"
        />

        <select v-model="filters.vessel_id" @change="fetchVoyages" class="select-filter">
          <option value="">All Vessels</option>
          <option v-for="v in lookups.vessels" :key="v.id" :value="v.id">{{ v.name }}</option>
        </select>

        <select v-model="filters.batch_id" @change="fetchVoyages" class="select-filter">
          <option value="">All Batches</option>
          <option v-for="b in lookups.batches" :key="b.id" :value="b.id">{{ b.name }}</option>
        </select>

        <select v-model="filters.status" @change="fetchVoyages" class="select-filter">
          <option value="">All Statuses</option>
          <option value="pending">⏳ Pending (Future)</option>
          <option value="transit">🚢 In Transit (Active)</option>
          <option value="completed">✅ Arrived (Past)</option>
        </select>

        <button @click="resetFilters" class="btn-clear-filters">Reset</button>
      </div>

      <button @click="openCreateModal" class="btn-add">+ Schedule Voyage</button>
    </div>


    <!-- Data Presentation Table -->
    <div class="table-wrapper">
      <table class="modern-table">
        <thead>
          <tr>
            <th>Voyage Info & Vessel</th>
            <th>Route Parameters</th>
            <th>Assigned Context (Line / Batch)</th>
            <th>status</th>
            <th class="text-right">Operations</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="voyage in voyages" :key="voyage.id">
            <td>
              <div class="font-bold text-slate">🚢 {{ voyage.vessel ? voyage.vessel.name : 'Unknown Ship' }}</div>
              <div class="text-sub">Voyage No: <strong>{{ voyage.voyage_number || 'N/A' }}</strong></div>
            </td>
            <td>
              <div>🛫 <strong>{{ voyage.origin_port || 'N/A' }}</strong> ➔ 🛬 <strong>{{ voyage.destination_port || 'N/A' }}</strong></div>
              <div class="text-sub">📅 {{ voyage.departure_date || 'N/A' }} to {{ voyage.arrival_date || 'N/A' }}</div>
            </td>
            <td>
              <div class="relation-chip text-blue">🏢 Line: {{ voyage.party ? voyage.party.name : 'None Assigned' }}</div>
              <div class="relation-chip text-purple">📦 Batch: {{ voyage.batch ? voyage.batch.name : 'None Assigned' }}</div>
            </td>
               <td>
                <span :class="['status-pill', getStatusClass(voyage)]">
                    {{ getStatusLabel(voyage) }}
                </span>
            </td>
            <td class="text-right actions-cell">
              <button @click="openViewModal(voyage)" class="btn-action view">👁️ View</button>
              <button @click="openEditModal(voyage)" class="btn-action edit">✏️ Edit</button>
              <button @click="deleteVoyage(voyage.id)" class="btn-action delete">🗑️ Delete</button>
            </td>
          </tr>
          <tr v-if="voyages.length === 0">
            <td colspan="4" class="text-center empty-state">No matching registered transit voyages cataloged.</td>
          </tr>

        </tbody>
      </table>
    </div>

    <!-- CREATE & EDIT ROUTE MODAL WINDOW LAYER -->
    <BaseModal :show="modal.formOpen" :title="modal.isEdit ? 'Modify Voyage Manifest' : 'Schedule New Voyage Route'" size="lg" @close="modal.formOpen = false">
      <div v-if="validationErrors.general" class="form-error-banner">{{ validationErrors.general }}</div>

      <form @submit.prevent="saveForm" class="grid-form">
        <div class="field" :class="{ 'has-error': validationErrors.vessel_id }">
          <label>Assign Vessel/Ship</label>
          <select v-model="form.vessel_id" required>
            <option value="">Select Target Vessel</option>
            <option v-for="v in lookups.vessels" :key="v.id" :value="v.id">{{ v.name }}</option>
          </select>
          <span v-if="validationErrors.vessel_id" class="error-msg">{{ validationErrors.vessel_id }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.voyage_number }">
          <label>Voyage Number Identifier</label>
          <input v-model="form.voyage_number" type="text" placeholder="e.g. V-2026-X45" />
          <span v-if="validationErrors.voyage_number" class="error-msg">{{ validationErrors.voyage_number }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.party_id }">
          <label>Shipping Line Agent (Party Filtered)</label>
          <select v-model="form.party_id">
            <option value="">Select Shipping Line</option>
            <option v-for="p in lookups.shippingLines" :key="p.id" :value="p.id">{{ p.name }}</option>
          </select>
          <span v-if="validationErrors.party_id" class="error-msg">{{ validationErrors.party_id }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.batch_id }">
          <label>Link Production Batch Allocation</label>
          <select v-model="form.batch_id">
            <option value="">No Batch Allocation Binding</option>
            <option v-for="b in lookups.batches" :key="b.id" :value="b.id">{{ b.name }}</option>
          </select>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.departure_date }">
          <label>Departure Date</label>
          <input v-model="form.departure_date" type="date" />
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.arrival_date }">
          <label>Arrival Date</label>
          <input v-model="form.arrival_date" type="date" />
          <span v-if="validationErrors.arrival_date" class="error-msg">{{ validationErrors.arrival_date }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.origin_port }">
          <label>Origin Port of Loading</label>
          <input v-model="form.origin_port" type="text" placeholder="e.g. Port of Rotterdam" />
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.destination_port }">
          <label>Destination Port of Discharge</label>
          <input v-model="form.destination_port" type="text" placeholder="e.g. Jebel Ali Port" />
        </div>

        <div class="form-actions full-width">
          <button type="button" @click="modal.formOpen = false" class="btn-cancel">Cancel</button>
          <button type="submit" class="btn-submit">Save Blueprint</button>
        </div>
      </form>
    </BaseModal>

    <!-- DETAILED VIEW MODAL MATRIX DISPLAYING RELATIONS BOTH DIRECTIONS -->
    <BaseModal :show="modal.viewOpen" title="Bidirectional Voyage Logistics Engine" size="lg" @close="modal.viewOpen = false">
      <div v-if="selectedVoyage" class="detail-card">
        <div class="detail-header">
          <h4>Manifest Trace ID: #{{ selectedVoyage.id }}</h4>
        </div>
        <div class="detail-grid-voyage">
          <div><strong>Voyage Track Code:</strong> {{ selectedVoyage.voyage_number || 'Internal Schedule' }}</div>
          <div><strong>Assigned Hull Carrier:</strong> 🚢 {{ selectedVoyage.vessel ? selectedVoyage.vessel.name : 'N/A' }}</div>
          <div><strong>Origin Load Terminal:</strong> 🛫 {{ selectedVoyage.origin_port || 'Not Tracked' }}</div>
          <div><strong>Destination Discharge:</strong> 🛬 {{ selectedVoyage.destination_port || 'Not Tracked' }}</div>
          <div><strong>Departure Timeline:</strong> 🗓️ {{ selectedVoyage.departure_date || 'Pending' }}</div>
          <div><strong>Arrival Timeline:</strong> 🗓️ {{ selectedVoyage.arrival_date || 'Pending' }}</div>

          <div class="full relationship-box">
            <h5>🔗 Bidirectional Database Entity Mappings</h5>
            <p><strong>Corporate Operator Parent:</strong> {{ selectedVoyage.party ? `🏢 ${selectedVoyage.party.name} [SHIPPING LINE]` : 'No linked commercial operator row.' }}</p>
            <p><strong>Cargo Batch Pipeline Node:</strong> {{ selectedVoyage.batch ? `📦 ${selectedVoyage.batch.name} [CONTAINER INVENTORY]` : 'No explicit batch tracking parameters bound to this leg.' }}</p>
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

const voyages = ref([]);
const selectedVoyage = ref(null);

let searchTimeout = null;

const filters = reactive({
  search: '',
  vessel_id: '',
  batch_id: '',
  status: ''
});
const resetFilters = () => {
  filters.search = '';
  filters.vessel_id = '';
  filters.batch_id = '';
  filters.status = '';
  fetchVoyages();
};
const validationErrors = ref({});
const modal = reactive({ formOpen: false, viewOpen: false, isEdit: false, currentId: null });

const form = reactive({
  vessel_id: '', voyage_number: '', party_id: '', batch_id: '',
  departure_date: '', arrival_date: '', origin_port: '', destination_port: ''
});

const lookups = reactive({ vessels: [], batches: [], shippingLines: [] });

const fetchVoyages = async () => {
  const response = await api.get('/voyages', { params: filters });
  voyages.value = response.data.data;
};

const fetchLookupDatasets = async () => {
  const [vRes, bRes, pRes] = await Promise.all([
    api.get('/vessels', { params: { paginate: 0 } }),
    api.get('/batches', { params: { paginate: 0 } }),
    api.get('/parties', { params: { type: 'shipping_line', paginate: 0 } })
  ]);
  lookups.vessels = vRes.data.data || vRes.data;
  lookups.batches = bRes.data.data || bRes.data;
  lookups.shippingLines = pRes.data.data || pRes.data;
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { fetchVoyages(); }, 350);
};

const resetValidation = () => { validationErrors.value = {}; };

const openCreateModal = () => {
  resetValidation();
  modal.isEdit = false; modal.currentId = null; modal.formOpen = true;
  Object.assign(form, { vessel_id: '', voyage_number: '', party_id: '', batch_id: '', departure_date: '', arrival_date: '', origin_port: '', destination_port: '' });
};

const openEditModal = (voyage) => {
  resetValidation();
  modal.isEdit = true; modal.currentId = voyage.id; modal.formOpen = true;
  Object.assign(form, voyage);
};

const openViewModal = (voyage) => {
  selectedVoyage.value = voyage; modal.viewOpen = true;
};

const saveForm = async () => {
  resetValidation();
  try {
    if (modal.isEdit) {
      await api.put(`/voyages/${modal.currentId}`, form);
    } else {
      await api.post('/voyages', form);
    }
    modal.formOpen = false;
    fetchVoyages();
  } catch (err) {
    if (err.response && err.response.status === 422) {
      validationErrors.value = err.response.data.errors || {};
    } else {
      validationErrors.value = { general: 'Critical constraints tracking collision exception occurred on backend logs.' };
    }
  }
};

const deleteVoyage = async (id) => {
    if (confirm('De-register this scheduled voyage mapping block completely?')) {
        await api.delete(`/voyages/${id}`);
        fetchVoyages();
    }
};

// Associated script computed method mappings
const systemDate = new Date('2026-10-05');

const getStatusLabel = (voyage) => {
  if (!voyage?.departure_date || !voyage?.arrival_date) return 'Draft';
  const dep = new Date(voyage?.departure_date);
  const arr = new Date(voyage?.arrival_date);

  if (arr < systemDate) return 'Arrived';
  if (dep <= systemDate && arr >= systemDate) return 'In Transit';
  return 'Pending';
};

const getStatusClass = (voyage) => {
  const lbl = getStatusLabel(voyage);
  if (lbl === 'Arrived') return 'pill-success';
  if (lbl === 'In Transit') return 'pill-warning';
  return 'pill-info';
};

onMounted(() => {
fetchVoyages();
fetchLookupDatasets();
});
</script>
<style scoped>
.voyage-container {
  color: var(--brand-text);
  font-family: var(--font-sans);
  padding: 0.5rem 0 1rem;
}

.action-bar-extended {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 1.5rem;
  background: linear-gradient(135deg, rgba(212, 232, 107, 0.12), rgba(255, 255, 255, 0.96));
  border: 1px solid rgba(23, 61, 50, 0.08);
  border-radius: 18px;
  padding: 1rem 1.25rem;
  box-shadow: 0 12px 28px var(--brand-shadow);
}

@media(min-width: 992px) {
  .action-bar-extended {
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
  }
}

.filter-tray {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
  flex: 1;
  max-width: 980px;
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
  min-width: 200px;
}

.select-filter {
  flex: 1;
  min-width: 140px;
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

.btn-clear-filters,
.btn-cancel {
  background: var(--brand-surface-soft);
  color: var(--brand-text);
  border: 1px solid var(--brand-border);
  padding: 0.7rem 1rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.9rem;
  transition: all 0.15s ease;
}

.btn-clear-filters:hover,
.btn-cancel:hover {
  background: rgba(23, 61, 50, 0.03);
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

.relation-chip {
  font-size: 0.78rem;
  font-weight: 600;
  display: inline-block;
  padding: 0.28rem 0.55rem;
  border-radius: 999px;
  margin-right: 0.5rem;
  margin-top: 0.35rem;
  background: rgba(23, 61, 50, 0.03);
  border: 1px solid var(--brand-border);
}

.text-blue {
  color: var(--brand-primary-strong);
}

.text-purple {
  color: #5b4f9b;
}

.status-pill {
  display: inline-flex;
  align-items: center;
  padding: 0.38rem 0.72rem;
  border-radius: 999px;
  font-size: 0.76rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  border: 1px solid transparent;
}

.pill-success {
  background: rgba(31, 122, 88, 0.1);
  color: var(--brand-success);
  border-color: rgba(31, 122, 88, 0.18);
}

.pill-warning {
  background: rgba(212, 232, 107, 0.18);
  color: var(--brand-primary-strong);
  border-color: rgba(212, 232, 107, 0.3);
}

.pill-info {
  background: rgba(23, 61, 50, 0.05);
  color: var(--brand-primary);
  border-color: rgba(23, 61, 50, 0.12);
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

.detail-card {
  background: var(--brand-surface-soft);
  border: 1px solid var(--brand-border);
  border-radius: 14px;
  padding: 1rem;
}

.detail-grid-voyage {
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

.relationship-box {
  grid-column: span 2;
  border-top: 1px dashed rgba(23, 61, 50, 0.18);
  padding-top: 1rem;
  margin-top: 0.5rem;
}

.relationship-box h5 {
  margin: 0 0 0.5rem 0;
  font-size: 0.96rem;
  font-weight: 700;
  color: var(--brand-primary-strong);
}

.relationship-box p {
  margin: 0.35rem 0;
  color: var(--brand-text);
}

.empty-state {
  text-align: center;
  color: var(--brand-muted);
  padding: 2rem 1rem;
  font-weight: 500;
}

@media (max-width: 768px) {
  .action-bar-extended {
    padding: 0.9rem;
  }

  .grid-form,
  .detail-grid-voyage {
    grid-template-columns: 1fr;
  }

  .form-actions,
  .relationship-box {
    grid-column: span 1;
  }

  .actions-cell {
    justify-content: flex-start;
    flex-wrap: wrap;
  }
}
</style>
