// src/stores/auth.js
import { defineStore } from 'pinia';
import http from '@/api/http';
import router from '@/router';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('sm_token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    userRole: (state) => state.user?.role || null,
    userName: (state) => state.user?.name || '',
  },

  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;
      try {
        const { data } = await http.post('/auth/login', credentials);
        this.token = data.token;
        this.user = data.user;

        localStorage.setItem('sm_token', data.token);

        // Set default header
        http.defaults.headers.common['Authorization'] = `Bearer ${data.token}`;

        // Go to dashboard
        await router.push({ name: 'dashboard' });
      } catch (err) {
        console.error(err);
        this.error =
          err.response?.data?.message || 'Login failed. Please check your credentials.';
      } finally {
        this.loading = false;
      }
    },

    async fetchMe() {
      if (!this.token) return;
      try {
        const { data } = await http.get('/auth/me');
        this.user = data;
      } catch (err) {
        console.error('fetchMe failed', err);
        // If token invalid → logout
        this.logout();
      }
    },

    logout() {
      // Try to tell backend, but even if it fails we clear frontend
      http.post('/auth/logout').catch(() => {});

      this.user = null;
      this.token = null;
      this.error = null;

      localStorage.removeItem('sm_token');
      delete http.defaults.headers.common['Authorization'];

      router.push({ name: 'login' });
    },
  },
});
