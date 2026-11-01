<!-- src/views/CatalogSettings.vue -->
<template>
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <section
            class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
        >
            <header
                class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6"
            >
                <div class="flex items-start gap-3">
                    <span
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-50 text-emerald-900"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 5.5A1.5 1.5 0 0 1 5.5 4H20v14H5.5A1.5 1.5 0 0 0 4 19.5v-14Z"
                            />
                            <path
                                d="M4 19.5A1.5 1.5 0 0 1 5.5 18H20M8 8h8M8 11h6"
                            />
                        </svg>
                    </span>
                    <div>
                        <h2
                            class="text-base font-semibold tracking-tight text-slate-900"
                        >
                            Product categories
                        </h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Organize product mappings for your documents.
                        </p>
                    </div>
                </div>
                <span
                    class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                    >{{ categories.length }}</span
                >
            </header>

            <div class="space-y-4 p-5 sm:p-6">
                <form
                    @submit.prevent="createCategory"
                    class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_auto_auto]"
                >
                    <input
                        v-model="catForm.name"
                        type="text"
                        placeholder="Category name"
                        aria-label="Category name"
                        required
                        class="min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition outline-none placeholder:text-slate-400 focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                    />
                    <select
                        v-model="catForm.type"
                        aria-label="Category type"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-700 transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                    >
                        <option value="pklist">PKList</option>
                        <option value="house_bill">House Bill</option>
                        <option value="edpass">EDPass</option>
                    </select>
                    <button
                        type="submit"
                        class="rounded-xl bg-[var(--brand-primary)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[var(--brand-primary-strong)] focus-visible:ring-4 focus-visible:ring-emerald-900/15 focus-visible:outline-none"
                    >
                        Add category
                    </button>
                </form>

                <div
                    class="max-h-[350px] overflow-y-auto rounded-xl border border-slate-200"
                >
                    <div
                        v-for="cat in categories"
                        :key="cat.id"
                        class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3.5 transition-colors last:border-b-0 hover:bg-slate-50/70"
                    >
                        <div class="min-w-0">
                            <p
                                class="truncate text-sm font-semibold text-slate-800"
                            >
                                {{ cat.name }}
                            </p>
                            <span
                                class="mt-1 inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-semibold tracking-wide text-slate-500 uppercase"
                                >{{ cat.type }}</span
                            >
                        </div>
                        <button
                            @click="deleteCategory(cat.id)"
                            :aria-label="`Delete ${cat.name} category`"
                            class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 focus-visible:ring-2 focus-visible:ring-rose-500/20 focus-visible:outline-none"
                        >
                            Remove
                        </button>
                    </div>
                    <div
                        v-if="categories.length === 0"
                        class="px-4 py-9 text-center"
                    >
                        <p class="text-sm font-medium text-slate-700">
                            No categories yet
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Create a category to organize product mappings.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
        >
            <header
                class="flex items-start justify-between gap-4 border-b border-slate-100 px-5 py-5 sm:px-6"
            >
                <div class="flex items-start gap-3">
                    <span
                        class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-lime-50 text-emerald-900"
                    >
                        <svg
                            class="h-5 w-5"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            aria-hidden="true"
                        >
                            <path d="M4 7h16M7 4v6m10-6v6M5 11h14v9H5z" />
                            <path d="M8 15h3m2 0h3" />
                        </svg>
                    </span>
                    <div>
                        <h2
                            class="text-base font-semibold tracking-tight text-slate-900"
                        >
                            Units of measurement
                        </h2>
                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Define the units used by product variations.
                        </p>
                    </div>
                </div>
                <span
                    class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600"
                    >{{ uoms.length }}</span
                >
            </header>

            <div class="space-y-4 p-5 sm:p-6">
                <form
                    @submit.prevent="createUom"
                    class="grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_7rem_auto]"
                >
                    <input
                        v-model="uomForm.name"
                        type="text"
                        placeholder="e.g. Kilogram"
                        aria-label="Unit name"
                        required
                        class="min-w-0 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition outline-none placeholder:text-slate-400 focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                    />
                    <input
                        v-model="uomForm.code"
                        type="text"
                        placeholder="Code"
                        aria-label="Unit code"
                        required
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-center font-mono text-sm uppercase transition outline-none placeholder:font-sans placeholder:text-slate-400 placeholder:normal-case focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                    />
                    <button
                        type="submit"
                        class="rounded-xl bg-[var(--brand-primary)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[var(--brand-primary-strong)] focus-visible:ring-4 focus-visible:ring-emerald-900/15 focus-visible:outline-none"
                    >
                        Add unit
                    </button>
                </form>

                <div
                    class="max-h-[350px] overflow-y-auto rounded-xl border border-slate-200"
                >
                    <div
                        v-for="u in uoms"
                        :key="u.id"
                        class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3.5 transition-colors last:border-b-0 hover:bg-slate-50/70"
                    >
                        <div class="flex min-w-0 items-center gap-2.5">
                            <p
                                class="truncate text-sm font-semibold text-slate-800"
                            >
                                {{ u.name }}
                            </p>
                            <span
                                class="shrink-0 rounded-md bg-slate-100 px-2 py-1 font-mono text-[11px] font-semibold text-slate-600"
                                >{{ u.code }}</span
                            >
                        </div>
                        <button
                            @click="deleteUom(u.id)"
                            :aria-label="`Delete ${u.name} unit`"
                            class="shrink-0 rounded-lg px-3 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 focus-visible:ring-2 focus-visible:ring-rose-500/20 focus-visible:outline-none"
                        >
                            Remove
                        </button>
                    </div>
                    <div v-if="uoms.length === 0" class="px-4 py-9 text-center">
                        <p class="text-sm font-medium text-slate-700">
                            No units defined
                        </p>
                        <p class="mt-1 text-xs text-slate-500">
                            Add a unit to make it available for product
                            variations.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../../../api/axios';

const categories = ref([]);
const uoms = ref([]);

const catForm = reactive({ name: '', type: 'pklist' });
const uomForm = reactive({ name: '', code: '' });

const fetchData = async () => {
    const [cRes, uRes] = await Promise.all([
        api.get('/categories'),
        api.get('/uoms'),
    ]);
    categories.value = cRes.data;
    uoms.value = uRes.data;
};

const createCategory = async () => {
    await api.post('/categories', catForm);
    catForm.name = '';
    fetchData();
};

const deleteCategory = async (id) => {
    if (
        confirm('Delete this product assignment category mapping context row?')
    ) {
        await api.delete(`/categories/${id}`);
        fetchData();
    }
};

const createUom = async () => {
    uomForm.code = uomForm.code.toUpperCase();
    await api.post('/uoms', uomForm);
    uomForm.name = '';
    uomForm.code = '';
    fetchData();
};

const deleteUom = async (id) => {
    if (
        confirm('Permanently purge this structural system configuration code?')
    ) {
        await api.delete(`/uoms/${id}`);
        fetchData();
    }
};

onMounted(() => fetchData());
</script>
