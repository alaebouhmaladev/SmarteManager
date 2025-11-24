<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
          Products
        </h2>
        <p class="text-xs text-neutral-500">
          Manage stock items and their quantities.
        </p>
      </div>

      <PrimaryButton @click="openCreate">
        + New Product
      </PrimaryButton>
    </div>

    <!-- Filters -->
    <div class="sm-card p-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex-1 max-w-xs">
        <InputField
          v-model="search"
          label="Search"
          placeholder="Search by name or SKU"
        />
      </div>

      <div class="flex items-center gap-2">
        <button
          v-for="opt in stockFilters"
          :key="opt.value"
          class="px-3 py-1.5 rounded-full text-xs border transition"
          :class="selectedStock === opt.value
            ? 'bg-sm-dark text-sm-cream border-sm-dark'
            : 'bg-white text-neutral-600 border-neutral-200 hover:bg-neutral-100'"
          @click="selectedStock = opt.value"
        >
          {{ opt.label }}
        </button>
      </div>
    </div>

    <!-- Products table -->
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
          Avg. cost
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Stock value
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Status
        </th>
        <th class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase">
          Actions
        </th>
      </template>

      <template #body>
        <tr
          v-for="p in filteredProducts"
          :key="p.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2">
            <p class="font-medium text-sm-dark dark:text-neutral-100">
              {{ p.name }}
            </p>
          </td>
          <td class="px-4 py-2">
            <span class="text-xs text-neutral-500">{{ p.sku }}</span>
          </td>
          <td class="px-4 py-2">
            {{ p.stock_qty }}
          </td>
          <td class="px-4 py-2">
            {{ p.avg_cost }} MAD
          </td>
          <td class="px-4 py-2">
            {{ formatMoney(p.stock_qty * p.avg_cost) }}
          </td>
          <td class="px-4 py-2">
            <span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
              :class="statusBadgeClass(p)"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="statusDotClass(p)"
              ></span>
              {{ statusLabel(p) }}
            </span>
          </td>
          <td class="px-4 py-2 text-right">
            <div class="inline-flex items-center gap-2">
              <button
                class="text-xs text-sm-dark hover:underline"
                @click="openStockMovement(p)"
              >
                Adjust stock
              </button>
              <button
                class="text-xs text-sm-dark hover:underline"
                @click="openEdit(p)"
              >
                Edit
              </button>
              <button
                class="text-xs text-red-500 hover:underline"
                @click="deleteProduct(p.id)"
              >
                Delete
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="filteredProducts.length === 0">
          <td colspan="7" class="px-4 py-6 text-center text-xs text-neutral-500">
            No products found.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>Showing {{ filteredProducts.length }} product(s)</span>
      </template>
    </TableBase>

    <!-- Product create/edit modal -->
    <ModalBase
      v-model="showProductModal"
      :title="isEditing ? 'Edit product' : 'Create product'"
      :subtitle="isEditing ? 'Update product information.' : 'Add a new stock item.'"
    >
      <form class="space-y-3" @submit.prevent="saveProduct">
        <InputField
          v-model="productForm.name"
          label="Name"
          placeholder="Flour 25kg bag"
          required
        />
        <InputField
          v-model="productForm.sku"
          label="SKU"
          placeholder="FL-25"
          required
        />
        <InputField
          v-model="productForm.avg_cost"
          label="Average cost (MAD)"
          type="number"
          required
        />
        <InputField
          v-model="productForm.stock_qty"
          label="Current stock quantity"
          type="number"
          required
        />
        <InputField
          v-model="productForm.low_stock_threshold"
          label="Low-stock threshold"
          type="number"
          required
        />
      </form>

      <template #footer>
        <button
          class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
          @click="showProductModal = false"
        >
          Cancel
        </button>
        <PrimaryButton type="button" :loading="saving" @click="saveProduct">
          {{ isEditing ? 'Save changes' : 'Create product' }}
        </PrimaryButton>
      </template>
    </ModalBase>

    <!-- Stock movement modal -->
    <ModalBase
      v-model="showMovementModal"
      :title="'Stock movement – ' + (activeProduct?.name || '')"
      subtitle="Record stock in/out for this product."
    >
      <div v-if="activeProduct" class="space-y-3 text-sm">
        <p class="text-xs text-neutral-500">
          Current stock:
          <span class="font-semibold text-sm-dark">
            {{ activeProduct.stock_qty }}
          </span>
        </p>

        <!-- Type -->
        <div class="space-y-1.5">
          <label class="block text-xs font-medium text-neutral-700">
            Movement type
          </label>
          <div class="flex gap-2">
            <button
              v-for="opt in movementTypes"
              :key="opt.value"
              type="button"
              class="px-3 py-1.5 rounded-full text-xs border transition"
              :class="movementForm.type === opt.value
                ? 'bg-sm-dark text-sm-cream border-sm-dark'
                : 'bg-white text-neutral-600 border-neutral-200 hover:bg-neutral-100'"
              @click="movementForm.type = opt.value"
            >
              {{ opt.label }}
            </button>
          </div>
        </div>

        <!-- Quantity -->
        <InputField
          v-model="movementForm.quantity"
          label="Quantity"
          type="number"
          required
        />

        <!-- Warning if negative -->
        <p
          v-if="wouldBeNegative"
          class="text-[11px] text-red-500"
        >
          This movement would make stock negative. Please reduce quantity.
        </p>

        <!-- Note -->
        <InputField
          v-model="movementForm.note"
          label="Note (optional)"
          placeholder="Delivery, correction, spoiled stock..."
        />
      </div>

      <template #footer>
        <button
          class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
          @click="showMovementModal = false"
        >
          Cancel
        </button>
        <PrimaryButton
          type="button"
          :disabled="wouldBeNegative"
          :loading="savingMovement"
          @click="saveMovement"
        >
          Save movement
        </PrimaryButton>
      </template>
    </ModalBase>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import InputField from '@/components/ui/InputField.vue'
