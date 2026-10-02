import { useEffect, useState } from 'react';
import { useAuth } from '../context/useAuth';
import { api } from '../services/api';

export default function ProfilePage() {
  const { token, updateUser, user } = useAuth();
  const [form, setForm] = useState({ name: user?.name || '', email: user?.email || '' });
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');

  useEffect(() => {
    let active = true;

    api.get('/me', token)
      .then((response) => {
        if (!active) return;
        setForm({ name: response.data.user.name, email: response.data.user.email });
        updateUser(response.data.user);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load your profile.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token, updateUser]);

  const handleSubmit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError('');
    setNotice('');

    try {
      const response = await api.patch('/me', { name: form.name }, token);
      updateUser(response.data.user);
      setForm((current) => ({ ...current, name: response.data.user.name, email: response.data.user.email }));
      setNotice('Profile saved. Your learning record will use this name.');
    } catch (requestError) {
      const firstError = Object.values(requestError.errors || {}).flat()[0];
      setError(firstError || requestError.message || 'Could not save your profile.');
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <p className="status-message">Loading your profile...</p>;

  return (
    <div className="learning-page profile-page">
      <header className="page-heading">
        <p className="eyebrow">Your account</p>
        <h1>Profile</h1>
        <p>Keep the name on your learning record accurate before you earn certificates.</p>
      </header>
      {error && <p className="error-banner" role="alert">{error}</p>}
      {notice && <p className="success-banner" role="status">{notice}</p>}
      <form className="profile-form" onSubmit={handleSubmit}>
        <label>
          Display name
          <input
            type="text"
            name="name"
            value={form.name}
            onChange={(event) => setForm((current) => ({ ...current, name: event.target.value }))}
            required
            maxLength="255"
          />
        </label>
        <label>
          Email address
          <input type="email" value={form.email} disabled />
          <small>Email changes are not part of the MVP yet.</small>
        </label>
        <button type="submit" disabled={saving}>{saving ? 'Saving…' : 'Save profile'}</button>
      </form>
    </div>
  );
}
