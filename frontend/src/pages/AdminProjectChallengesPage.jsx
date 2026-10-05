import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/useAuth';

const blankChallenge = {
  id: null,
  topic_id: '',
  title: '',
  brief: '',
  requirements: [''],
  is_active: false,
};

function firstError(requestError) {
  return Object.values(requestError.errors || {}).flat()[0] || requestError.message || 'Could not save this project challenge.';
}

export default function AdminProjectChallengesPage() {
  const { token } = useAuth();
  const [challenges, setChallenges] = useState([]);
  const [topics, setTopics] = useState([]);
  const [form, setForm] = useState(blankChallenge);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');

  const fetchData = () => Promise.all([
    api.get('/admin/project-challenges', token),
    api.get('/admin/topics', token),
  ]);

  useEffect(() => {
    let active = true;

    Promise.all([
      api.get('/admin/project-challenges', token),
      api.get('/admin/topics', token),
    ])
      .then(([challengesResponse, topicsResponse]) => {
        if (!active) return;

        setChallenges(challengesResponse.data || []);
        setTopics(topicsResponse.data || []);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load project challenges.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token]);

  const editChallenge = (challenge) => {
    setError('');
    setNotice('');
    setForm({
      id: challenge.id,
      topic_id: challenge.topic_id || '',
      title: challenge.title,
      brief: challenge.brief,
      requirements: challenge.requirements,
      is_active: challenge.is_active,
    });
  };

  const updateRequirement = (index, value) => {
    setForm((current) => ({
      ...current,
      requirements: current.requirements.map((requirement, requirementIndex) => requirementIndex === index ? value : requirement),
    }));
  };

  const saveChallenge = async (event) => {
    event.preventDefault();
    setSaving(true);
    setError('');
    setNotice('');

    const payload = {
      topic_id: form.topic_id ? Number(form.topic_id) : null,
      title: form.title,
      brief: form.brief,
      requirements: form.requirements,
      is_active: form.is_active,
    };

    try {
      const response = form.id
        ? await api.put(`/admin/project-challenges/${form.id}`, payload, token)
        : await api.post('/admin/project-challenges', payload, token);
      setNotice(`“${response.data.title}” ${form.id ? 'updated' : 'created'}.`);
      setForm(blankChallenge);
      const [challengesResponse, topicsResponse] = await fetchData();
      setChallenges(challengesResponse.data || []);
      setTopics(topicsResponse.data || []);
    } catch (requestError) {
      setError(firstError(requestError));
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="learning-page admin-page">
      <Link className="back-link" to="/admin">← Admin dashboard</Link>
      <header className="page-heading">
        <p className="eyebrow">Practical learning</p>
        <h1>Project challenges</h1>
        <p>Create build-focused missions learners can submit for feedback and approval.</p>
      </header>

      {error && <p className="error-banner" role="alert">{error}</p>}
      {notice && <p className="success-banner" role="status">{notice}</p>}

      <section className="admin-project-workspace">
        <div>
          <div className="admin-project-list-heading">
            <h2>Challenge library</h2>
            <button type="button" className="secondary-button" onClick={() => { setForm(blankChallenge); setError(''); setNotice(''); }}>New challenge</button>
          </div>
          {loading ? <p className="status-message">Loading challenges...</p> : challenges.length === 0 ? (
            <p className="empty-message">No project challenges yet. Create the first practical mission.</p>
          ) : (
            <div className="admin-project-list">
              {challenges.map((challenge) => (
                <article key={challenge.id} className="admin-project-row">
                  <div>
                    <div className="admin-question-meta">
                      <span>{challenge.topic_name || 'General project'}</span>
                      <span>{challenge.submissions_count} submissions</span>
                      <span>{challenge.is_active ? 'Live' : 'Draft'}</span>
                    </div>
                    <h3>{challenge.title}</h3>
                  </div>
                  <div className="admin-project-row-actions">
                    <Link className="text-link" to={`/admin/projects/${challenge.id}/submissions`}>Review</Link>
                    <button type="button" className="secondary-button" onClick={() => editChallenge(challenge)}>Edit</button>
                  </div>
                </article>
              ))}
            </div>
          )}
        </div>

        <form className="admin-project-form" onSubmit={saveChallenge}>
          <div>
            <p className="eyebrow">{form.id ? 'Edit challenge' : 'New challenge'}</p>
            <h2>{form.id ? 'Refine the mission' : 'Write a practical mission'}</h2>
          </div>
          <label>
            Related topic <span>(optional)</span>
            <select value={form.topic_id} onChange={(event) => setForm((current) => ({ ...current, topic_id: event.target.value }))}>
              <option value="">General project</option>
              {topics.map((topic) => <option key={topic.id} value={topic.id}>{topic.subject_name} — {topic.name}</option>)}
            </select>
          </label>
          <label>
            Challenge title
            <input value={form.title} onChange={(event) => setForm((current) => ({ ...current, title: event.target.value }))} required maxLength="255" />
          </label>
          <label>
            Mission brief
            <textarea value={form.brief} onChange={(event) => setForm((current) => ({ ...current, brief: event.target.value }))} rows="6" required maxLength="5000" placeholder="Set the real-world scenario, learner goal, and expected outcome." />
          </label>
          <fieldset className="admin-options-fieldset">
            <legend>Delivery requirements</legend>
            <p>Give learners a short, observable checklist for their build.</p>
            {form.requirements.map((requirement, index) => (
              <div className="admin-project-requirement" key={index}>
                <input value={requirement} onChange={(event) => updateRequirement(index, event.target.value)} aria-label={`Requirement ${index + 1}`} required maxLength="1000" />
                <button type="button" className="danger-button" disabled={form.requirements.length === 1} onClick={() => setForm((current) => ({ ...current, requirements: current.requirements.filter((_, requirementIndex) => requirementIndex !== index) }))}>Remove</button>
              </div>
            ))}
            <button type="button" className="secondary-button" disabled={form.requirements.length >= 20} onClick={() => setForm((current) => ({ ...current, requirements: [...current.requirements, ''] }))}>Add requirement</button>
          </fieldset>
          <label className="admin-project-active">
            <input type="checkbox" checked={form.is_active} onChange={(event) => setForm((current) => ({ ...current, is_active: event.target.checked }))} />
            <span><strong>Make available to learners</strong><small>Drafts remain visible to administrators only.</small></span>
          </label>
          <div className="form-actions">
            {form.id && <button type="button" className="secondary-button" onClick={() => setForm(blankChallenge)}>Cancel edit</button>}
            <button type="submit" disabled={saving}>{saving ? 'Saving…' : form.id ? 'Save challenge' : 'Create challenge'}</button>
          </div>
        </form>
      </section>
    </div>
  );
}
