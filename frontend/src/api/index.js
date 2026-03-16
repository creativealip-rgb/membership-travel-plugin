/**
 * TravelShip API Client
 * Uses WordPress REST API with nonce authentication.
 */
const getConfig = () => window.travelshipData || {
  api_url: '/wp-json/travelship/v1',
  nonce: '',
  is_logged_in: false,
};

const api = {
  async request(endpoint, options = {}) {
    const config = getConfig();
    const url = `${config.api_url}${endpoint}`;
    
    const headers = {
      'X-WP-Nonce': config.nonce,
      ...options.headers,
    };

    if (options.body && !(options.body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
      options.body = JSON.stringify(options.body);
    }

    const res = await fetch(url, { ...options, headers });
    const data = await res.json();

    if (!res.ok) {
      throw new Error(data.message || 'Request gagal');
    }
    return data;
  },

  // Tours
  getTours(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/tours${query ? '?' + query : ''}`);
  },

  getTour(id) {
    return this.request(`/tours/${id}`);
  },

  getTourReviews(id) {
    return this.request(`/tours/${id}/reviews`);
  },

  // Dashboard
  getDashboard() {
    return this.request('/dashboard');
  },

  // Profile
  getProfile() {
    return this.request('/profile');
  },

  updateProfile(data) {
    return this.request('/profile', { method: 'PUT', body: data });
  },

  // Bookings
  getBookings(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request(`/bookings${query ? '?' + query : ''}`);
  },

  getBooking(id) {
    return this.request(`/bookings/${id}`);
  },

  createBooking(data) {
    return this.request('/bookings', { method: 'POST', body: data });
  },

  cancelBooking(id) {
    return this.request(`/bookings/${id}/cancel`, { method: 'POST' });
  },

  uploadPaymentProof(bookingId, file) {
    const formData = new FormData();
    formData.append('file', file);
    return this.request(`/bookings/${bookingId}/upload-proof`, {
      method: 'POST',
      body: formData,
      headers: { 'X-WP-Nonce': getConfig().nonce },
    });
  },

  // Reviews
  createReview(data) {
    return this.request('/reviews', { method: 'POST', body: data });
  },

  // Settings
  getSettings() {
    return this.request('/settings');
  },

  updateSettings(data) {
    return this.request('/settings', { method: 'PUT', body: data });
  },

  // Password
  changePassword(data) {
    return this.request('/change-password', { method: 'POST', body: data });
  },
};

export default api;
