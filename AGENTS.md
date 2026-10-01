# AGENTS.md

# StudyFlow - AI Development Guidelines

## Project Identity

StudyFlow is a game-based learning platform that combines education with interactive gameplay.

The goal is to make studying engaging through:

- Interactive questions
- Game mechanics
- Animations
- Progress systems
- Rewards
- Learning analytics

The application is NOT a traditional exam system. The core experience should feel like a learning game.

---

# Technology Stack

## Frontend

- React
- Vite
- JavaScript/TypeScript
- CSS/Tailwind CSS
- Animation libraries when justified

Responsibilities:

- User interface
- Game rendering
- Animations
- Client-side interaction
- Temporary game state

## Backend

- Laravel
- PHP
- REST API

Responsibilities:

- Authentication
- Business rules
- Validation
- Scoring
- Progress tracking
- Data security

## Database

- MySQL

The database is the source of truth for:

- Users
- Questions
- Progress
- Scores
- Achievements

---

# Architecture Rules

Follow this architecture:
```
React Frontend
|
| REST API
|
Laravel Backend
|
|
MySQL Database

```
Rules:

- React must NEVER connect directly to MySQL.
- Business logic belongs in Laravel.
- push after Each big change and add this file to git ignore.
- Database queries should not exist inside React.
- Keep frontend and backend responsibilities separated.

---

# Development Philosophy

## Build Complete Features

Prefer:
```
Small complete feature
↓
Test
↓
Improve
↓
Next feature

```
Avoid:
```
Build 50 unfinished systems

```
A working simple feature is better than a complex unfinished feature.

---

# MVP Scope

The first release must include:

## Authentication

- Register
- Login
- Logout
- User profile

## Learning System

- Subjects
- Topics
- Questions
- Multiple-choice answers

## Game System

- XP
- Levels
- Progress tracking
- Basic animations

## First Game World

Network Adventure: "Become a Network Engineer."

The learner progresses through a static network map with Computer, Switch, Router, Internet, and Server nodes. Challenges and packet movement are introduced only after the static world and server-side unlock rules are verified.

## Results

Display:

- Score
- XP gained
- Questions completed
- Progress

---

# Database Rules

Use Laravel migrations.

Never manually modify production databases.

Database changes must follow:
```
Create migration
↓
Test locally
↓
Commit migration
↓
Deploy

```
---

# Database Design Rules

Avoid premature complexity.

Start with:
```
users
subjects
topics
questions
question_options
attempts
attempt_answers
player_progress

```
Do not create tables unless a real feature requires them.

---

# React Rules

## Components

Keep components focused.

Good:
```
QuestionCard
Timer
XPBar
PacketAnimation

```
Bad:
```
Everything.jsx

```
---

## State Management

Use:

- React state for local UI
- Context for simple global state
- Add external state libraries only when necessary

Do not introduce complexity before the problem exists.

---

# Game Development Rules

The game layer should enhance learning.

Avoid:

- Random animations with no purpose
- Features that distract from studying
- Complex physics before gameplay works

Priority:

1. Learning experience
2. Reliability
3. Performance
4. Visual polish

## Phase 4: StudyFlow Game Engine

Build the Network Adventure in small milestones:

1. Phase 4.1: static world and node map, world/nodes API, server-calculated locked/unlocked state, and node selection. No animations or physics.
2. Phase 4.2: connect nodes to existing questions and attempts; Laravel validates completion and awards rewards.
3. Phase 4.3: purposeful React movement and effects after the learning/game state is correct.

Authority rules:

- Laravel is the source of truth for XP, node unlocks, mission completion, and rewards.
- React may render a requested action but must never submit client-calculated XP, unlock status, or completion as trusted state.
- Reuse `player_progress` for user-level XP/level/accuracy. Store per-node completion separately; do not overload the existing table.
- Keep the first world specific to networking. Do not add a generic game engine, achievements, multiplayer, AI tutor, or physics in Phase 4.1.

---

# Animation Rules

Animations should communicate something.

Examples:

Good:
```
Correct answer
↓
Packet moves forward
↓
XP gained

```
Bad:
```
Random explosion every click

```
---

# Security Rules

Never:

- Store passwords manually
- Expose database credentials
- Trust frontend validation only
- Allow users to modify XP directly

Always:

- Validate input on Laravel
- Use authentication middleware
- Authorize actions
- Sanitize user input

---

# API Rules

API responses should be consistent.

Example:

```json
{
    "success": true,
    "data": {},
    "message": "Operation completed"
}
```
Errors:

```
{
    "success": false,
    "message": "Validation failed",
    "errors": {}
}
```

---

# Testing Rules
Test important logic:

Backend:

- Authentication
- Scoring
- Permissions
- Progress calculation
Frontend:

- Question interaction
- Game state
- Critical user flows
Do not chase 100% coverage.

Test what can break users.

---

# Code Quality Rules
Before adding code:

Ask:

1. Does this solve a real user problem?
2. Is this required for the current milestone?
3. Will another developer understand this in two years?
Prefer:

- Simple code
- Clear naming
- Small functions
- Documentation
Avoid:

- Clever hacks
- Over-engineering
- Unnecessary abstractions

---

# Git Rules
Commit frequently.

Commit format:

```
feat: add question engine

fix: correct scoring calculation

refactor: simplify API service
```
Never commit:

```
.env
node_modules/
vendor/
```

---

# Deployment Rules
Development:

```
React/Vite
Laravel
MySQL
WAMP
```
Production:

```
React build
Laravel
MySQL
```
Build locally before deployment.

---

# Feature Priority Rules
Priority order:

1. Core learning experience
2. Question system
3. Progress tracking
4. Game mechanics
5. Visual improvements
6. Extra features
Do not add:

- Chat
- Payments
- AI tutor
- Multiplayer
- Social features
until the core learning loop is excellent.

---

# AI Assistant Instructions
When modifying this project:

- Understand existing architecture first.
- Do not rewrite working systems unnecessarily.
- Explain trade-offs before major changes.
- Prefer maintainable solutions.
- Challenge unnecessary complexity.
- Keep the MVP focused.
The goal is not to build the biggest platform.

The goal is to build a polished learning game that users actually enjoy using.
