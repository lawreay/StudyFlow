export default function AnswerOption({ option, selected, disabled, onSelect }) {
  return (
    <label className={`answer-option${selected ? ' is-selected' : ''}${disabled ? ' is-disabled' : ''}`}>
      <input
        type="radio"
        name="question-answer"
        value={option.id}
        checked={selected}
        disabled={disabled}
        onChange={() => onSelect(option.id)}
      />
      <span>{option.option_text}</span>
    </label>
  );
}