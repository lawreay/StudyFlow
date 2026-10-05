import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/useAuth';
import { api } from '../services/api';

const statusLabels = {
  submitted: 'Awaiting review',
  needs_revision: 'Revision requested',
  approved: 'Approved',
};

export default function ProjectChallengesPage() {
  const { token } = useAuth();
  const [challenges, setChallenges] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;

    api.get('/project-challenges', token)
      .then((response) => {
        if (active) setChallenges(response.data || []);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load project challenges.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token]);

  if (loading) return <p className="status-message">Loading project challenges...</p>;

  return (
    <div className="learning-page projects-page">
      <header className="page-heading">
        <p className="eyebrow">Build real skills</p>
        <h1>Project challenges</h1>
        <p>Use what you have learned in a practical build. Submit a link when it is ready for review.</p>
      </header>

      {error && <p className="error-banner" role="alert">{error}</p>}

      {challenges.length === 0 ? (
        <section className="empty-state">
          <span aria-hidden="true">🛠️</span>
          <h2>Your next build is being prepared</h2>
          <p>Finish your current lessons and check back for a practical challenge.</p>
          <Link className="primary-button" to="/subjects">Continue learning</Link>
        </section>
      ) : (
        <div className="project-challenge-grid">
          {challenges.map((challenge) => {
            const status = challenge.submission?.status;

            return (
              <article className="project-challenge-card" key={challenge.id}>
                <div className="project-challenge-card-heading">
                  <span className="project-icon" aria-hidden="true">🧩</span>
                  {status && <span className={`project-status is-${status}`}>{statusLabels[status]}</span>}
                </div>
                {challenge.topic_name && <p className="eyebrow">{challenge.topic_name}</p>}
                <h2>{challenge.title}</h2>
                <p>{challenge.brief}</p>
                <p className="project-requirements-count">{challenge.requirements.length} delivery requirements</p>
                <Link className="secondary-button" to={`/projects/${challenge.id}`}>
                  {status === 'needs_revision' ? 'Revise project' : status ? 'View submission' : 'Start project'}
                </Link>
              </article>
            );
          })}
        </div>
      )}
    </div>
  );
}
