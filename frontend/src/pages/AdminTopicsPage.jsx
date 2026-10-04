import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/useAuth';

export default function AdminTopicsPage() {
  const { token } = useAuth();
  const [topics, setTopics] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;

    api.get('/admin/topics', token)
      .then((response) => {
        if (active) setTopics(response.data || []);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load topics.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token]);

  return (
    <div className="learning-page admin-page">
      <Link className="back-link" to="/admin">← Admin dashboard</Link>
      <header className="page-heading">
        <p className="eyebrow">Content administration</p>
        <h1>Lesson content</h1>
        <p>Maintain the learner-facing mission briefings for each topic.</p>
      </header>
      {error && <p className="error-banner" role="alert">{error}</p>}
      {loading ? <p className="status-message">Loading topics...</p> : (
        <div className="admin-topic-list">
          {topics.map((topic) => (
            <article className="admin-topic-row" key={topic.id}>
              <div>
                <div className="admin-question-meta">
                  <span>{topic.subject_name}</span>
                  <span>{topic.questions_count} questions</span>
                </div>
                <h2>{topic.name}</h2>
                <p>{topic.lesson_title || 'No lesson briefing yet'}</p>
              </div>
              <Link className="secondary-button" to={`/admin/topics/${topic.id}/edit`}>Edit lesson</Link>
            </article>
          ))}
        </div>
      )}
    </div>
  );
}
