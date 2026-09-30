import { useEffect, useState } from 'react';
import { api } from '../services/api';
import { useAuth } from '../context/AuthContext';

export default function SubjectsPage() {
  const { token } = useAuth();
  const [subjects, setSubjects] = useState([]);

  useEffect(() => {
    api.get('/subjects', token)
      .then((response) => setSubjects(response.data || []))
      .catch(() => setSubjects([]));
  }, [token]);

  return (
    <div style={{ padding: '2rem' }}>
      <h1>Subjects</h1>
      <div style={{ display: 'grid', gap: '1rem', marginTop: '1.5rem' }}>
        {subjects.map((subject) => (
          <div key={subject.id} style={{ border: '1px solid #ddd', borderRadius: 10, padding: '1rem' }}>
            <h3>{subject.name}</h3>
            <p>{subject.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
}
