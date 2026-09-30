import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';

export default function SubjectSelectionPage() {
  const { token } = useAuth();
  const [subjects, setSubjects] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;
    api.get('/subjects', token)
      .then((response) => {
        if (active) setSubjects(response.data || []);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load subjects.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token]);

  return (
    <div className="learning-page">
      <header className="page-heading">
        <p className="eyebrow">Learning library</p>
        <h1>Choose a subject</h1>
        <p>Select a subject to see its available topics.</p>
      </header>
      {loading && <p className="status-message">Loading subjects...</p>}
      {error && <p className="error-banner" role="alert">{error}</p>}
      {!loading && !error && subjects.length === 0 && <p className="empty-message">No subjects are available yet.</p>}
      <div className="selection-grid">
        {subjects.map((subject) => (
          <Link className="selection-row" to={`/subjects/${subject.id}/topics`} key={subject.id}>
            <span>
              <strong>{subject.name}</strong>
              <small>{subject.description || 'Explore lessons and practice questions.'}</small>
            </span>
            <span className="row-arrow" aria-hidden="true">→</span>
          </Link>
        ))}
      </div>
    </div>
  );
}