<!-- src/views/PartyManager.vue -->
<template>
  <div class="party-container">
    <div class="action-bar">
      <div class="search-filters">
        <input v-model="filters.search" @input="debounceSearch" type="text" placeholder="🔍 Search Name, Email, or Phone..." class="input-search" />
        <select v-model="filters.type" @change="fetchParties" class="select-filter">
          <option value="">All Tiers</option>
          <option value="customer">Customer</option>
          <option value="shipping_line">Shipping Line</option>
          <option value="transitor">Transitor</option>
          <option value="shipper">Shipper</option>
        </select>
      </div>
      <button @click="openCreateModal" class="btn-add">+ Add New Party</button>
    </div>

    <!-- Data Presentation Table -->
    <div class="table-wrapper">
      <table class="modern-table">
        <thead>
          <tr>
            <th>Company Name</th>
            <th>Type</th>
            <th>Contact Details</th>
            <th class="text-right">Operations</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="party in parties" :key="party.id">
            <td>
              <div class="font-bold text-slate">{{ party.name }}</div>
              <div class="text-sub">{{ party.address || 'No registered address' }}</div>
            </td>
            <td><span :class="['badge', `badge-${party.type}`]">{{ formatType(party.type) }}</span></td>
            <td>
              <div>✉️ {{ party.email || 'N/A' }}</div>
              <div class="text-sub">📞 {{ party.phone || 'N/A' }}</div>
            </td>
            <td class="text-right actions-cell">
              <button @click="openViewModal(party)" class="btn-action view">👁️ View</button>
              <button @click="openEditModal(party)" class="btn-action edit">✏️ Edit</button>
              <button @click="deleteParty(party.id)" class="btn-action delete">🗑️ Delete</button>
            </td>
          </tr>
          <tr v-if="parties.length === 0">
            <td colspan="4" class="text-center empty-state">No matching registered parties matching your criteria.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- REUSABLE SYSTEM MODAL (Configured for Creation / Editing) -->
    <BaseModal :show="modal.formOpen" :title="modal.isEdit ? 'Modify Party Record' : 'Register New Party'" @close="modal.formOpen = false">
      <!-- General Top Alert Banner for Fallback Error Processing -->
      <div v-if="validationErrors.general" class="form-error-banner">
        {{ validationErrors.general }}
      </div>

      <form @submit.prevent="saveForm" class="grid-form">
        <!-- Field: Name -->
        <div class="field" :class="{ 'has-error': validationErrors.name }">
          <label>Company Name</label>
          <input v-model="form.name" type="text" placeholder="e.g. Acme Logistics LLC" />
          <span v-if="validationErrors.name" class="error-msg">{{ validationErrors.name[0] }}</span>
        </div>

        <!-- Field: Type -->
        <div class="field" :class="{ 'has-error': validationErrors.type }">
          <label>Party Type Classification</label>
          <select v-model="form.type">
            <option value="customer">Customer</option>
            <option value="shipping_line">Shipping Line</option>
            <option value="transitor">Transitor</option>
            <option value="shipper">Shipper</option>
          </select>
          <span v-if="validationErrors.type" class="error-msg">{{ validationErrors.type[0] }}</span>
        </div>

        <!-- Field: Email -->
        <div class="field" :class="{ 'has-error': validationErrors.email }">
          <label>Email Address</label>
          <input v-model="form.email" type="email" placeholder="corporate@email.com" />
          <span v-if="validationErrors.email" class="error-msg">{{ validationErrors.email[0] }}</span>
        </div>

        <!-- Field: Phone -->
        <div class="field" :class="{ 'has-error': validationErrors.phone }">
          <label>Phone Number</label>
          <input v-model="form.phone" type="text" placeholder="+971 50 000 0000" />
          <span v-if="validationErrors.phone" class="error-msg">{{ validationErrors.phone[0] }}</span>
        </div>

        <!-- Field: Address -->
        <div class="field full-width" :class="{ 'has-error': validationErrors.address }">
          <label>Physical Address Headquarters</label>
          <textarea v-model="form.address" rows="3" placeholder="Street, Building, City, Country..."></textarea>
          <span v-if="validationErrors.address" class="error-msg">{{ validationErrors.address[0] }}</span>
        </div>

        <div class="form-actions full-width">
          <button type="button" @click="modal.formOpen = false" class="btn-cancel">Cancel</button>
          <button type="submit" class="btn-submit">Save Settings</button>
        </div>
      </form>
    </BaseModal>

    <!-- REUSABLE SYSTEM MODAL (Configured for Detailed View Matrix) -->
    <BaseModal :show="modal.viewOpen" title="Corporate Registry Blueprint" size="md" @close="modal.viewOpen = false">
      <div v-if="selectedParty" class="detail-card">
        <div class="detail-header">
          <h4>{{ selectedParty.name }}</h4>
          <span :class="['badge', `badge-${selectedParty.type}`]">{{ formatType(selectedParty.type) }}</span>
        </div>
        <div class="detail-grid">
          <div class="detail-item"><strong>Contact Email:</strong> {{ selectedParty.email || 'None Provided' }}</div>
          <div class="detail-item"><strong>Phone Number:</strong> {{ selectedParty.phone || 'None Provided' }}</div>
          <div class="detail-item full"><strong>Headquarters Address:</strong> {{ selectedParty.address || 'None Registered' }}</div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../../api/axios';
