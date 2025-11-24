<template>
  <div class="space-y-4">
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
        Back to inventory
      </button>
    </div>

    <div v-if="product" class="sm-card p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="space-y-1">
        <p class="text-xs text-neutral-500">Current stock</p>
        <p class="text-2xl font-semibold">
          {{ product.stock_qty }}
        </p>
      </div>
      <div class="space-y-1">
        <p class="text-xs text-neutral-500">Average cost</p>
        <p class="text-lg font-semibold">
          {{ product.avg_cost }} MAD
        </p>
      </div>
      <div class="space-y-1">
        <p class="text-xs text-neutral-500">Stock value</p>
        <p class="text-lg font-semibold">
          {{ formatMoney(product.stock_qty * product.avg_cost) }}
        </p>
      </div>
    </div>

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
          Stock after
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Note
        </th>
      </template>

      <template #body>
        <tr
          v-for="m in productMovements"
          :key="m.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2">{{ m.date }}</td>
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
          <td class="px-4 py-2">{{ m.quantity }}</td>
          <td class="px-4 py-2">{{ m.stock_after }}</td>
          <td class="px-4 py-2">{{ m.note || '—' }}</td>
        </tr>

        <tr v-if="productMovements.length === 0">
          <td colspan="5" class="px-4 py-6 text-center text-xs text-neutral-500">
            No movements recorded for this product (mock data).
          </td>
        </tr>
      </template>

      <template #footer>
        <span>{{ productMovements.length }} movement(s)</span>
      </template>
    </TableBase>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import TableBase from '@/components/ui/TableBase.vue'

const route = useRoute()
const router = useRouter()

const id = Number(route.params.id)

// MOCK products (must match ids used elsewhere)
const products = [
  {
    id: 1,
    name: 'Flour 25kg bag',
    sku: 'FL-25',
    stock_qty: 6,
    avg_cost: 180,
  },
  {
    id: 2,
    name: 'Tomato sauce 5L',
    sku: 'TS-5',
    stock_qty: 2,
    avg_cost: 70,
  },
  {
    id: 3,
    name: 'Cheese 10kg block',
    sku: 'CH-10',
    stock_qty: 0,
    avg_cost: 320,
  },
]

const movements = [
  {
    id: 1,
    product_id: 1,
    date: '2025-11-18',
    type: 'in',
    quantity: 10,
    stock_after: 10,
    note: 'Initial stock',
  },
  {
    id: 2,
    product_id: 1,
    date: '2025-11-19',
    type: 'out',
    quantity: 4,
    stock_after: 6,
    note: 'Used in production',
  },
  {
    id: 3,
    product_id: 2,
    date: '2025-11-19',
    type: 'in',
    quantity: 5,
    stock_after: 5,
    note: 'Purchase from supplier',
  },
]

const product = computed(() => products.find((p) => p.id === id) || null)

const productMovements = computed(() =>
  movements.filter((m) => m.product_id === id)
)

function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value)
}

function goBack() {
  router.push({ name: 'inventory' })
}
</script>
