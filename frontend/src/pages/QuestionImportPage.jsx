import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';

const csvTemplate = 'question_text,difficulty,points,option_a,option_b,option_c,option_d,correct_option\n"What does CPU stand for?",Easy,2,"Central Processing Unit",Other,Another,No,b\n';

export default function QuestionImportPage() {
  const { token } = useAuth();
  const [topics, setTopics] = useState([]);
  const [topicId, setTopicId] = useState('');
  const [file, setFile] = useState(null);
  const [loading, setLoading] = useState(true);
  const [importing, setImporting] = useState(false);
  const [error, setError] = useState('');
  const [message, setMessage] = useState('');

  useEffect(() => {
    let active = true;
    api.get('/subjects', token)
      .then((response) => {
        if (active) setTopics((response.data || []).flatMap((subject) => subject.topics || []));
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load topics.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token]);

  const importFile = async (event) => {
    event.preventDefault();
    if (!topicId || !file) return;
    setImporting(true);
    setError('');
    setMessage('');

    try {
      const response = await api.upload(`/admin/topics/${topicId}/questions/import`, file, token);
      setMessage(`${response.data.imported_count} questions imported.`);
      setFile(null);
      event.currentTarget.reset();
    } catch (requestError) {
      const firstError = Object.values(requestError.errors || {})[0];
      setError(Array.isArray(firstError) ? firstError.join(' ') : requestError.message || 'Could not import questions.');
    } finally {
      setImporting(false);
    }
  };

  if (loading) return <p className="status-message">Loading topics...</p>;

  return (
    <div className="learning-page admin-page">
      <Link className="back-link" to="/admin">← Admin dashboard</Link>
      <header className="page-heading">
        <p className="eyebrow">Content administration</p>
        <h1>Import questions</h1>
        <p>Upload a CSV file with four answer options per question.</p>
      </header>
      <section className="import-format">
        <h2>CSV columns</h2>
        <code>question_text,difficulty,points,option_a,option_b,option_c,option_d,correct_option</code>
        <p>Difficulty: Easy, Medium, or Hard. Correct option: a, b, c, or d. Up to 500 rows per import.</p>
        <a className="text-link" href={`data:text/csv;charset=utf-8,${encodeURIComponent(csvTemplate)}`} download="studyflow-questions-template.csv">Download CSV template</a>
      </section>
      {error && <p className="error-banner" role="alert">{error}</p>}
      {message && <p className="success-banner" role="status">{message}</p>}
      <form className="admin-question-form" onSubmit={importFile}>
        <label>
          Topic
          <select value={topicId} onChange={(event) => setTopicId(event.target.value)} required>
            <option value="">Select a topic</option>
            {topics.map((topic) => <option key={topic.id} value={topic.id}>{topic.name}</option>)}
          </select>
        </label>
        <label>
          CSV file
          <input type="file" accept=".csv,text/csv" onChange={(event) => setFile(event.target.files?.[0] || null)} required />
        </label>
        <div className="form-actions">
          <Link className="secondary-button" to="/admin">Cancel</Link>
          <button type="submit" disabled={!file || !topicId || importing}>{importing ? 'Importing...' : 'Validate and import'}</button>
        </div>
      </form>
    </div>
  );
}