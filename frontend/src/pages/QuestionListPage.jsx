import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/useAuth';

export default function QuestionListPage() {
  const { token } = useAuth();
  const [topics, setTopics] = useState([]);
  const [questions, setQuestions] = useState([]);
  const [filters, setFilters] = useState({ topic_id: '', difficulty: '', search: '' });
  const [appliedFilters, setAppliedFilters] = useState({ topic_id: '', difficulty: '', search: '' });
  const [reloadKey, setReloadKey] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;
    const query = new URLSearchParams(
      Object.entries(appliedFilters).filter(([, value]) => value !== ''),
    );

    api.get(`/admin/questions?${query}`, token)
      .then((response) => {
        if (active) setQuestions(response.data || []);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load questions.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [appliedFilters, reloadKey, token]);

  useEffect(() => {
    let active = true;
    api.get('/subjects', token)
      .then((response) => {
        if (active) setTopics((response.data || []).flatMap((subject) => subject.topics || []));
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load topics.');
      });

    return () => { active = false; };
  }, [token]);

  const updateFilter = (event) => {
    setFilters((current) => ({ ...current, [event.target.name]: event.target.value }));
  };

  const deleteQuestion = async (question) => {
    if (!window.confirm('Delete this question? Questions used in attempts cannot be deleted.')) return;
    setError('');
    setError('');

    try {
      setLoading(true);
      await api.delete(`/admin/questions/${question.id}`, token);
      setReloadKey((current) => current + 1);
    } catch (requestError) {
      setError(requestError.message || 'Could not delete question.');
    }
  };

  return (
    <div className="learning-page admin-page">
      <div className="back-link-row">
        <Link className="back-link" to="/admin">← Admin dashboard</Link>
        <Link className="primary-button" to="/admin/questions/new">Create question</Link>
      </div>
      <header className="page-heading">
        <p className="eyebrow">Content administration</p>
        <h1>Question bank</h1>
        <p>Find and maintain the questions available in each topic.</p>
      </header>
      <form className="question-filters" onSubmit={(event) => { event.preventDefault(); setError(''); setLoading(true); setAppliedFilters(filters); }}>
        <input name="search" value={filters.search} onChange={updateFilter} placeholder="Search question text" />
        <select name="topic_id" value={filters.topic_id} onChange={updateFilter}>
          <option value="">All topics</option>
          {topics.map((topic) => <option key={topic.id} value={topic.id}>{topic.name}</option>)}
        </select>
        <select name="difficulty" value={filters.difficulty} onChange={updateFilter}>
          <option value="">All difficulties</option>
          <option value="Easy">Easy</option>
          <option value="Medium">Medium</option>
          <option value="Hard">Hard</option>
        </select>
        <button type="submit">Apply filters</button>
      </form>
      {error && <p className="error-banner" role="alert">{error}</p>}
      {loading ? <p className="status-message">Loading questions...</p> : (
        <div className="admin-question-list">
          {questions.length === 0 && <p className="empty-message">No questions match these filters.</p>}
          {questions.map((question) => (
            <article className="admin-question-row" key={question.id}>
              <div className="admin-question-info">
                <div className="admin-question-meta">
                  <span>{question.topic_name}</span>
                  <span>{question.difficulty}</span>
                  <span>{question.points} {question.points === 1 ? 'point' : 'points'}</span>
                </div>
                <h2>{question.question_text}</h2>
                <small>{question.options?.length || 0} answer options</small>
              </div>
              <div className="admin-question-actions">
                <Link className="secondary-button" to={`/admin/questions/${question.id}/edit`}>Edit</Link>
                <button className="danger-button" type="button" onClick={() => deleteQuestion(question)}>Delete</button>
              </div>
            </article>
          ))}
        </div>
      )}
    </div>
  );
}