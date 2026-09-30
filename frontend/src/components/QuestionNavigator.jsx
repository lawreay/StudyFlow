export default function QuestionNavigator({ questions, answers, currentIndex, disabled, onNavigate, onReviewUnanswered }) {
  const answered = new Set(answers.map((answer) => answer.question_id));
  const unansweredIndex = questions.findIndex((question) => !answered.has(question.id));

  return (
    <section className="question-navigator" aria-label="Question navigation">
      <div className="navigator-heading">
        <h2>Questions</h2>
        <button type="button" className="text-action" disabled={unansweredIndex < 0 || disabled} onClick={() => onReviewUnanswered(unansweredIndex)}>
          Review unanswered
        </button>
      </div>
      <div className="question-number-list">
        {questions.map((question, index) => {
          const isAnswered = answered.has(question.id);

          return (
            <button
              key={question.id}
              type="button"
              className={`question-number${isAnswered ? ' is-answered' : ''}${index === currentIndex ? ' is-current' : ''}`}
              aria-current={index === currentIndex ? 'step' : undefined}
              aria-label={`Question ${index + 1}${isAnswered ? ', answered' : ', unanswered'}`}
              disabled={disabled}
              onClick={() => onNavigate(index)}
            >
              {index + 1}
            </button>
          );
        })}
      </div>
      <p>{answered.size} of {questions.length} answered</p>
    </section>
  );
}