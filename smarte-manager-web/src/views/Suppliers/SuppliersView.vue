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

      <PrimaryButton @click="openCreate">
        + New Supplier
      </PrimaryButton>
    </div>

    <!-- Search Bar -->
    <div class="sm-card p-4 max-w-md">
      <InputField v-model="search" label="Search" placeholder="Search suppliers..." />
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
        <tr
          v-for="s in filteredSuppliers"
          :key="s.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2 font-medium text-sm-dark">{{ s.name }}</td>
          <td class="px-4 py-2">
            <p class="text-sm">{{ s.phone }}</p>
            <p class="text-xs text-neutral-500">{{ s.email }}</p>
          </td>
          <td class="px-4 py-2 font-semibold">
            {{ formatMoney(s.total_purchases) }}
          </td>
          <td class="px-4 py-2 text-right">
            <div class="flex items-center justify-end gap-3">
              <button class="text-xs text-sm-dark hover:underline" @click="openEdit(s)">
                Edit
              </button>

              <button class="text-xs text-sm-dark hover:underline" @click="goToDetails(s.id)">
                View
              </button>

              <button class="text-xs text-red-500 hover:underline" @click="deleteSupplier(s.id)">
                Delete
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="filteredSuppliers.length === 0">
          <td colspan="4" class="px-4 py-6 text-center text-xs text-neutral-500">
            No suppliers found.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>{{ filteredSuppliers.length }} supplier(s)</span>
      </template>
    </TableBase>

    <!-- Create/Edit Supplier Modal -->
    <ModalBase
      v-model="showModal"
      :title="isEditing ? 'Edit Supplier' : 'New Supplier'"
      :subtitle="isEditing ? 'Update supplier information.' : 'Add a supplier to your list.'"
    >
      <form class="space-y-3" @submit.prevent="saveSupplier">

        <InputField v-model="form.name" label="Name" required />
        <InputField v-model="form.phone" label="Phone" required />
        <InputField v-model="form.email" label="Email" type="email" />
        <InputField v-model="form.address" label="Address" />

      </form>

      <template #footer>
        <button
          class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
          @click="showModal = false"
        >
          Cancel
        </button>

        <PrimaryButton :loading="saving" @click="saveSupplier">
          {{ isEditing ? 'Save changes' : 'Add Supplier' }}
        </PrimaryButton>
      </template>
    </ModalBase>

  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { useRouter } from 'vue-router'
import InputField from '@/components/ui/InputField.vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import TableBase from '@/components/ui/TableBase.vue'
import ModalBase from '@/components/ui/ModalBase.vue'

const router = useRouter()

/* ---------------- MOCK SUPPLIERS ---------------- */
const suppliers = ref([
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
])

/* ---------------- SEARCH ---------------- */
const search = ref('')

const filteredSuppliers = computed(() =>
  suppliers.value.filter((s) =>
    s.name.toLowerCase().includes(search.value.toLowerCase())
  )
)

/* ---------------- CREATE/EDIT ---------------- */
const showModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)
let editId = null

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
  editId = null
}

function openCreate() {
  resetForm()
  isEditing.value = false
  showModal.value = true
}

function openEdit(s) {
  isEditing.value = true
  editId = s.id

  form.name = s.name
  form.phone = s.phone
  form.email = s.email
  form.address = s.address

  showModal.value = true
}

function saveSupplier() {
  saving.value = true

  setTimeout(() => {
    if (isEditing.value) {
      const index = suppliers.value.findIndex((s) => s.id === editId)
      if (index !== -1) {
        suppliers.value[index] = {
          ...suppliers.value[index],
          name: form.name,
          phone: form.phone,
          email: form.email,
          address: form.address,
        }
      }
    } else {
      const newId =
        suppliers.value.length > 0
          ? Math.max(...suppliers.value.map((s) => s.id)) + 1
          : 1

      suppliers.value.push({
        id: newId,
        name: form.name,
        phone: form.phone,
        email: form.email,
        address: form.address,
        total_purchases: 0,
      })
    }

    saving.value = false
    showModal.value = false
    resetForm()
  }, 300)
}

function deleteSupplier(id) {
  suppliers.value = suppliers.value.filter((s) => s.id !== id)
}

function goToDetails(id) {
  router.push({ name: 'supplier-overview', params: { id } })
}

/* ---------------- UTILS ---------------- */
function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value)
}
</script>
