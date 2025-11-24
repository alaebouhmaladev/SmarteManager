<template>
  <div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-lg font-semibold text-sm-dark dark:text-neutral-50">
          Expenses
        </h2>
        <p class="text-xs text-neutral-500">
          Track your monthly expenses and see how much each supplier costs you.
        </p>
      </div>

      <PrimaryButton @click="openCreate">
        + New expense
      </PrimaryButton>
    </div>

    <!-- Filters + summary -->
    <div
      class="sm-card p-4 space-y-4 md:space-y-0 md:flex md:items-start md:justify-between md:gap-6"
    >
      <!-- Filters -->
      <div class="space-y-3 max-w-md w-full">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <!-- Month -->
          <div>
            <label class="block text-xs font-medium text-neutral-700 mb-1">
              Month
            </label>
            <input
              v-model="filters.month"
              type="month"
              class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
            />
          </div>

          <!-- Supplier -->
          <div>
            <label class="block text-xs font-medium text-neutral-700 mb-1">
              Supplier
            </label>
            <select
              v-model="filters.supplierId"
              class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
            >
              <option value="">All suppliers</option>
              <option
                v-for="s in suppliers"
                :key="s.id"
                :value="s.id"
              >
                {{ s.name }}
              </option>
            </select>
          </div>
        </div>

        <!-- Category filter -->
        <div>
          <label class="block text-xs font-medium text-neutral-700 mb-1">
            Category
          </label>
          <select
            v-model="filters.category"
            class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
          >
            <option value="">All categories</option>
            <option v-for="cat in categories" :key="cat" :value="cat">
              {{ cat }}
            </option>
          </select>
        </div>
      </div>

      <!-- Summary cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 flex-1">
        <div class="sm-card p-4">
          <p class="text-xs text-neutral-500">Total expenses</p>
          <p class="text-xl font-semibold mt-1">
            {{ formatMoney(summary.totalAmount) }}
          </p>
        </div>

        <div class="sm-card p-4">
          <p class="text-xs text-neutral-500">Transactions</p>
          <p class="text-xl font-semibold mt-1">
            {{ summary.count }}
          </p>
        </div>

        <div class="sm-card p-4">
          <p class="text-xs text-neutral-500">Top supplier</p>
          <p class="text-sm font-medium mt-1">
            {{ summary.topSupplier || '—' }}
          </p>
        </div>
      </div>
    </div>

    <!-- Expenses table -->
    <TableBase>
      <template #head>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Date
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Supplier
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Category
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Amount
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Status
        </th>
        <th class="px-4 py-2 text-left text-[11px] font-medium text-neutral-500 uppercase">
          Note
        </th>
        <th class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase">
          Actions
        </th>
      </template>

      <template #body>
        <tr
          v-for="exp in filteredExpenses"
          :key="exp.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2">
            {{ exp.date }}
          </td>
          <td class="px-4 py-2">
            {{ getSupplierName(exp.supplier_id) || 'Other' }}
          </td>
          <td class="px-4 py-2">
            {{ exp.category }}
          </td>
          <td class="px-4 py-2 font-medium">
            {{ formatMoney(exp.amount) }}
          </td>
          <td class="px-4 py-2">
            <span
              class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px]"
              :class="exp.paid ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'"
            >
              <span
                class="h-1.5 w-1.5 rounded-full"
                :class="exp.paid ? 'bg-emerald-500' : 'bg-amber-500'"
              />
              {{ exp.paid ? 'Paid' : 'Pending' }}
            </span>
          </td>
          <td class="px-4 py-2">
            <span class="text-xs text-neutral-600">
              {{ exp.note || '—' }}
            </span>
          </td>
          <td class="px-4 py-2 text-right">
            <div class="inline-flex items-center gap-2">
              <button
                class="text-xs text-sm-dark hover:underline"
                @click="openEdit(exp)"
              >
                Edit
              </button>
              <button
                class="text-xs text-red-500 hover:underline"
                @click="deleteExpense(exp.id)"
              >
                Delete
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="filteredExpenses.length === 0">
          <td colspan="7" class="px-4 py-6 text-center text-xs text-neutral-500">
            No expenses for the selected filters.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>{{ filteredExpenses.length }} expense(s) in this view</span>
      </template>
    </TableBase>

    <!-- Create/Edit expense modal -->
    <ModalBase
      v-model="showModal"
      :title="isEditing ? 'Edit expense' : 'New expense'"
      :subtitle="isEditing ? 'Update this expense record.' : 'Add a new expense to your list.'"
    >
      <form class="space-y-3" @submit.prevent="saveExpense">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <InputField
            v-model="form.date"
            label="Date"
            type="date"
            required
          />

          <div>
            <label class="block text-xs font-medium text-neutral-700 mb-1">
              Supplier
            </label>
            <select
              v-model="form.supplier_id"
              class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
            >
              <option value="">Other / none</option>
              <option
                v-for="s in suppliers"
                :key="s.id"
                :value="s.id"
              >
                {{ s.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-neutral-700 mb-1">
              Category
            </label>
            <select
              v-model="form.category"
              class="w-full rounded-xl border border-neutral-200 bg-white px-3 py-2 text-sm
                     focus:outline-none focus:ring-2 focus:ring-sm-yellow focus:border-sm-yellow"
              required
            >
              <option disabled value="">Select category</option>
              <option v-for="cat in categories" :key="cat" :value="cat">
                {{ cat }}
              </option>
            </select>
          </div>

          <InputField
            v-model="form.amount"
            label="Amount (MAD)"
            type="number"
            required
          />
        </div>

        <InputField
          v-model="form.note"
          label="Note (optional)"
          placeholder="Rent, utilities, ingredients..."
        />

        <!-- Paid toggle -->
        <div class="flex items-center justify-between pt-2">
          <span class="text-xs text-neutral-700">Paid</span>
          <button
            type="button"
            class="relative inline-flex h-6 w-10 items-center rounded-full transition border border-neutral-200"
            :class="form.paid ? 'bg-emerald-500' : 'bg-neutral-200'"
            @click="form.paid = !form.paid"
          >
            <span
              class="h-4 w-4 inline-block transform rounded-full bg-white shadow transition-transform"
              :class="form.paid ? 'translate-x-4' : 'translate-x-1'"
            ></span>
          </button>
        </div>
      </form>

      <template #footer>
        <button
          class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-600 hover:bg-neutral-100"
          @click="showModal = false"
        >
          Cancel
        </button>
        <PrimaryButton type="button" :loading="saving" @click="saveExpense">
          {{ isEditing ? 'Save changes' : 'Add expense' }}
        </PrimaryButton>
      </template>
    </ModalBase>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import InputField from '@/components/ui/InputField.vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import TableBase from '@/components/ui/TableBase.vue'
import ModalBase from '@/components/ui/ModalBase.vue'

/* ---------------- MOCK SUPPLIERS (same vibe as SuppliersView) --------------- */
const suppliers = [
  {
    id: 1,
    name: 'AgroDist Industries',
  },
  {
    id: 2,
    name: 'FoodMaster Delivery',
  },
  {
    id: 3,
    name: 'Moroccan Flour Co.',
  },
]

/* ---------------- MOCK EXPENSES -------------------------------------------- */
const expenses = ref([
  {
    id: 1,
    date: '2025-11-01',
    supplier_id: 3,
    category: 'Ingredients',
    amount: 1800,
    paid: true,
    note: 'Flour order',
  },
  {
    id: 2,
    date: '2025-11-02',
    supplier_id: 2,
    category: 'Delivery',
    amount: 450,
    paid: true,
    note: 'Delivery service',
  },
  {
    id: 3,
    date: '2025-11-03',
    supplier_id: null,
    category: 'Rent',
    amount: 8000,
    paid: true,
    note: 'Local rent',
  },
  {
    id: 4,
    date: '2025-11-05',
    supplier_id: 1,
    category: 'Ingredients',
    amount: 1250,
    paid: false,
    note: 'Tomato & cheese',
  },
])

/* ---------------- FILTERS -------------------------------------------------- */
const filters = reactive({
  month: '2025-11',
  supplierId: '',
  category: '',
})

const categories = ['Ingredients', 'Rent', 'Delivery', 'Utilities', 'Other']

const filteredExpenses = computed(() => {
  return expenses.value.filter((exp) => {
    // Month filter (YYYY-MM compare with date substring)
    if (filters.month && exp.date.slice(0, 7) !== filters.month) return false

    // Supplier filter
    if (filters.supplierId && exp.supplier_id !== Number(filters.supplierId)) {
      return false
    }

    // Category filter
    if (filters.category && exp.category !== filters.category) return false

    return true
  })
})

/* ---------------- SUMMARY (monthly) ---------------------------------------- */
const summary = computed(() => {
  const list = filteredExpenses.value
  const totalAmount = list.reduce((sum, e) => sum + e.amount, 0)
  const count = list.length

  // Compute amount by supplier
  const bySupplier = new Map()
  for (const e of list) {
    if (!e.supplier_id) continue
    const prev = bySupplier.get(e.supplier_id) || 0
    bySupplier.set(e.supplier_id, prev + e.amount)
  }

  let topSupplier = ''
  let max = 0
  for (const [id, amount] of bySupplier.entries()) {
    if (amount > max) {
      max = amount
      const supplier = suppliers.find((s) => s.id === id)
      topSupplier = supplier ? supplier.name : ''
    }
  }

  return {
    totalAmount,
    count,
    topSupplier,
  }
})

/* ---------------- CREATE / EDIT MODAL -------------------------------------- */
const showModal = ref(false)
const isEditing = ref(false)
const saving = ref(false)
const editId = ref(null)

const form = reactive({
  date: '',
  supplier_id: '',
  category: '',
  amount: '',
  note: '',
  paid: true,
})

function resetForm() {
  form.date = ''
  form.supplier_id = ''
  form.category = ''
  form.amount = ''
  form.note = ''
  form.paid = true
  editId.value = null
  isEditing.value = false
}

function openCreate() {
  resetForm()
  showModal.value = true
}

function openEdit(exp) {
  form.date = exp.date
  form.supplier_id = exp.supplier_id || ''
  form.category = exp.category
  form.amount = exp.amount
  form.note = exp.note
  form.paid = exp.paid
  editId.value = exp.id
  isEditing.value = true
  showModal.value = true
}

function saveExpense() {
  saving.value = true

  setTimeout(() => {
    if (isEditing.value && editId.value != null) {
      const index = expenses.value.findIndex((e) => e.id === editId.value)
      if (index !== -1) {
        expenses.value[index] = {
          ...expenses.value[index],
          date: form.date,
          supplier_id: form.supplier_id ? Number(form.supplier_id) : null,
          category: form.category,
          amount: Number(form.amount),
          note: form.note,
          paid: form.paid,
        }
      }
    } else {
      const newId =
        expenses.value.length > 0
          ? Math.max(...expenses.value.map((e) => e.id)) + 1
          : 1

      expenses.value.push({
        id: newId,
        date: form.date,
        supplier_id: form.supplier_id ? Number(form.supplier_id) : null,
        category: form.category,
        amount: Number(form.amount),
        note: form.note,
        paid: form.paid,
      })
    }

    saving.value = false
    showModal.value = false
    resetForm()
  }, 300)
}

function deleteExpense(id) {
  expenses.value = expenses.value.filter((e) => e.id !== id)
}

/* ---------------- HELPERS -------------------------------------------------- */
function getSupplierName(id) {
  const s = suppliers.find((s) => s.id === id)
  return s ? s.name : ''
}

function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(value || 0)
}
</script>
