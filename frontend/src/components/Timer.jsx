import { useEffect, useState } from 'react';

function formatDuration(totalSeconds) {
  const hours = Math.floor(totalSeconds / 3600);
  const minutes = Math.floor((totalSeconds % 3600) / 60);
  const seconds = totalSeconds % 60;
  const clock = [minutes, seconds].map((part) => String(part).padStart(2, '0')).join(':');

  return hours > 0 ? `${String(hours).padStart(2, '0')}:${clock}` : clock;
}

export default function Timer({ elapsedSeconds, startedAt }) {
  const [now, setNow] = useState(0);

  useEffect(() => {
    const intervalId = window.setInterval(() => setNow(Date.now()), 1000);

    return () => window.clearInterval(intervalId);
  }, []);

  const startedAtMs = startedAt ? new Date(startedAt).getTime() : 0;
  const elapsed = Math.max(elapsedSeconds, now && startedAtMs ? Math.floor((now - startedAtMs) / 1000) : 0);

  return (
    <div className="quiz-timer" aria-label={`Elapsed time ${formatDuration(elapsed)}`}>
      <span>Time</span>
      <strong>{formatDuration(elapsed)}</strong>
    </div>
  );
}