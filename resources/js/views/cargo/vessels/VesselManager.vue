<!-- src/views/VesselManager.vue -->
<template>
  <div class="vessel-container">
    <div class="action-bar">
      <div class="search-filters">
        <input v-model="filters.search" @input="debounceSearch" type="text" placeholder="🔍 Search Vessel Name, IMO, or Call Sign..." class="input-search" />
      </div>
      <button @click="openCreateModal" class="btn-add">+ Register New Vessel</button>
    </div>

    <!-- Data Presentation Layout Table -->
    <div class="table-wrapper">
      <table class="modern-table">
        <thead>
          <tr>
            <th>Vessel Specs</th>
            <th>Identifiers (IMO / Call Sign)</th>
            <th>Managing Party / Operator</th>
            <th class="text-right">Operations</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="vessel in vessels" :key="vessel.id">
            <td>
              <div class="font-bold text-slate">{{ vessel.name }}</div>
              <div class="text-sub">🏳️ {{ vessel.flag || 'Unknown Flag' }} • 🏗️ {{ vessel.vessel_type || 'N/A' }}</div>
            </td>
            <td>
              <div><strong>IMO:</strong> {{ vessel.imo_number }}</div>
              <div class="text-sub"><strong>Sign:</strong> {{ vessel.call_sign || 'N/A' }}</div>
            </td>
            <td>
              <div class="party-link">🏢 {{ vessel.party ? vessel.party.name : 'Unassigned' }}</div>
            </td>
            <td class="text-right actions-cell">
              <button @click="openViewModal(vessel)" class="btn-action view">👁️ View</button>
              <button @click="openEditModal(vessel)" class="btn-action edit">✏️ Edit</button>
              <button @click="deleteVessel(vessel.id)" class="btn-action delete">🗑️ Delete</button>
            </td>
          </tr>
          <tr v-if="vessels?.length === 0">
            <td colspan="4" class="text-center empty-state">No matching registered vessels found matching your criteria.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- REGISTER & EDIT DYNAMIC SYSTEM FORM MODAL -->
    <BaseModal :show="modal.formOpen" :title="modal.isEdit ? 'Modify Vessel Matrix' : 'Register Asset Blueprint'" size="lg" @close="modal.formOpen = false">
      <div v-if="validationErrors.general" class="form-error-banner">{{ validationErrors.general }}</div>

      <form @submit.prevent="saveForm" class="grid-form">
        <div class="field" :class="{ 'has-error': validationErrors.name }">
          <label>Vessel Name</label>
          <input v-model="form.name" type="text" required placeholder="e.g. MAERSK MC-KINNEY MOLLER" />
          <span v-if="validationErrors.name" class="error-msg">{{ validationErrors.name[0] }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.imo_number }">
          <label>IMO Number</label>
          <input v-model="form.imo_number" type="text" required placeholder="e.g. 9632064" />
          <span v-if="validationErrors.imo_number" class="error-msg">{{ validationErrors.imo_number[0] }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.party_id }">
          <label>Managing Agent / Shipping Line (Party Relation)</label>
          <select v-model="form.party_id" required>
            <option value="">Select Responsible Party</option>
            <option v-for="party in parties" :key="party.id" :value="party.id">{{ party.name }}</option>
          </select>
          <span v-if="validationErrors.party_id" class="error-msg">{{ validationErrors.party_id[0] }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.call_sign }">
          <label>Call Sign</label>
          <input v-model="form.call_sign" type="text" placeholder="e.g. OWJD2" />
          <span v-if="validationErrors.call_sign" class="error-msg">{{ validationErrors.call_sign[0] }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.mmsi_number }">
          <label>MMSI Number</label>
          <input v-model="form.mmsi_number" type="text" placeholder="e.g. 219401000" />
          <span v-if="validationErrors.mmsi_number" class="error-msg">{{ validationErrors.mmsi_number[0] }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.flag }">
          <label>Country Flag</label>
          <input v-model="form.flag" type="text" placeholder="e.g. Denmark" />
          <span v-if="validationErrors.flag" class="error-msg">{{ validationErrors.flag[0] }}</span>
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.vessel_type }">
          <label>Vessel Class/Type</label>
          <input v-model="form.vessel_type" type="text" placeholder="e.g. Container Ship" />
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.gross_tonnage }">
          <label>Gross Tonnage (GT)</label>
          <input v-model="form.gross_tonnage" type="text" placeholder="e.g. 194849" />
        </div>

        <div class="field" :class="{ 'has-error': validationErrors.net_tonnage }">
          <label>Net Tonnage (NT)</label>
          <input v-model="form.net_tonnage" type="text" placeholder="e.g. 79120" />
        </div>

        <div class="form-actions full-width">
          <button type="button" @click="modal.formOpen = false" class="btn-cancel">Cancel</button>
          <button type="submit" class="btn-submit">Save Settings</button>
        </div>
      </form>
    </BaseModal>

    <!-- DETAILED COMPONENT BLUEPRINT BLUEPRINT MODAL -->
    <BaseModal :show="modal.viewOpen" title="Marine Vessel Asset Analytics" size="lg" @close="modal.viewOpen = false">
      <div v-if="selectedVessel" class="detail-card">
        <div class="detail-header">
          <h4>🚢 {{ selectedVessel.name }}</h4>
          <span class="vessel-badge-type">{{ selectedVessel.vessel_type || 'Unclassified Class' }}</span>
        </div>
        <div class="detail-grid-vessel">
          <div><strong>IMO Number:</strong> {{ selectedVessel.imo_number }}</div>
          <div><strong>MMSI Number:</strong> {{ selectedVessel.mmsi_number || 'N/A' }}</div>
          <div><strong>Call Sign Code:</strong> {{ selectedVessel.call_sign || 'N/A' }}</div>
          <div><strong>Flag Registry:</strong> 🏳️ {{ selectedVessel.flag || 'Not Logged' }}</div>
          <div><strong>Gross Tonnage:</strong> {{ selectedVessel.gross_tonnage || 'N/A' }} GT</div>
          <div><strong>Net Tonnage:</strong> {{ selectedVessel.net_tonnage || 'N/A' }} NT</div>
          <div class="full"><strong>Operating Owner / Entity:</strong> 🏢 {{ selectedVessel.party ? selectedVessel.party.name : 'Unknown Operator' }}</div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../../api/axios';
