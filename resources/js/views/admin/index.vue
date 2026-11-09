<!-- src/views/DashboardView.vue -->
<!-- src/views/DashboardView.vue -->
<template>
  <div class="dashboard-root space-y-6">
    <!-- Top Welcome Header -->
    <header class="welcome-section border-b border-slate-200 pb-5">
      <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Logistics Hub Overview</h1>
      <p class="text-sm text-slate-500 mt-1">System Time: October 2026 • Welcome back to your operation logs.</p>
    </header>

    <!-- AUTOMATED CATEGORY & MODULE METRIC COUNTERS TRAY -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
      <!-- KPI: Products Total -->
      <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Products</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">{{ metrics.totals.products }}</div>
      </div>
      <!-- KPI: Packing Lists -->
      <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-4 shadow-sm">
        <div class="text-xs font-semibold text-blue-500 uppercase tracking-wider">PKList Cats</div>
        <div class="text-2xl font-bold text-blue-900 mt-1">{{ metrics.categories.pklist }}</div>
      </div>
      <!-- KPI: House Bills -->
      <div class="bg-amber-50/50 border border-amber-100 rounded-xl p-4 shadow-sm">
        <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider">HBill Cats</div>
        <div class="text-2xl font-bold text-amber-900 mt-1">{{ metrics.categories.house_bill }}</div>
      </div>
      <!-- KPI: EDPass Codes -->
      <div class="bg-emerald-50/50 border border-emerald-100 rounded-xl p-4 shadow-sm">
        <div class="text-xs font-semibold text-emerald-600 uppercase tracking-wider">EDPass Cats</div>
        <div class="text-2xl font-bold text-emerald-900 mt-1">{{ metrics.categories.edpass }}</div>
      </div>
      <!-- KPI: Containers -->
      <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Containers</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">{{ metrics.totals.containers }}</div>
      </div>
      <!-- KPI: Voyages -->
      <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Voyages</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">{{ metrics.totals.voyages }}</div>
      </div>
      <!-- KPI: Parties -->
      <div class="bg-white border border-slate-100 rounded-xl p-4 shadow-sm">
        <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Parties</div>
        <div class="text-2xl font-bold text-slate-900 mt-1">{{ metrics.totals.parties }}</div>
      </div>
    </div>

    <!-- CORE CHANNELS GRID LINK MATRIX -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pt-2">
      <!-- Access Controls Card -->
      <div v-if="roles.includes('admin')" class="bg-purple-50/40 border border-purple-100 rounded-2xl p-6 hover:shadow-md transition flex gap-4">
        <div class="text-3xl">🛡️</div>
        <div class="flex-1 space-y-3">
          <h3 class="text-base font-bold text-slate-900">Access Controls</h3>
          <p class="text-xs text-slate-500 leading-relaxed">Modify role scopes, register system permissions, and bind permissions matrices dynamically.</p>
          <RouterLink to="/permissions" class="inline-block rounded-xl bg-purple-600 px-4 py-2 text-xs font-semibold text-white hover:bg-purple-700 transition">Manage Security</RouterLink>
        </div>
      </div>

      <!-- Corporate Parties Card -->
      <div class="bg-white border border-slate-100 rounded-2xl p-6 hover:shadow-md transition flex gap-4">
        <div class="text-3xl">🏢</div>
        <div class="flex-1 space-y-3">
          <h3 class="text-base font-bold text-slate-900">Corporate Parties</h3>
          <p class="text-xs text-slate-500 leading-relaxed">Manage entities across system registers including Shippers, Transitors, and Shipping Lines.</p>
          <RouterLink to="/cargo/parties" class="inline-block rounded-xl bg-[var(--brand-primary)] px-4 py-2 text-xs font-semibold text-white hover:opacity-95 transition">Open Registry</RouterLink>
        </div>
      </div>

      <!-- Transit Voyages Card -->
      <div class="bg-white border border-slate-100 rounded-2xl p-6 hover:shadow-md transition flex gap-4">
        <div class="text-3xl">🗺️</div>
        <div class="flex-1 space-y-3">
          <h3 class="text-base font-bold text-slate-900">Transit Voyages</h3>
          <p class="text-xs text-slate-500 leading-relaxed">Schedule routes, map load/discharge ports, and trace active line operations with real-time tracking.</p>
          <RouterLink to="/cargo/voyages" class="inline-block rounded-xl bg-[var(--brand-primary)] px-4 py-2 text-xs font-semibold text-white hover:opacity-95 transition">Track Routes</RouterLink>
        </div>
      </div>

      <!-- Cargo Containers Card -->
      <div class="bg-white border border-slate-100 rounded-2xl p-6 hover:shadow-md transition flex gap-4">
        <div class="text-3xl">📥</div>
        <div class="flex-1 space-y-3">
          <h3 class="text-base font-bold text-slate-900">Cargo Containers</h3>
          <p class="text-xs text-slate-500 leading-relaxed">Log container size variations, review load VGM weights, and trace active inventory processing states.</p>
          <RouterLink to="/cargo/containers" class="inline-block rounded-xl bg-[var(--brand-primary)] px-4 py-2 text-xs font-semibold text-white hover:opacity-95 transition">Open Inventory</RouterLink>
        </div>
      </div>

      <!-- Products Catalog Card -->
      <div class="bg-white border border-slate-100 rounded-2xl p-6 hover:shadow-md transition flex gap-4">
        <div class="text-3xl">🏷️</div>
        <div class="flex-1 space-y-3">
          <h3 class="text-base font-bold text-slate-900">Products Catalog</h3>
          <p class="text-xs text-slate-500 leading-relaxed">Maintain master goods catalogs, pack variations schemas, and global customs tariff rules.</p>
          <RouterLink to="/cargo/products" class="inline-block rounded-xl bg-[var(--brand-primary)] px-4 py-2 text-xs font-semibold text-white hover:opacity-95 transition">View Catalog</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '../../stores/auth';
