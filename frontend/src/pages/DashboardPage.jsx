import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/useAuth';
import { api } from '../services/api';

function formatDuration(seconds) {
  const minutes = Math.floor((seconds || 0) / 60);
  const remainder = (seconds || 0) % 60;

  return `${minutes}m ${remainder}s`;
}

export default function DashboardPage() {
  const { user, token } = useAuth();
  const [dashboard, setDashboard] = useState(null);
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!token) return;
    let active = true;

    api.get('/dashboard', token)
      .then((response) => {
        if (active) setDashboard(response.data);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load your dashboard.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token]);

  const progress = dashboard?.progress;
  const activeAttempt = dashboard?.active_attempt;
  const game = dashboard?.game;
  const nextMission = game?.next_nodes?.[0];

  return (
    <div className="learning-page dashboard-page">
      <header className="page-heading">
        <p className="eyebrow">Your learning</p>
        <h1>Welcome, {user?.name || 'Student'}.</h1>
        <p>Choose a topic and keep building your understanding.</p>
      </header>
      {error && <p className="error-banner" role="alert">{error}</p>}
      {loading ? <p className="status-message">Loading your progress...</p> : dashboard && (
        <>
          <div className="dashboard-stats">
            <article><span>Total XP</span><strong>{progress.xp}</strong></article>
            <article><span>Level</span><strong>{progress.level}</strong></article>
            <article><span>Completed quizzes</span><strong>{dashboard.completed_quizzes}</strong></article>
            <article><span>Average score</span><strong>{dashboard.average_score_percent}%</strong></article>
          </div>
          <div className="dashboard-secondary-stats">
            <span>{progress.completed_questions} questions answered</span>
            <span>{progress.accuracy}% accuracy</span>
          </div>
          {game?.current_world && (
            <section className="adventure-summary">
              <div className="adventure-summary-copy">
                <p className="eyebrow">Your adventure</p>
                <h2>{game.current_world.name}</h2>
                <p>{game.completed_nodes} of {game.total_nodes} missions completed</p>
                <div className="world-progress-track" aria-label={`${game.completion_percentage}% of world complete`}>
                  <span style={{ width: `${game.completion_percentage}%` }} />
                </div>
                {nextMission && <small>Next mission: <strong>{nextMission.name}</strong> · +{nextMission.reward_xp} XP</small>}
              </div>
              <Link to="/world" className="primary-button">View adventure</Link>
            </section>
          )}
          {activeAttempt ? (
            <section className="dashboard-next-step">
              <div>
                <p className="eyebrow">Quiz in progress</p>
                <h2>Continue your topic</h2>
                <p>{activeAttempt.answered_questions} of {activeAttempt.total_questions} answered</p>
              </div>
              <Link to={`/topics/${activeAttempt.topic_id}/quiz`} className="primary-button">Resume quiz</Link>
            </section>
          ) : (
            <section className="dashboard-next-step">
              <div>
                <p className="eyebrow">Next step</p>
                <h2>Ready for another topic?</h2>
                <p>Your answers and progress are saved as you study.</p>
              </div>
              <Link to="/subjects" className="primary-button">Choose a subject</Link>
            </section>
          )}
          <section className="recent-activity">
            <div className="recent-heading">
              <h2>Recent activity</h2>
              <Link to="/subjects">Study</Link>
            </div>
            {dashboard.recent_activity.length === 0 ? <p className="empty-message">Completed quizzes will appear here.</p> : (
              <div className="activity-list">
                {dashboard.recent_activity.map((activity) => (
                  <article className="activity-row" key={activity.id}>
                    <div>
                      <strong>{activity.topic_name}</strong>
                      <small>{activity.correct_answers} of {activity.total_questions} correct · {formatDuration(activity.duration_seconds)}</small>
                    </div>
                    <strong>{activity.score} pts</strong>
                  </article>
                ))}
              </div>
            )}
          </section>
        </>
      )}
    </div>
  );
}
