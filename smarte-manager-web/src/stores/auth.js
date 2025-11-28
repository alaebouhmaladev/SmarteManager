// src/stores/auth.js
import { defineStore } from 'pinia';
import http from '@/api/http';
import router from '@/router';
import { useUiStore } from '@/stores/ui';

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
    // Central place to update token + axios header
    setToken(token) {
      this.token = token;

      if (token) {
        localStorage.setItem('sm_token', token);
        http.defaults.headers.common.Authorization = `Bearer ${token}`;
      } else {
        localStorage.removeItem('sm_token');
        delete http.defaults.headers.common.Authorization;
      }
    },

    async login(credentials) {
      this.loading = true;
      this.error = null;

      const ui = useUiStore();

      try {
        const { data } = await http.post('/auth/login', credentials);

        // Laravel returns { token, user }
        this.setToken(data.token);
        this.user = data.user;

        ui.pushToast({
          type: 'success',
          title: 'Welcome back!',
          message: `Logged in as ${data.user.name}`,
        });

        // redirect support
        const current = router.currentRoute.value;
        const redirect = current.query.redirect || { name: 'dashboard' };

        await router.push(redirect);
        return true;

      } catch (err) {
        console.error('LOGIN ERROR:', err);

        let msg = 'Login failed. Please check your credentials.';

        // Laravel validation errors
        if (err.response) {
          const status = err.response.status;
          const data = err.response.data;

          if (status === 422 && data?.errors) {
            const firstField = Object.keys(data.errors)[0];
            const firstMessage = data.errors[firstField][0];
            msg = firstMessage;
          } else if (data?.message) {
            msg = data.message;
          }
        }

        this.error = msg;

        ui.pushToast({
          type: 'error',
          title: 'Login failed',
          message: msg,
        });

        return false;

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
        console.error('fetchMe failed → logging out', err);
        this.logout();
      }
    },

    async logout() {
      const ui = useUiStore();

      try {
        if (this.token) {
          await http.post('/auth/logout');
        }
      } catch (e) {
        console.warn('Logout API failed, clearing anyway.');
      }

      this.user = null;
      this.error = null;
      this.setToken(null);

      ui.pushToast({
        type: 'info',
        title: 'Logged out',
        message: 'You have been successfully logged out.',
      });

      router.push({ name: 'login' });
    },
  },
});
