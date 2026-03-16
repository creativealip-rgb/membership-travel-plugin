import React, { useState, useEffect } from 'react';
import api from '../api';

export default function Settings() {
  const [settings, setSettings] = useState({ email_notifications: true, profile_visible: true });
  const [passwordForm, setPasswordForm] = useState({ current_password: '', new_password: '', confirm_password: '' });
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState('');
  const [pwMessage, setPwMessage] = useState('');

  useEffect(() => {
    api.getSettings().then(setSettings).catch(console.error).finally(() => setLoading(false));
  }, []);

  const handleSaveSettings = async () => {
    try {
      await api.updateSettings(settings);
      setMessage('✅ Pengaturan disimpan');
      setTimeout(() => setMessage(''), 3000);
    } catch (err) {
      setMessage('❌ ' + err.message);
    }
  };

  const handleChangePassword = async (e) => {
    e.preventDefault();
    setPwMessage('');

    if (passwordForm.new_password !== passwordForm.confirm_password) {
      setPwMessage('❌ Password baru tidak cocok');
      return;
    }
    if (passwordForm.new_password.length < 6) {
      setPwMessage('❌ Password minimal 6 karakter');
      return;
    }

    try {
      await api.changePassword({
        current_password: passwordForm.current_password,
        new_password: passwordForm.new_password,
      });
      setPwMessage('✅ Password berhasil diubah! Silakan login ulang.');
      setPasswordForm({ current_password: '', new_password: '', confirm_password: '' });
    } catch (err) {
      setPwMessage('❌ ' + err.message);
    }
  };

  if (loading) return <div className="ts-loader">Memuat pengaturan...</div>;

  return (
    <div className="ts-page">
      <div className="ts-page-header">
        <h2>⚙️ Pengaturan</h2>
      </div>

      {/* Notifications */}
      <div className="ts-card">
        <h3>🔔 Notifikasi</h3>
        {message && <div className="ts-message">{message}</div>}

        <div className="ts-toggle-group">
          <label className="ts-toggle">
            <input
              type="checkbox"
              checked={settings.email_notifications}
              onChange={e => setSettings({...settings, email_notifications: e.target.checked})}
            />
            <span className="ts-toggle-slider"></span>
            <span className="ts-toggle-label">Email notifikasi (booking, status update, reminder)</span>
          </label>
        </div>

        <div className="ts-toggle-group">
          <label className="ts-toggle">
            <input
              type="checkbox"
              checked={settings.profile_visible}
              onChange={e => setSettings({...settings, profile_visible: e.target.checked})}
            />
            <span className="ts-toggle-slider"></span>
            <span className="ts-toggle-label">Tampilkan profil ke sesama traveler</span>
          </label>
        </div>

        <button className="ts-btn ts-btn-primary" onClick={handleSaveSettings} style={{ marginTop: 16 }}>
          💾 Simpan Pengaturan
        </button>
      </div>

      {/* Change Password */}
      <div className="ts-card">
        <h3>🔒 Ganti Password</h3>
        {pwMessage && <div className="ts-message">{pwMessage}</div>}

        <form onSubmit={handleChangePassword}>
          <div className="ts-field-group">
            <label>Password Lama</label>
            <input
              type="password"
              value={passwordForm.current_password}
              onChange={e => setPasswordForm({...passwordForm, current_password: e.target.value})}
              required
            />
          </div>
          <div className="ts-field-group">
            <label>Password Baru</label>
            <input
              type="password"
              value={passwordForm.new_password}
              onChange={e => setPasswordForm({...passwordForm, new_password: e.target.value})}
              required
              minLength="6"
            />
          </div>
          <div className="ts-field-group">
            <label>Konfirmasi Password Baru</label>
            <input
              type="password"
              value={passwordForm.confirm_password}
              onChange={e => setPasswordForm({...passwordForm, confirm_password: e.target.value})}
              required
            />
          </div>
          <button type="submit" className="ts-btn ts-btn-primary">🔒 Ubah Password</button>
        </form>
      </div>

      {/* Danger Zone */}
      <div className="ts-card ts-danger-zone">
        <h3>⚠️ Zona Berbahaya</h3>
        <p>Hapus akun kamu secara permanen. Tindakan ini tidak bisa dibatalkan.</p>
        <button className="ts-btn ts-btn-danger" onClick={() => alert('Fitur ini akan segera tersedia. Silakan hubungi admin.')}>
          🗑️ Hapus Akun Saya
        </button>
      </div>
    </div>
  );
}
