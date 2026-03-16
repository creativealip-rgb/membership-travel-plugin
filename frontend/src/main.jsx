import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';
import './styles/main.css';

const mountEl = document.getElementById('travelship-app');
if (mountEl) {
  const page = mountEl.getAttribute('data-page') || 'dashboard';
  ReactDOM.createRoot(mountEl).render(
    <React.StrictMode>
      <App initialPage={page} />
    </React.StrictMode>
  );
}
