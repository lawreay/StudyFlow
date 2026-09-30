import { useEffect, useRef, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import ProgressBar from '../components/ProgressBar';
import QuestionCard from '../components/QuestionCard';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';

export default function QuizPage() {
  const { topicId } = useParams();
  const { token } = useAuth();
  const navigate = useNavigate();
  const startRequest = useRef(null);
  const [attempt, setAttempt] = useState(null);
  const [questionIndex, setQuestionIndex] = useState(0);
  const [selectedOptionId, setSelectedOptionId] = useState(null);
  const [answerResult, setAnswerResult] = useState(null);
  const [progress, setProgress] = useState(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;
    if (startRequest.current?.topicId !== topicId) {
      startRequest.current = {
        topicId,
        promise: api.post('/attempts', { topic_id: Number(topicId) }, token),
      };
    }

    startRequest.current.promise
      .then((response) => {
        if (active) setAttempt(response.data);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not start this quiz.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [topicId, token]);

  const question = attempt?.questions?.[questionIndex];

  const submitAnswer = async () => {
    if (!selectedOptionId || !question || submitting) return;
    setSubmitting(true);
    setError('');

    try {
      const response = await api.post(`/attempts/${attempt.id}/answers`, {
        question_id: question.id,
        selected_option_id: selectedOptionId,
      }, token);
      setAttempt(response.data.attempt);
      setProgress(response.data.progress);
      setAnswerResult(response.data.answer);

      if (response.data.attempt.is_completed) {
        navigate(`/attempts/${attempt.id}/results`, {
          replace: true,
          state: { attempt: response.data.attempt, progress: response.data.progress },
        });
      }
    } catch (requestError) {
      setError(requestError.message || 'Your answer could not be saved. Try again.');
    } finally {
      setSubmitting(false);
    }
  };

  const continueQuiz = () => {
    setQuestionIndex((current) => current + 1);
    setSelectedOptionId(null);
    setAnswerResult(null);
  };

  if (loading) return <p className="status-message">Starting quiz...</p>;
  if (error && !attempt) {
    return <div className="learning-page"><p className="error-banner" role="alert">{error}</p><Link className="primary-button" to="/subjects">Choose another topic</Link></div>;
  }
  if (!attempt || !question) return null;

  return (
    <div className="learning-page quiz-page">
      <Link className="back-link" to={`/subjects`}>Exit quiz</Link>
      <header className="page-heading">
        <p className="eyebrow">Quiz in progress</p>
        <h1>Focus on one question at a time</h1>
      </header>
      <ProgressBar current={attempt.answered_questions} total={attempt.total_questions} />
      <QuestionCard
        question={question}
        selectedOptionId={selectedOptionId}
        submitted={Boolean(answerResult)}
        onSelect={setSelectedOptionId}
      />
      {error && <p className="error-banner" role="alert">{error}</p>}
      {answerResult && (
        <div className={`answer-feedback${answerResult.is_correct ? ' is-correct' : ' is-wrong'}`} role="status">
          <strong>{answerResult.is_correct ? 'Correct' : 'Not quite'}</strong>
          <span>{answerResult.is_correct ? `You earned ${answerResult.marks_earned} points and 10 XP.` : 'No points or XP for this answer.'}</span>
          {answerResult.explanation && <p>{answerResult.explanation}</p>}
        </div>
      )}
      <div className="quiz-actions">
        <span>{progress ? `${progress.xp} total XP` : ''}</span>
        {answerResult ? (
          <button type="button" onClick={continueQuiz}>Next question</button>
        ) : (
          <button type="button" onClick={submitAnswer} disabled={!selectedOptionId || submitting}>
            {submitting ? 'Saving answer...' : 'Submit answer'}
          </button>
        )}
      </div>
    </div>
  );
}