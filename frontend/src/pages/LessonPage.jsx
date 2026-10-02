import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useAuth } from '../context/useAuth';
import { api } from '../services/api';

export default function LessonPage() {
  const { topicId } = useParams();
  const { token } = useAuth();
  const [lessonData, setLessonData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [completing, setCompleting] = useState(false);
  const [checkingPractice, setCheckingPractice] = useState(false);
  const [selectedOptionId, setSelectedOptionId] = useState('');
  const [practiceResult, setPracticeResult] = useState(null);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;

    api.get(`/topics/${topicId}/lesson`, token)
      .then((response) => {
        if (active) setLessonData(response.data);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load this lesson.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token, topicId]);

  const completeLesson = async () => {
    setCompleting(true);
    setError('');

    try {
      const response = await api.post(`/topics/${topicId}/lesson/complete`, {}, token);
      setLessonData(response.data);
    } catch (requestError) {
      setError(requestError.message || 'Could not save lesson completion.');
    } finally {
      setCompleting(false);
    }
  };

  const checkPractice = async () => {
    if (!selectedOptionId || checkingPractice) return;

    setCheckingPractice(true);
    setError('');
    setPracticeResult(null);

    try {
      const response = await api.post(`/topics/${topicId}/lesson/practice`, {
        selected_option_id: selectedOptionId,
      }, token);
      setPracticeResult(response.data);
      setLessonData((current) => ({
        ...current,
        lesson: {
          ...current.lesson,
          practice: {
            ...current.lesson.practice,
            is_completed: response.data.is_practice_completed,
          },
        },
      }));
    } catch (requestError) {
      setError(requestError.message || 'Could not check your answer.');
    } finally {
      setCheckingPractice(false);
    }
  };

  if (loading) return <p className="status-message">Preparing your lesson...</p>;
  if (error && !lessonData) {
    return <div className="learning-page"><p className="error-banner" role="alert">{error}</p><Link className="primary-button" to="/subjects">Choose another topic</Link></div>;
  }

  const lesson = lessonData?.lesson;
  const practice = lesson?.practice;

  return (
    <article className="learning-page lesson-page">
      <Link className="back-link" to="/subjects">← Learning library</Link>
      <header className="lesson-hero">
        <p className="eyebrow">Mission briefing</p>
        <h1>{lesson?.title || lessonData?.name}</h1>
        <p>{lesson?.summary}</p>
        <span className={`lesson-status${lesson?.is_completed ? ' is-completed' : ''}`}>
          {lesson?.is_completed ? 'Lesson complete' : 'Read the briefing to unlock the quiz'}
        </span>
      </header>

      {error && <p className="error-banner" role="alert">{error}</p>}
      <div className="lesson-sections">
        {lesson?.sections?.map((section, index) => (
          <section className="lesson-section" key={section.heading}>
            <span className="lesson-section-number">{String(index + 1).padStart(2, '0')}</span>
            <div>
              <h2>{section.heading}</h2>
              <p>{section.body}</p>
            </div>
          </section>
        ))}
      </div>

      {practice && (
        <section className="lesson-practice" aria-labelledby="mission-check-heading">
          <div className="lesson-practice-heading">
            <div>
              <p className="eyebrow">Mission check</p>
              <h2 id="mission-check-heading">Put the idea into practice</h2>
            </div>
            <span className={`lesson-status${practice.is_completed ? ' is-completed' : ''}`}>
              {practice.is_completed ? 'Passed' : 'Required'}
            </span>
          </div>
          <p className="lesson-practice-question">{practice.question}</p>
          <div className="lesson-practice-options">
            {practice.options.map((option) => (
              <label className={`lesson-practice-option${selectedOptionId === option.id ? ' is-selected' : ''}`} key={option.id}>
                <input
                  type="radio"
                  name="lesson-practice"
                  value={option.id}
                  checked={selectedOptionId === option.id}
                  onChange={() => { setSelectedOptionId(option.id); setPracticeResult(null); }}
                  disabled={practice.is_completed || checkingPractice}
                />
                <span>{option.label}</span>
              </label>
            ))}
          </div>
          {practiceResult && (
            <div className={`lesson-practice-feedback${practiceResult.is_correct ? ' is-correct' : ' is-wrong'}`} role="status">
              <strong>{practiceResult.is_correct ? 'Mission check passed' : 'Not quite yet'}</strong>
              <p>{practiceResult.feedback}</p>
            </div>
          )}
          {!practice.is_completed && (
            <button type="button" onClick={checkPractice} disabled={!selectedOptionId || checkingPractice}>
              {checkingPractice ? 'Checking…' : 'Check answer'}
            </button>
          )}
        </section>
      )}

      <section className="lesson-next-step">
        <div>
          <p className="eyebrow">Ready when you are</p>
          <h2>{lesson?.is_completed ? 'Test what you learned' : practice && !practice.is_completed ? 'Pass the mission check first' : 'Complete this lesson'}</h2>
          <p>{lesson?.is_completed ? 'Your quiz is unlocked. Take it one question at a time.' : practice && !practice.is_completed ? 'Use the mission check to prove you understand the key idea.' : 'Mark the briefing complete when you are ready to begin the assessment.'}</p>
        </div>
        {lesson?.is_completed ? (
          <Link className="primary-button" to={`/topics/${topicId}/quiz`}>Start quiz</Link>
        ) : (
          <button type="button" onClick={completeLesson} disabled={completing || (practice && !practice.is_completed)}>
            {completing ? 'Saving…' : practice && !practice.is_completed ? 'Mission check required' : 'Complete lesson'}
          </button>
        )}
      </section>
    </article>
  );
}
