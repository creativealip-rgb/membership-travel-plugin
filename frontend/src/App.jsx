import React, { useState } from 'react';
import Dashboard from './components/Dashboard';
import Profile from './components/Profile';
import MyTravel from './components/MyTravel';
import TravelHistory from './components/TravelHistory';
import TravelList from './components/TravelList';
import TravelDetail from './components/TravelDetail';
import BookingForm from './components/BookingForm';
import Settings from './components/Settings';

const NAV_ITEMS = [
  { id: 'dashboard', label: 'Dashboard', icon: '🏠' },
  { id: 'profile', label: 'Profil Saya', icon: '👤' },
  { id: 'my-travel', label: 'My Travel', icon: '✈️' },
  { id: 'history', label: 'Travel History', icon: '📋' },
  { id: 'tours', label: 'Travel List', icon: '🗺️' },
  { id: 'settings', label: 'Pengaturan', icon: '⚙️' },
];

export default function App({ initialPage = 'dashboard' }) {
  const [page, setPage] = useState(initialPage === 'tours' ? 'tours' : 'dashboard');
  const [pageData, setPageData] = useState(null);

  const navigate = (newPage, data = null) => {
    setPage(newPage);
    setPageData(data);
    window.scrollTo(0, 0);
  };

  const renderPage = () => {
    switch (page) {
      case 'dashboard':
        return <Dashboard onNavigate={navigate} />;
      case 'profile':
        return <Profile />;
      case 'my-travel':
        return <MyTravel onNavigate={navigate} />;
      case 'history':
        return <TravelHistory onNavigate={navigate} />;
      case 'tours':
        return <TravelList onNavigate={navigate} />;
      case 'tour-detail':
        return <TravelDetail tourId={pageData?.tourId} onNavigate={navigate} />;
      case 'booking':
        return <BookingForm tourId={pageData?.tourId} onNavigate={navigate} />;
      case 'settings':
        return <Settings />;
      default:
        return <Dashboard onNavigate={navigate} />;
    }
  };

  const isFullPage = ['tours', 'tour-detail'].includes(page) && initialPage === 'tours';

  if (isFullPage) {
    return (
      <div className="ts-public-wrapper">
        {renderPage()}
      </div>
    );
  }

  return (
    <div className="ts-dashboard-wrapper">
      {/* Sidebar */}
      <aside className="ts-sidebar">
        <div className="ts-sidebar-header">
          <span className="ts-logo">✈️</span>
          <h2>TravelShip</h2>
        </div>
        <nav className="ts-nav">
          {NAV_ITEMS.map(item => (
            <button
              key={item.id}
              className={`ts-nav-item ${page === item.id ? 'active' : ''}`}
              onClick={() => navigate(item.id)}
            >
              <span className="ts-nav-icon">{item.icon}</span>
              <span className="ts-nav-label">{item.label}</span>
            </button>
          ))}
        </nav>
        <div className="ts-sidebar-footer">
          <a href={window.travelshipData?.home_url || '/'} className="ts-nav-item">
            <span className="ts-nav-icon">🏡</span>
            <span className="ts-nav-label">Kembali ke Home</span>
          </a>
        </div>
      </aside>

      {/* Main Content */}
      <main className="ts-main">
        {renderPage()}
      </main>
    </div>
  );
}
