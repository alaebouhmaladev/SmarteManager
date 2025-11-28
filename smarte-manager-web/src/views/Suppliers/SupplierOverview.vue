<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark">
          {{ supplier?.name || 'Supplier' }}
        </h2>
        <p class="text-xs text-neutral-500">
          Supplier information, expenses and stock purchases.
        </p>
      </div>

      <button
        class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
        type="button"
        @click="goBack"
      >
        Back
      </button>
    </div>

    <!-- Loading / error -->
    <div v-if="suppliersStore.loadingOverview" class="text-xs text-neutral-500">
      Loading supplier overview...
    </div>
    <div v-else-if="suppliersStore.error" class="text-xs text-red-600">
      {{ suppliersStore.error }}
    </div>

    <!-- Supplier Card -->
    <div
      v-if="supplier"
      class="sm-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4"
    >
      <div>
        <p class="text-xs text-neutral-500">Contact person</p>
        <p class="text-sm font-medium">
          {{ supplier.contact_name || supplier.name }}
        </p>
      </div>

      <div>
        <p class="text-xs text-neutral-500">Phone</p>
        <p class="text-sm font-medium">
          {{ supplier.phone || '—' }}
        </p>
      </div>

      <div>
        <p class="text-xs text-neutral-500">Email</p>
        <p class="text-sm font-medium">
          {{ supplier.email || '—' }}
        </p>
      </div>

      <div>
        <p class="text-xs text-neutral-500">Address</p>
        <p class="text-sm font-medium">
          {{ supplier.address || '—' }}
        </p>
      </div>

      <div>
        <p class="text-xs text-neutral-500">Total expenses</p>
        <p class="text-xl font-semibold">
          {{ formatMoney(totals.total_expenses || 0) }}
        </p>
      </div>

      <div>
        <p class="text-xs text-neutral-500">Total purchases</p>
        <p class="text-xl font-semibold">
          {{ formatMoney(totals.total_purchases || 0) }}
        </p>
      </div>

      <div class="sm:col-span-2">
        <p class="text-xs text-neutral-500">Total spent</p>
        <p class="text-xl font-semibold">
          {{ formatMoney(totals.total_spent || 0) }}
        </p>
      </div>
    </div>

    <!-- Purchases table -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Date
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Type
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Quantity
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Unit price
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Total
        </th>
      </template>

      <template #body>
        <tr
          v-for="m in purchases"
          :key="m.id"
          class="hover:bg-sm-cream/50 text-sm"
        >
          <td class="px-4 py-2">
            {{ formatDate(m.movement_date) }}
          </td>
          <td class="px-4 py-2">
            <span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
              :class="m.type === 'in'
                ? 'bg-emerald-50 text-emerald-700'
                : 'bg-amber-50 text-amber-700'"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="m.type === 'in' ? 'bg-emerald-500' : 'bg-amber-500'"
              />
              {{ m.type === 'in' ? 'Stock in' : 'Stock out' }}
            </span>
          </td>
          <td class="px-4 py-2">
            {{ m.quantity }}
          </td>
          <td class="px-4 py-2">
            {{ formatMoney(m.unit_price || 0) }}
          </td>
          <td class="px-4 py-2 font-medium">
            {{ formatMoney((m.quantity || 0) * (m.unit_price || 0)) }}
          </td>
        </tr>

        <tr v-if="purchases.length === 0">
          <td colspan="5" class="px-4 py-6 text-center text-xs text-neutral-500">
            No stock purchases found for this supplier.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>{{ purchases.length }} purchase(s)</span>
      </template>
    </TableBase>
  </div>
</template>

<script setup>
import {
  computed,
  onMounted,
} from 'vue'
import { useRoute, useRouter } from 'vue-router'
import TableBase from '@/components/ui/TableBase.vue'
import { useSuppliersStore } from '@/stores/suppliers'

const route = useRoute()
const router = useRouter()
const suppliersStore = useSuppliersStore()

const id = Number(route.params.id)

/* ---------------- COMPUTED FROM STORE ---------------- */
const supplier = computed(
  () => suppliersStore.supplierOverview?.supplier || null,
)

const totals = computed(
  () => suppliersStore.supplierOverview?.totals || {},
)

const purchases = computed(
  () => suppliersStore.supplierOverview?.purchases || [],
)

/* ---------------- HELPERS ---------------- */
function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(Number(value) || 0)
}

function formatDate(value) {
  if (!value) return '—'
  const d = new Date(value)
  if (Number.isNaN(d.getTime())) return value
  return d.toLocaleDateString('fr-MA', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

function goBack() {
  router.push({ name: 'suppliers' })
}

/* ---------------- INIT ---------------- */
onMounted(async () => {
  if (id) {
    await suppliersStore.fetchSupplierOverview(id)
  }
})
</script>
