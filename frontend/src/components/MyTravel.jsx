import React, { useState, useEffect, useRef } from 'react';
import api from '../api';

export default function MyTravel({ onNavigate }) {
  const [bookings, setBookings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [uploadingId, setUploadingId] = useState(null);
  const [message, setMessage] = useState('');
  const fileRef = useRef(null);

  useEffect(() => {
    loadBookings();
  }, []);

  const loadBookings = async () => {
    setLoading(true);
    try {
      const res = await api.getBookings({ type: 'upcoming' });
      setBookings(res.items || []);
    } catch (err) {
      console.error(err);
    }
    setLoading(false);
  };

  const handleCancel = async (id) => {
    if (!confirm('Yakin batalkan booking ini?')) return;
    try {
      await api.cancelBooking(id);
      setMessage('✅ Booking dibatalkan');
      loadBookings();
      setTimeout(() => setMessage(''), 3000);
    } catch (err) {
      setMessage('❌ ' + err.message);
    }
  };

  const handleUpload = async (bookingId, file) => {
    setUploadingId(bookingId);
    try {
      await api.uploadPaymentProof(bookingId, file);
      setMessage('✅ Bukti pembayaran berhasil diupload!');
      loadBookings();
      setTimeout(() => setMessage(''), 3000);
    } catch (err) {
      setMessage('❌ ' + err.message);
    }
    setUploadingId(null);
  };

  const statusLabels = {
    pending: 'Menunggu Konfirmasi',
    confirmed: 'Dikonfirmasi',
    paid: 'Sudah Bayar',
  };

  if (loading) return <div className="ts-loader">Memuat trip Anda...</div>;

  return (
    <div className="ts-page">
      <div className="ts-page-header">
        <h2>✈️ My Travel</h2>
        <button className="ts-btn ts-btn-primary" onClick={() => onNavigate('tours')}>
          🗺️ Cari Tour Baru
        </button>
      </div>

      {message && <div className="ts-message">{message}</div>}

      {bookings.length === 0 ? (
        <div className="ts-empty">
          <div className="ts-empty-icon">✈️</div>
          <h3>Belum ada travel yang dipesan</h3>
          <p>Mulai petualanganmu dengan memesan tour!</p>
          <button className="ts-btn ts-btn-primary" onClick={() => onNavigate('tours')}>
            🗺️ Lihat Daftar Tour
          </button>
        </div>
      ) : (
        <div className="ts-booking-list">
          {bookings.map(b => (
            <div key={b.id} className="ts-booking-card">
              <img src={b.thumbnail} alt="" className="ts-booking-img" />
              <div className="ts-booking-info">
                <h4>{b.tour_title}</h4>
                <p>📍 {b.destination}</p>
                <p>📅 {b.date_range}</p>
                <p>👥 {b.participants} peserta</p>
                <p className="ts-booking-price">{b.price_formatted}</p>
              </div>
              <div className="ts-booking-actions">
                <span className={`ts-status-pill ts-status-${b.status}`}>
                  {statusLabels[b.status] || b.status}
                </span>

                <div className="ts-booking-code">#{b.booking_code}</div>

                {b.status === 'pending' && !b.has_proof && (
                  <>
                    <input
                      type="file"
                      ref={fileRef}
                      accept="image/*"
                      style={{ display: 'none' }}
                      onChange={e => {
                        if (e.target.files[0]) handleUpload(b.id, e.target.files[0]);
                      }}
                    />
                    <button
                      className="ts-btn ts-btn-sm ts-btn-success"
                      onClick={() => fileRef.current?.click()}
                      disabled={uploadingId === b.id}
                    >
                      {uploadingId === b.id ? '⏳ Uploading...' : '📎 Upload Bukti Bayar'}
                    </button>
                  </>
                )}

                {b.has_proof && b.status === 'pending' && (
                  <span className="ts-proof-badge">✅ Bukti terkirim</span>
                )}

                {['pending', 'confirmed'].includes(b.status) && (
                  <button className="ts-btn ts-btn-sm ts-btn-danger" onClick={() => handleCancel(b.id)}>
                    ❌ Batalkan
                  </button>
                )}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
