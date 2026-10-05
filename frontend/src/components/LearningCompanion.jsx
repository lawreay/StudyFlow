import { useState } from 'react';
import { useLocation } from 'react-router-dom';

function companionMessage(pathname) {
  if (pathname.endsWith('/lesson')) return 'Take the mission one idea at a time. Your quiz waits when you are ready.';
  if (pathname.startsWith('/topics/')) return 'One question at a time. I’ll help you keep your momentum.';
  if (pathname === '/world') return 'Each mission unlocks a real step in your learning journey.';
  if (pathname.startsWith('/projects')) return 'Build something useful, then show what you made. I’ll help you keep it practical.';
  if (pathname.startsWith('/subjects/')) return 'Choose a topic that feels like the right next challenge.';
  if (pathname === '/subjects') return 'Every skill starts with one small, useful lesson.';
  if (pathname.startsWith('/attempts/')) return 'Nice work. Use your results to choose the next mission.';

  return 'Your progress is building one mission at a time.';
}

export default function LearningCompanion() {
  const { pathname } = useLocation();
  const [isOpen, setIsOpen] = useState(true);
  const message = companionMessage(pathname);

  if (!isOpen) {
    return (
      <button
        className="companion-launcher"
        type="button"
        onClick={() => setIsOpen(true)}
        aria-label="Open StudyFlow learning companion"
      >
        <span className="companion-mini-bot" aria-hidden="true">●</span>
      </button>
    );
  }

  return (
    <aside className="learning-companion" aria-label="StudyFlow learning companion">
      <div className="companion-message" role="status">
        <strong>Flow</strong>
        <p>{message}</p>
        <button type="button" className="companion-dismiss" onClick={() => setIsOpen(false)} aria-label="Minimise learning companion">×</button>
      </div>
      <div className="companion-bot" aria-hidden="true">
        <span className="companion-antenna" />
        <span className="companion-head"><i /><i /></span>
        <span className="companion-body" />
      </div>
    </aside>
  );
}
