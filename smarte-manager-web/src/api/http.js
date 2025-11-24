// src/api/http.js
import axios from 'axios';

// backend Laravel API
const http = axios.create({
  baseURL: 'http://127.0.0.1:8000/api', 
  timeout: 10000,
});

// Load token from localStorage if exists
const token = localStorage.getItem('sm_token');
if (token) {
  http.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Attach token automatically on each request (in case it changes)
http.interceptors.request.use((config) => {
  const t = localStorage.getItem('sm_token');
  if (t) {
    config.headers.Authorization = `Bearer ${t}`;
  }
  return config;
});

export default http;
