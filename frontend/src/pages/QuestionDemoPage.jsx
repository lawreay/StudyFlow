import { useEffect, useState } from 'react';
import { api } from '../services/api';
import { useAuth } from '../context/useAuth';

export default function QuestionDemoPage() {
  const { token } = useAuth();
  const [questions, setQuestions] = useState([]);
  const [selected, setSelected] = useState({});

  useEffect(() => {
    api.get('/questions', token)
      .then((response) => setQuestions(response.data || []))
      .catch(() => setQuestions([]));
  }, [token]);

  const handleAnswer = (questionId, optionId) => {
    setSelected((current) => ({ ...current, [questionId]: optionId }));
  };

  return (
    <div style={{ padding: '2rem' }}>
      <h1>Question Demo</h1>
      <div style={{ display: 'grid', gap: '1.5rem', marginTop: '1.5rem' }}>
        {questions.map((question) => (
          <div key={question.id} style={{ border: '1px solid #ddd', borderRadius: 10, padding: '1rem' }}>
            <h3>{question.question_text}</h3>
            <div style={{ display: 'grid', gap: '0.75rem', marginTop: '1rem' }}>
              {question.options?.map((option) => (
                <button
                  key={option.id}
                  type="button"
                  onClick={() => handleAnswer(question.id, option.id)}
                  style={{
                    textAlign: 'left',
                    padding: '0.75rem',
                    borderRadius: 8,
                    border: selected[question.id] === option.id ? '2px solid #2563eb' : '1px solid #ccc',
                    background: selected[question.id] === option.id ? '#eff6ff' : '#fff',
                  }}
                >
                  {option.option_text}
                </button>
              ))}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
