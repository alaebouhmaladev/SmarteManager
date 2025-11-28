// src/stores/products.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'

export const useProductsStore = defineStore('products', {
  state: () => ({
    products: [],   // list from /products
    product: null,  // single product for edit/view

    loadingList: false,
    saving: false,
    deleting: false,

    error: null,
  }),

  actions: {
    /* --------------------------------------------------
     * LIST PRODUCTS
     * GET /products
     * -------------------------------------------------- */
    async fetchProducts() {
      this.loadingList = true
      this.error = null
      const ui = useUiStore()

      try {
        const { data } = await http.get('/products')
        this.products = data || []
      } catch (err) {
        console.error('FETCH PRODUCTS ERROR', err)
        this.error = 'Failed to load products.'
        ui.pushToast({
          type: 'error',
          title: 'Products',
          message: 'Failed to load products from server.',
        })
      } finally {
        this.loadingList = false
      }
    },

    /* --------------------------------------------------
     * CREATE PRODUCT
     * POST /products
     * -------------------------------------------------- */
    async createProduct(form) {
      this.saving = true
      const ui = useUiStore()

      try {
        const { data } = await http.post('/products', form)

        if (!Array.isArray(this.products)) this.products = []
        this.products.push(data)

        ui.pushToast({
          type: 'success',
          title: 'Products',
          message: `Product "${data.name}" created successfully.`,
        })

        return data
      } catch (err) {
        console.error('CREATE PRODUCT ERROR', err.response?.data || err)
        const msg =
          err.response?.data?.message ||
          'Unable to create product. Check required fields and numeric values.'
        ui.pushToast({
          type: 'error',
          title: 'Products',
          message: msg,
        })
        throw err
      } finally {
        this.saving = false
      }
    },

    /* --------------------------------------------------
     * UPDATE PRODUCT
     * PUT /products/{id}
     * -------------------------------------------------- */
    async updateProduct(id, form) {
      this.saving = true
      const ui = useUiStore()

      try {
        const { data } = await http.put(`/products/${id}`, form)

        const idx = this.products.findIndex((p) => p.id === id)
        if (idx !== -1) {
          this.products[idx] = data
        }
        this.product = data

        ui.pushToast({
          type: 'success',
          title: 'Products',
          message: `Product "${data.name}" updated successfully.`,
        })

        return data
      } catch (err) {
        console.error('UPDATE PRODUCT ERROR', err.response?.data || err)
        const msg =
          err.response?.data?.message ||
          'Unable to update product.'
        ui.pushToast({
          type: 'error',
          title: 'Products',
          message: msg,
        })
        throw err
      } finally {
        this.saving = false
      }
    },

    /* --------------------------------------------------
     * DELETE PRODUCT
     * DELETE /products/{id}
     * -------------------------------------------------- */
    async deleteProduct(id) {
      this.deleting = true
      const ui = useUiStore()

      try {
        await http.delete(`/products/${id}`)
        this.products = (this.products || []).filter((p) => p.id !== id)

        ui.pushToast({
          type: 'success',
          title: 'Products',
          message: 'Product deleted.',
        })
      } catch (err) {
        console.error('DELETE PRODUCT ERROR', err.response?.data || err)
        const msg =
          err.response?.data?.message ||
          'Unable to delete product.'
        ui.pushToast({
          type: 'error',
          title: 'Products',
          message: msg,
        })
      } finally {
        this.deleting = false
      }
    },
  },
})