import api from '../../api/axios';

const authStore = useAuthStore();
const { roles } = storeToRefs(authStore);

// Reactive nested tracking dictionary mapping structural counters
const metrics = ref({
  categories: { pklist: 0, house_bill: 0, edpass: 0 },
  totals: { products: 0, containers: 0, voyages: 0, parties: 0 }
});

const fetchDashboardMetrics = async () => {
  try {
    const response = await api.get('/dashboard/metrics');
    metrics.value = response.data.data;
  } catch (err) {
    console.error('Error hydrating dashboard aggregates metrics.', err);
  }
};

onMounted(() => {
  fetchDashboardMetrics();
});
</script>

<style scoped>
.dashboard-root {
  padding: 2rem;
  max-width: 1300px;
  margin: 0 auto;
  font-family: var(--font-sans);
  color: var(--brand-text);
}

.welcome-section {
  background: linear-gradient(135deg, rgba(212, 232, 107, 0.12), rgba(255, 255, 255, 0.9));
  border: 1px solid rgba(23, 61, 50, 0.08);
  border-radius: 20px;
  padding: 1.5rem 1.5rem 1.25rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 12px 28px var(--brand-shadow);
}

.welcome-section h1 {
  font-size: clamp(1.8rem, 2.4vw, 2.4rem);
  font-weight: 700;
  color: var(--brand-primary);
  margin: 0;
}

.subtitle {
  color: var(--brand-muted);
  font-size: 0.95rem;
  margin-top: 0.4rem;
}

.frequent-pages-banner {
  background: rgba(23, 61, 50, 0.03);
  border: 1px solid var(--brand-border);
  padding: 0.9rem 1.1rem;
  border-radius: 14px;
  margin-bottom: 2rem;
  font-size: 0.9rem;
  color: var(--brand-primary-strong);
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.no-badge {
  font-style: italic;
  color: var(--brand-muted);
}

.visit-badge {
  background: linear-gradient(135deg, var(--brand-primary), var(--brand-primary-strong));
  color: white;
  padding: 0.3rem 0.7rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
  cursor: pointer;
  transition: transform 0.15s ease, filter 0.15s ease;
}

.visit-badge:hover {
  transform: translateY(-1px);
  filter: brightness(1.04);
}

.modules-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.35rem;
}

.module-card {
  background: var(--brand-surface);
  border: 1px solid var(--brand-border);
  border-radius: 18px;
  padding: 1.5rem;
  display: flex;
  gap: 1rem;
  box-shadow: 0 8px 20px rgba(23, 61, 50, 0.06);
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.module-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 30px rgba(23, 61, 50, 0.08);
  border-color: rgba(23, 61, 50, 0.09);
}

.card-icon {
  font-size: 2.2rem;
  line-height: 1;
  display: flex;
  align-items: flex-start;
  justify-content: center;
  width: 3rem;
  height: 3rem;
  background: rgba(212, 232, 107, 0.18);
  border-radius: 14px;
  border: 1px solid rgba(23, 61, 50, 0.06);
}

.card-body {
  display: flex;
  flex-direction: column;
  width: 100%;
}

.card-body h3 {
  margin: 0 0 0.55rem 0;
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--brand-primary);
}

.card-body p {
  margin: 0 0 1.25rem 0;
  color: var(--brand-muted);
  font-size: 0.9rem;
  line-height: 1.6;
  flex: 1;
}

.btn-navigate {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: fit-content;
  background: linear-gradient(135deg, var(--brand-primary), var(--brand-primary-strong));
  color: white;
  text-decoration: none;
  padding: 0.62rem 1.1rem;
  border-radius: 10px;
  font-size: 0.85rem;
  font-weight: 700;
  text-align: center;
  transition: transform 0.15s ease, filter 0.15s ease;
}

.btn-navigate:hover {
  transform: translateY(-1px);
  filter: brightness(1.04);
}

.access-card {
  background: linear-gradient(135deg, rgba(212, 232, 107, 0.08), rgba(255, 255, 255, 0.96));
  border-color: rgba(23, 61, 50, 0.08);
}

.admin-btn {
  background: linear-gradient(135deg, #5f4bb6, #7b61d8);
}

@media (max-width: 640px) {
  .dashboard-root {
    padding: 1rem;
  }

  .module-card {
    flex-direction: column;
  }

  .card-icon {
    width: 2.8rem;
    height: 2.8rem;
  }
}
</style>
