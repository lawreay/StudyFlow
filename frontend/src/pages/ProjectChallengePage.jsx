import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useAuth } from '../context/useAuth';
import { api } from '../services/api';

const statusLabels = {
  submitted: 'Awaiting review',
  needs_revision: 'Revision requested',
  approved: 'Approved',
};

export default function ProjectChallengePage() {
  const { challengeId } = useParams();
  const { token } = useAuth();
  const [challenge, setChallenge] = useState(null);
  const [projectUrl, setProjectUrl] = useState('');
  const [notes, setNotes] = useState('');
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');

  const loadChallenge = async () => {
    const response = await api.get(`/project-challenges/${challengeId}`, token);
    const loadedChallenge = response.data;
    setChallenge(loadedChallenge);
    setProjectUrl(loadedChallenge.submission?.project_url || '');
    setNotes(loadedChallenge.submission?.notes || '');
  };

  useEffect(() => {
    let active = true;

    api.get(`/project-challenges/${challengeId}`, token)
      .then((response) => {
        if (!active) return;

        const loadedChallenge = response.data;
        setChallenge(loadedChallenge);
        setProjectUrl(loadedChallenge.submission?.project_url || '');
        setNotes(loadedChallenge.submission?.notes || '');
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load this project challenge.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [challengeId, token]);

  const submitProject = async (event) => {
    event.preventDefault();
    setSubmitting(true);
    setError('');
    setNotice('');

    try {
      await api.post(`/project-challenges/${challengeId}/submissions`, {
        project_url: projectUrl,
        notes,
      }, token);
      await loadChallenge();
      setNotice('Your project is submitted. An instructor will review it soon.');
    } catch (requestError) {
      setError(requestError.message || 'Could not submit your project.');
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) return <p className="status-message">Loading project challenge...</p>;
  if (!challenge) return <p className="error-banner" role="alert">{error || 'Project challenge not found.'}</p>;

  const submission = challenge.submission;
  const isApproved = submission?.status === 'approved';

  return (
    <div className="learning-page project-detail-page">
      <Link className="back-link" to="/projects">← All project challenges</Link>
      <header className="project-brief">
        {challenge.topic_name && <p className="eyebrow">{challenge.topic_name} project</p>}
        <h1>{challenge.title}</h1>
        <p>{challenge.brief}</p>
      </header>

      {error && <p className="error-banner" role="alert">{error}</p>}
      {notice && <p className="success-banner" role="status">{notice}</p>}

      <section className="project-requirements">
        <h2>Your mission checklist</h2>
        <ol>
          {challenge.requirements.map((requirement) => <li key={requirement}>{requirement}</li>)}
        </ol>
      </section>

      {submission && (
        <section className={`project-review is-${submission.status}`}>
          <span className={`project-status is-${submission.status}`}>{statusLabels[submission.status]}</span>
          <h2>{submission.status === 'approved' ? 'Project approved' : submission.status === 'needs_revision' ? 'Make a few improvements' : 'Your project is in review'}</h2>
          {submission.feedback && <p>{submission.feedback}</p>}
          {submission.status === 'submitted' && <p>Your reviewer will leave feedback here when they have looked at your work.</p>}
          <a href={submission.project_url} target="_blank" rel="noreferrer" className="text-link">Open your submitted project ↗</a>
        </section>
      )}

      {!isApproved && (
        <form className="project-submission-form" onSubmit={submitProject}>
          <div>
            <p className="eyebrow">{submission ? 'Update your work' : 'Submit your work'}</p>
            <h2>{submission?.status === 'needs_revision' ? 'Share your revision' : 'Send your project for review'}</h2>
            <p>Provide a public link to your project, repository, document, or live demo.</p>
          </div>
          <label>
            Project link
            <input type="url" value={projectUrl} onChange={(event) => setProjectUrl(event.target.value)} placeholder="https://..." required />
          </label>
          <label>
            Notes for your reviewer <span>(optional)</span>
            <textarea value={notes} onChange={(event) => setNotes(event.target.value)} rows="5" placeholder="Explain your choices, testing, or anything you want reviewed." />
          </label>
          <button type="submit" disabled={submitting}>{submitting ? 'Submitting…' : submission ? 'Submit revision' : 'Submit project'}</button>
        </form>
      )}
    </div>
  );
}
