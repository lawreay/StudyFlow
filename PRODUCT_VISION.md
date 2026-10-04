# StudyFlow Product Vision

StudyFlow is a gamified learning and certification platform that makes education engaging, practical, and measurable. It helps learners develop real skills through structured learning paths, interactive assessments, challenges, progress tracking, and verified certificates.

## Product promise

StudyFlow is a skill-development journey, not a catalogue of courses. A learner should be able to move from “I want to learn this” to “I completed it and can prove it.”

The core journey is:

```text
Discover a skill
  -> Learn concepts
  -> Complete exercises
  -> Take assessments
  -> Earn XP and rewards
  -> Unlock new learning paths
  -> Build real projects
  -> Earn a certificate
```

## Learning principles

1. **Small achievable goals** — present short missions and challenges rather than overwhelming courses.
2. **Immediate feedback** — explain corrections and guide the learner after every activity.
3. **Visible progress** — always show what is complete, what is current, and what unlocks next.
4. **Practical learning** — knowledge must lead to projects and realistic problem solving.
5. **Human communication** — explain technical ideas plainly through examples, stories, humour, and relatable missions.

For example, introduce database normalisation as a mission to repair a chaotic student-record system with duplicated data, rather than as an abstract definition.

## MVP scope and authority

The current stack is React/Vite on the frontend and Laravel/MySQL REST APIs on the backend.

Laravel is the source of truth for scores, XP, rewards, node completion, and unlocks. The React application may display progress and request actions, but must never grant or calculate trusted progression. Certificates will follow the same rule when they are introduced.

## Delivery status

This is the implementation status, not a future feature list. Check an item only when the learner or administrator can use it through the application and the backend supports it.

### Done

- [x] Registration, login, logout, authenticated user details, and protected learner/admin routes.
- [x] Subjects and topics that learners can browse.
- [x] Multiple-choice question bank with answer options.
- [x] Quiz attempts that can be resumed, answered, submitted, and reviewed.
- [x] Immediate quiz feedback with explanations where content provides one.
- [x] Server-calculated score, XP, level, accuracy, and question progress.
- [x] Learner dashboard with progress, recent activity, active-quiz resume, and next-adventure summary.
- [x] Digital Foundations learning world with ordered nodes, server-validated prerequisite rules, XP unlock thresholds, and rewards.
- [x] Learner-facing adventure map showing completed, available, and locked missions.
- [x] Reward event tracking that prevents duplicate node-completion rewards.
- [x] Admin dashboard and question management: create, edit, delete, filter, and import questions.
- [x] Admin lesson-content editor for maintaining topic mission briefings, structured lesson sections, and required mission checks.
- [x] Floating learner companion with contextual guidance, minimise/reopen controls, and reduced-motion support.

### Next to build

- [x] Short, structured lesson content before a topic quiz, with learner completion stored server-side and quiz entry gated until the lesson is complete.
- [x] One server-validated mission check within each seeded lesson, with immediate feedback before quiz unlock.
- [ ] Additional practice exercises beyond the lesson mission check and quiz flow.
- [ ] Course model and course-management screens (subjects/topics exist, but a complete course-management system does not).
- [ ] Project-based challenges with learner submissions and review criteria.
- [x] Dedicated achievement milestone for completing the Digital Foundations world, awarded server-side and shown on the learner dashboard.
- [x] World-completion certificate issuance with unique certificate IDs and public verification.
- [x] Learner certificate area for claiming and viewing verified certificates.
- [ ] Downloadable, branded certificate documents.
- [x] Learner profile page with display-name editing and account identity synced across the portal.
- [ ] Student reports beyond the current dashboard aggregates.
- [ ] Offline support, AI mentor features, peer learning, and institution management.

## Product direction

Build the learner experience in this order:

1. Short lesson content before each assessment.
2. Mission-based course journeys with clear unlock rules.
3. Project challenges that demonstrate practical ability.
4. Certificate generation and public verification.
5. Focused administrator tools for content and learner progress.

Potential future work includes personalised recommendations, an AI mentor, peer learning, and institution management. Do not add them until they directly help a learner understand, practise, complete, or prove a skill.

## Learning companion

StudyFlow includes a small floating companion in learner-facing screens. It should provide concise, context-sensitive encouragement and explain the next meaningful action. Its motion must be gentle, respect reduced-motion preferences, and never block quiz controls or become a substitute for clear instructional content.
