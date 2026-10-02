import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/useAuth';
import { api } from '../services/api';

function formatIssuedDate(value) {
  return new Intl.DateTimeFormat(undefined, { dateStyle: 'long' }).format(new Date(value));
}

export default function CertificatesPage() {
  const { token } = useAuth();
  const [certificateData, setCertificateData] = useState(null);
  const [loading, setLoading] = useState(true);
  const [issuingWorldId, setIssuingWorldId] = useState(null);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');

  useEffect(() => {
    let active = true;

    api.get('/certificates', token)
      .then((response) => {
        if (active) setCertificateData(response.data);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load your certificates.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token]);

  const issueCertificate = async (world) => {
    setIssuingWorldId(world.id);
    setError('');
    setNotice('');

    try {
      const response = await api.post(`/certificates/worlds/${world.id}`, {}, token);
      setNotice(`Your ${response.data.program_name} certificate is ready.`);
      const certificatesResponse = await api.get('/certificates', token);
      setCertificateData(certificatesResponse.data);
    } catch (requestError) {
      setError(requestError.message || 'Could not issue this certificate.');
    } finally {
      setIssuingWorldId(null);
    }
  };

  if (loading) return <p className="status-message">Loading your certificates...</p>;

  const certificates = certificateData?.certificates || [];
  const eligibleWorlds = certificateData?.eligible_worlds || [];

  return (
    <div className="learning-page certificates-page">
      <header className="page-heading">
        <p className="eyebrow">Proof of progress</p>
        <h1>Your certificates</h1>
        <p>Complete every mission in a learning world to earn a shareable, verifiable record of your achievement.</p>
      </header>

      {error && <p className="error-banner" role="alert">{error}</p>}
      {notice && <p className="success-banner" role="status">{notice}</p>}

      {eligibleWorlds.length > 0 && (
        <section className="certificate-ready">
          <div>
            <p className="eyebrow">Ready to claim</p>
            <h2>You completed a learning world</h2>
            <p>Claim your certificate now to create a public verification record.</p>
          </div>
          <div className="certificate-ready-actions">
            {eligibleWorlds.map((world) => (
              <button type="button" key={world.id} onClick={() => issueCertificate(world)} disabled={issuingWorldId === world.id}>
                {issuingWorldId === world.id ? 'Issuing…' : `Claim ${world.name}`}
              </button>
            ))}
          </div>
        </section>
      )}

      {certificates.length === 0 ? (
        <section className="empty-state certificate-empty">
          <span aria-hidden="true">🏅</span>
          <h2>Your first certificate is ahead</h2>
          <p>Finish every mission in a learning world, then return here to claim your proof of skill.</p>
          <Link className="primary-button" to="/world">Continue adventure</Link>
        </section>
      ) : (
        <div className="certificate-grid">
          {certificates.map((certificate) => (
            <article className="certificate-card" key={certificate.id}>
              <span className="certificate-seal" aria-hidden="true">SF</span>
              <p className="eyebrow">StudyFlow certificate</p>
              <h2>{certificate.program_name}</h2>
              <p className="certificate-awarded">Awarded to <strong>{certificate.learner_name}</strong></p>
              <dl>
                <div><dt>Certificate ID</dt><dd>{certificate.certificate_number}</dd></div>
                <div><dt>Issued</dt><dd>{formatIssuedDate(certificate.issued_at)}</dd></div>
              </dl>
              <Link className="secondary-button" to={`/verify/${certificate.certificate_number}`}>Verify certificate</Link>
            </article>
          ))}
        </div>
      )}
    </div>
  );
}
