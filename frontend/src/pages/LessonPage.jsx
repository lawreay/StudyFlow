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

  if (loading) return <p className="status-message">Preparing your lesson...</p>;
  if (error && !lessonData) {
    return <div className="learning-page"><p className="error-banner" role="alert">{error}</p><Link className="primary-button" to="/subjects">Choose another topic</Link></div>;
  }

  const lesson = lessonData?.lesson;

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

      <section className="lesson-next-step">
        <div>
          <p className="eyebrow">Ready when you are</p>
          <h2>{lesson?.is_completed ? 'Test what you learned' : 'Complete this lesson'}</h2>
          <p>{lesson?.is_completed ? 'Your quiz is unlocked. Take it one question at a time.' : 'Mark the briefing complete when you are ready to begin the assessment.'}</p>
        </div>
        {lesson?.is_completed ? (
          <Link className="primary-button" to={`/topics/${topicId}/quiz`}>Start quiz</Link>
        ) : (
          <button type="button" onClick={completeLesson} disabled={completing}>
            {completing ? 'Saving…' : 'Complete lesson'}
          </button>
        )}
      </section>
    </article>
  );
}
