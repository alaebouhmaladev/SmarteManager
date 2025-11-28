// src/stores/inventory.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'

export const useInventoryStore = defineStore('inventory', {
  state: () => ({
    // From InventoryController
    overviewProducts: [],    // /inventory/overview
    lowStockProducts: [],    // /inventory/low-stock
    totalValuation: 0,       // /inventory/valuation

    // Product history page
    productHistory: null,    // { product, from, to, movements: [...] }

    // Global movements list (optional screen)
    stockMovements: [],      // /stock-movements

    // Loading flags
    loadingOverview: false,
    loadingHistory: false,
    loadingMovements: false,

    // When creating movement (for buttons)
    creatingMovement: false,

    error: null,
  }),

  actions: {
    /* --------------------------------------------------
     * OVERVIEW: 3 endpoints in parallel
     *  - GET /inventory/overview
     *  - GET /inventory/low-stock
     *  - GET /inventory/valuation
     * -------------------------------------------------- */
    async fetchOverview() {
      this.loadingOverview = true
      this.error = null
      const ui = useUiStore()

      try {
        const [overviewRes, lowRes, valRes] = await Promise.all([
          http.get('/inventory/overview'),
          http.get('/inventory/low-stock'),
          http.get('/inventory/valuation'),
        ])

        this.overviewProducts = overviewRes.data || []
        this.lowStockProducts = lowRes.data || []
        this.totalValuation = valRes.data?.total_value ?? 0
      } catch (err) {
        console.error('INVENTORY OVERVIEW ERROR', err)
        this.error = 'Failed to load inventory overview.'
        ui.pushToast({
          type: 'error',
          title: 'Inventory',
          message: 'Failed to load inventory overview.',
        })
      } finally {
        this.loadingOverview = false
      }
    },

    /* --------------------------------------------------
     * PRODUCT HISTORY
     * GET /inventory/product/{productId}/history
     * Optional: from, to (YYYY-MM-DD)
     * -------------------------------------------------- */
    async fetchProductHistory(productId, { from = null, to = null } = {}) {
      if (!productId) return

      this.loadingHistory = true
      this.error = null
      const ui = useUiStore()

      const params = {}
      if (from) params.from = from
      if (to) params.to = to

      try {
        const { data } = await http.get(
          `/inventory/product/${productId}/history`,
          { params },
        )
        this.productHistory = data
      } catch (err) {
        console.error('PRODUCT HISTORY ERROR', err)
        this.error = 'Failed to load product history.'
        ui.pushToast({
          type: 'error',
          title: 'Inventory',
          message: 'Failed to load product history.',
        })
      } finally {
        this.loadingHistory = false
      }
    },

    /* --------------------------------------------------
     * STOCK MOVEMENTS LIST
     * GET /stock-movements
     * params example: { product_id, from, to }
     * -------------------------------------------------- */
    async fetchStockMovements(params = {}) {
      this.loadingMovements = true
      this.error = null
      const ui = useUiStore()

      try {
        const { data } = await http.get('/stock-movements', { params })
        this.stockMovements = data || []
      } catch (err) {
        console.error('STOCK MOVEMENTS ERROR', err)
        this.error = 'Failed to load stock movements.'
        ui.pushToast({
          type: 'error',
          title: 'Stock movements',
          message: 'Failed to load stock movements from server.',
        })
      } finally {
        this.loadingMovements = false
      }
    },

    /* --------------------------------------------------
     * CREATE STOCK MOVEMENT
     * POST /stock-movements
     * payload:
     * {
     *   product_id: 1,
     *   supplier_id: 2,
     *   type: "in" | "out",
     *   quantity: 10,
     *   unit_price: 20.5,
     *   movement_date: "2025-11-20",
     *   reason: "Purchase"
     * }
     * -------------------------------------------------- */
    async createStockMovement(payload) {
      this.creatingMovement = true
      const ui = useUiStore()

      try {
        const { data } = await http.post('/stock-movements', payload)

        // Keep movements list in sync (if loaded)
        if (!Array.isArray(this.stockMovements)) {
          this.stockMovements = []
        }
        this.stockMovements.unshift(data)

        // Refresh overview (stock, valuation, low-stock)
        this.fetchOverview()

        // If current history is for same product, refresh it
        if (this.productHistory?.product?.id === payload.product_id) {
          await this.fetchProductHistory(payload.product_id, {
            from: this.productHistory.from,
            to: this.productHistory.to,
          })
        }

        ui.pushToast({
          type: 'success',
          title: 'Stock movement',
          message: 'Stock movement saved successfully.',
        })

        return data
      } catch (err) {
        console.error('CREATE MOVEMENT ERROR', err.response?.data || err)
        const msg =
          err.response?.data?.message ||
          'Unable to save stock movement.'
        ui.pushToast({
          type: 'error',
          title: 'Stock movement',
          message: msg,
        })
        throw err
      } finally {
        this.creatingMovement = false
      }
    },
  },
})