import BaseModal from '../../../componets/shared/BaseModal.vue';

const vessels = ref([]);
const parties = ref([]);
const selectedVessel = ref(null);
let searchTimeout = null;

const filters = reactive({ search: '' });
const validationErrors = ref({});
const modal = reactive({ formOpen: false, viewOpen: false, isEdit: false, currentId: null });

const form = reactive({
  name: '', imo_number: '', party_id: '', mmsi_number: '',
  call_sign: '', flag: '', vessel_type: '', gross_tonnage: '', net_tonnage: ''
});

const fetchVessels = async () => {
  const response = await api.get('/vessels', { params: filters });
  vessels.value = response.data.data;
};

const fetchAllPartiesLookupList = async () => {
  const response = await api.get('/parties', { params: { paginate: 0, type:'shipping_line' } });
  parties.value = response.data.data || response.data;
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { fetchVessels(); }, 350);
};

const resetValidation = () => { validationErrors.value = {}; };

const openCreateModal = () => {
  resetValidation();
  modal.isEdit = false; modal.currentId = null; modal.formOpen = true;
  Object.assign(form, { name: '', imo_number: '', party_id: '', mmsi_number: '', call_sign: '', flag: '', vessel_type: '', gross_tonnage: '', net_tonnage: '' });
};

const openEditModal = (vessel) => {
  resetValidation();
  modal.isEdit = true; modal.currentId = vessel.id; modal.formOpen = true;
  Object.assign(form, vessel);
};

const openViewModal = (vessel) => {
  selectedVessel.value = vessel; modal.viewOpen = true;
};

const saveForm = async () => {
  resetValidation();
  try {
    if (modal.isEdit) {
      await api.put(`/vessels/${modal.currentId}`, form);
    } else {
      await api.post('/vessels', form);
    }
    modal.formOpen = false;
    fetchVessels();
  } catch (err) {
    if (err.response && err.response.status === 422) {
      validationErrors.value = err.response.data.errors || {};
    } else {
      validationErrors.value = { general: 'Critical server tracking constraint collision error.' };
    }
  }
};

const deleteVessel = async (id) => {
  if (confirm('Permanently de-register this vessel and detach logistics assets?')) {
    await api.delete(`/vessels/${id}`);
    fetchVessels();
  }
};

onMounted(() => {
  fetchVessels();
  fetchAllPartiesLookupList();
});
</script>

<style scoped>
.vessel-container {
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
  border: 1px solid rgba(23, 61, 50, 0.1);
  border-radius: 18px;
  padding: 1rem 1.25rem;
  box-shadow: 0 12px 28px var(--brand-shadow);
}

.search-filters {
  display: flex;
  gap: 0.75rem;
  flex: 1;
  max-width: 520px;
}

.input-search,
.field input,
.field select {
  padding: 0.8rem 0.95rem;
  border: 1px solid var(--brand-border);
  border-radius: 12px;
  font-size: 0.92rem;
  background: var(--brand-surface);
  color: var(--brand-text);
  width: 100%;
  box-sizing: border-box;
  transition: all 0.2s ease;
  box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
}

.input-search:focus,
.field input:focus,
.field select:focus {
  outline: none;
  border-color: var(--brand-primary);
  box-shadow: 0 0 0 4px rgba(23, 61, 50, 0.08);
}