import BaseModal from '../../../componets/shared/BaseModal.vue';

const parties = ref([]);
const selectedParty = ref(null);
let searchTimeout = null;

const filters = reactive({ search: '', type: '' });
const modal = reactive({ formOpen: false, viewOpen: false, isEdit: false, currentId: null });
const form = reactive({ name: '', email: '', phone: '', address: '', type: 'customer' });

// Validation tracking reactive object state dictionary
const validationErrors = ref({});

const fetchParties = async () => {
  const response = await api.get('/parties', { params: filters });
  parties.value = response.data.data;
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { fetchParties(); }, 350);
};

const resetValidation = () => {
  validationErrors.value = {};
};

const openCreateModal = () => {
  resetValidation();
  modal.isEdit = false; modal.currentId = null; modal.formOpen = true;
  Object.assign(form, { name: '', email: '', phone: '', address: '', type: 'customer' });
};

const openEditModal = (party) => {
  resetValidation();
  modal.isEdit = true; modal.currentId = party.id; modal.formOpen = true;
  Object.assign(form, party);
};

const openViewModal = (party) => {
  selectedParty.value = party; modal.viewOpen = true;
};

const saveForm = async () => {
  resetValidation();
  try {
    if (modal.isEdit) {
      await api.put(`/parties/${modal.currentId}`, form);
    } else {
      await api.post('/parties', form);
    }
    modal.formOpen = false;
    fetchParties();
  } catch (err) {
    if (err.response && err.response.status === 422) {
      // Feed Laravel custom FormRequest error key-value collections directly into your state references
      validationErrors.value = err.response.data.errors || {};
    } else {
      validationErrors.value = { general: err.response?.data?.message || 'An unexpected error occurred. Please try again.' };
    }
  }
};

const deleteParty = async (id) => {
  if (confirm('Are you certain you wish to invalidate this registry row?')) {
    await api.delete(`/parties/${id}`);
    fetchParties();
  }
};

const formatType = (val) => val.replace('_', ' ').toUpperCase();
onMounted(() => fetchParties());
</script>

<style scoped>
.party-container {
  color: var(--brand-text);
  padding: 0.5rem 0 1rem;
  font-family: var(--font-sans);
}

.action-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  gap: 1rem;
  background: linear-gradient(135deg, rgba(212, 232, 107, 0.12), rgba(255, 255, 255, 0.96));
  border: 1px solid rgba(23, 61, 50, 0.12);
  border-radius: 18px;
  padding: 1rem 1.25rem;
  box-shadow: 0 12px 28px var(--brand-shadow);
}

.search-filters {
  display: flex;
  gap: 0.75rem;
  flex: 1;
  max-width: 640px;
}

.input-search,
.select-filter,
.field input,
.field select,
.field textarea {
  padding: 0.8rem 0.95rem;
  border: 1px solid var(--brand-border);
  border-radius: 12px;
  font-size: 0.92rem;
  background: var(--brand-surface);
  color: var(--brand-text);
  transition: all 0.2s ease;
  box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.02);
  font-family: var(--font-sans);
}

.input-search {
  flex: 1;
}

.input-search:focus,
.select-filter:focus,
.field input:focus,
.field select:focus,
.field textarea:focus {
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
  padding: 0.75rem 1.2rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 700;
  font-size: 0.9rem;
  letter-spacing: 0.01em;
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
  border-bottom: 1px solid rgba(23, 61, 50, 0.12);
  letter-spacing: 0.02em;
}

.modern-table td {
  padding: 1.2rem 1.25rem;
  border-bottom: 1px solid rgba(23, 61, 50, 0.06);
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

.badge {
  display: inline-block;
  padding: 0.35rem 0.7rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  letter-spacing: 0.02em;
  text-transform: uppercase;
}

.badge-customer {
  background: rgba(212, 232, 107, 0.2);
  color: var(--brand-primary);
}

.badge-shipping_line {
  background: rgba(23, 61, 50, 0.06);
  color: var(--brand-primary);
}

.badge-transitor {
  background: rgba(23, 61, 50, 0.08);
  color: var(--brand-primary-strong);
}

.badge-shipper {
  background: rgba(46, 125, 109, 0.12);
  color: var(--brand-success);
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
  padding-bottom: 1.1rem;
}

.field.full-width {
  grid-column: span 2;
}

.field label {
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--brand-primary);
}

.field input,
.field select,
.field textarea {
  width: 100%;
  box-sizing: border-box;
}

.field textarea {
  resize: vertical;
  min-height: 110px;
}

.field.has-error input,
.field.has-error select,
.field.has-error textarea {
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

.detail-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid rgba(23, 61, 50, 0.08);
  padding-bottom: 0.85rem;
}

.detail-header h4 {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--brand-primary);
  margin: 0;
  letter-spacing: -0.02em;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  background: linear-gradient(180deg, rgba(212, 232, 107, 0.05), rgba(255, 255, 255, 0.9));
  padding: 1.1rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(23, 61, 50, 0.08);
}

.detail-item {
  color: #334155;
  line-height: 1.5;
}

.detail-item.full {
  grid-column: span 2;
}

@media (max-width: 720px) {
  .action-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-filters,
  .grid-form,
  .detail-grid {
    grid-template-columns: 1fr;
    display: grid;
  }

  .search-filters {
    max-width: none;
  }

  .field.full-width,
  .detail-item.full {
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
