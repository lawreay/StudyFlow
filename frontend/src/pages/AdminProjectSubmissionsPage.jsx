import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/useAuth';

const statusLabels = {
  submitted: 'Awaiting review',
  needs_revision: 'Needs revision',
  approved: 'Approved',
};

export default function AdminProjectSubmissionsPage() {
  const { challengeId } = useParams();
  const { token } = useAuth();
  const [challenge, setChallenge] = useState(null);
  const [submissions, setSubmissions] = useState([]);
  const [reviewDrafts, setReviewDrafts] = useState({});
  const [loading, setLoading] = useState(true);
  const [reviewingId, setReviewingId] = useState(null);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');

  const fetchData = () => Promise.all([
    api.get(`/admin/project-challenges/${challengeId}`, token),
    api.get(`/admin/project-challenges/${challengeId}/submissions`, token),
  ]);

  useEffect(() => {
    let active = true;
    Promise.all([
      api.get(`/admin/project-challenges/${challengeId}`, token),
      api.get(`/admin/project-challenges/${challengeId}/submissions`, token),
    ])
      .then(([challengeResponse, submissionsResponse]) => {
        if (!active) return;

        setChallenge(challengeResponse.data);
        setSubmissions(submissionsResponse.data || []);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load project submissions.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });
    return () => { active = false; };
  }, [challengeId, token]);

  const draftFor = (submission) => reviewDrafts[submission.id] || {
    status: submission.status === 'approved' ? 'approved' : 'needs_revision',
    feedback: submission.feedback || '',
  };

  const updateDraft = (submissionId, field, value) => {
    setReviewDrafts((current) => ({
      ...current,
      [submissionId]: { ...draftFor({ id: submissionId, status: 'submitted', feedback: '' }), ...current[submissionId], [field]: value },
    }));
  };

  const review = async (submission) => {
    const draft = draftFor(submission);
    setReviewingId(submission.id);
    setError('');
    setNotice('');
    try {
      await api.patch(`/admin/project-submissions/${submission.id}/review`, draft, token);
      setNotice(`${submission.learner_name}'s submission was reviewed.`);
      const [challengeResponse, submissionsResponse] = await fetchData();
      setChallenge(challengeResponse.data);
      setSubmissions(submissionsResponse.data || []);
    } catch (requestError) {
      setError(Object.values(requestError.errors || {}).flat()[0] || requestError.message || 'Could not save this review.');
    } finally {
      setReviewingId(null);
    }
  };

  if (loading) return <p className="status-message">Loading project submissions...</p>;

  return (
    <div className="learning-page admin-page">
      <Link className="back-link" to="/admin/projects">← Project challenges</Link>
      <header className="page-heading">
        <p className="eyebrow">Submission review</p>
        <h1>{challenge?.title || 'Project submissions'}</h1>
        <p>Give specific feedback so every learner knows what they achieved or should improve next.</p>
      </header>
      {error && <p className="error-banner" role="alert">{error}</p>}
      {notice && <p className="success-banner" role="status">{notice}</p>}

      {submissions.length === 0 ? (
        <section className="empty-state"><h2>No submissions yet</h2><p>When learners submit this project, their work will appear here.</p></section>
      ) : (
        <div className="admin-submission-list">
          {submissions.map((submission) => {
            const draft = draftFor(submission);
            return (
              <article className="admin-submission-card" key={submission.id}>
                <div className="admin-submission-heading">
                  <div><h2>{submission.learner_name}</h2><p>{submission.learner_email}</p></div>
                  <span className={`project-status is-${submission.status}`}>{statusLabels[submission.status]}</span>
                </div>
                <a className="text-link" href={submission.project_url} target="_blank" rel="noreferrer">Open submitted project ↗</a>
                {submission.notes && <p className="admin-submission-notes">“{submission.notes}”</p>}
                <div className="admin-review-form">
                  <label>
                    Review decision
                    <select value={draft.status} onChange={(event) => updateDraft(submission.id, 'status', event.target.value)}>
                      <option value="needs_revision">Request revision</option>
                      <option value="approved">Approve project</option>
                    </select>
                  </label>
                  <label>
                    Feedback for learner
                    <textarea value={draft.feedback} onChange={(event) => updateDraft(submission.id, 'feedback', event.target.value)} rows="4" maxLength="5000" required placeholder="Be clear about what works and what to improve." />
                  </label>
                  <button type="button" onClick={() => review(submission)} disabled={reviewingId === submission.id || !draft.feedback.trim()}>{reviewingId === submission.id ? 'Saving…' : 'Save review'}</button>
                </div>
              </article>
            );
          })}
        </div>
      )}
    </div>
  );
}
