// src/stores/expenses.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'

export const useExpensesStore = defineStore('expenses', {
  state: () => ({
    expenses: [],          // full list
    loadingList: false,
    saving: false,
    deleting: false,

    // Monthly summary
    monthlySummary: {
      month: null,
      total: 0,
      by_category: [],
    },

    error: null,
  }),

  actions: {
    /* ---------------- LIST ---------------- */
    async fetchExpenses() {
      this.loadingList = true
      this.error = null
      const ui = useUiStore()

      try {
        const { data } = await http.get('/expenses')
        this.expenses = data
      } catch (err) {
        console.error('FETCH EXPENSES ERROR', err)
        this.error = 'Failed to load expenses.'
        ui.pushToast({
          type: 'error',
          title: 'Expenses',
          message: 'Failed to load expenses from server.',
        })
      } finally {
        this.loadingList = false
      }
    },

    /* ---------------- CREATE ---------------- */
    async createExpense(payload) {
      this.saving = true
      const ui = useUiStore()

      try {
        const { data } = await http.post('/expenses', payload)
        this.expenses.unshift(data)

        ui.pushToast({
          type: 'success',
          title: 'Expenses',
          message: 'Expense added successfully.',
        })

        return data
      } catch (err) {
        console.error('CREATE EXPENSE ERROR', err.response?.data || err)
        const msg = err.response?.data?.message || 'Unable to create expense.'
        ui.pushToast({
          type: 'error',
          title: 'Expenses',
          message: msg,
        })
        throw err
      } finally {
        this.saving = false
      }
    },

    /* ---------------- UPDATE ---------------- */
    async updateExpense(id, payload) {
      this.saving = true
      const ui = useUiStore()

      try {
        const { data } = await http.put(`/expenses/${id}`, payload)

        const idx = this.expenses.findIndex((e) => e.id === id)
        if (idx !== -1) {
          this.expenses[idx] = data
        }

        ui.pushToast({
          type: 'success',
          title: 'Expenses',
          message: 'Expense updated successfully.',
        })

        return data
      } catch (err) {
        console.error('UPDATE EXPENSE ERROR', err.response?.data || err)
        const msg = err.response?.data?.message || 'Unable to update expense.'
        ui.pushToast({
          type: 'error',
          title: 'Expenses',
          message: msg,
        })
        throw err
      } finally {
        this.saving = false
      }
    },

    /* ---------------- DELETE ---------------- */
    async deleteExpense(id) {
      this.deleting = true
      const ui = useUiStore()

      try {
        await http.delete(`/expenses/${id}`)
        this.expenses = this.expenses.filter((e) => e.id !== id)

        ui.pushToast({
          type: 'success',
          title: 'Expenses',
          message: 'Expense deleted.',
        })
      } catch (err) {
        console.error('DELETE EXPENSE ERROR', err.response?.data || err)
        const msg = err.response?.data?.message || 'Unable to delete expense.'
        ui.pushToast({
          type: 'error',
          title: 'Expenses',
          message: msg,
        })
      } finally {
        this.deleting = false
      }
    },

    /* ---------------- MONTHLY SUMMARY ---------------- */
    async fetchMonthlySummary(month) {
      const ui = useUiStore()
      try {
        const { data } = await http.get('/expenses/monthly-summary', {
          params: { month },
        })
        this.monthlySummary = data
      } catch (err) {
        console.error('MONTHLY SUMMARY ERROR', err.response?.data || err)
        ui.pushToast({
          type: 'error',
          title: 'Expenses',
          message: 'Failed to load monthly summary.',
        })
      }
    },

    /* optional: expenses by supplier */
    async fetchBySupplier({ supplierId, from = null, to = null }) {
      const params = {}
      if (from) params.from = from
      if (to) params.to = to

      const { data } = await http.get(
        `/expenses/by-supplier/${supplierId}`,
        { params },
      )
      return data
    },
  },
})
