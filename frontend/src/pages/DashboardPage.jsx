import { useEffect, useState } from 'react';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';

export default function DashboardPage() {
  const { user, token } = useAuth();
  const [progress, setProgress] = useState([]);

  useEffect(() => {
    if (!token) return;

    api.get('/progress', token)
      .then((response) => setProgress(response.data || []))
      .catch(() => setProgress([]));
  }, [token]);

  return (
    <div style={{ padding: '2rem' }}>
      <h1>Dashboard</h1>
      <p>Welcome, {user?.name || 'Student'}.</p>

      <section style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))', gap: '1rem', marginTop: '2rem' }}>
        <div style={{ padding: '1rem', border: '1px solid #ddd', borderRadius: 10 }}>
          <h3>XP</h3>
          <p>{progress[0]?.xp || 0}</p>
        </div>
        <div style={{ padding: '1rem', border: '1px solid #ddd', borderRadius: 10 }}>
          <h3>Level</h3>
          <p>{progress[0]?.level || 1}</p>
        </div>
        <div style={{ padding: '1rem', border: '1px solid #ddd', borderRadius: 10 }}>
          <h3>Completed</h3>
          <p>{progress[0]?.completed_questions || 0}</p>
        </div>
      </section>
    </div>
  );
}
