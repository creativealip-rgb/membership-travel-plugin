import React, { useState, useEffect } from 'react';
import api from '../api';

export default function Profile() {
  const [profile, setProfile] = useState(null);
  const [editing, setEditing] = useState(false);
  const [form, setForm] = useState({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState('');

  useEffect(() => {
    api.getProfile().then(data => {
      setProfile(data);
      setForm(data);
    }).catch(console.error).finally(() => setLoading(false));
  }, []);

  const handleSave = async () => {
    setSaving(true);
    setMessage('');
    try {
      const updated = await api.updateProfile(form);
      setProfile(updated);
      setEditing(false);
      setMessage('✅ Profil berhasil diperbarui!');
      setTimeout(() => setMessage(''), 3000);
    } catch (err) {
      setMessage('❌ ' + err.message);
    }
    setSaving(false);
  };

  if (loading) return <div className="ts-loader">Memuat profil...</div>;
  if (!profile) return <div className="ts-error">Gagal memuat profil</div>;

  return (
    <div className="ts-page">
      <div className="ts-page-header">
        <h2>👤 Profil Saya</h2>
        {!editing && (
          <button className="ts-btn ts-btn-primary" onClick={() => setEditing(true)}>
            ✏️ Edit Profil
          </button>
        )}
      </div>

      {message && <div className="ts-message">{message}</div>}

      {/* Membership Card */}
      <div className={`ts-membership-card ts-mc-${profile.membership_level}`}>
        <div className="ts-mc-bg"></div>
        <div className="ts-mc-content">
          <img src={profile.avatar} alt="" className="ts-mc-avatar" />
          <div>
            <h3>{profile.display_name}</h3>
            <p>{profile.email}</p>
            <span className="ts-mc-level">{profile.membership_level.toUpperCase()} MEMBER</span>
          </div>
          <div className="ts-mc-points">
            <span className="ts-mc-points-value">⭐ {profile.points.toLocaleString()}</span>
            <span className="ts-mc-points-label">Poin</span>
          </div>
        </div>
      </div>

      {/* Profile Form */}
      <div className="ts-card">
        <h3>Informasi Pribadi</h3>
        <div className="ts-form-grid-2">
          <div className="ts-field-group">
            <label>Nama Lengkap</label>
            {editing ? (
              <input type="text" value={form.display_name || ''} onChange={e => setForm({...form, display_name: e.target.value})} />
            ) : (
              <p className="ts-field-value">{profile.display_name}</p>
            )}
          </div>
          <div className="ts-field-group">
            <label>Email</label>
            <p className="ts-field-value">{profile.email}</p>
          </div>
          <div className="ts-field-group">
            <label>No. Telepon</label>
            {editing ? (
              <input type="text" value={form.phone || ''} onChange={e => setForm({...form, phone: e.target.value})} placeholder="08xxxxxxxxxx" />
            ) : (
              <p className="ts-field-value">{profile.phone || '-'}</p>
            )}
          </div>
          <div className="ts-field-group">
            <label>No. KTP / Passport</label>
            {editing ? (
              <input type="text" value={form.id_number || ''} onChange={e => setForm({...form, id_number: e.target.value})} />
            ) : (
              <p className="ts-field-value">{profile.id_number || '-'}</p>
            )}
          </div>
          <div className="ts-field-group ts-full-width">
            <label>Alamat</label>
            {editing ? (
              <textarea value={form.address || ''} onChange={e => setForm({...form, address: e.target.value})} rows="3"></textarea>
            ) : (
              <p className="ts-field-value">{profile.address || '-'}</p>
            )}
          </div>
        </div>
      </div>

      <div className="ts-card">
        <h3>Kontak Darurat</h3>
        <div className="ts-form-grid-2">
          <div className="ts-field-group">
            <label>Nama Kontak Darurat</label>
            {editing ? (
              <input type="text" value={form.emergency_contact || ''} onChange={e => setForm({...form, emergency_contact: e.target.value})} />
            ) : (
              <p className="ts-field-value">{profile.emergency_contact || '-'}</p>
            )}
          </div>
          <div className="ts-field-group">
            <label>No. Telepon Darurat</label>
            {editing ? (
              <input type="text" value={form.emergency_phone || ''} onChange={e => setForm({...form, emergency_phone: e.target.value})} />
            ) : (
              <p className="ts-field-value">{profile.emergency_phone || '-'}</p>
            )}
          </div>
        </div>
      </div>

      {editing && (
        <div className="ts-form-actions">
          <button className="ts-btn ts-btn-primary" onClick={handleSave} disabled={saving}>
            {saving ? '⏳ Menyimpan...' : '💾 Simpan Perubahan'}
          </button>
          <button className="ts-btn ts-btn-secondary" onClick={() => { setEditing(false); setForm(profile); }}>
            Batal
          </button>
        </div>
      )}
    </div>
  );
}
