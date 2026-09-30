export default function ProgressBar({ current, total }) {
  const percentage = total > 0 ? Math.round((current / total) * 100) : 0;

  return (
    <div className="quiz-progress">
      <div className="quiz-progress-label">
        <span>Progress</span>
        <span>{current} of {total}</span>
      </div>
      <div
        className="quiz-progress-track"
        role="progressbar"
        aria-label="Quiz progress"
        aria-valuemin={0}
        aria-valuemax={total}
        aria-valuenow={current}
      >
        <span style={{ width: `${percentage}%` }} />
      </div>
    </div>
  );
}