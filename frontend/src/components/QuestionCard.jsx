import AnswerOption from './AnswerOption';

export default function QuestionCard({ question, selectedOptionId, submitted, onSelect }) {
  return (
    <section className="question-card">
      <div className="question-card-heading">
        <span>{question.difficulty} question</span>
        <span>{question.points} {question.points === 1 ? 'point' : 'points'}</span>
      </div>
      <h2>{question.question_text}</h2>
      <div className="answer-list">
        {question.options.map((option) => (
          <AnswerOption
            key={option.id}
            option={option}
            selected={selectedOptionId === option.id}
            disabled={submitted}
            onSelect={onSelect}
          />
        ))}
      </div>
    </section>
  );
}