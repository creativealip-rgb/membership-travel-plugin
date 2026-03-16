import React, { useState, useEffect } from 'react';
import api from '../api';

export default function TravelList({ onNavigate }) {
  const [tours, setTours] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  useEffect(() => {
    loadTours();
  }, [page]);

  const loadTours = async (s = search) => {
    setLoading(true);
    try {
      const res = await api.getTours({ search: s, page, per_page: 12 });
      setTours(res.items || []);
      setTotalPages(res.total_pages);
    } catch (err) {
      console.error(err);
    }
    setLoading(false);
  };

  const handleSearch = (e) => {
    e.preventDefault();
    setPage(1);
    loadTours(search);
  };

  return (
    <div className="ts-page">
      <div className="ts-page-header">
        <h2>🗺️ Daftar Tour</h2>
      </div>

      {/* Search */}
      <form onSubmit={handleSearch} className="ts-search-bar">
        <input
          type="text"
          placeholder="Cari tour atau destinasi..."
          value={search}
          onChange={e => setSearch(e.target.value)}
        />
        <button type="submit" className="ts-btn ts-btn-primary">🔍 Cari</button>
      </form>

      {loading ? (
        <div className="ts-loader">Memuat daftar tour...</div>
      ) : tours.length === 0 ? (
        <div className="ts-empty">
          <div className="ts-empty-icon">🗺️</div>
          <h3>Tidak ada tour tersedia</h3>
          <p>Coba cari dengan kata kunci lain atau kembali nanti.</p>
        </div>
      ) : (
        <>
          <div className="ts-tour-grid">
            {tours.map(tour => (
              <div key={tour.id} className="ts-tour-card" onClick={() => onNavigate('tour-detail', { tourId: tour.id })}>
                <div className="ts-tour-card-img">
                  <img src={tour.thumbnail} alt={tour.title} />
                  {tour.available <= 5 && tour.available > 0 && (
                    <span className="ts-tour-badge-limited">🔥 Sisa {tour.available} slot</span>
                  )}
                  {tour.available === 0 && (
                    <span className="ts-tour-badge-full">FULL</span>
                  )}
                </div>
                <div className="ts-tour-card-body">
                  <h4>{tour.title}</h4>
                  <p className="ts-tour-dest">📍 {tour.destination}</p>
                  <p className="ts-tour-date">📅 {tour.date_range}</p>
                  <div className="ts-tour-meta">
                    <span className="ts-tour-duration">{tour.duration} Hari</span>
                    {tour.avg_rating > 0 && (
                      <span className="ts-tour-rating">⭐ {tour.avg_rating}</span>
                    )}
                  </div>
                  <div className="ts-tour-footer">
                    <span className="ts-tour-price">{tour.price_formatted}</span>
                    <span className="ts-tour-per">/orang</span>
                  </div>
                </div>
              </div>
            ))}
          </div>

          {/* Pagination */}
          {totalPages > 1 && (
            <div className="ts-pagination">
              {page > 1 && (
                <button className="ts-btn ts-btn-sm ts-btn-secondary" onClick={() => setPage(p => p - 1)}>← Prev</button>
              )}
              <span className="ts-page-info">Halaman {page} dari {totalPages}</span>
              {page < totalPages && (
                <button className="ts-btn ts-btn-sm ts-btn-secondary" onClick={() => setPage(p => p + 1)}>Next →</button>
              )}
            </div>
          )}
        </>
      )}
    </div>
  );
}
