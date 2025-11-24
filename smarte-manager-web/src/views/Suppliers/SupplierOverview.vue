<template>
  <div class="space-y-4">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark">
          {{ supplier?.name || 'Supplier' }}
        </h2>
        <p class="text-xs text-neutral-500">
          Supplier information and linked purchases.
        </p>
      </div>

      <button
        class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
        @click="goBack"
      >
        Back
      </button>
    </div>

    <!-- Supplier Card -->
    <div class="sm-card p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

      <div>
        <p class="text-xs text-neutral-500">Contact person</p>
        <p class="text-sm font-medium">{{ supplier?.name }}</p>
      </div>

      <div>
        <p class="text-xs text-neutral-500">Phone</p>
        <p class="text-sm font-medium">{{ supplier?.phone }}</p>
      </div>

      <div>
        <p class="text-xs text-neutral-500">Email</p>
        <p class="text-sm font-medium">{{ supplier?.email }}</p>
      </div>

      <div>
        <p class="text-xs text-neutral-500">Address</p>
        <p class="text-sm font-medium">{{ supplier?.address }}</p>
      </div>

      <div class="sm:col-span-2">
        <p class="text-xs text-neutral-500">Total Purchases</p>
        <p class="text-xl font-semibold">
          {{ formatMoney(supplier?.total_purchases || 0) }}
        </p>
      </div>

    </div>

    <!-- List of linked purchases/expenses -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Date
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Item
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Cost
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Status
        </th>
      </template>

      <template #body>
        <tr
          v-for="p in supplierPurchases"
          :key="p.id"
          class="hover:bg-sm-cream/50 text-sm"
        >
          <td class="px-4 py-2">{{ p.date }}</td>
          <td class="px-4 py-2">{{ p.item }}</td>
          <td class="px-4 py-2 font-medium">{{ formatMoney(p.cost) }}</td>
          <td class="px-4 py-2">
            <span
              class="text-xs px-2 py-1 rounded-full"
              :class="p.paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
            >
              {{ p.paid ? 'Paid' : 'Pending' }}
            </span>
          </td>
        </tr>

        <tr v-if="supplierPurchases.length === 0">
          <td colspan="4" class="px-4 py-6 text-center text-xs text-neutral-500">
            No records found.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>{{ supplierPurchases.length }} record(s)</span>
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

// Mock suppliers
const suppliers = [
  {
    id: 1,
    name: 'AgroDist Industries',
    phone: '+212 600-123456',
    email: 'contact@agrodist.ma',
    address: 'Casablanca',
    total_purchases: 24500,
  },
  {
    id: 2,
    name: 'FoodMaster Delivery',
    phone: '+212 662-998877',
    email: 'support@foodmaster.ma',
    address: 'Rabat',
    total_purchases: 16780,
  },
  {
    id: 3,
    name: 'Moroccan Flour Co.',
    phone: '+212 611-445566',
    email: 'sales@flourco.ma',
    address: 'Marrakech',
    total_purchases: 9800,
  },
]

// Mock purchase/expense records
const purchases = [
  { id: 1, supplier_id: 1, date: '2025-11-01', item: 'Flour 25kg', cost: 1800, paid: true },
  { id: 2, supplier_id: 1, date: '2025-11-05', item: 'Tomato sauce', cost: 700, paid: false },
  { id: 3, supplier_id: 2, date: '2025-11-08', item: 'Cheese', cost: 2400, paid: true },
]

const id = Number(route.params.id)

const supplier = computed(() => suppliers.find((s) => s.id === id) || null)

const supplierPurchases = computed(() =>
  purchases.filter((p) => p.supplier_id === id)
)

function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value)
}

function goBack() {
  router.push({ name: 'suppliers' })
}
</script>
