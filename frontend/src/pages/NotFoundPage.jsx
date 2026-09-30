import { Link } from 'react-router-dom';

export default function NotFoundPage() {
  return (
    <div className="empty-state">
      <h1>Page not found</h1>
      <p>The requested route does not exist in the current StudyFlow foundation.</p>
      <Link to="/" className="primary-button">Back home</Link>
    </div>
  );
}
