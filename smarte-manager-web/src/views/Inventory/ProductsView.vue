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

      <!-- Right side buttons -->
      <div class="flex items-center gap-2">
        <button
          class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
          type="button"
          @click="goBack"
        >
          Back to products
        </button>

        <PrimaryButton type="button" @click="openCreate">
          Add product
        </PrimaryButton>
      </div>
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
    </div>

    <!-- Products table -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Name
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          SKU
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Unit
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Current stock
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Average cost
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Stock value
        </th>
        <th class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase">
          Actions
        </th>
      </template>

      <template #body>
        <!-- Loading -->
        <tr v-if="productsStore.loadingList">
          <td colspan="7" class="px-4 py-6 text-center text-xs text-neutral-500">
            Loading products...
          </td>
        </tr>

        <!-- Rows -->
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
            <span class="text-xs text-neutral-500">
              {{ p.sku || '—' }}
            </span>
          </td>
          <td class="px-4 py-2">
            <span class="text-xs text-neutral-600">
              {{ p.unit || '—' }}
            </span>
          </td>
          <td class="px-4 py-2">
            {{ Number(p.current_stock ?? 0).toFixed(2) }}
          </td>
          <td class="px-4 py-2">
            {{ formatMoney(p.average_cost) }}
          </td>
          <td class="px-4 py-2">
            {{ formatMoney((p.current_stock || 0) * (p.average_cost || 0)) }}
          </td>
          <td class="px-4 py-2 text-right">
            <div class="inline-flex items-center gap-2">
              <button
                class="text-xs text-sm-dark hover:underline"
                type="button"
                @click="openMovement(p)"
              >
                Add movement
              </button>
              <button
                class="text-xs text-sm-dark hover:underline"
                type="button"
                @click="openEdit(p)"
              >
                Edit
              </button>
              <button
                class="text-xs text-sm-dark hover:underline"
                type="button"
                @click="goToHistory(p.id)"
              >
                View history
              </button>
              <button
                class="text-xs text-red-500 hover:underline"
                type="button"
                @click="confirmDelete(p.id)"
              >
                Delete
              </button>
            </div>
          </td>
        </tr>

        <!-- Empty -->
        <tr v-if="!productsStore.loadingList && filteredProducts.length === 0">
          <td colspan="7" class="px-4 py-6 text-center text-xs text-neutral-500">
            No products found.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>Showing {{ filteredProducts.length }} product(s)</span>
      </template>
    </TableBase>

    <!-- SIMPLE INLINE MODAL: Create / Edit product -->
    <div
      v-if="showProductModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/40"
    >
      <div class="sm-card w-full max-w-md mx-4 p-4 bg-white relative">
        <!-- Close -->
        <button
          class="absolute top-3 right-3 text-neutral-400 hover:text-neutral-700 text-sm"
          type="button"
          @click="showProductModal = false"
        >
          ✕
        </button>

        <div class="mb-3">
          <h3 class="text-sm font-semibold text-sm-dark">
            {{ isEditing ? 'Edit product' : 'Add product' }}
          </h3>
          <p class="text-[11px] text-neutral-500">
            {{ isEditing
              ? 'Update product information.'
              : 'Create a new product in your inventory.'
            }}
          </p>
        </div>

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
          />
          <InputField
            v-model="productForm.unit"
            label="Unit"
            placeholder="kg, L, pcs…"
            required
          />
          <InputField
            v-model.number="productForm.min_stock"
            label="Minimum stock"
            type="number"
            min="0"
            step="0.01"
            required
          />
          <!-- No current_stock / average_cost here:
               stock & cost are managed ONLY via movements -->
        </form>

        <div class="mt-4 flex items-center justify-end gap-2">
          <button
            type="button"
            class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
            @click="showProductModal = false"
          >
            Cancel
          </button>
          <PrimaryButton
            type="button"
            :loading="productsStore.saving"
            @click="saveProduct"
          >
            {{ isEditing ? 'Save changes' : 'Create product' }}
          </PrimaryButton>
        </div>
      </div>
    </div>

    <!-- SIMPLE INLINE MODAL: Stock movement -->
    <div
      v-if="showMovementModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/40"
    >
      <div class="sm-card w-full max-w-md mx-4 p-4 bg-white relative">
        <!-- Close -->
        <button
          class="absolute top-3 right-3 text-neutral-400 hover:text-neutral-700 text-sm"
          type="button"
          @click="showMovementModal = false"
        >
          ✕
        </button>

        <div class="mb-3">
          <h3 class="text-sm font-semibold text-sm-dark">
            Stock movement – {{ activeProduct?.name || '' }}
          </h3>
          <p class="text-[11px] text-neutral-500">
            Record IN/OUT stock movement for this product.
          </p>
        </div>

        <div v-if="activeProduct" class="space-y-3 text-sm">
          <p class="text-xs text-neutral-500">
            Current stock:
            <span class="font-semibold text-sm-dark">
              {{ Number(activeProduct.current_stock ?? 0).toFixed(2) }}
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
            v-model.number="movementForm.quantity"
            label="Quantity"
            type="number"
            min="0"
            step="0.01"
            required
          />

          <InputField
            v-if="movementForm.type === 'in'"
            v-model.number="movementForm.unit_price"
            label="Unit price (MAD)"
            type="number"
            min="0"
            step="0.01"
            required
          />

          <div v-if="movementForm.type === 'in'" class="space-y-1.5">
            <label class="block text-xs font-medium text-neutral-700">
              Supplier (optional)
            </label>
            <select
              v-model="movementForm.supplier_id"
              class="w-full rounded-xl border border-neutral-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sm-dark focus:border-sm-dark bg-white"
            >
              <option :value="null">No supplier</option>
              <option
                v-for="s in suppliersStore.suppliers"
                :key="s.id"
                :value="s.id"
              >
                {{ s.name }}
              </option>
            </select>
          </div>

          <!-- Date -->
          <InputField
            v-model="movementForm.movement_date"
            label="Movement date"
            type="date"
          />

          <!-- Reason -->
          <InputField
            v-model="movementForm.reason"
            label="Reason (optional)"
            placeholder="Purchase, adjustment, waste..."
          />

          <!-- Warning if OUT would go negative -->
          <p
            v-if="wouldBeNegative"
            class="text-[11px] text-red-500"
          >
            This movement would make stock negative. Please reduce quantity.
          </p>
        </div>

        <div class="mt-4 flex items-center justify-end gap-2">
          <button
            type="button"
            class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
            @click="showMovementModal = false"
          >
            Cancel
          </button>
          <PrimaryButton
            type="button"
            :disabled="wouldBeNegative"
            :loading="inventoryStore.creatingMovement"
            @click="saveMovement"
          >
            Save movement
          </PrimaryButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import {
  computed,
  onMounted,
  reactive,
  ref,
} from 'vue'
import { useRouter } from 'vue-router'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import InputField from '@/components/ui/InputField.vue'
import TableBase from '@/components/ui/TableBase.vue'
import { useProductsStore } from '@/stores/products'
import { useInventoryStore } from '@/stores/inventory'
import { useSuppliersStore } from '@/stores/suppliers'

