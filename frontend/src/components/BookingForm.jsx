import React, { useState, useEffect } from 'react';
import api from '../api';

export default function BookingForm({ tourId, onNavigate }) {
  const [tour, setTour] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [participants, setParticipants] = useState(1);
  const [notes, setNotes] = useState('');
  const [success, setSuccess] = useState(null);
  const [error, setError] = useState('');

  const config = window.travelshipData || {};

  useEffect(() => {
    if (!tourId) return;
    api.getTour(tourId).then(setTour).catch(console.error).finally(() => setLoading(false));
  }, [tourId]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setError('');
    try {
      const result = await api.createBooking({
        tour_id: tourId,
        participants,
        notes,
        payment_method: 'transfer',
      });
      setSuccess(result);
    } catch (err) {
      setError(err.message);
    }
    setSubmitting(false);
  };

  if (loading) return <div className="ts-loader">Memuat data tour...</div>;
  if (!tour) return <div className="ts-error">Tour tidak ditemukan</div>;

  if (success) {
    return (
      <div className="ts-page">
        <div className="ts-success-card">
          <div className="ts-success-icon">🎉</div>
          <h2>Booking Berhasil!</h2>
          <p>Kode booking kamu: <strong>{success.booking_code}</strong></p>
          
          <div className="ts-booking-summary">
            <div className="ts-bs-row">
              <span>Tour</span>
              <strong>{tour.title}</strong>
            </div>
            <div className="ts-bs-row">
              <span>Destinasi</span>
              <strong>{tour.destination}</strong>
            </div>
            <div className="ts-bs-row">
              <span>Tanggal</span>
              <strong>{tour.date_range}</strong>
            </div>
            <div className="ts-bs-row">
              <span>Peserta</span>
              <strong>{participants} orang</strong>
            </div>
            <div className="ts-bs-row ts-bs-total">
              <span>Total</span>
              <strong>{success.price_formatted}</strong>
            </div>
          </div>

          {config.bank_info?.name && (
            <div className="ts-bank-info">
              <h4>💳 Transfer ke:</h4>
              <p className="ts-bank-detail">
                <strong>{config.bank_info.name}</strong><br />
                {config.bank_info.account}<br />
                a/n {config.bank_info.holder}
              </p>
            </div>
          )}

          <p className="ts-note">Silakan lakukan pembayaran dan upload bukti transfer melalui menu "My Travel".</p>

          <div className="ts-form-actions">
            <button className="ts-btn ts-btn-primary" onClick={() => onNavigate('my-travel')}>
              ✈️ Lihat My Travel
            </button>
            <button className="ts-btn ts-btn-secondary" onClick={() => onNavigate('tours')}>
              🗺️ Lihat Tour Lain
            </button>
          </div>
        </div>
      </div>
    );
  }

  const totalPrice = tour.price * participants;

  return (
    <div className="ts-page">
      <button className="ts-btn ts-btn-secondary ts-btn-sm" onClick={() => onNavigate('tour-detail', { tourId })} style={{ marginBottom: 16 }}>
        ← Kembali ke Detail Tour
      </button>

      <h2>🎫 Booking Tour</h2>

      <div className="ts-booking-layout">
        {/* Form */}
        <div className="ts-card">
          <h3>{tour.title}</h3>
          <p>📍 {tour.destination} &bull; 📅 {tour.date_range}</p>
          <p>Harga: <strong>{tour.price_formatted}</strong> /orang</p>
          <p>Sisa kuota: <strong>{tour.available}</strong> orang</p>

          <hr style={{ margin: '20px 0', border: 'none', borderTop: '1px solid #e2e8f0' }} />

          {error && <div className="ts-message ts-message-error">{error}</div>}

          <form onSubmit={handleSubmit}>
            <div className="ts-field-group">
              <label>Jumlah Peserta</label>
              <div className="ts-counter">
                <button type="button" onClick={() => setParticipants(Math.max(1, participants - 1))}>−</button>
                <span>{participants}</span>
                <button type="button" onClick={() => setParticipants(Math.min(tour.available, participants + 1))}>+</button>
              </div>
            </div>

            <div className="ts-field-group">
              <label>Catatan (opsional)</label>
              <textarea value={notes} onChange={e => setNotes(e.target.value)} rows="3" placeholder="Contoh: Saya vegetarian, butuh kursi depan, dll."></textarea>
            </div>

            <div className="ts-field-group">
              <label>Metode Pembayaran</label>
              <p className="ts-field-value">🏦 Transfer Bank</p>
            </div>

            <button type="submit" className="ts-btn ts-btn-primary ts-btn-lg ts-btn-full" disabled={submitting}>
              {submitting ? '⏳ Memproses...' : `🎫 Book Sekarang`}
            </button>
          </form>
        </div>

        {/* Summary */}
        <div className="ts-price-card">
          <h4>Ringkasan Booking</h4>
          <div className="ts-bs-row">
            <span>Harga per orang</span>
            <strong>{tour.price_formatted}</strong>
          </div>
          <div className="ts-bs-row">
            <span>Jumlah peserta</span>
            <strong>× {participants}</strong>
          </div>
          <hr style={{ margin: '12px 0', border: 'none', borderTop: '1px solid #e2e8f0' }} />
          <div className="ts-bs-row ts-bs-total">
            <span>Total</span>
            <strong>Rp {totalPrice.toLocaleString('id-ID')}</strong>
          </div>
        </div>
      </div>
    </div>
  );
}
