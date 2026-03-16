import React, { useState, useEffect } from 'react';
import api from '../api';

export default function Dashboard({ onNavigate }) {
  const [data, setData] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    api.getDashboard().then(setData).catch(console.error).finally(() => setLoading(false));
  }, []);

  if (loading) return <div className="ts-loader">Memuat...</div>;
  if (!data) return <div className="ts-error">Gagal memuat data</div>;

  const { user, stats, next_trip } = data;

  return (
    <div className="ts-page">
      {/* Welcome Card */}
      <div className="ts-welcome-card">
        <div className="ts-welcome-left">
          <img src={user.avatar} alt="" className="ts-avatar" />
          <div>
            <h1>Halo, {user.display_name}! 👋</h1>
            <p className="ts-membership">
              <span className={`ts-level-badge ts-level-${user.membership_level}`}>
                {user.membership_level.toUpperCase()}
              </span>
              <span className="ts-points">⭐ {user.points.toLocaleString()} poin</span>
            </p>
          </div>
        </div>
      </div>

      {/* Stats */}
      <div className="ts-stat-cards">
        <div className="ts-stat-card" onClick={() => onNavigate('history')}>
          <div className="ts-stat-emoji">🏖️</div>
          <div className="ts-stat-value">{stats.total_trips}</div>
          <div className="ts-stat-text">Trip Selesai</div>
        </div>
        <div className="ts-stat-card" onClick={() => onNavigate('my-travel')}>
          <div className="ts-stat-emoji">📅</div>
          <div className="ts-stat-value">{stats.upcoming_trips}</div>
          <div className="ts-stat-text">Upcoming Trip</div>
        </div>
        <div className="ts-stat-card">
          <div className="ts-stat-emoji">💰</div>
          <div className="ts-stat-value ts-stat-value-small">{stats.total_spending}</div>
          <div className="ts-stat-text">Total Spending</div>
        </div>
      </div>

      {/* Next Trip */}
      {next_trip && (
        <div className="ts-next-trip-card">
          <h3>🚀 Trip Terdekatmu</h3>
          <div className="ts-next-trip-content">
            <img src={next_trip.thumbnail} alt="" className="ts-next-trip-img" />
            <div className="ts-next-trip-info">
              <h4>{next_trip.tour_title}</h4>
              <p>📍 {next_trip.destination}</p>
              <p>📅 {next_trip.date_range}</p>
              <p className="ts-next-trip-status">
                Status: <span className={`ts-status ts-status-${next_trip.status}`}>{next_trip.status}</span>
              </p>
            </div>
          </div>
        </div>
      )}

      {/* Quick Actions */}
      <div className="ts-quick-actions">
        <h3>Aksi Cepat</h3>
        <div className="ts-actions-grid">
          <button className="ts-action-btn" onClick={() => onNavigate('tours')}>
            🗺️ Cari Tour
          </button>
          <button className="ts-action-btn" onClick={() => onNavigate('my-travel')}>
            ✈️ Trip Saya
          </button>
          <button className="ts-action-btn" onClick={() => onNavigate('profile')}>
            👤 Edit Profil
          </button>
          <button className="ts-action-btn" onClick={() => onNavigate('settings')}>
            ⚙️ Pengaturan
          </button>
        </div>
      </div>
    </div>
  );
}