.btn-add,
.btn-submit,
.btn-cancel,
.btn-action {
  transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}

.btn-add,
.btn-submit {
  background: linear-gradient(135deg, var(--brand-primary), var(--brand-primary-strong));
  color: white;
  border: none;
  padding: 0.75rem 0.75rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 700;
  font-size: 0.9rem;
  box-shadow: 0 12px 22px rgba(23, 61, 50, 0.18);
}

.btn-add:hover,
.btn-submit:hover {
  transform: translateY(-1px);
  box-shadow: 0 16px 26px rgba(23, 61, 50, 0.22);
}

.table-wrapper {
  background: rgba(255, 255, 255, 0.96);
  border: 1px solid rgba(23, 61, 50, 0.08);
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.05);
}

.modern-table {
  width: 100%;
  border-collapse: collapse;
  text-align: left;
  font-size: 0.95rem;
}

.modern-table th {
  background: linear-gradient(180deg, rgba(212, 232, 107, 0.12), rgba(255, 255, 255, 0.9));
  padding: 1rem 1.25rem;
  font-weight: 700;
  color: var(--brand-primary);
  border-bottom: 1px solid rgba(23, 61, 50, 0.08);
  letter-spacing: 0.02em;
}

.modern-table td {
  padding: 1.2rem 1.25rem;
  border-bottom: 1px solid rgba(23, 61, 50, 0.05);
  vertical-align: middle;
}

.modern-table tbody tr {
  transition: background 0.15s ease;
}

.modern-table tbody tr:hover {
  background: rgba(212, 232, 107, 0.06);
}

.text-sub {
  font-size: 0.8rem;
  color: var(--brand-muted);
  margin-top: 0.3rem;
  line-height: 1.4;
}

.font-bold {
  font-weight: 700;
  color: var(--brand-text);
}

.party-link {
  color: var(--brand-primary);
  font-weight: 600;
}

.actions-cell {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-end;
  flex-wrap: wrap;
}

.btn-action {
  border: 1px solid rgba(23, 61, 50, 0.12);
  background: white;
  padding: 0.5rem 0.8rem;
  border-radius: 10px;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 600;
  color: var(--brand-primary);
}

.btn-action:hover {
  transform: translateY(-1px);
  background: rgba(212, 232, 107, 0.08);
}

.btn-action.delete:hover {
  background: rgba(239, 68, 68, 0.06);
  color: #b91c1c;
  border-color: rgba(239, 68, 68, 0.2);
}

.grid-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.1rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  position: relative;
  padding-bottom: 1rem;
}

.field.full-width {
  grid-column: span 2;
}

.field label {
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--brand-primary);
}

.field.has-error input,
.field.has-error select {
  border-color: #ef4444;
  background-color: #fff5f5;
}

.error-msg {
  font-size: 0.75rem;
  color: #dc2626;
  font-weight: 600;
  position: absolute;
  bottom: -5px;
  left: 0;
}

.form-error-banner {
  background: linear-gradient(135deg, #fff1f2, #fee2e2);
  border: 1px solid #fecdd3;
  color: #be123c;
  padding: 0.8rem 1rem;
  border-radius: 10px;
  margin-bottom: 1.25rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.btn-cancel {
  background: var(--brand-surface-soft);
  color: var(--brand-primary);
  border: 1px solid rgba(23, 61, 50, 0.12);
  padding: 0.72rem 1.1rem;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
}

.btn-cancel:hover {
  background: rgba(23, 61, 50, 0.04);
}

.detail-card {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.detail-card h4 {
  font-size: 1.2rem;
  margin: 0;
  color: var(--brand-primary);
  letter-spacing: -0.02em;
}

.vessel-badge-type {
  background: rgba(212, 232, 107, 0.14);
  border: 1px solid rgba(23, 61, 50, 0.1);
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  color: var(--brand-primary);
}

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(23, 61, 50, 0.08);
  padding-bottom: 0.85rem;
}

.detail-grid-vessel {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  background: linear-gradient(180deg, rgba(212, 232, 107, 0.05), rgba(255, 255, 255, 0.9));
  padding: 1.1rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(23, 61, 50, 0.08);
  margin-top: 0.5rem;
}

.detail-grid-vessel .full {
  grid-column: span 2;
  border-top: 1px solid rgba(23, 61, 50, 0.08);
  padding-top: 0.8rem;
  margin-top: 0.2rem;
}

@media (max-width: 720px) {
  .action-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-filters,
  .grid-form,
  .detail-grid-vessel {
    grid-template-columns: 1fr;
    display: grid;
  }

  .search-filters {
    max-width: none;
  }

  .field.full-width,
  .detail-grid-vessel .full {
    grid-column: span 1;
  }

  .actions-cell {
    justify-content: flex-start;
  }

  .modern-table {
    display: block;
    overflow-x: auto;
  }
}
</style>
