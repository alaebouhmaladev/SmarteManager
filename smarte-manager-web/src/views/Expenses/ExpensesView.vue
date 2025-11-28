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

      <div class="flex items-center gap-2">
        <button
          type="button"
          class="text-xs px-3 py-2 rounded-xl border border-neutral-200 text-neutral-700 hover:bg-neutral-100"
          @click="exportCsv"
        >
          Export CSV
        </button>

        <PrimaryButton type="button" @click="openCreate">
          + New expense
        </PrimaryButton>
      </div>
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
              @change="reloadForMonth"
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
                v-for="s in suppliersStore.suppliers"
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
          Note
        </th>
        <th class="px-4 py-2 text-right text-[11px] font-medium text-neutral-500 uppercase">
          Actions
        </th>
      </template>

      <template #body>
        <tr v-if="expensesStore.loadingList">
          <td colspan="6" class="px-4 py-6 text-center text-xs text-neutral-500">
            Loading expenses...
          </td>
        </tr>

        <tr
          v-for="exp in filteredExpenses"
          :key="exp.id"
          class="hover:bg-sm-cream/50 dark:hover:bg-neutral-900/60 text-sm"
        >
          <td class="px-4 py-2">
            {{ exp.expense_date }}
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
            <span class="text-xs text-neutral-600">
              {{ exp.notes || '—' }}
            </span>
          </td>
          <td class="px-4 py-2 text-right">
            <div class="inline-flex items-center gap-2">
              <button
                type="button"
                class="text-xs text-sm-dark hover:underline"
                @click="openEdit(exp)"
              >
                Edit
              </button>
              <button
                type="button"
                class="text-xs text-red-500 hover:underline"
                @click="confirmDelete(exp.id)"
              >
                Delete
              </button>
            </div>
          </td>
        </tr>

        <tr
          v-if="!expensesStore.loadingList && filteredExpenses.length === 0"
        >
          <td colspan="6" class="px-4 py-6 text-center text-xs text-neutral-500">
            No expenses for the selected filters.
          </td>
        </tr>
      </template>

      <template #footer>
        <span>{{ filteredExpenses.length }} expense(s) in this view</span>
      </template>
    </TableBase>

    <!-- SIMPLE INLINE MODAL: create / edit expense -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-40 flex items-center justify-center bg-black/40"
    >
      <div class="sm-card w-full max-w-md mx-4 p-4 bg-white relative">
        <!-- Close -->
        <button
          type="button"
          class="absolute top-3 right-3 text-neutral-400 hover:text-neutral-700 text-sm"
          @click="showModal = false"
        >
          ✕
        </button>

        <div class="mb-3">
          <h3 class="text-sm font-semibold text-sm-dark">
            {{ isEditing ? 'Edit expense' : 'New expense' }}
          </h3>
          <p class="text-[11px] text-neutral-500">
            {{ isEditing ? 'Update this expense record.' : 'Add a new expense to your list.' }}
          </p>
        </div>

        <form class="space-y-3" @submit.prevent="saveExpense">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <InputField
              v-model="form.expense_date"
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
                <option :value="null">Other / none</option>
                <option
                  v-for="s in suppliersStore.suppliers"
                  :key="s.id"
                  :value="s.id"
                >
                  {{ s.name }}
                </option>
              </select>
            </div>
          </div>

          <InputField
            v-model="form.category"
            label="Category"
            placeholder="Ingredients, Rent, Delivery..."
            required
          />

          <InputField
            v-model.number="form.amount"
            label="Amount (MAD)"
            type="number"
            min="0"
            step="0.01"
            required
          />

          <InputField
            v-model="form.notes"
            label="Note (optional)"
            placeholder="Rent, utilities, ingredients..."
          />
        </form>

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
            :loading="expensesStore.saving"
            @click="saveExpense"
          >
            {{ isEditing ? 'Save changes' : 'Add expense' }}
          </PrimaryButton>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue'
import PrimaryButton from '@/components/ui/PrimaryButton.vue'
import InputField from '@/components/ui/InputField.vue'
import TableBase from '@/components/ui/TableBase.vue'
import { useSuppliersStore } from '@/stores/suppliers'
import { useExpensesStore } from '@/stores/expenses'

