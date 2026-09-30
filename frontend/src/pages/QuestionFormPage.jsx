import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';

const emptyQuestion = {
  topic_id: '',
  question_text: '',
  difficulty: 'Medium',
  points: 1,
  explanation: '',
  options: [
    { option_text: '', is_correct: true },
    { option_text: '', is_correct: false },
  ],
};

function errorMessage(error) {
  return Object.values(error.errors || {})[0]?.[0] || error.message || 'Could not save this question.';
}

export default function QuestionFormPage() {
  const { questionId } = useParams();
  const { token } = useAuth();
  const navigate = useNavigate();
  const [form, setForm] = useState(emptyQuestion);
  const [topics, setTopics] = useState([]);
  const [loading, setLoading] = useState(Boolean(questionId));
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;
    const requests = [api.get('/subjects', token)];
    if (questionId) requests.push(api.get(`/admin/questions/${questionId}`, token));

    Promise.all(requests)
      .then(([subjectsResponse, questionResponse]) => {
        if (!active) return;
        setTopics((subjectsResponse.data || []).flatMap((subject) => subject.topics || []));
        if (questionResponse) setForm(questionResponse.data);
      })
      .catch((requestError) => {
        if (active) setError(errorMessage(requestError));
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [questionId, token]);

  const updateField = (event) => {
    setForm((current) => ({ ...current, [event.target.name]: event.target.value }));
  };

  const updateOption = (index, optionText) => {
    setForm((current) => ({
      ...current,
      options: current.options.map((option, optionIndex) => optionIndex === index
        ? { ...option, option_text: optionText }
        : option),
    }));
  };

  const setCorrectOption = (index) => {
    setForm((current) => ({
      ...current,
      options: current.options.map((option, optionIndex) => ({ ...option, is_correct: optionIndex === index })),
    }));
  };

  const addOption = () => {
    setForm((current) => current.options.length >= 6
      ? current
      : { ...current, options: [...current.options, { option_text: '', is_correct: false }] });
  };

  const removeOption = (index) => {
    setForm((current) => {
      if (current.options.length <= 2) return current;
      const options = current.options.filter((_, optionIndex) => optionIndex !== index);
      if (!options.some((option) => option.is_correct)) options[0].is_correct = true;
      return { ...current, options };
    });
  };

  const submit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError('');

    const payload = {
      ...form,
      topic_id: Number(form.topic_id),
      points: Number(form.points),
    };

    try {
      const response = questionId
        ? await api.put(`/admin/questions/${questionId}`, payload, token)
        : await api.post('/admin/questions', payload, token);
      navigate('/admin/questions', { replace: true, state: { notice: response.message } });
    } catch (requestError) {
      setError(errorMessage(requestError));
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <p className="status-message">Loading question...</p>;

  return (
    <div className="learning-page admin-page">
      <Link className="back-link" to="/admin/questions">← Question bank</Link>
      <header className="page-heading">
        <p className="eyebrow">Content administration</p>
        <h1>{questionId ? 'Edit question' : 'Create question'}</h1>
        <p>Set the prompt, answer options, difficulty, and scoring.</p>
      </header>
      {error && <p className="error-banner" role="alert">{error}</p>}
      <form className="admin-question-form" onSubmit={submit}>
        <label>
          Topic
          <select name="topic_id" value={form.topic_id} onChange={updateField} required>
            <option value="">Select a topic</option>
            {topics.map((topic) => <option key={topic.id} value={topic.id}>{topic.name}</option>)}
          </select>
        </label>
        <label>
          Question
          <textarea name="question_text" value={form.question_text} onChange={updateField} maxLength={2000} required rows={4} />
        </label>
        <div className="admin-form-pair">
          <label>
            Difficulty
            <select name="difficulty" value={form.difficulty} onChange={updateField}>
              <option>Easy</option><option>Medium</option><option>Hard</option>
            </select>
          </label>
          <label>
            Points
            <input name="points" type="number" min="1" max="100" value={form.points} onChange={updateField} required />
          </label>
        </div>
        <fieldset className="admin-options-fieldset">
          <legend>Answer options</legend>
          <p>Mark exactly one option as correct.</p>
          {form.options.map((option, index) => (
            <div className="admin-option-row" key={index}>
              <label className="correct-radio" title="Mark as correct answer">
                <input type="radio" name="correct-option" checked={option.is_correct} onChange={() => setCorrectOption(index)} />
                <span>Correct</span>
              </label>
              <input
                aria-label={`Option ${index + 1}`}
                value={option.option_text}
                onChange={(event) => updateOption(index, event.target.value)}
                maxLength={255}
                required
              />
              <button type="button" className="danger-button" disabled={form.options.length <= 2} onClick={() => removeOption(index)}>Remove</button>
            </div>
          ))}
          <button type="button" className="secondary-button" disabled={form.options.length >= 6} onClick={addOption}>Add option</button>
        </fieldset>
        <label>
          Explanation
          <textarea name="explanation" value={form.explanation || ''} onChange={updateField} maxLength={5000} rows={3} />
        </label>
        <div className="form-actions">
          <Link className="secondary-button" to="/admin/questions">Cancel</Link>
          <button type="submit" disabled={saving}>{saving ? 'Saving...' : questionId ? 'Save changes' : 'Create question'}</button>
        </div>
      </form>
    </div>
  );
}