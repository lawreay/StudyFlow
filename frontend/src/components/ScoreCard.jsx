export default function ScoreCard({ label, value, detail }) {
  return (
    <section className="score-card">
      <span>{label}</span>
      <strong>{value}</strong>
      {detail && <small>{detail}</small>}
    </section>
  );
}