# StudyFlow

## Game-Based Learning Platform

## Project Overview

StudyFlow is an interactive learning platform that transforms studying into a game experience.

Students answer educational questions, earn experience points (XP), unlock levels, and progress through animated learning worlds.

The platform combines:

* React for interactive gameplay and animations
* Laravel API for business logic
* MySQL for data storage

Architecture:

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

---

# Core Gameplay Loop

```
Start Learning Mission
          |
          ↓
Receive Question
          |
          ↓
Choose Answer
          |
     ┌────┴────┐
     ↓         ↓
 Correct     Wrong
     ↓         ↓
 Gain XP    Explanation
     ↓         ↓
 Animation  Retry
     |
     ↓
Complete Mission
     |
     ↓
Unlock Progress
```

---

# Version 1: Networking World

The first learning world is:

## "Packet Journey"

The player controls a network packet traveling through a computer network.

Visual world:

```
Computer
   |
   |
Router
   |
   |
Internet
   |
   |
Server
```

Each correct answer moves the packet closer to the destination.

Each wrong answer causes the packet to lose energy and displays an explanation.

---

# Frontend Features (React)

## 1. Player Dashboard

Display:

* Username
* Level
* XP
* Learning streak
* Completed missions
* Achievements

Example:

```
PLAYER

Lawrence

Level 3
██████░░░░

XP: 240/500

🔥 5 Day Streak
```

---

## 2. Learning World

Create an animated environment.

Example:

```
NETWORK WORLD


       💻

        \
         \
          🔀

            \
             🌐

              \
               🖥️
```

Animations:

* Packet movement
* Particle effects
* Success animations
* Failure animations
* Level-up animation

---

## 3. Question Engine

Question screen:

```
Mission 4/20


Which protocol provides reliable
data transmission?


A. UDP

B. TCP

C. DNS

D. ARP


SUBMIT
```

Features:

* Answer selection
* Timer
* Progress indicator
* Previous/next navigation
* Auto-save

---

## 4. Physics System

Create simple physics:

Objects:

```
position
velocity
acceleration
gravity
collision
```

Example:

Correct answer:

```
Packet velocity increases

Router → Internet
```

Wrong answer:

```
Packet slows down

Energy decreases
```

Use:

* requestAnimationFrame()
* Canvas
* SVG animations

---

# Backend Features (Laravel)

## API Structure

```
/api/auth

/api/users

/api/worlds

/api/topics

/api/questions

/api/exams

/api/attempts

/api/progress

/api/achievements
```

---

# MySQL Database

Database:

```
studyflow
```

## users

```
id
name
email
password
role
created_at
updated_at
```

---

## subjects

```
id
name
description
created_at
updated_at
```

Example:

```
1
Computer Networking
```

---

## topics

```
id
subject_id
name
description
level_required
```

Example:

```
TCP/IP Basics
```

---

## questions

```
id
topic_id
question_text
difficulty
points
created_at
updated_at
```

---

## question_options

```
id
question_id
option_text
is_correct
```

---

## attempts

```
id
user_id
topic_id
score
xp_earned
started_at
completed_at
```

---

## attempt_answers

```
id
attempt_id
question_id
selected_option_id
correct
```

---

## player_progress

```
id
user_id
level
xp
energy
streak
```

---

## achievements

```
id
name
description
icon
```

---

## user_achievements

```
id
user_id
achievement_id
unlocked_at
```

---

# Game Rules

## XP System

Correct answer:

```
+10 XP
```

Hard question:

```
+20 XP
```

Complete topic:

```
+100 XP
```

---

## Level System

```
Level 1
Beginner
0 XP


Level 2
Explorer
100 XP


Level 3
Builder
250 XP


Level 4
Engineer
500 XP
```

---

# React Components

Structure:

```
frontend/src

components/

PlayerCard.jsx

QuestionCard.jsx

Timer.jsx

Packet.jsx

World.jsx

XPBar.jsx

AchievementPopup.jsx


pages/

Dashboard.jsx

WorldPage.jsx

Mission.jsx

Results.jsx


hooks/

useGameState.js

useTimer.js

useProgress.js


services/

api.js
```

---

# Laravel Structure

```
backend/

app/

Models/

Controllers/

Services/


routes/

api.php


database/

migrations/

seeders/
```

---

# Development Milestones

## Phase 1

Foundation

* Laravel setup
* React setup
* MySQL connection
* Authentication

## Phase 2

Learning Engine

* Subjects
* Topics
* Questions
* Answers

## Phase 3

Game Engine

* XP system
* Levels
* Animations
* Packet movement

## Phase 4

Progress System

* Achievements
* Streaks
* Statistics

## Phase 5

Deployment

* Build React
* Deploy Laravel
* Configure MySQL
* Host on InfinityFree

---

# MVP Goal

The first working version must allow:

```
User Login

        ↓

Choose Networking World

        ↓

Answer 20 Questions

        ↓

Watch Packet Move

        ↓

Earn XP

        ↓

Receive Result

        ↓

Save Progress
```

Do not add:

* Chat
* Payments
* Social network
* AI tutor
* Multiplayer
* Mobile app

until the core learning game works.
