import React, { useState, useEffect } from 'react';
import api from '../api';

export default function TravelDetail({ tourId, onNavigate }) {
  const [tour, setTour] = useState(null);
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [activeTab, setActiveTab] = useState('description');

  useEffect(() => {
    if (!tourId) return;
    Promise.all([
      api.getTour(tourId),
      api.getTourReviews(tourId),
    ]).then(([tourData, reviewsData]) => {
      setTour(tourData);
      setReviews(reviewsData);
    }).catch(console.error).finally(() => setLoading(false));
  }, [tourId]);

  if (loading) return <div className="ts-loader">Memuat detail tour...</div>;
  if (!tour) return <div className="ts-error">Tour tidak ditemukan</div>;

  return (
    <div className="ts-page">
      <button className="ts-btn ts-btn-secondary ts-btn-sm" onClick={() => onNavigate('tours')} style={{ marginBottom: 16 }}>
        ← Kembali ke Daftar Tour
      </button>

      {/* Hero */}
      <div className="ts-tour-hero">
        <img src={tour.thumbnail} alt={tour.title} />
        <div className="ts-tour-hero-overlay">
          <h1>{tour.title}</h1>
          <p>📍 {tour.destination}</p>
        </div>
      </div>

      {/* Quick Info */}
      <div className="ts-tour-quick-info">
        <div className="ts-tqi-item">
          <span className="ts-tqi-icon">📅</span>
          <div>
            <small>Tanggal</small>
            <strong>{tour.date_range}</strong>
          </div>
        </div>
        <div className="ts-tqi-item">
          <span className="ts-tqi-icon">⏱️</span>
          <div>
            <small>Durasi</small>
            <strong>{tour.duration} Hari</strong>
          </div>
        </div>
        <div className="ts-tqi-item">
          <span className="ts-tqi-icon">👥</span>
          <div>
            <small>Sisa Kuota</small>
            <strong>{tour.available} / {tour.max_participants}</strong>
          </div>
        </div>
        <div className="ts-tqi-item">
          <span className="ts-tqi-icon">⭐</span>
          <div>
            <small>Rating</small>
            <strong>{tour.avg_rating > 0 ? tour.avg_rating + ' / 5' : 'Belum ada'}</strong>
          </div>
        </div>
      </div>

      {/* Content Tabs */}
      <div className="ts-detail-content">
        <div className="ts-detail-main">
          <div className="ts-tabs">
            {['description', 'itinerary', 'includes', 'reviews'].map(tab => (
              <button
                key={tab}
                className={`ts-tab-btn ${activeTab === tab ? 'active' : ''}`}
                onClick={() => setActiveTab(tab)}
              >
                {tab === 'description' && '📝 Deskripsi'}
                {tab === 'itinerary' && '🗓️ Itinerary'}
                {tab === 'includes' && '✅ Include/Exclude'}
                {tab === 'reviews' && `⭐ Review (${reviews.length})`}
              </button>
            ))}
          </div>

          <div className="ts-tab-panel">
            {activeTab === 'description' && (
              <div className="ts-content" dangerouslySetInnerHTML={{ __html: tour.description || '<p>Deskripsi belum tersedia.</p>' }} />
            )}

            {activeTab === 'itinerary' && (
              <div className="ts-content" dangerouslySetInnerHTML={{ __html: tour.itinerary || '<p>Itinerary belum tersedia.</p>' }} />
            )}

            {activeTab === 'includes' && (
              <div className="ts-includes-grid">
                <div>
                  <h4>✅ Yang Termasuk</h4>
                  <div className="ts-content" dangerouslySetInnerHTML={{ __html: (tour.includes || '-').replace(/\n/g, '<br>') }} />
                </div>
                <div>
                  <h4>❌ Yang Tidak Termasuk</h4>
                  <div className="ts-content" dangerouslySetInnerHTML={{ __html: (tour.excludes || '-').replace(/\n/g, '<br>') }} />
                </div>
              </div>
            )}

            {activeTab === 'reviews' && (
              <div className="ts-reviews">
                {reviews.length === 0 ? (
                  <p style={{ color: '#9ca3af' }}>Belum ada review untuk tour ini.</p>
                ) : (
                  reviews.map(r => (
                    <div key={r.id} className="ts-review-item">
                      <div className="ts-review-header">
                        <img src={r.avatar} alt="" className="ts-review-avatar" />
                        <div>
                          <strong>{r.user_name}</strong>
                          <div className="ts-review-stars">{'★'.repeat(r.rating)}{'☆'.repeat(5 - r.rating)}</div>
                        </div>
                      </div>
                      <p>{r.review}</p>
                    </div>
                  ))
                )}
              </div>
            )}
          </div>

          {/* Terms */}
          {tour.terms && (
            <div className="ts-card" style={{ marginTop: 20 }}>
              <h4>📜 Syarat & Ketentuan</h4>
              <div className="ts-content ts-text-small" dangerouslySetInnerHTML={{ __html: tour.terms.replace(/\n/g, '<br>') }} />
            </div>
          )}
        </div>

        {/* Sidebar — Pricing & Book */}
        <div className="ts-detail-sidebar">
          <div className="ts-price-card">
            <div className="ts-price-amount">{tour.price_formatted}</div>
            <div className="ts-price-per">/orang</div>

            {tour.gallery && tour.gallery.length > 0 && (
              <div className="ts-gallery-mini">
                {tour.gallery.slice(0, 4).map((img, i) => (
                  <img key={i} src={img} alt="" />
                ))}
              </div>
            )}

            {tour.available > 0 ? (
              <button
                className="ts-btn ts-btn-primary ts-btn-lg ts-btn-full"
                onClick={() => onNavigate('booking', { tourId: tour.id })}
              >
                🎫 Book Sekarang
              </button>
            ) : (
              <button className="ts-btn ts-btn-disabled ts-btn-lg ts-btn-full" disabled>
                😔 Kuota Penuh
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
