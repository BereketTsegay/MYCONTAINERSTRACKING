<!-- src/views/BatchManager.vue -->
<template>
  <div class="batch-container">
    <div class="action-bar">
      <div class="search-filters">
        <input v-model="filters.search" @input="debounceSearch" type="text" placeholder="🔍 Search Batch Name or Code..." class="input-search" />
      </div>
      <button @click="openCreateModal" class="btn-add">+ Create New Batch</button>
    </div>

    <!-- Data Presentation Layout Table -->
    <div class="table-wrapper">
      <table class="modern-table">
        <thead>
          <tr>
            <th>Batch Details</th>
            <th>System Identity Signature</th>
            <th class="text-right">Operations</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="batch in batches" :key="batch.id">
            <td>
              <div class="font-bold text-slate">📦 {{ batch.name }}</div>
            </td>
            <td>
              <div class="text-sub">System ID: #{{ batch.id }}</div>
            </td>
            <td class="text-right actions-cell">
              <button @click="openViewModal(batch)" class="btn-action view">👁️ View</button>
              <button @click="openEditModal(batch)" class="btn-action edit">✏️ Edit</button>
              <button @click="deleteBatch(batch.id)" class="btn-action delete">🗑️ Delete</button>
            </td>
          </tr>
          <tr v-if="batches.length === 0">
            <td colspan="3" class="text-center empty-state">No matching production batches discovered.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- REGISTER & EDIT DYNAMIC SYSTEM FORM MODAL -->
    <BaseModal :show="modal.formOpen" :title="modal.isEdit ? 'Modify Batch Matrix' : 'Initialize Batch Tracking'" size="md" @close="modal.formOpen = false">
      <div v-if="validationErrors.general" class="form-error-banner">{{ validationErrors.general }}</div>

      <form @submit.prevent="saveForm" class="grid-form">
        <div class="field full-width" :class="{ 'has-error': validationErrors.name }">
          <label>Batch Code / Name</label>
          <input v-model="form.name" type="text" required placeholder="e.g. BATCH-2026-OCT-01A" />
          <span v-if="validationErrors.name" class="error-msg">{{ validationErrors.name }}</span>
        </div>

        <div class="form-actions full-width">
          <button type="button" @click="modal.formOpen = false" class="btn-cancel">Cancel</button>
          <button type="submit" class="btn-submit">Save Settings</button>
        </div>
      </form>
    </BaseModal>

    <!-- DETAILED COMPONENT BLUEPRINT MODAL -->
    <BaseModal :show="modal.viewOpen" title="Batch Analytics Overview" size="md" @close="modal.viewOpen = false">
      <div v-if="selectedBatch" class="detail-card">
        <div class="detail-header">
          <h4>📦 {{ selectedBatch.name }}</h4>
        </div>
        <div class="detail-grid-batch">
          <div><strong>Database Key ID:</strong> #{{ selectedBatch.id }}</div>
          <div><strong>Created Logs Timestamp:</strong> {{ formatDate(selectedBatch.created_at) }}</div>
        </div>
      </div>
    </BaseModal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../../api/axios.js';
import BaseModal from '../../../componets/shared/BaseModal.vue';

const batches = ref([]);
const selectedBatch = ref(null);
let searchTimeout = null;

const filters = reactive({ search: '' });
const validationErrors = ref({});
const modal = reactive({ formOpen: false, viewOpen: false, isEdit: false, currentId: null });

const form = reactive({ name: '' });

const fetchBatches = async () => {
  const response = await api.get('/batches', { params: filters });
  batches.value = response.data.data;
};

const debounceSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => { fetchBatches(); }, 350);
};

const resetValidation = () => { validationErrors.value = {}; };

const openCreateModal = () => {
  resetValidation();
  modal.isEdit = false; modal.currentId = null; modal.formOpen = true;
  form.name = '';
};

const openEditModal = (batch) => {
  resetValidation();
  modal.isEdit = true; modal.currentId = batch.id; modal.formOpen = true;
  form.name = batch.name;
};

const openViewModal = (batch) => {
  selectedBatch.value = batch; modal.viewOpen = true;
};

const saveForm = async () => {
  resetValidation();
  try {
    if (modal.isEdit) {
      await api.put(`/batches/${modal.currentId}`, form);
    } else {
      await api.post('/batches', form);
    }
    modal.formOpen = false;
    fetchBatches();
  } catch (err) {
    if (err.response && err.response.status === 422) {
      validationErrors.value = err.response.data.errors || {};
    } else {
      validationErrors.value = { general: 'Critical workflow conflict handling database parameters.' };
    }
  }
};

const deleteBatch = async (id) => {
  if (confirm('Permanently de-register this batch number allocation?')) {
    await api.delete(`/batches/${id}`);
    fetchBatches();
  }
};

const formatDate = (dateStr) => {
  return new Date(dateStr).toLocaleString();
};

onMounted(() => { fetchBatches(); });
</script>

<style scoped>
.batch-container {
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
  padding: 1.15rem 1.25rem;
  border-bottom: 1px solid #edf2ed;
  vertical-align: middle;
}

.modern-table tbody tr:hover {
  background: rgba(23, 61, 50, 0.02);
}

.text-sub {
  font-size: 0.78rem;
  color: var(--brand-muted);
}

.font-bold {
  font-weight: 700;
  color: var(--brand-text);
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
  border-color: rgba(23, 61, 50, 0.18);
}

.btn-action.edit:hover {
  background: rgba(212, 232, 107, 0.18);
  border-color: rgba(23, 61, 50, 0.18);
}

.btn-action.delete:hover {
  background: rgba(239, 68, 68, 0.05);
  color: #b91c1c;
  border-color: rgba(239, 68, 68, 0.2);
}

.grid-form {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1.1rem;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 0.55rem;
  position: relative;
  padding-bottom: 1.15rem;
}

.field.full-width {
  grid-column: span 1;
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
  bottom: -2px;
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
  margin-top: 0.3rem;
}

.btn-cancel {
  background: var(--brand-surface-soft);
  color: var(--brand-text);
  border: 1px solid var(--brand-border);
  padding: 0.72rem 1.15rem;
  border-radius: 12px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.15s ease;
}

.btn-cancel:hover {
  background: rgba(23, 61, 50, 0.03);
}

.detail-card {
  background: var(--brand-surface-soft);
  border: 1px solid var(--brand-border);
  border-radius: 14px;
  padding: 1rem;
}

.detail-card h4 {
  font-size: 1.2rem;
  margin: 0;
  color: var(--brand-primary-strong);
}

.detail-grid-batch {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.8rem;
  background: rgba(23, 61, 50, 0.02);
  padding: 1.1rem 1rem;
  border-radius: 12px;
  border: 1px solid var(--brand-border);
  margin-top: 1rem;
  font-size: 0.95rem;
}

.empty-state {
  text-align: center;
  color: var(--brand-muted);
  padding: 2rem 1rem;
  font-weight: 500;
}

@media (max-width: 768px) {
  .action-bar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-filters {
    max-width: none;
  }

  .actions-cell {
    justify-content: flex-start;
    flex-wrap: wrap;
  }
}
</style>
