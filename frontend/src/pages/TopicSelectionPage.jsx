import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/useAuth';

export default function TopicSelectionPage() {
  const { subjectId } = useParams();
  const { token } = useAuth();
  const [subject, setSubject] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;
    api.get(`/subjects/${subjectId}`, token)
      .then((response) => {
        if (active) setSubject(response.data);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load topics.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [subjectId, token]);

  return (
    <div className="learning-page">
      <Link className="back-link" to="/subjects">← Subjects</Link>
      <header className="page-heading">
        <p className="eyebrow">Topic selection</p>
        <h1>{subject?.name || 'Choose a topic'}</h1>
        <p>Select a topic to start a question attempt.</p>
      </header>
      {loading && <p className="status-message">Loading topics...</p>}
      {error && <p className="error-banner" role="alert">{error}</p>}
      {!loading && !error && subject?.topics?.length === 0 && <p className="empty-message">This subject has no topics yet.</p>}
      <div className="selection-grid">
        {subject?.topics?.map((topic) => (
          <Link
            className={`selection-row${topic.questions_count === 0 ? ' is-unavailable' : ''}`}
            to={topic.questions_count > 0 ? `/topics/${topic.id}/quiz` : '#'}
            aria-disabled={topic.questions_count === 0}
            onClick={(event) => topic.questions_count === 0 && event.preventDefault()}
            key={topic.id}
          >
            <span>
              <strong>{topic.name}</strong>
              <small>{topic.description || `${topic.questions_count} questions`}</small>
            </span>
            <span className="row-meta">{topic.questions_count} questions</span>
          </Link>
        ))}
      </div>
    </div>
  );
}