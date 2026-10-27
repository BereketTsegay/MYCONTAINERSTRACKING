<!-- src/views/DashboardView.vue -->
<template>
  <div class="dashboard-root">
    <!-- Top Welcome Header -->
    <header class="welcome-section">
      <div class="welcome-text">
        <h1>Logistics Hub Overview</h1>
        <p class="subtitle">System Time: October 6, 2026 • Welcome back to your operation logs.</p>
      </div>
    </header>


    <!-- PERSISTENT CLIENT COMPONENT: FREQUENTLY VISITED PAGES TRACKER -->
    <div class="frequent-pages-banner">
      <strong>⚡ Quick Links (Most Visited Channels):</strong>
      <span v-if="trackerStore.frequentlyVisited.length === 0" class="no-badge">No page telemetry recorded yet. Navigate below to begin tracking.</span>
      <span
        v-for="page in trackerStore.frequentlyVisited"
        :key="page.name"
        class="visit-badge"
        @click="navigateToPage(page.name)"
      >
        {{ formatRouteLabel(page.name) }} ({{ page.count }})
      </span>
    </div>

    <!-- CORE CHANNELS GRID LINK MATRIX -->
    <div class="modules-grid">
      <!-- Card 1: Core Access Control Configuration Matrix -->
      <div class="module-card access-card" v-if="roles.includes('admin')">
        <div class="card-icon">🛡️</div>
        <div class="card-body">
          <h3>Access Controls</h3>
          <p>Modify role scopes, register system permissions, and bind permissions matrices dynamically.</p>
          <RouterLink to="/admin/permissions" class="btn-navigate admin-btn">Manage Security</RouterLink>
        </div>
      </div>

      <!-- Card 2: Corporate Registry / Parties -->
      <div class="module-card">
        <div class="card-icon">🏢</div>
        <div class="card-body">
          <h3>Corporate Parties</h3>
          <p>Manage entities across system registers including Shippers, Transitors, and Shipping Lines.</p>
          <RouterLink to="/cargo/parties" class="btn-navigate">Open Registry</RouterLink>
        </div>
      </div>

      <!-- Card 3: Marine Vessels -->
      <div class="module-card">
        <div class="card-icon">🚢</div>
        <div class="card-body">
          <h3>Marine Vessels</h3>
          <p>Track vessel specifications, register active carrier names, and log IMO classification profiles.</p>
          <RouterLink to="/cargo/vessels" class="btn-navigate">Manage Fleet</RouterLink>
        </div>
      </div>

      <!-- Card 4: Production Batches -->
      <div class="module-card">
        <div class="card-icon">📦</div>
        <div class="card-body">
          <h3>Inventory Batches</h3>
          <p>Initialize cargo tracking allocations and allocate production batch numbers.</p>
          <RouterLink to="/cargo/batches" class="btn-navigate">View Batches</RouterLink>
        </div>
      </div>

      <!-- Card 5: Voyage Transits -->
      <div class="module-card">
        <div class="card-icon">🗺️</div>
        <div class="card-body">
          <h3>Transit Voyages</h3>
          <p>Schedule routes, map load/discharge ports, and trace active line operations.</p>
          <RouterLink to="/cargo/voyages" class="btn-navigate">Track Routes</RouterLink>
        </div>
      </div>

      <!-- Card 6: Cargo Containers -->
      <div class="module-card">
        <div class="card-icon">📥</div>
        <div class="card-body">
          <h3>Cargo Containers</h3>
          <p>Log container size variations, review load VGM metrics, and trace active inventory states.</p>
          <RouterLink to="/cargo/containers" class="btn-navigate">Open Inventory</RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useAuthStore } from '../../stores/auth';
import { usePageTrackerStore } from '../../stores/pageTracker';

const router = useRouter();
const authStore = useAuthStore();
const trackerStore = usePageTrackerStore();

const { roles } = storeToRefs(authStore);

// Helper mapping client names to clear textual strings
const formatRouteLabel = (routeName) => {
  if (typeof routeName !== 'string') return routeName;
  return routeName.replace('admin.cargo.', '').replace('admin.', '').toUpperCase();
};

// Jump handler for the local telemetry badge items
const navigateToPage = (target) => {
  router.push({ name: target }).catch(() => {
    // Fallback block if route tracks paths instead of explicit route names
    if (target.startsWith('/')) router.push(target);
  });
};
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
