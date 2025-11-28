// src/stores/suppliers.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'

export const useSuppliersStore = defineStore('suppliers', {
  state: () => ({
    // List + single
    suppliers: [],        // all suppliers (index)
    supplier: null,       // single supplier (show/edit)

    // Overview dashboard data
    supplierOverview: null, // { supplier, totals, expenses, purchases }

    // Loading flags
    loadingList: false,
    loadingSingle: false,
    loadingOverview: false,
    saving: false,
    deleting: false,

    error: null,
  }),

  actions: {
    /* --------------------------------------------------
     * GET /suppliers  (list)
     * -------------------------------------------------- */
    async fetchSuppliers() {
      const ui = useUiStore()
      this.loadingList = true
      this.error = null

      try {
        const { data } = await http.get('/suppliers')
        this.suppliers = data
      } catch (err) {
        console.error('FETCH SUPPLIERS ERROR', err.response?.data || err)
        this.error = 'Failed to load suppliers.'
        ui.pushToast({
          type: 'error',
          title: 'Suppliers',
          message: 'Failed to load suppliers from server.',
        })
      } finally {
        this.loadingList = false
      }
    },

    /* --------------------------------------------------
     * GET /suppliers/{id}  (single)
     * -------------------------------------------------- */
    async fetchSupplier(id) {
      if (!id) return
      const ui = useUiStore()
      this.loadingSingle = true
      this.error = null

      try {
        const { data } = await http.get(`/suppliers/${id}`)
        this.supplier = data

        // Also update it in list if already present
        const idx = this.suppliers.findIndex((s) => s.id === data.id)
        if (idx !== -1) {
          this.suppliers[idx] = data
        }
      } catch (err) {
        console.error('FETCH SUPPLIER ERROR', err.response?.data || err)
        this.error = 'Failed to load supplier.'
        ui.pushToast({
          type: 'error',
          title: 'Suppliers',
          message: 'Failed to load this supplier.',
        })
      } finally {
        this.loadingSingle = false
      }
    },

    /* --------------------------------------------------
     * GET /suppliers/{id}/overview
     * -------------------------------------------------- */
    async fetchSupplierOverview(id) {
      if (!id) return
      const ui = useUiStore()
      this.loadingOverview = true
      this.error = null

      try {
        const { data } = await http.get(`/suppliers/${id}/overview`)
        this.supplierOverview = data
      } catch (err) {
        console.error('SUPPLIER OVERVIEW ERROR', err.response?.data || err)
        this.error = 'Failed to load supplier overview.'
        ui.pushToast({
          type: 'error',
          title: 'Suppliers',
          message: 'Failed to load supplier overview.',
        })
      } finally {
        this.loadingOverview = false
      }
    },

    /* --------------------------------------------------
     * POST /suppliers  (create)
     * -------------------------------------------------- */
    async createSupplier(payload) {
      const ui = useUiStore()
      this.saving = true
      this.error = null

      try {
        const { data } = await http.post('/suppliers', payload)

        // Add to list
        this.suppliers.push(data)

        ui.pushToast({
          type: 'success',
          title: 'Suppliers',
          message: `Supplier "${data.name}" created successfully.`,
        })

        return data
      } catch (err) {
        console.error('CREATE SUPPLIER ERROR', err.response?.data || err)
        const msg =
          err.response?.data?.message ||
          'Unable to create supplier. Please check the required fields.'
        this.error = msg
        ui.pushToast({
          type: 'error',
          title: 'Suppliers',
          message: msg,
        })
        throw err
      } finally {
        this.saving = false
      }
    },

    /* --------------------------------------------------
     * PUT /suppliers/{id}  (update)
     * -------------------------------------------------- */
    async updateSupplier(id, payload) {
      if (!id) return
      const ui = useUiStore()
      this.saving = true
      this.error = null

      try {
        const { data } = await http.put(`/suppliers/${id}`, payload)

        // Update in list
        const idx = this.suppliers.findIndex((s) => s.id === id)
        if (idx !== -1) {
          this.suppliers[idx] = data
        }

        // Update single if currently selected
        if (this.supplier?.id === id) {
          this.supplier = data
        }

        ui.pushToast({
          type: 'success',
          title: 'Suppliers',
          message: `Supplier "${data.name}" updated successfully.`,
        })

        return data
      } catch (err) {
        console.error('UPDATE SUPPLIER ERROR', err.response?.data || err)
        const msg =
          err.response?.data?.message ||
          'Unable to update supplier. Please check your input.'
        this.error = msg
        ui.pushToast({
          type: 'error',
          title: 'Suppliers',
          message: msg,
        })
        throw err
      } finally {
        this.saving = false
      }
    },

    /* --------------------------------------------------
     * DELETE /suppliers/{id}
     * -------------------------------------------------- */
    async deleteSupplier(id) {
      if (!id) return
      const ui = useUiStore()
      this.deleting = true
      this.error = null

      try {
        await http.delete(`/suppliers/${id}`)

        // Remove from list
        this.suppliers = this.suppliers.filter((s) => s.id !== id)

        // Clear single / overview if they were this supplier
        if (this.supplier?.id === id) this.supplier = null
        if (this.supplierOverview?.supplier?.id === id) {
          this.supplierOverview = null
        }

        ui.pushToast({
          type: 'success',
          title: 'Suppliers',
          message: 'Supplier deleted.',
        })
      } catch (err) {
        console.error('DELETE SUPPLIER ERROR', err.response?.data || err)
        const msg =
          err.response?.data?.message ||
          'Unable to delete supplier.'
        this.error = msg
        ui.pushToast({
          type: 'error',
          title: 'Suppliers',
          message: msg,
        })
        throw err
      } finally {
        this.deleting = false
      }
    },
  },
})
