import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { api } from '../services/api';

export default function DashboardPage() {
  const { user, token } = useAuth();
  const [progress, setProgress] = useState([]);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!token) return;

    api.get('/progress', token)
      .then((response) => setProgress(response.data || []))
      .catch((requestError) => setError(requestError.message || 'Could not load your progress.'))
      .finally(() => setLoading(false));
  }, [token]);

  return (
    <div className="learning-page dashboard-page">
      <header className="page-heading">
        <p className="eyebrow">Your learning</p>
        <h1>Welcome, {user?.name || 'Student'}.</h1>
        <p>Choose a topic and keep building your understanding.</p>
      </header>
      {error && <p className="error-banner" role="alert">{error}</p>}
      {loading ? <p className="status-message">Loading your progress...</p> : (
        <div className="dashboard-stats">
          <article><span>Total XP</span><strong>{progress[0]?.xp || 0}</strong></article>
          <article><span>Level</span><strong>{progress[0]?.level || 1}</strong></article>
          <article><span>Questions answered</span><strong>{progress[0]?.completed_questions || 0}</strong></article>
          <article><span>Accuracy</span><strong>{progress[0]?.accuracy || 0}%</strong></article>
        </div>
      )}
      <section className="dashboard-next-step">
        <div>
          <p className="eyebrow">Next step</p>
          <h2>Ready for another topic?</h2>
          <p>Your answers and progress are saved as you study.</p>
        </div>
        <Link to="/subjects" className="primary-button">Choose a subject</Link>
      </section>
    </div>
  );
}
