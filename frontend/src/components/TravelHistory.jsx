import React, { useState, useEffect } from 'react';
import api from '../api';

export default function TravelHistory({ onNavigate }) {
  const [bookings, setBookings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [reviewModal, setReviewModal] = useState(null);
  const [reviewForm, setReviewForm] = useState({ rating: 5, review: '' });
  const [message, setMessage] = useState('');

  useEffect(() => {
    api.getBookings({ type: 'history' }).then(res => setBookings(res.items || [])).catch(console.error).finally(() => setLoading(false));
  }, []);

  const handleReview = async () => {
    try {
      await api.createReview({
        tour_id: reviewModal.tour_id,
        rating: reviewForm.rating,
        review: reviewForm.review,
      });
      setMessage('✅ Review berhasil dikirim!');
      setReviewModal(null);
      setReviewForm({ rating: 5, review: '' });
      setTimeout(() => setMessage(''), 3000);
    } catch (err) {
      setMessage('❌ ' + err.message);
    }
  };

  const statusLabels = {
    completed: 'Selesai',
    cancelled: 'Dibatalkan',
  };

  if (loading) return <div className="ts-loader">Memuat riwayat...</div>;

  return (
    <div className="ts-page">
      <div className="ts-page-header">
        <h2>📋 Travel History</h2>
      </div>

      {message && <div className="ts-message">{message}</div>}

      {bookings.length === 0 ? (
        <div className="ts-empty">
          <div className="ts-empty-icon">📋</div>
          <h3>Belum ada riwayat travel</h3>
          <p>Riwayat trip yang sudah selesai atau dibatalkan akan muncul di sini.</p>
        </div>
      ) : (
        <div className="ts-booking-list">
          {bookings.map(b => (
            <div key={b.id} className={`ts-booking-card ${b.status === 'cancelled' ? 'ts-booking-cancelled' : ''}`}>
              <img src={b.thumbnail} alt="" className="ts-booking-img" />
              <div className="ts-booking-info">
                <h4>{b.tour_title}</h4>
                <p>📍 {b.destination}</p>
                <p>📅 {b.date_range}</p>
                <p className="ts-booking-price">{b.price_formatted}</p>
              </div>
              <div className="ts-booking-actions">
                <span className={`ts-status-pill ts-status-${b.status}`}>
                  {statusLabels[b.status] || b.status}
                </span>
                <div className="ts-booking-code">#{b.booking_code}</div>

                {b.status === 'completed' && (
                  <button className="ts-btn ts-btn-sm ts-btn-secondary" onClick={() => setReviewModal(b)}>
                    ⭐ Beri Review
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Review Modal */}
      {reviewModal && (
        <div className="ts-modal-overlay" onClick={() => setReviewModal(null)}>
          <div className="ts-modal" onClick={e => e.stopPropagation()}>
            <h3>⭐ Review untuk {reviewModal.tour_title}</h3>
            <div className="ts-field-group">
              <label>Rating</label>
              <div className="ts-star-rating">
                {[1,2,3,4,5].map(star => (
                  <button
                    key={star}
                    type="button"
                    className={`ts-star ${star <= reviewForm.rating ? 'active' : ''}`}
                    onClick={() => setReviewForm({...reviewForm, rating: star})}
                  >
                    ★
                  </button>
                ))}
              </div>
            </div>
            <div className="ts-field-group">
              <label>Ceritakan pengalaman kamu</label>
              <textarea
                value={reviewForm.review}
                onChange={e => setReviewForm({...reviewForm, review: e.target.value})}
                rows="4"
                placeholder="Review kamu..."
              ></textarea>
            </div>
            <div className="ts-modal-actions">
              <button className="ts-btn ts-btn-primary" onClick={handleReview}>Kirim Review</button>
              <button className="ts-btn ts-btn-secondary" onClick={() => setReviewModal(null)}>Batal</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
