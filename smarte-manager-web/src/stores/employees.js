// src/stores/employees.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'

export const useEmployeesStore = defineStore('employees', {
  state: () => ({
    employees: [],
    loading: false,
    saving: false,
    deletingId: null,
    error: null,
  }),

  actions: {
    async fetchEmployees() {
      this.loading = true
      this.error = null
      const ui = useUiStore()

      try {
        const { data } = await http.get('/employees')
        this.employees = data
      } catch (err) {
        console.warn('EMPLOYEES LIST ERROR', err.response?.status, err.response?.data)
        this.error = 'Failed to load employees.'
        ui.pushToast({
          type: 'error',
          title: 'Employees',
          message: 'Failed to load employees from server.',
        })
      } finally {
        this.loading = false
      }
    },

    async createEmployee(payload) {
      this.saving = true
      this.error = null
      const ui = useUiStore()

      try {
        const { data } = await http.post('/employees', payload)
        this.employees.unshift(data) // newest first

        ui.pushToast({
          type: 'success',
          title: 'Employee created',
          message: `Employee ${data.first_name} ${data.last_name} has been created.`,
        })

        return data
      } catch (err) {
        console.warn('CREATE EMPLOYEE ERROR', err.response?.status, err.response?.data)

        let msg = 'Failed to create employee.'
        const res = err.response

        if (res?.status === 422 && res.data?.errors) {
          const firstField = Object.keys(res.data.errors)[0]
          msg = res.data.errors[firstField][0]
        } else if (res?.data?.message) {
          msg = res.data.message
        }

        this.error = msg
        useUiStore().pushToast({
          type: 'error',
          title: 'Create employee failed',
          message: msg,
        })

        throw err
      } finally {
        this.saving = false
      }
    },

    async updateEmployee(id, payload) {
      this.saving = true
      this.error = null
      const ui = useUiStore()

      try {
        const { data } = await http.put(`/employees/${id}`, payload)

        const idx = this.employees.findIndex((e) => e.id === id)
        if (idx !== -1) this.employees[idx] = data

        ui.pushToast({
          type: 'success',
          title: 'Employee updated',
          message: `Employee ${data.first_name} ${data.last_name} has been updated.`,
        })

        return data
      } catch (err) {
        console.warn('UPDATE EMPLOYEE ERROR', err.response?.status, err.response?.data)

        let msg = 'Failed to update employee.'
        const res = err.response

        if (res?.status === 422 && res.data?.errors) {
          const firstField = Object.keys(res.data.errors)[0]
          msg = res.data.errors[firstField][0]
        } else if (res?.data?.message) {
          msg = res.data.message
        }

        this.error = msg
        ui.pushToast({
          type: 'error',
          title: 'Update employee failed',
          message: msg,
        })

        throw err
      } finally {
        this.saving = false
      }
    },

    async deleteEmployee(id) {
      this.deletingId = id
      this.error = null
      const ui = useUiStore()

      try {
        await http.delete(`/employees/${id}`)
        this.employees = this.employees.filter((e) => e.id !== id)

        ui.pushToast({
          type: 'success',
          title: 'Employee deleted',
          message: 'The employee has been deleted.',
        })
      } catch (err) {
        console.warn('DELETE EMPLOYEE ERROR', err.response?.status, err.response?.data)
        this.error = 'Failed to delete employee.'
        ui.pushToast({
          type: 'error',
          title: 'Delete employee failed',
          message: 'Could not delete employee.',
        })
        throw err
      } finally {
        this.deletingId = null
      }
    },
  },
})