const router = useRouter()
const productsStore = useProductsStore()
const inventoryStore = useInventoryStore()
const suppliersStore = useSuppliersStore()

/* ------------------ Filters ------------------ */
const search = ref('')

const filteredProducts = computed(() => {
  const list = productsStore.products || []
  const q = search.value.toLowerCase()

  return list.filter((p) => {
    const n = (p.name || '').toLowerCase()
    const sku = (p.sku || '').toLowerCase()
    return n.includes(q) || sku.includes(q)
  })
})

/* ------------------ Product modal ------------------ */
const showProductModal = ref(false)
const isEditing = ref(false)
const editId = ref(null)

const productForm = reactive({
  name: '',
  sku: '',
  unit: '',
  min_stock: 0,
})

function resetProductForm() {
  productForm.name = ''
  productForm.sku = ''
  productForm.unit = ''
  productForm.min_stock = 0
  isEditing.value = false
  editId.value = null
}

function openCreate() {
  resetProductForm()
  showProductModal.value = true
}

function openEdit(p) {
  productForm.name = p.name
  productForm.sku = p.sku
  productForm.unit = p.unit
  productForm.min_stock = p.min_stock
  editId.value = p.id
  isEditing.value = true
  showProductModal.value = true
}

async function saveProduct() {
  const payload = {
    name: productForm.name,
    sku: productForm.sku || null,
    unit: productForm.unit,
    min_stock: Number(productForm.min_stock ?? 0),
  }

  try {
    if (isEditing.value && editId.value != null) {
      await productsStore.updateProduct(editId.value, payload)
    } else {
      await productsStore.createProduct(payload)
    }
    showProductModal.value = false
    resetProductForm()
  } catch (e) {
    // errors already toasted by store
  }
}

async function confirmDelete(id) {
  const ok = window.confirm('Delete this product? This action cannot be undone.')
  if (!ok) return
  await productsStore.deleteProduct(id)
}

/* ------------------ Stock movement modal ------------------ */
const showMovementModal = ref(false)
const activeProduct = ref(null)

const movementForm = reactive({
  type: 'in',
  quantity: 0,
  unit_price: 0,
  supplier_id: null,
  movement_date: '',
  reason: '',
})

const movementTypes = [
  { value: 'in', label: 'In To Stock' },
  { value: 'out', label: 'Out Of Stock' },
]

const wouldBeNegative = computed(() => {
  if (!activeProduct.value) return false
  if (movementForm.type !== 'out') return false
  const qty = Number(movementForm.quantity || 0)
  return qty > Number(activeProduct.value.current_stock || 0)
})

function resetMovementForm() {
  movementForm.type = 'in'
  movementForm.quantity = 0
  movementForm.unit_price = 0
  movementForm.supplier_id = null
  movementForm.movement_date = ''
  movementForm.reason = ''
}

function openMovement(p) {
  activeProduct.value = p
  resetMovementForm()
  showMovementModal.value = true
}

async function saveMovement() {
  if (!activeProduct.value) return
  if (wouldBeNegative.value) return

  const payload = {
    product_id: activeProduct.value.id,
    supplier_id: movementForm.supplier_id || null,
    type: movementForm.type,
    quantity: Number(movementForm.quantity || 0),
    unit_price:
      movementForm.type === 'in'
        ? Number(movementForm.unit_price || 0)
        : null,
    movement_date: movementForm.movement_date || null,
    reason: movementForm.reason || null,
  }

  try {
    await inventoryStore.createStockMovement(payload)
    showMovementModal.value = false
    resetMovementForm()
    await productsStore.fetchProducts()
  } catch (e) {
    // errors handled by store
  }
}

/* ------------------ Helpers ------------------ */
function formatMoney(value) {
  if (value == null) return '0 MAD'
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(Number(value) || 0)
}

function goToHistory(id) {
  router.push({ name: 'product-history', params: { id } })
}

/* back button */
function goBack() {
  // either go back in history, or push to a specific route if you want
  router.back()
  // or: router.push({ name: 'inventory-overview' })
}

/* ------------------ Init ------------------ */
onMounted(async () => {
  await Promise.all([
    productsStore.fetchProducts(),
    suppliersStore.fetchSuppliers(),
  ])
})
</script>
