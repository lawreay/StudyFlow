import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/useAuth';

export default function AdminDashboardPage() {
  const { token } = useAuth();
  const [summary, setSummary] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;
    api.get('/admin/dashboard', token)
      .then((response) => {
        if (active) setSummary(response.data);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load admin dashboard.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token]);

  return (
    <div className="learning-page admin-page">
      <header className="page-heading admin-heading">
        <div>
          <p className="eyebrow">Content administration</p>
          <h1>Admin dashboard</h1>
          <p>Manage the questions and topics learners use to study.</p>
        </div>
        <div className="admin-actions">
          <Link className="secondary-button" to="/admin/questions">Question bank</Link>
          <Link className="secondary-button" to="/admin/topics">Lesson content</Link>
          <Link className="secondary-button" to="/admin/projects">Project challenges</Link>
          <Link className="primary-button" to="/admin/import">Import CSV</Link>
        </div>
      </header>
      {loading && <p className="status-message">Loading administration data...</p>}
      {error && <p className="error-banner" role="alert">{error}</p>}
      {summary && (
        <div className="dashboard-stats">
          <article><span>Learners</span><strong>{summary.users_count}</strong></article>
          <article><span>Subjects</span><strong>{summary.subjects_count}</strong></article>
          <article><span>Topics</span><strong>{summary.topics_count}</strong></article>
          <article><span>Questions</span><strong>{summary.questions_count}</strong></article>
        </div>
      )}
      <section className="dashboard-next-step">
        <div>
          <p className="eyebrow">Question bank</p>
          <h2>Keep learning content current</h2>
          <p>Create individual questions or import a prepared CSV file.</p>
        </div>
        <Link className="primary-button" to="/admin/questions/new">Create question</Link>
      </section>
    </div>
  );
}
