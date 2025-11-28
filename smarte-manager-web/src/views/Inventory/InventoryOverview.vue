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

      <PrimaryButton @click="goToProducts">
        View all products
      </PrimaryButton>
    </div>

    <!-- Stat cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="sm-card p-5">
        <p class="text-xs text-neutral-500">Total products</p>
        <p class="text-2xl font-semibold mt-1">
          {{ products.length }}
        </p>
      </div>

      <div class="sm-card p-5">
        <p class="text-xs text-neutral-500">Stock valuation</p>
        <p class="text-2xl font-semibold mt-1">
          {{ formatMoney(stockValuation) }}
        </p>
      </div>

      <div class="sm-card p-5">
        <p class="text-xs text-neutral-500">Low-stock items</p>
        <p class="text-2xl font-semibold mt-1">
          {{ lowStockProducts.length }}
        </p>
      </div>

      <div class="sm-card p-5">
        <p class="text-xs text-neutral-500">Out of stock</p>
        <p class="text-2xl font-semibold mt-1">
          {{ outOfStockCount }}
        </p>
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
        <!-- Loading -->
        <tr v-if="inventoryStore.loadingOverview">
          <td colspan="6" class="px-4 py-6 text-center text-xs text-neutral-500">
            Loading inventory overview...
          </td>
        </tr>

        <!-- Rows -->
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
              {{ p.sku || '—' }}
            </span>
          </td>
          <td class="px-4 py-2">
            {{ p.current_stock }}
          </td>
          <td class="px-4 py-2">
            {{ p.min_stock }}
          </td>
          <td class="px-4 py-2">
            <span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
              :class="p.current_stock === 0
                ? 'bg-red-50 text-red-600'
                : 'bg-amber-50 text-amber-700'"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="p.current_stock === 0 ? 'bg-red-500' : 'bg-amber-500'"
              ></span>
              {{ p.current_stock === 0 ? 'Out of stock' : 'Low stock' }}
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

        <!-- Empty -->
        <tr
          v-if="!inventoryStore.loadingOverview && lowStockProducts.length === 0"
        >
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
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import TableBase from '@/components/ui/TableBase.vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import { useInventoryStore } from '@/stores/inventory'

const router = useRouter()
const inventoryStore = useInventoryStore()

const products = computed(() => inventoryStore.overviewProducts || [])
const lowStockProducts = computed(() => inventoryStore.lowStockProducts || [])
const stockValuation = computed(() => inventoryStore.totalValuation || 0)

const outOfStockCount = computed(
  () => products.value.filter((p) => Number(p.current_stock) === 0).length,
)

function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value || 0)
}

function goToHistory(id) {
  router.push({ name: 'product-history', params: { id } })
}

function goToProducts() {
  router.push({ name: 'products' })
}

onMounted(async () => {
  await inventoryStore.fetchOverview()
})
</script>
