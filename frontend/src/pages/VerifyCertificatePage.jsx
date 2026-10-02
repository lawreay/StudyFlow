import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { api } from '../services/api';

function formatIssuedDate(value) {
  return new Intl.DateTimeFormat(undefined, { dateStyle: 'long' }).format(new Date(value));
}

export default function VerifyCertificatePage() {
  const { certificateNumber } = useParams();
  const [certificate, setCertificate] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let active = true;

    api.get(`/certificates/verify/${encodeURIComponent(certificateNumber)}`)
      .then((response) => {
        if (active) setCertificate(response.data);
      })
      .catch(() => {
        if (active) setError('This certificate could not be verified. Check the certificate ID and try again.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [certificateNumber]);

  if (loading) return <p className="status-message">Verifying certificate...</p>;

  return (
    <div className="verification-page">
      <Link className="brand-block verification-brand" to="/"><span className="brand-mark">S</span><span>StudyFlow</span></Link>
      {error ? (
        <section className="verification-card is-invalid">
          <span className="verification-icon" aria-hidden="true">!</span>
          <p className="eyebrow">Verification unavailable</p>
          <h1>Certificate not found</h1>
          <p>{error}</p>
        </section>
      ) : (
        <section className="verification-card">
          <span className="verification-icon" aria-hidden="true">✓</span>
          <p className="eyebrow">Verified certificate</p>
          <h1>This achievement is valid</h1>
          <p><strong>{certificate.learner_name}</strong> completed the <strong>{certificate.program_name}</strong> learning world.</p>
          <dl>
            <div><dt>Certificate ID</dt><dd>{certificate.certificate_number}</dd></div>
            <div><dt>Issued</dt><dd>{formatIssuedDate(certificate.issued_at)}</dd></div>
          </dl>
        </section>
      )}
    </div>
  );
}
