import { useEffect, useState } from 'react';
import { Link, useLocation, useParams } from 'react-router-dom';
import ScoreCard from '../components/ScoreCard';
import { api } from '../services/api';
import { useAuth } from '../context/useAuth';

function formatDuration(seconds) {
  const minutes = Math.floor((seconds || 0) / 60);
  const remainder = (seconds || 0) % 60;

  return `${minutes}m ${remainder}s`;
}

export default function ResultsPage() {
  const { attemptId } = useParams();
  const location = useLocation();
  const { token } = useAuth();
  const [attempt, setAttempt] = useState(location.state?.attempt || null);
  const [progress, setProgress] = useState(location.state?.progress || null);
  const [loading, setLoading] = useState(!location.state?.attempt);
  const [error, setError] = useState('');

  useEffect(() => {
    if (attempt?.id === Number(attemptId)) return undefined;

    let active = true;
    api.get(`/attempts/${attemptId}`, token)
      .then((response) => {
        if (active) setAttempt(response.data);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load quiz results.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });
    api.get('/progress', token)
      .then((response) => {
        if (active) setProgress(response.data?.[0] || null);
      })
      .catch(() => {});

    return () => { active = false; };
  }, [attempt?.id, attemptId, token]);

  if (loading) return <p className="status-message">Loading results...</p>;
  if (error || !attempt) return <div className="learning-page"><p className="error-banner" role="alert">{error || 'Results are unavailable.'}</p><Link className="primary-button" to="/subjects">Choose a subject</Link></div>;

  const xpGained = attempt.xp_earned || 0;

  return (
    <div className="learning-page results-page">
      <header className="page-heading">
        <p className="eyebrow">Attempt complete</p>
        <h1>Your results</h1>
        <p>You answered {attempt.total_questions} questions in {formatDuration(attempt.duration_seconds)}.</p>
      </header>
      <div className="score-grid">
        <ScoreCard label="Score" value={`${attempt.score} pts`} detail={`${attempt.correct_answers} correct`} />
        <ScoreCard label="XP earned" value={`+${xpGained}`} detail={`${progress?.xp ?? xpGained} total XP`} />
        <ScoreCard label="Accuracy" value={`${progress?.accuracy ?? (attempt.total_questions ? Math.round(attempt.correct_answers / attempt.total_questions * 100) : 0)}%`} detail="Across completed questions" />
        <ScoreCard label="Level" value={progress?.level ?? 1} detail="Current level" />
      </div>
      <section className="results-breakdown">
        <h2>Answer review</h2>
        <div className="answer-review-list">
          {attempt.answers?.map((answer, index) => (
            <article className="answer-review" key={answer.question_id}>
              <div>
                <span className="review-index">{String(index + 1).padStart(2, '0')}</span>
                <div>
                  <h3>{answer.question_text}</h3>
                  <p>{answer.selected_option_text}</p>
                  {answer.explanation && <p className="answer-explanation">{answer.explanation}</p>}
                </div>
              </div>
              <strong className={answer.is_correct ? 'review-correct' : 'review-wrong'}>
                {answer.is_correct ? `+${answer.marks_earned} pts` : '0 pts'}
              </strong>
            </article>
          ))}
        </div>
      </section>
      <div className="results-actions">
        <Link className="secondary-button" to="/dashboard">Dashboard</Link>
        <Link className="primary-button" to="/subjects">Study another topic</Link>
      </div>
    </div>
  );
}