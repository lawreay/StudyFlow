import { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/useAuth';

function firstError(requestError) {
  return Object.values(requestError.errors || {}).flat()[0] || requestError.message || 'Could not save this lesson.';
}

export default function AdminTopicLessonPage() {
  const { topicId } = useParams();
  const { token } = useAuth();
  const navigate = useNavigate();
  const [form, setForm] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;

    api.get(`/admin/topics/${topicId}`, token)
      .then((response) => {
        if (!active) return;
        setForm({
          ...response.data,
          description: response.data.description || '',
          lesson_title: response.data.lesson_title || '',
          lesson_summary: response.data.lesson_summary || '',
          lesson_content: response.data.lesson_content || [{ heading: '', body: '' }],
        });
      })
      .catch((requestError) => {
        if (active) setError(firstError(requestError));
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token, topicId]);

  const updateField = (event) => {
    setForm((current) => ({ ...current, [event.target.name]: event.target.value }));
  };

  const updateSection = (index, field, value) => {
    setForm((current) => ({
      ...current,
      lesson_content: current.lesson_content.map((section, sectionIndex) => sectionIndex === index
        ? { ...section, [field]: value }
        : section),
    }));
  };

  const addSection = () => {
    setForm((current) => ({ ...current, lesson_content: [...current.lesson_content, { heading: '', body: '' }] }));
  };

  const removeSection = (index) => {
    setForm((current) => current.lesson_content.length === 1
      ? current
      : { ...current, lesson_content: current.lesson_content.filter((_, sectionIndex) => sectionIndex !== index) });
  };

  const submit = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError('');

    try {
      await api.put(`/admin/topics/${topicId}`, {
        name: form.name,
        description: form.description || null,
        lesson_title: form.lesson_title || null,
        lesson_summary: form.lesson_summary || null,
        lesson_content: form.lesson_content,
      }, token);
      navigate('/admin/topics', { replace: true });
    } catch (requestError) {
      setError(firstError(requestError));
    } finally {
      setSaving(false);
    }
  };

  if (loading) return <p className="status-message">Loading lesson content...</p>;
  if (!form) return <p className="error-banner" role="alert">{error || 'Topic not found.'}</p>;

  return (
    <div className="learning-page admin-page">
      <Link className="back-link" to="/admin/topics">← Lesson content</Link>
      <header className="page-heading">
        <p className="eyebrow">{form.subject_name}</p>
        <h1>Edit {form.name}</h1>
        <p>Write a focused mission briefing before the learner takes the quiz.</p>
      </header>
      {error && <p className="error-banner" role="alert">{error}</p>}
      <form className="admin-question-form" onSubmit={submit}>
        <label>Topic name<input name="name" value={form.name} onChange={updateField} required maxLength="255" /></label>
        <label>Topic description<textarea name="description" value={form.description} onChange={updateField} rows={2} maxLength="2000" /></label>
        <label>Lesson title<input name="lesson_title" value={form.lesson_title} onChange={updateField} maxLength="255" /></label>
        <label>Lesson summary<textarea name="lesson_summary" value={form.lesson_summary} onChange={updateField} rows={3} maxLength="2000" /></label>
        <fieldset className="admin-options-fieldset">
          <legend>Lesson sections</legend>
          <p>Use short, clear sections that explain one practical idea at a time.</p>
          {form.lesson_content.map((section, index) => (
            <div className="lesson-editor-section" key={index}>
              <label>Heading<input value={section.heading} onChange={(event) => updateSection(index, 'heading', event.target.value)} required maxLength="255" /></label>
              <label>Explanation<textarea value={section.body} onChange={(event) => updateSection(index, 'body', event.target.value)} required rows={4} maxLength="5000" /></label>
              <button type="button" className="danger-button" disabled={form.lesson_content.length === 1} onClick={() => removeSection(index)}>Remove section</button>
            </div>
          ))}
          <button type="button" className="secondary-button" disabled={form.lesson_content.length >= 20} onClick={addSection}>Add section</button>
        </fieldset>
        <div className="form-actions">
          <Link className="secondary-button" to="/admin/topics">Cancel</Link>
          <button type="submit" disabled={saving}>{saving ? 'Saving…' : 'Save lesson'}</button>
        </div>
      </form>
    </div>
  );
}