const suppliersStore = useSuppliersStore()
const expensesStore = useExpensesStore()

/* -------- Filters -------- */
const filters = reactive({
  month: new Date().toISOString().slice(0, 7),
  supplierId: '',
  category: '',
})

const categories = ['Ingredients', 'Rent', 'Delivery', 'Utilities', 'Other']

const filteredExpenses = computed(() => {
  const list = expensesStore.expenses || []
  return list.filter((exp) => {
    if (filters.month && exp.expense_date?.slice(0, 7) !== filters.month) {
      return false
    }
    if (filters.supplierId && exp.supplier_id !== Number(filters.supplierId)) {
      return false
    }
    if (filters.category && exp.category !== filters.category) {
      return false
    }
    return true
  })
})

/* -------- Summary -------- */
const summary = computed(() => {
  const list = filteredExpenses.value
  const totalAmount = list.reduce((sum, e) => sum + Number(e.amount || 0), 0)
  const count = list.length

  const bySupplier = new Map()
  for (const e of list) {
    if (!e.supplier_id) continue
    const prev = bySupplier.get(e.supplier_id) || 0
    bySupplier.set(e.supplier_id, prev + Number(e.amount || 0))
  }

  let topSupplier = ''
  let max = 0
  for (const [id, amount] of bySupplier.entries()) {
    if (amount > max) {
      max = amount
      const supplier = suppliersStore.suppliers.find((s) => s.id === id)
      topSupplier = supplier ? supplier.name : ''
    }
  }

  return { totalAmount, count, topSupplier }
})

/* -------- Modal state -------- */
const showModal = ref(false)
const isEditing = ref(false)
const editId = ref(null)

const form = reactive({
  expense_date: '',
  supplier_id: null,
  category: '',
  amount: 0,
  notes: '',
})

function resetForm() {
  form.expense_date = filters.month ? `${filters.month}-01` : ''
  form.supplier_id = null
  form.category = ''
  form.amount = 0
  form.notes = ''
  editId.value = null
  isEditing.value = false
}

function openCreate() {
  resetForm()
  showModal.value = true
}

function openEdit(exp) {
  isEditing.value = true
  editId.value = exp.id
  form.expense_date = exp.expense_date
  form.supplier_id = exp.supplier_id
  form.category = exp.category
  form.amount = exp.amount
  form.notes = exp.notes || ''
  showModal.value = true
}

async function saveExpense() {
  const payload = {
    category: form.category,
    amount: Number(form.amount || 0),
    expense_date: form.expense_date,
    supplier_id: form.supplier_id || null,
    notes: form.notes || null,
  }

  try {
    if (isEditing.value && editId.value != null) {
      await expensesStore.updateExpense(editId.value, payload)
    } else {
      await expensesStore.createExpense(payload)
    }
    showModal.value = false
    resetForm()
  } catch (e) {
    // errors handled by store
  }
}

async function confirmDelete(id) {
  const ok = window.confirm('Delete this expense? This action cannot be undone.')
  if (!ok) return
  await expensesStore.deleteExpense(id)
}

/* -------- Helpers -------- */
function getSupplierName(id) {
  const s = suppliersStore.suppliers.find((s) => s.id === id)
  return s ? s.name : ''
}

function formatMoney(value) {
  return new Intl.NumberFormat('fr-MA', {
    style: 'currency',
    currency: 'MAD',
    maximumFractionDigits: 0,
  }).format(Number(value) || 0)
}

function reloadForMonth() {
  expensesStore.fetchMonthlySummary(filters.month)
}

function exportCsv() {
  const baseUrl = import.meta.env.VITE_API_BASE_URL || ''
  const url = `${baseUrl}/expenses/export-csv?month=${filters.month}`
  window.open(url, '_blank')
}

/* -------- Init -------- */
onMounted(async () => {
  await Promise.all([
    suppliersStore.fetchSuppliers?.(),
    expensesStore.fetchExpenses(),
    expensesStore.fetchMonthlySummary(filters.month),
  ])
  resetForm()
})
</script>
