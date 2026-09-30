import { useEffect, useRef, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import ProgressBar from '../components/ProgressBar';
import QuestionNavigator from '../components/QuestionNavigator';
import QuestionCard from '../components/QuestionCard';
import Timer from '../components/Timer';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';

function getSavedAnswer(attempt, questionId) {
  return attempt?.answers?.find((answer) => answer.question_id === questionId) || null;
}

function resultFromAnswer(answer) {
  return answer ? {
    is_correct: answer.is_correct,
    marks_earned: answer.marks_earned,
    explanation: answer.explanation,
  } : null;
}

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
  const [savingPosition, setSavingPosition] = useState(false);
  const [completing, setCompleting] = useState(false);
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
        if (!active) return;
        const resumedAttempt = response.data;
        const resumedIndex = Math.min(resumedAttempt.current_question_index, resumedAttempt.questions.length - 1);
        const savedAnswer = getSavedAnswer(resumedAttempt, resumedAttempt.questions[resumedIndex]?.id);
        setAttempt(resumedAttempt);
        setQuestionIndex(resumedIndex);
        setSelectedOptionId(savedAnswer?.selected_option_id || null);
        setAnswerResult(resultFromAnswer(savedAnswer));

        if (resumedAttempt.is_completed) {
          navigate(`/attempts/${resumedAttempt.id}/results`, { replace: true, state: { attempt: resumedAttempt } });
        }
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not start this quiz.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [topicId, token, navigate]);

  const question = attempt?.questions?.[questionIndex];

  const saveAnswer = async () => {
    if (!selectedOptionId || !question || submitting) return false;
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
      return true;
    } catch (requestError) {
      setError(requestError.message || 'Your answer could not be saved. Try again.');
      return false;
    } finally {
      setSubmitting(false);
    }
  };

  const navigateQuestion = async (targetIndex) => {
    if (targetIndex < 0 || targetIndex >= attempt.total_questions || targetIndex === questionIndex) return;
    const savedAnswer = getSavedAnswer(attempt, question.id);
    const hasUnsavedSelection = selectedOptionId && selectedOptionId !== savedAnswer?.selected_option_id;
    setSavingPosition(true);
    setError('');

    try {
      if (hasUnsavedSelection && !(await saveAnswer())) return;
      const response = await api.patch(`/attempts/${attempt.id}/position`, {
        current_question_index: targetIndex,
      }, token);
      const nextQuestion = response.data.questions[targetIndex];
      const nextAnswer = getSavedAnswer(response.data, nextQuestion.id);
      setAttempt(response.data);
      setQuestionIndex(targetIndex);
      setSelectedOptionId(nextAnswer?.selected_option_id || null);
      setAnswerResult(resultFromAnswer(nextAnswer));
    } catch (requestError) {
      setError(requestError.message || 'Could not save your place. Try again.');
    } finally {
      setSavingPosition(false);
    }
  };

  const completeQuiz = async () => {
    if (submitting || completing) return;
    const answeredIds = new Set(attempt.answers.map((answer) => answer.question_id));
    const unansweredIndex = attempt.questions.findIndex((item) => !answeredIds.has(item.id));

    if (unansweredIndex >= 0) {
      await navigateQuestion(unansweredIndex);
      setError('Answer every question before submitting the quiz.');
      return;
    }

    const savedAnswer = getSavedAnswer(attempt, question.id);
    if (selectedOptionId && selectedOptionId !== savedAnswer?.selected_option_id && !(await saveAnswer())) return;

    setCompleting(true);
    setError('');

    try {
      const response = await api.post(`/attempts/${attempt.id}/complete`, {}, token);
      navigate(`/attempts/${attempt.id}/results`, {
        replace: true,
        state: { attempt: response.data.attempt, progress: response.data.progress || progress },
      });
    } catch (requestError) {
      setError(requestError.message || 'Could not submit this quiz. Try again.');
    } finally {
      setCompleting(false);
    }
  };

  if (loading) return <p className="status-message">Starting quiz...</p>;
  if (error && !attempt) {
    return <div className="learning-page"><p className="error-banner" role="alert">{error}</p><Link className="primary-button" to="/subjects">Choose another topic</Link></div>;
  }
  if (!attempt || !question) return <p className="status-message">No questions are available in this attempt.</p>;

  return (
    <div className="learning-page quiz-page">
      <Link className="back-link" to={`/subjects`}>Exit quiz</Link>
      <header className="page-heading">
        <p className="eyebrow">Quiz in progress</p>
        <h1>Focus on one question at a time</h1>
      </header>
      <div className="quiz-toolbar">
        <span>{questionIndex + 1} of {attempt.total_questions}</span>
        <Timer elapsedSeconds={attempt.elapsed_seconds || 0} startedAt={attempt.started_at} />
      </div>
      <ProgressBar current={attempt.answered_questions} total={attempt.total_questions} />
      <QuestionCard
        question={question}
        selectedOptionId={selectedOptionId}
        submitted={false}
        onSelect={(optionId) => { setSelectedOptionId(optionId); setAnswerResult(null); }}
      />
      <QuestionNavigator
        questions={attempt.questions}
        answers={attempt.answers}
        currentIndex={questionIndex}
        disabled={savingPosition || submitting || completing}
        onNavigate={navigateQuestion}
        onReviewUnanswered={navigateQuestion}
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
        <div className="quiz-action-buttons">
          <button className="secondary-button" type="button" onClick={() => navigateQuestion(questionIndex - 1)} disabled={questionIndex === 0 || savingPosition || submitting}>
            Previous
          </button>
          <button className="secondary-button" type="button" onClick={() => navigateQuestion(questionIndex + 1)} disabled={questionIndex === attempt.total_questions - 1 || savingPosition || submitting}>
            Next
          </button>
          <button type="button" onClick={saveAnswer} disabled={!selectedOptionId || submitting || savingPosition}>
            {submitting ? 'Saving answer...' : getSavedAnswer(attempt, question.id) ? 'Update answer' : 'Save answer'}
          </button>
          <button type="button" onClick={completeQuiz} disabled={submitting || completing || savingPosition}>
            {completing ? 'Submitting...' : 'Submit quiz'}
          </button>
        </div>
      </div>
    </div>
  );
}