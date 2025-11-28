<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
          {{ product?.name || 'Product history' }}
        </h2>
        <p class="text-xs text-neutral-500">
          SKU: {{ product?.sku || '—' }}
        </p>
      </div>

      <button
        class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
        @click="goBack"
      >
        Back to products
      </button>
    </div>

    <!-- Summary cards -->
    <div
      v-if="product"
      class="sm-card p-4 grid grid-cols-1 sm:grid-cols-4 gap-4"
    >
      <div>
        <p class="text-xs text-neutral-500">Current stock</p>
        <p class="text-2xl font-semibold">
          {{ product.current_stock }}
        </p>
      </div>
      <div>
        <p class="text-xs text-neutral-500">Average cost</p>
        <p class="text-lg font-semibold">
          {{ formatMoney(product.average_cost) }}
        </p>
      </div>
      <div>
        <p class="text-xs text-neutral-500">Total IN quantity</p>
        <p class="text-lg font-semibold">
          {{ totalIn }}
        </p>
      </div>
      <div>
        <p class="text-xs text-neutral-500">Total OUT quantity</p>
        <p class="text-lg font-semibold">
          {{ totalOut }}
        </p>
      </div>
    </div>

    <!-- Movements table -->
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
          Supplier
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Reason
        </th>
      </template>

      <template #body>
        <!-- Loading -->
        <tr v-if="inventoryStore.loadingHistory">
          <td colspan="6" class="px-4 py-6 text-center text-xs text-neutral-500">
            Loading product history...
          </td>
        </tr>

        <!-- Rows -->
        <tr
          v-for="m in movements"
          :key="m.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
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
              ></span>
              {{ m.type === 'in' ? 'Stock IN' : 'Stock OUT' }}
            </span>
          </td>
          <td class="px-4 py-2">
            {{ m.quantity }}
          </td>
          <td class="px-4 py-2">
            {{ m.type === 'in' ? formatMoney(m.unit_price) : '—' }}
          </td>
          <td class="px-4 py-2">
            {{ m.supplier?.name || '—' }}
          </td>
          <td class="px-4 py-2">
            {{ m.reason || '—' }}
          </td>
        </tr>

        <!-- Empty -->
        <tr
          v-if="!inventoryStore.loadingHistory && movements.length === 0"
        >
          <td colspan="6" class="px-4 py-6 text-center text-xs text-neutral-500">
            No stock movements recorded for this product.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>{{ movements.length }} movement(s)</span>
      </template>
    </TableBase>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import TableBase from '@/components/ui/TableBase.vue'
import { useInventoryStore } from '@/stores/inventory'

const route = useRoute()
const router = useRouter()
const inventoryStore = useInventoryStore()

const id = Number(route.params.id)

const productHistory = computed(() => inventoryStore.productHistory)
const product = computed(() => productHistory.value?.product || null)
const movements = computed(() => productHistory.value?.movements || [])

const totalIn = computed(() =>
  movements.value
    .filter((m) => m.type === 'in')
    .reduce((sum, m) => sum + Number(m.quantity || 0), 0),
)

const totalOut = computed(() =>
  movements.value
    .filter((m) => m.type === 'out')
    .reduce((sum, m) => sum + Number(m.quantity || 0), 0),
)

function formatMoney(value) {
  if (value == null) return '0 MAD'
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 2,
  }).format(Number(value) || 0)
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  if (Number.isNaN(d.getTime())) return dateStr
  return d.toLocaleDateString('fr-MA', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  })
}

function goBack() {
  router.push({ name: 'products' })
}

onMounted(async () => {
  if (id) {
    await inventoryStore.fetchProductHistory(id)
  }
})
</script>
