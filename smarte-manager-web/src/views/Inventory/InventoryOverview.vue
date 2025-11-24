<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
          Inventory overview
        </h2>
        <p class="text-xs text-neutral-500">
          Global stock valuation, low-stock alerts and quick summary.
        </p>
      </div>

      <!-- 👇 New button to open ProductsView -->
      <PrimaryButton @click="goToProducts">
        View all products
      </PrimaryButton>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="sm-card p-5">
        <p class="text-xs text-neutral-500">Total products</p>
        <p class="text-2xl font-semibold mt-1">{{ products.length }}</p>
      </div>

      <div class="sm-card p-5">
        <p class="text-xs text-neutral-500">Stock valuation</p>
        <p class="text-2xl font-semibold mt-1">
          {{ formatMoney(stockValuation) }}
        </p>
      </div>

      <div class="sm-card p-5">
        <p class="text-xs text-neutral-500">Low-stock items</p>
        <p class="text-2xl font-semibold mt-1">{{ lowStockCount }}</p>
      </div>

      <div class="sm-card p-5">
        <p class="text-xs text-neutral-500">Out of stock</p>
        <p class="text-2xl font-semibold mt-1">{{ outOfStockCount }}</p>
      </div>
    </div>

    <!-- Low-stock table -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Product
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          SKU
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Stock
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Threshold
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Status
        </th>
        <th class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase">
          History
        </th>
      </template>

      <template #body>
        <tr
          v-for="p in lowStockProducts"
          :key="p.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2">
            <p class="font-medium text-sm-dark dark:text-neutral-100">
              {{ p.name }}
            </p>
          </td>
          <td class="px-4 py-2">
            <span class="text-xs text-neutral-500">
              {{ p.sku }}
            </span>
          </td>
          <td class="px-4 py-2">
            {{ p.stock_qty }}
          </td>
          <td class="px-4 py-2">
            {{ p.low_stock_threshold }}
          </td>
          <td class="px-4 py-2">
            <span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
              :class="p.stock_qty === 0
                ? 'bg-red-50 text-red-600'
                : 'bg-amber-50 text-amber-700'"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="p.stock_qty === 0 ? 'bg-red-500' : 'bg-amber-500'"
              ></span>
              {{ p.stock_qty === 0 ? 'Out of stock' : 'Low stock' }}
            </span>
          </td>
          <td class="px-4 py-2 text-right">
            <button
              class="text-xs text-sm-dark hover:underline"
              @click="goToHistory(p.id)"
            >
              View history
            </button>
          </td>
        </tr>

        <tr v-if="lowStockProducts.length === 0">
          <td colspan="6" class="px-4 py-6 text-center text-xs text-neutral-500">
            No low-stock products. Good job!
          </td>
        </tr>
      </template>

      <template #footer>
        <span>Low-stock items: {{ lowStockProducts.length }}</span>
      </template>
    </TableBase>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import TableBase from '@/components/ui/TableBase.vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue' // 👈 added

const router = useRouter()

// MOCK PRODUCTS (same structure as ProductsView)
const products = ref([
  {
    id: 1,
    name: 'Flour 25kg bag',
    sku: 'FL-25',
    stock_qty: 6,
    avg_cost: 180,
    low_stock_threshold: 5,
  },
  {
    id: 2,
    name: 'Tomato sauce 5L',
    sku: 'TS-5',
    stock_qty: 2,
    avg_cost: 70,
    low_stock_threshold: 4,
  },
  {
    id: 3,
    name: 'Cheese 10kg block',
    sku: 'CH-10',
    stock_qty: 0,
    avg_cost: 320,
    low_stock_threshold: 2,
  },
])

const stockValuation = computed(() =>
  products.value.reduce((sum, p) => sum + p.stock_qty * p.avg_cost, 0)
)

const lowStockProducts = computed(() =>
  products.value.filter((p) => p.stock_qty <= p.low_stock_threshold)
)

const lowStockCount = computed(() => lowStockProducts.value.length)

const outOfStockCount = computed(
  () => products.value.filter((p) => p.stock_qty === 0).length
)

function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value)
}

function goToHistory(id) {
  router.push({ name: 'product-history', params: { id } })
}

// function to open Products View
function goToProducts() {
  router.push({ name: 'products' })
}
</script>
