// src/stores/users.js
import { defineStore } from 'pinia'
import http from '@/api/http'
import { useUiStore } from '@/stores/ui'

export const useUsersStore = defineStore('users', {
  state: () => ({
    users: [],
    loading: false,
    saving: false,
    deletingId: null,
    error: null,
  }),

  actions: {
    async fetchUsers() {
      this.loading = true
      this.error = null
      const ui = useUiStore()

      try {
        const { data } = await http.get('/users')
        // Expect array of users
        this.users = data
      } catch (err) {
        console.warn('FETCH USERS ERROR', err.response?.status, err.response?.data)
        this.error = 'Failed to load users.'
        ui.pushToast({
          type: 'error',
          title: 'Users',
          message: 'Failed to load users from server.',
        })
      } finally {
        this.loading = false
      }
    },

    async createUser(payload) {
      // payload = { name, email, password, role }
      this.saving = true
      this.error = null
      const ui = useUiStore()

      try {
        const { data } = await http.post('/users', payload)
        // Backend should return created user
        this.users.push(data)

        ui.pushToast({
          type: 'success',
          title: 'User created',
          message: `User ${data.name} has been created.`,
        })

        return data
      } catch (err) {
        console.warn('CREATE USER ERROR', err.response?.status, err.response?.data)
        let msg = 'Failed to create user.'

        if (err.response?.status === 422 && err.response.data?.errors) {
          const firstField = Object.keys(err.response.data.errors)[0]
          msg = err.response.data.errors[firstField][0]
        } else if (err.response?.data?.message) {
          msg = err.response.data.message
        }

        this.error = msg
        ui.pushToast({
          type: 'error',
          title: 'Create user failed',
          message: msg,
        })

        throw err
      } finally {
        this.saving = false
      }
    },

    async updateUser(id, payload) {
      // payload = { name?, email?, role? ... }
      this.saving = true
      this.error = null
      const ui = useUiStore()

      try {
        const { data } = await http.put(`/users/${id}`, payload)

        // Update in local list
        const idx = this.users.findIndex((u) => u.id === id)
        if (idx !== -1) this.users[idx] = data

        ui.pushToast({
          type: 'success',
          title: 'User updated',
          message: `User ${data.name} has been updated.`,
        })

        return data
      } catch (err) {
        console.warn('UPDATE USER ERROR', err.response?.status, err.response?.data)
        let msg = 'Failed to update user.'

        if (err.response?.status === 422 && err.response.data?.errors) {
          const firstField = Object.keys(err.response.data.errors)[0]
          msg = err.response.data.errors[firstField][0]
        } else if (err.response?.data?.message) {
          msg = err.response.data.message
        }

        this.error = msg
        ui.pushToast({
          type: 'error',
          title: 'Update user failed',
          message: msg,
        })

        throw err
      } finally {
        this.saving = false
      }
    },

    async deleteUser(id) {
      this.deletingId = id
      this.error = null
      const ui = useUiStore()

      try {
        await http.delete(`/users/${id}`)
        this.users = this.users.filter((u) => u.id !== id)

        ui.pushToast({
          type: 'success',
          title: 'User deleted',
          message: 'The user has been deleted.',
        })
      } catch (err) {
        console.warn('DELETE USER ERROR', err.response?.status, err.response?.data)
        this.error = 'Failed to delete user.'
        ui.pushToast({
          type: 'error',
          title: 'Delete user failed',
          message: 'Could not delete user.',
        })
        throw err
      } finally {
        this.deletingId = null
      }
    },
  },
})
