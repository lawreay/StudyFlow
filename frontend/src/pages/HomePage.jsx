import { Link } from 'react-router-dom';

export default function HomePage() {
  return (
    <div className="hero-panel">
      <div className="hero-copy">
        <p className="eyebrow">StudyFlow</p>
        <h1>Learn by playing your next mission.</h1>
        <p>
          A focused foundation for a game-based learning platform where students answer questions,
          earn XP, and progress through structured subjects.
        </p>
        <div className="cta-row">
          <Link to="/login" className="primary-button">Login</Link>
          <Link to="/register" className="secondary-button">Create account</Link>
        </div>
      </div>
      <div className="feature-grid">
        <div className="feature-card">
          <h3>Learning loop</h3>
          <p>Questions, feedback, and progress tracking for a simple educational flow.</p>
        </div>
        <div className="feature-card">
          <h3>Progress</h3>
          <p>Students build XP and levels as they move through content and practice.</p>
        </div>
        <div className="feature-card">
          <h3>Foundation</h3>
          <p>The product is intentionally scoped to a stable MVP structure.</p>
        </div>
      </div>
    </div>
  );
}
