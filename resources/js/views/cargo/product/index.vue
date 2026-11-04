<!-- src/views/ProductManager.vue -->
<template>
    <div class="space-y-7">
        <header
            class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >
            <div class="space-y-1">
                <p
                    class="text-xs font-semibold tracking-[0.18em] text-emerald-800/70 uppercase"
                >
                    Inventory management
                </p>
                <h1
                    class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl"
                >
                    Product catalogue
                </h1>
                <p class="text-sm text-slate-500">
                    Manage product details, tariff codes, and packaging
                    variations.
                </p>
            </div>
            <button
                @click="openCreateModal"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-[var(--brand-primary)] px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-950/10 transition hover:bg-[var(--brand-primary-strong)] focus-visible:ring-4 focus-visible:ring-emerald-900/15 focus-visible:outline-none"
            >
                <span class="text-lg leading-none">+</span>
                Add product
            </button>
        </header>

        <section
            class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm sm:p-5"
            aria-label="Product filters"
        >
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative min-w-0 flex-1">
                    <svg
                        class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        aria-hidden="true"
                    >
                        <circle cx="11" cy="11" r="7" />
                        <path d="m20 20-4-4" />
                    </svg>
                    <input
                        v-model="filters.search"
                        @input="debounceSearch"
                        type="text"
                        placeholder="Search product name, SKU, or HS code"
                        aria-label="Search products"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-3 pr-4 pl-10 text-sm text-slate-800 transition outline-none placeholder:text-slate-400 focus:border-emerald-800 focus:bg-white focus:ring-4 focus:ring-emerald-900/10"
                    />
                </div>
                <select
                    v-model="filters.category_id"
                    @change="fetchProducts"
                    aria-label="Filter by category"
                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10 sm:w-64"
                >
                    <option value="">All Categories</option>
                    <option
                        v-for="cat in lookups.categories"
                        :key="cat.id"
                        :value="cat.id"
                    >
                        {{ cat.name }} ({{ cat.type.toUpperCase() }})
                    </option>
                </select>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm"
            aria-label="Product catalogue"
        >
            <div
                class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6"
            >
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                        All products
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Product specifications and assigned categories
                    </p>
                </div>
                <span
                    class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-900"
                    >{{ products.length }} items</span
                >
            </div>
            <div class="overflow-x-auto">
                <table
                    class="w-full min-w-[760px] border-collapse text-left text-sm"
                >
                    <thead
                        class="border-b border-slate-100 bg-slate-50/80 text-[11px] font-semibold tracking-wider text-slate-500 uppercase"
                    >
                        <tr>
                            <th class="px-6 py-4">Item Architecture / SKU</th>
                            <th class="px-6 py-4">Tariff Mapping</th>
                            <th class="px-6 py-4">Variation Parameters</th>
                            <th class="px-6 py-4 text-right">Operations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr
                            v-for="prod in products"
                            :key="prod.id"
                            class="transition-colors hover:bg-emerald-50/30"
                        >
                            <td class="px-5 py-4 sm:px-6">
                                <div class="font-semibold text-slate-900">
                                    {{ prod.name }}
                                </div>
                                <div
                                    class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-500"
                                >
                                    <span class="font-mono"
                                        >SKU: {{ prod.sku }}</span
                                    >
                                    <span
                                        v-if="prod.pk_category"
                                        class="rounded-md bg-blue-50 px-1.5 py-0.5 font-medium text-blue-700"
                                        >PK · {{ prod.pk_category.name }}</span
                                    >
                                    <span
                                        v-if="prod.hbill_category"
                                        class="rounded-md bg-amber-50 px-1.5 py-0.5 font-medium text-amber-700"
                                        >HB ·
                                        {{ prod.hbill_category.name }}</span
                                    >
                                    <span
                                        v-if="prod.edpass_category"
                                        class="rounded-md bg-emerald-50 px-1.5 py-0.5 font-medium text-emerald-800"
                                        >ED ·
                                        {{ prod.edpass_category.name }}</span
                                    >
                                </div>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <span
                                    class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 font-mono text-xs font-medium text-slate-600"
                                >
                                    HS: {{ prod.hs_code || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 sm:px-6">
                                <div
                                    v-if="prod.has_variations"
                                    class="flex flex-wrap gap-1.5"
                                >
                                    <span
                                        v-for="variant in prod.variations"
                                        :key="variant.id"
                                        class="inline-flex items-center rounded-lg border border-emerald-100 bg-emerald-50 px-2 py-1 text-[11px] font-medium text-emerald-900"
                                    >
                                        {{ variant.uom?.code }} (x{{
                                            variant.qty_per_unit
                                        }})
                                    </span>
                                </div>
                                <span v-else class="text-xs text-slate-400"
                                    >Standard unit</span
                                >
                            </td>
                            <td
                                class="space-x-1.5 px-5 py-4 text-right sm:px-6"
                            >
                                <button
                                    @click="openViewModal(prod)"
                                    class="rounded-lg px-2.5 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:ring-2 focus-visible:ring-slate-300 focus-visible:outline-none"
                                >
                                    View
                                </button>
                                <button
                                    @click="openEditModal(prod)"
                                    class="rounded-lg px-2.5 py-2 text-xs font-semibold text-emerald-800 transition hover:bg-emerald-50 focus-visible:ring-2 focus-visible:ring-emerald-800/20 focus-visible:outline-none"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="deleteProduct(prod.id)"
                                    :aria-label="`Delete ${prod.name}`"
                                    class="rounded-lg px-2.5 py-2 text-xs font-semibold text-rose-600 transition hover:bg-rose-50 focus-visible:ring-2 focus-visible:ring-rose-500/20 focus-visible:outline-none"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="products.length === 0">
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div
                                    class="mx-auto flex max-w-sm flex-col items-center gap-2"
                                >
                                    <span
                                        class="grid h-11 w-11 place-items-center rounded-2xl bg-slate-100 text-slate-500"
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
                                                d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"
                                            />
                                            <path
                                                d="m4.5 7.7 7.5 4.4 7.5-4.4M12 12v9"
                                            />
                                        </svg>
                                    </span>
                                    <p
                                        class="text-sm font-semibold text-slate-800"
                                    >
                                        No products found
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        Try adjusting your filters or add a new
                                        product to the catalogue.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- DYNAMIC CREATE/EDIT PRODUCT MODAL -->
        <BaseModal
            :show="modal.formOpen"
            :title="
                modal.isEdit
                    ? 'Modify Product Parameters'
                    : 'Register Product Node'
            "
            size="lg"
            @close="modal.formOpen = false"
        >
            <form @submit.prevent="saveForm" class="space-y-6">
                <!-- Section: Parent Product Specifications -->
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-slate-700"
                            >Product title</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition outline-none placeholder:text-slate-400 focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                        />
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-slate-700"
                            >Parent SKU</label
                        >
                        <input
                            v-model="form.sku"
                            type="text"
                            required
                            class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                        />
                    </div>
                    <div
                        class="grid grid-cols-1 gap-4 rounded-2xl border border-slate-200 bg-slate-50/80 p-4 md:col-span-2 md:grid-cols-3"
                    >
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-700"
                                >Packing list category</label
                            >
                            <select
                                v-model="form.pk_category_id"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                            >
                                <option value="">Select category</option>
                                <option
                                    v-for="c in lookups.categories.filter(
                                        (cat) => cat.type === 'pklist',
                                    )"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-700"
                                >House bill category</label
                            >
                            <select
                                v-model="form.hbill_category_id"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                            >
                                <option value="">Select category</option>
                                <option
                                    v-for="c in lookups.categories.filter(
                                        (cat) => cat.type === 'house_bill',
                                    )"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold text-slate-700"
                                >EDPass category</label
                            >
                            <select
                                v-model="form.edpass_category_id"
                                class="rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                            >
                                <option value="">Select category</option>
                                <option
                                    v-for="c in lookups.categories.filter(
                                        (cat) => cat.type === 'edpass',
                                    )"
                                    :key="c.id"
                                    :value="c.id"
                                >
                                    {{ c.name }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-slate-700"
                            >Global HS code</label
                        >
                        <input
                            v-model="form.hs_code"
                            type="text"
                            maxlength="12"
                            class="rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 font-mono text-sm transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                            placeholder="e.g. 84713010"
                        />
                    </div>
                    <div class="flex flex-col gap-1 md:col-span-2">
                        <label class="text-xs font-semibold text-slate-700"
                            >Product description</label
                        >
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="resize-y rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-sm transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                        ></textarea>
                    </div>
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-3.5 md:col-span-2"
                    >
                        <label
                            for="has_variations"
                            class="flex cursor-pointer items-start gap-3"
                        >
                            <input
                                v-model="form.has_variations"
                                type="checkbox"
                                id="has_variations"
                                class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]"
                            />
                            <span>
                                <span
                                    class="block text-sm font-semibold text-slate-800"
                                    >This product has variations</span
                                >
                                <span
                                    class="mt-0.5 block text-xs text-slate-500"
                                    >Enable to configure packaging, quantities,
                                    weight, and dimensions.</span
                                >
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Section: Dynamic Child Variations Array Subform Matrix -->
                <div
                    v-if="form.has_variations"
                    class="space-y-4 border-t border-slate-100 pt-5"
                >
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h4 class="text-sm font-semibold text-slate-900">
                                Product variations
                            </h4>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Add a packaging option and its logistics
                                details.
                            </p>
                        </div>
                        <button
                            type="button"
                            @click="addVariationRow"
                            class="inline-flex items-center justify-center gap-1.5 self-start rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-900 transition hover:bg-emerald-100 focus-visible:ring-2 focus-visible:ring-emerald-800/20 focus-visible:outline-none"
                        >
                            <span class="text-base leading-none">+</span> Add
                            variation
                        </button>
                    </div>

                    <div
                        v-if="form.variations.length === 0"
                        class="rounded-xl border border-dashed border-slate-300 bg-slate-50/70 px-4 py-6 text-center text-xs text-slate-500"
                    >
                        No variations added yet. Use “Add variation” to
                        configure a packaging option.
                    </div>

                    <div
                        v-for="(v, index) in form.variations"
                        :key="index"
                        class="relative grid grid-cols-1 gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:grid-cols-2 md:grid-cols-4"
                    >
                        <button
                            type="button"
                            @click="removeVariationRow(index)"
                            :aria-label="`Remove variation ${index + 1}`"
                            class="absolute top-2 right-2 grid h-8 w-8 place-items-center rounded-lg border border-slate-200 bg-white text-lg text-slate-500 transition hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 focus-visible:ring-2 focus-visible:ring-rose-500/20 focus-visible:outline-none"
                        >
                            &times;
                        </button>

                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[10px] font-semibold tracking-wide text-slate-500 uppercase"
                                >Unit of measure</label
                            >
                            <select
                                v-model="v.unit_of_measurement_id"
                                required
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                            >
                                <option
                                    v-for="u in lookups.uoms"
                                    :key="u.id"
                                    :value="u.id"
                                >
                                    {{ u.name }} ({{ u.code }})
                                </option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[10px] font-semibold tracking-wide text-slate-500 uppercase"
                                >Quantity per unit</label
                            >
                            <input
                                v-model="v.qty_per_unit"
                                type="number"
                                step="0.01"
                                required
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[10px] font-semibold tracking-wide text-slate-500 uppercase"
                                >Variation SKU</label
                            >
                            <input
                                v-model="v.variation_sku"
                                type="text"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[10px] font-semibold tracking-wide text-slate-500 uppercase"
                                >Price</label
                            >
                            <input
                                v-model="v.price"
                                type="number"
                                step="0.01"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[10px] font-semibold tracking-wide text-slate-500 uppercase"
                                >Weight (kg)</label
                            >
                            <input
                                v-model="v.weight"
                                type="number"
                                step="0.001"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label
                                class="text-[10px] font-semibold tracking-wide text-slate-500 uppercase"
                                >Dimensions (L × W × H cm)</label
                            >
                            <div class="flex gap-1">
                                <input
                                    v-model="v.length"
                                    type="number"
                                    aria-label="Length"
                                    placeholder="L"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-2 py-2.5 text-center text-xs transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                                />
                                <input
                                    v-model="v.width"
                                    type="number"
                                    aria-label="Width"
                                    placeholder="W"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-2 py-2.5 text-center text-xs transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                                />
                                <input
                                    v-model="v.height"
                                    type="number"
                                    aria-label="Height"
                                    placeholder="H"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-2 py-2.5 text-center text-xs transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                                />
                            </div>
                        </div>
                        <div class="flex flex-col gap-1 sm:col-span-2">
                            <label
                                class="text-[10px] font-semibold tracking-wide text-slate-500 uppercase"
                                >HS code override</label
                            >
                            <input
                                v-model="v.variation_hs_code"
                                type="text"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-2.5 font-mono text-xs transition outline-none focus:border-emerald-800 focus:ring-4 focus:ring-emerald-900/10"
                                placeholder="e.g. 84713020"
                            />
                        </div>
                    </div>
                </div>

                <!-- Form Commit Footer Steps -->
                <div
                    class="flex flex-col-reverse gap-2 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end"
                >
                    <button
                        type="button"
                        @click="modal.formOpen = false"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 focus-visible:ring-2 focus-visible:ring-slate-300 focus-visible:outline-none"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="rounded-xl bg-[var(--brand-primary)] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[var(--brand-primary-strong)] focus-visible:ring-4 focus-visible:ring-emerald-900/15 focus-visible:outline-none"
                    >
                        {{ modal.isEdit ? 'Save changes' : 'Create product' }}
                    </button>
                </div>
            </form>
        </BaseModal>

        <!-- INTEGRATED READ-ONLY BLUEPRINT DETAILS MODAL -->
        <BaseModal
            :show="modal.viewOpen"
            title="Integrated Product Catalog Logistics Blueprint"
            size="lg"
            @close="modal.viewOpen = false"
        >
            <div v-if="selectedProduct" class="space-y-5">
                <div
                    class="flex flex-col gap-3 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div>
                        <h3
                            class="text-xl font-bold tracking-tight text-slate-900"
                        >
                            {{ selectedProduct.name }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            SKU
                            <span
                                class="font-mono font-semibold text-slate-700"
                                >{{ selectedProduct.sku }}</span
                            >
                        </p>
                    </div>
                    <span
                        v-if="selectedProduct.category?.name"
                        class="self-start rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-900"
                        >{{ selectedProduct.category.name }}</span
                    >
                </div>

                <div
                    class="rounded-xl border border-slate-200 bg-slate-50/80 p-4"
                >
                    <p
                        class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase"
                    >
                        Description
                    </p>
                    <p class="mt-1.5 text-sm leading-6 text-slate-700">
                        {{
                            selectedProduct.description ||
                            'No description provided.'
                        }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p
                            class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase"
                        >
                            HS code
                        </p>
                        <p
                            class="mt-1.5 font-mono text-sm font-semibold text-slate-800"
                        >
                            {{ selectedProduct.hs_code || 'Not assigned' }}
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <p
                            class="text-[11px] font-semibold tracking-wide text-slate-500 uppercase"
                        >
                            Packaging
                        </p>
                        <p class="mt-1.5 text-sm font-semibold text-slate-800">
                            {{
                                selectedProduct.has_variations
                                    ? 'Multiple variations'
                                    : 'Standard unit'
                            }}
                        </p>
                    </div>
                </div>

                <!-- Bidirectional Variations Configuration Tables Matrix -->
                <div v-if="selectedProduct.has_variations" class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-slate-900">
                            Registered variations
                        </h4>
                        <span class="text-xs text-slate-500"
                            >{{
                                selectedProduct.variations.length
                            }}
                            options</span
                        >
                    </div>
                    <div
                        class="overflow-x-auto rounded-xl border border-slate-200"
                    >
                        <table
                            class="w-full min-w-[640px] border-collapse text-left text-xs"
                        >
                            <thead
                                class="border-b border-slate-200 bg-slate-50 font-semibold text-slate-500"
                            >
                                <tr>
                                    <th class="p-3">Variant SKU</th>
                                    <th class="p-3">UOM Target Block</th>
                                    <th class="p-3">Price</th>
                                    <th class="p-3">Weight Mass</th>
                                    <th class="p-3">Physical Dimensions</th>
                                    <th class="p-3">HS Override</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                <tr
                                    v-for="v in selectedProduct.variations"
                                    :key="v.id"
                                    class="transition-colors hover:bg-slate-50"
                                >
                                    <td class="p-3 font-mono text-slate-600">
                                        {{ v.variation_sku || 'Inherited' }}
                                    </td>
                                    <td class="p-3">
                                        <span
                                            class="font-semibold text-slate-900"
                                            >{{ v.uom?.name }}</span
                                        >
                                        (x{{ v.qty_per_unit }})
                                    </td>
                                    <td
                                        class="p-3 font-semibold text-emerald-800"
                                    >
                                        ${{ v.price || '0.00' }}
                                    </td>
                                    <td class="p-3 text-slate-600">
                                        {{
                                            v.weight ? `${v.weight} KG` : 'N/A'
                                        }}
                                    </td>
                                    <td class="p-3 font-mono text-slate-600">
                                        {{
                                            v.length
                                                ? `${v.length}×${v.width}×${v.height} cm`
                                                : 'N/A'
                                        }}
                                    </td>
                                    <td class="p-3 font-mono text-slate-500">
                                        {{ v.variation_hs_code || 'Inherited' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
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

const products = ref([]);
const selectedProduct = ref(null);
let searchTimeout = null;

const filters = reactive({ search: '', category_id: '' });
const modal = reactive({
    formOpen: false,
    viewOpen: false,
    isEdit: false,
    currentId: null,
});
const lookups = reactive({ categories: [], uoms: [] });

const form = reactive({
    name: '',
    sku: '',
    pk_category_id: '',
    hbill_category_id: '',
    edpass_category_id: '',
    description: '',
    hs_code: '',
    has_variations: false,
    variations: [],
});

const fetchProducts = async () => {
    const response = await api.get('/products', { params: filters });
    products.value = response.data.data;
};

const fetchLookupDatasets = async () => {
    const response = await api.get('/products/lookups');
    lookups.categories = response.data.categories;
    lookups.uoms = response.data.uoms;
};

const debounceSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        fetchProducts();
    }, 350);
};

const openCreateModal = () => {
    modal.isEdit = false;
    modal.currentId = null;
    modal.formOpen = true;
    Object.assign(form, {
        name: '',
        sku: '',
        pk_category_id: '',
        hbill_category_id: '',
        edpass_category_id: '',
        description: '',
        hs_code: '',
        has_variations: false,
        variations: [],
    });
};

const openEditModal = (prod) => {
    modal.isEdit = true;
    modal.currentId = prod.id;
    modal.formOpen = true;
    Object.assign(form, JSON.parse(JSON.stringify(prod)));
    form.has_variations = !!form.has_variations;
};

const openViewModal = (prod) => {
    selectedProduct.value = prod;
    modal.viewOpen = true;
};

const addVariationRow = () => {
    form.variations.push({
        unit_of_measurement_id: '',
        qty_per_unit: 1.0,
        variation_sku: '',
        price: 0.0,
        weight: 0.0,
        length: 0.0,
        width: 0.0,
        height: 0.0,
        variation_hs_code: '',
    });
};

const removeVariationRow = (index) => {
    form.variations.splice(index, 1);
};

const saveForm = async () => {
    try {
        const payload = {
            ...form,
            has_variations: form.has_variations ? 1 : 0,
        };
        if (modal.isEdit) {
            await api.put(`/products/${modal.currentId}`, payload);
        } else {
            await api.post('/products', payload);
        }
        modal.formOpen = false;
        fetchProducts();
    } catch (err) {
        alert(
            err.response?.data?.message ||
                'Database constraints collision parsing product inputs.',
        );
    }
};

const deleteProduct = async (id) => {
    if (
        confirm(
            'Permanently wipe this catalog product row and cascade purge variations records?',
        )
    ) {
        await api.delete(`/products/${id}`);
        fetchProducts();
    }
};

onMounted(() => {
    fetchProducts();
    fetchLookupDatasets();
});
</script>

<style scoped>
input[type='checkbox'] {
    accent-color: var(--brand-primary);
}
.divide-y > :not([hidden]) ~ :not([hidden]) {
    border-top-width: 1px;
}
</style>
