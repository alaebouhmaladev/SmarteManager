// src/api/http.js
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const http = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 10000,
});

// Load token on boot
const token = localStorage.getItem('sm_token');
if (token) {
  http.defaults.headers.common.Authorization = `Bearer ${token}`;
}

// Auto-inject token on each request
http.interceptors.request.use((config) => {
  const t = localStorage.getItem('sm_token');
  if (t) {
    config.headers.Authorization = `Bearer ${t}`;
  }
  return config;
});

// Auto-logout on 401 response
http.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const auth = useAuthStore();
      auth.logout();
    }
    return Promise.reject(error);
  }
);

export default http;