import TableBase from '@/components/ui/TableBase.vue'
import ModalBase from '@/components/ui/ModalBase.vue'

/* ------------------ MOCK PRODUCTS ------------------ */
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

/* ------------------ Filters ------------------ */
const search = ref('')
const selectedStock = ref('all')

const stockFilters = [
  { value: 'all', label: 'All' },
  { value: 'ok', label: 'In stock' },
  { value: 'low', label: 'Low stock' },
  { value: 'out', label: 'Out of stock' },
]

const filteredProducts = computed(() => {
  return products.value.filter((p) => {
    const matchesSearch =
      p.name.toLowerCase().includes(search.value.toLowerCase()) ||
      p.sku.toLowerCase().includes(search.value.toLowerCase())

    let matchesStock = true
    if (selectedStock.value === 'ok') {
      matchesStock = p.stock_qty > p.low_stock_threshold
    } else if (selectedStock.value === 'low') {
      matchesStock =
        p.stock_qty > 0 && p.stock_qty <= p.low_stock_threshold
    } else if (selectedStock.value === 'out') {
      matchesStock = p.stock_qty === 0
    }

    return matchesSearch && matchesStock
  })
})

/* ------------------ Product create/edit ------------------ */
const showProductModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const editId = ref(null)

const productForm = reactive({
  name: '',
  sku: '',
  avg_cost: '',
  stock_qty: '',
  low_stock_threshold: '',
})

function resetProductForm() {
  productForm.name = ''
  productForm.sku = ''
  productForm.avg_cost = ''
  productForm.stock_qty = ''
  productForm.low_stock_threshold = ''
  editId.value = null
  isEditing.value = false
}

function openCreate() {
  resetProductForm()
  showProductModal.value = true
}

function openEdit(p) {
  productForm.name = p.name
  productForm.sku = p.sku
  productForm.avg_cost = p.avg_cost
  productForm.stock_qty = p.stock_qty
  productForm.low_stock_threshold = p.low_stock_threshold
  editId.value = p.id
  isEditing.value = true
  showProductModal.value = true
}

function saveProduct() {
  saving.value = true

  setTimeout(() => {
    if (isEditing.value && editId.value != null) {
      const index = products.value.findIndex((p) => p.id === editId.value)
      if (index !== -1) {
        products.value[index] = {
          ...products.value[index],
          name: productForm.name,
          sku: productForm.sku,
          avg_cost: Number(productForm.avg_cost),
          stock_qty: Number(productForm.stock_qty),
          low_stock_threshold: Number(productForm.low_stock_threshold),
        }
      }
    } else {
      const newId =
        products.value.length > 0
          ? Math.max(...products.value.map((p) => p.id)) + 1
          : 1
      products.value.push({
        id: newId,
        name: productForm.name,
        sku: productForm.sku,
        avg_cost: Number(productForm.avg_cost),
        stock_qty: Number(productForm.stock_qty),
        low_stock_threshold: Number(productForm.low_stock_threshold),
      })
    }

    saving.value = false
    showProductModal.value = false
    resetProductForm()
  }, 400)
}

function deleteProduct(id) {
  products.value = products.value.filter((p) => p.id !== id)
}

/* ------------------ Status helpers ------------------ */
function statusLabel(p) {
  if (p.stock_qty === 0) return 'Out of stock'
  if (p.stock_qty <= p.low_stock_threshold) return 'Low stock'
  return 'In stock'
}

function statusBadgeClass(p) {
  if (p.stock_qty === 0) return 'bg-red-50 text-red-600'
  if (p.stock_qty <= p.low_stock_threshold) return 'bg-amber-50 text-amber-700'
  return 'bg-emerald-50 text-emerald-700'
}

function statusDotClass(p) {
  if (p.stock_qty === 0) return 'bg-red-500'
  if (p.stock_qty <= p.low_stock_threshold) return 'bg-amber-500'
  return 'bg-emerald-500'
}

/* ------------------ Stock movement modal ------------------ */
const showMovementModal = ref(false)
const savingMovement = ref(false)
const activeProduct = ref(null)

const movementForm = reactive({
  type: 'in',
  quantity: '',
  note: '',
})

const movementTypes = [
  { value: 'in', label: 'Stock in' },
  { value: 'out', label: 'Stock out' },
]

const wouldBeNegative = computed(() => {
  if (!activeProduct.value) return false
  if (movementForm.type !== 'out') return false
  const qty = Number(movementForm.quantity || 0)
  return qty > activeProduct.value.stock_qty
})

function resetMovementForm() {
  movementForm.type = 'in'
  movementForm.quantity = ''
  movementForm.note = ''
}

function openStockMovement(p) {
  activeProduct.value = p
  resetMovementForm()
  showMovementModal.value = true
}

function saveMovement() {
  if (!activeProduct.value) return
  if (wouldBeNegative.value) return

  savingMovement.value = true

  setTimeout(() => {
    const qty = Number(movementForm.quantity || 0)
    const index = products.value.findIndex((p) => p.id === activeProduct.value.id)
    if (index !== -1) {
      const delta = movementForm.type === 'in' ? qty : -qty
      products.value[index].stock_qty += delta
    }

    savingMovement.value = false
    showMovementModal.value = false
    resetMovementForm()
  }, 300)
}

/* ------------------ Utils ------------------ */
function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value)
}
</script>
