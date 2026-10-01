import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useAuth } from '../context/useAuth';
import { api } from '../services/api';

const nodeIcons = {
  'computer-basics': '💻',
  'internet-fundamentals': '🌐',
  'networking-basics': '🔧',
  'cybersecurity-basics': '🔐',
  'programming-introduction': '⌨️',
};

function nodeIcon(node) {
  return nodeIcons[node.slug] || '✦';
}

function completionError(requestError) {
  const firstFieldErrors = Object.values(requestError.errors || {}).flat();

  return firstFieldErrors[0] || requestError.message || 'The mission could not be completed.';
}

export default function GameWorldPage() {
  const { token } = useAuth();
  const [worlds, setWorlds] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');
  const [completingNodeId, setCompletingNodeId] = useState(null);

  useEffect(() => {
    let active = true;

    api.get('/game/worlds', token)
      .then((response) => {
        if (active) setWorlds(response.data || []);
      })
      .catch((requestError) => {
        if (active) setError(requestError.message || 'Could not load the learning adventure.');
      })
      .finally(() => {
        if (active) setLoading(false);
      });

    return () => { active = false; };
  }, [token]);

  const completeNode = async (node) => {
    setCompletingNodeId(node.id);
    setError('');
    setNotice('');

    try {
      const response = await api.post(`/game/nodes/${node.id}/complete`, {}, token);
      const nextNode = response.data.next_available_node;
      setNotice(nextNode
        ? `${node.name} complete! +${response.data.reward_xp} XP. ${nextNode.name} is now available.`
        : `${node.name} complete! +${response.data.reward_xp} XP.`);
      const worldsResponse = await api.get('/game/worlds', token);
      setWorlds(worldsResponse.data || []);
    } catch (requestError) {
      setError(completionError(requestError));
    } finally {
      setCompletingNodeId(null);
    }
  };

  if (loading) return <p className="status-message">Loading your learning adventure...</p>;

  return (
    <div className="learning-page world-page">
      <header className="page-heading">
        <p className="eyebrow">Learning adventure</p>
        <h1>Build your digital foundations</h1>
        <p>Each mission represents a real learning milestone. Complete its quiz, then claim the reward to unlock your route forward.</p>
      </header>

      {error && <p className="error-banner" role="alert">{error}</p>}
      {notice && <p className="success-banner" role="status">{notice}</p>}
      {!error && worlds.length === 0 && <p className="empty-message">No learning worlds are available yet.</p>}

      {worlds.map((world) => {
        const completedNodes = world.nodes.filter((node) => node.is_completed).length;
        const completion = world.nodes.length ? Math.round((completedNodes / world.nodes.length) * 100) : 0;

        return (
          <section className="world-card" key={world.id}>
            <div className="world-card-heading">
              <div>
                <p className="eyebrow">World progress</p>
                <h2>{world.name}</h2>
                <p>{world.description}</p>
              </div>
              <strong>{completion}%</strong>
            </div>
            <div className="world-progress-track" aria-label={`${completion}% complete`}>
              <span style={{ width: `${completion}%` }} />
            </div>

            <ol className="world-map">
              {world.nodes.map((node) => {
                const isCompleting = completingNodeId === node.id;
                const hasLearningActivity = Boolean(node.topic_id);

                return (
                  <li className={`world-node is-${node.status}`} key={node.id}>
                    <div className="world-node-marker" aria-hidden="true">{node.is_completed ? '✓' : nodeIcon(node)}</div>
                    <article className="world-node-card">
                      <div className="world-node-heading">
                        <div>
                          <span className="node-order">Mission {node.order}</span>
                          <h3>{node.name}</h3>
                        </div>
                        <span className={`node-status status-${node.status}`}>{node.status === 'available' ? 'Available' : node.status === 'completed' ? 'Completed' : 'Locked'}</span>
                      </div>
                      <p>{node.description}</p>
                      {node.status === 'locked' && (
                        <small>{node.required_xp > 0 ? `Reach ${node.required_xp} XP and complete the earlier mission to unlock.` : 'Complete the earlier mission to unlock.'}</small>
                      )}
                      {node.status === 'available' && hasLearningActivity && (
                        <div className="world-node-actions">
                          <Link className="secondary-button" to={`/topics/${node.topic_id}/quiz`}>Start quiz</Link>
                          <button type="button" onClick={() => completeNode(node)} disabled={isCompleting}>
                            {isCompleting ? 'Checking…' : `Claim +${node.reward_xp} XP`}
                          </button>
                        </div>
                      )}
                      {node.status === 'available' && !hasLearningActivity && (
                        <small>Mission content is being prepared. This route will open when the lesson is ready.</small>
                      )}
                      {node.status === 'completed' && <small>Reward claimed: +{node.reward_xp} XP</small>}
                    </article>
                  </li>
                );
              })}
            </ol>
          </section>
        );
      })}
    </div>
  );
}
