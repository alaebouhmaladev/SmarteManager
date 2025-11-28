<!-- src/views/Suppliers/SuppliersView.vue -->
<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
          Suppliers
        </h2>
        <p class="text-xs text-neutral-500">
          Manage your business suppliers, contacts and linked purchases.
        </p>
      </div>

      <PrimaryButton type="button" @click="openCreate">
        + New Supplier
      </PrimaryButton>
    </div>

    <!-- Search Bar -->
    <div class="sm-card p-4 max-w-md">
      <InputField
        v-model="search"
        label="Search"
        placeholder="Search suppliers..."
      />
    </div>

    <!-- Suppliers Table -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Name
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Contact
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Total Purchases
        </th>
        <th class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase">
          Actions
        </th>
      </template>

      <template #body>
        <!-- Loading -->
        <tr v-if="suppliersStore.loadingList">
          <td colspan="4" class="px-4 py-6 text-center text-xs text-neutral-500">
            Loading suppliers...
          </td>
        </tr>

        <!-- Rows -->
        <tr
          v-for="s in filteredSuppliers"
          :key="s.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2 font-medium text-sm-dark">
            {{ s.name }}
          </td>
          <td class="px-4 py-2">
            <p class="text-sm">{{ s.phone || '—' }}</p>
            <p class="text-xs text-neutral-500">{{ s.email || '—' }}</p>
          </td>
          <td class="px-4 py-2 font-semibold">
            {{ formatMoney(s.total_purchases || 0) }}
          </td>
          <td class="px-4 py-2 text-right">
            <div class="flex items-center justify-end gap-3">
              <button
                class="text-xs text-sm-dark hover:underline"
                type="button"
                @click="openEdit(s)"
              >
                Edit
              </button>

              <button
                class="text-xs text-sm-dark hover:underline"
                type="button"
                @click="goToDetails(s.id)"
              >
                View
              </button>

              <button
                class="text-xs text-red-500 hover:underline"
                type="button"
                @click="confirmDelete(s.id)"
              >
                Delete
              </button>
            </div>
          </td>
        </tr>

        <!-- Empty -->
        <tr v-if="!suppliersStore.loadingList && filteredSuppliers.length === 0">
          <td colspan="4" class="px-4 py-6 text-center text-xs text-neutral-500">
            No suppliers found.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>{{ filteredSuppliers.length }} supplier(s)</span>
      </template>
    </TableBase>

    <!-- INLINE MODAL: Create / Edit supplier -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/40"
    >
      <div class="sm-card w-full max-w-xl mx-4 p-6 bg-white relative">
        <!-- Close button -->
        <button
          type="button"
          class="absolute top-3 right-3 text-neutral-400 hover:text-neutral-700 text-sm"
          @click="showModal = false"
        >
          ✕
        </button>

        <!-- Header inside modal -->
        <div class="mb-4">
          <h3 class="text-base font-semibold text-sm-dark">
            {{ isEditing ? 'Edit supplier' : 'Add supplier' }}
          </h3>
          <p class="text-xs text-neutral-500">
            {{ isEditing
              ? 'Update supplier information.'
              : 'Create a new supplier in your list.'
            }}
          </p>
        </div>

        <!-- Form -->
        <form class="space-y-3" @submit.prevent="saveSupplier">
          <InputField
            v-model="form.name"
            label="Name"
            placeholder="AgroDist Industries"
            required
          />

          <InputField
            v-model="form.phone"
            label="Phone"
            placeholder="+212..."
          />

          <InputField
            v-model="form.email"
            label="Email"
            type="email"
            placeholder="contact@supplier.ma"
          />

          <InputField
            v-model="form.address"
            label="Address"
            placeholder="City, street..."
          />
        </form>

        <!-- Footer buttons -->
        <div class="mt-4 flex items-center justify-end gap-2">
          <button
            type="button"
            class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
            @click="showModal = false"
          >
            Cancel
          </button>

          <PrimaryButton
            type="button"
            :loading="suppliersStore.saving"
            @click="saveSupplier"
          >
            {{ isEditing ? 'Save changes' : 'Add Supplier' }}
          </PrimaryButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import InputField from '@/components/ui/InputField.vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import TableBase from '@/components/ui/TableBase.vue'
import { useSuppliersStore } from '@/stores/suppliers'

const router = useRouter()
const suppliersStore = useSuppliersStore()

/* ---------- Search ---------- */
const search = ref('')

const filteredSuppliers = computed(() => {
  const list = suppliersStore.suppliers || []
  const q = search.value.toLowerCase()

  return list.filter((s) =>
    (s.name || '').toLowerCase().includes(q),
  )
})

/* ---------- Modal state ---------- */
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const form = reactive({
  name: '',
  phone: '',
  email: '',
  address: '',
})

function resetForm() {
  form.name = ''
  form.phone = ''
  form.email = ''
  form.address = ''
  editingId.value = null
  isEditing.value = false
}

function openCreate() {
  resetForm()
  showModal.value = true
}

function openEdit(supplier) {
  isEditing.value = true
  editingId.value = supplier.id

  form.name = supplier.name || ''
  form.phone = supplier.phone || ''
  form.email = supplier.email || ''
  form.address = supplier.address || ''

  showModal.value = true
}

async function saveSupplier() {
  const payload = {
    name: form.name,
    phone: form.phone || null,
    email: form.email || null,
    address: form.address || null,
  }

  try {
    if (isEditing.value && editingId.value) {
      await suppliersStore.updateSupplier(editingId.value, payload)
    } else {
      await suppliersStore.createSupplier(payload)
    }

    showModal.value = false
    resetForm()
  } catch (e) {
    // errors already handled via toasts in the store
  }
}

async function confirmDelete(id) {
  const ok = window.confirm('Delete this supplier? This action cannot be undone.')
  if (!ok) return
  await suppliersStore.deleteSupplier(id)
}

function goToDetails(id) {
  router.push({ name: 'supplier-overview', params: { id } })
}

/* ---------- Utils ---------- */
function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(Number(value) || 0)
}

/* ---------- Init ---------- */
onMounted(() => {
  suppliersStore.fetchSuppliers()
})
</script>
