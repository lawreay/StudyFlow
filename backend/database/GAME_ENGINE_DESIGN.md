# Phase 4 Game Engine Database Design

## Scope

Phase 4.1 supports one static world, Network Adventure, with ordered nodes and server-calculated unlock state. It does not yet add missions, challenge assignments, animation state, or reward transactions. Those belong to Phase 4.2 or later when a real flow needs them.

## Existing Progress

`player_progress` remains the user-level record for XP, level, accuracy, completed questions, and score. Do not repurpose it for node state or accept XP values from React.

## Tables

### `game_worlds`

- `id`: primary key
- `slug`: unique stable identifier, e.g. `network-adventure`
- `name`: display name, e.g. `Network Adventure`
- `description`: world introduction and theme
- `is_active`: whether learners can access the world
- timestamps

### `game_nodes`

- `id`: primary key
- `world_id`: foreign key to `game_worlds`, cascade on world deletion
- `topic_id`: optional foreign key to existing `topics`; null means this node has no linked quiz requirement
- `slug`: stable identifier unique within a world
- `name`: node label
- `description`: optional learning context
- `position`: ordered map position, unique within a world
- `unlock_xp`: minimum server-recorded player XP required to unlock
- `reward_xp`: XP Laravel grants once when the learner completes this node
- `required_score`: optional minimum raw marks from a completed attempt for the linked topic
- `is_start_node`: marks the initial node
- timestamps

Network Adventure's initial node sequence is Computer, Switch, Router, Internet, Server. Phase 4.1 may seed this content separately from the schema migration.

### `player_node_progress`

- `id`: primary key
- `user_id`: foreign key to `users`, cascade on user deletion
- `game_node_id`: foreign key to `game_nodes`, cascade on node deletion
- `completed_at`: nullable completion time; null means not completed
- timestamps
- unique (`user_id`, `game_node_id`)

A row is created by trusted Laravel game logic, never by treating client-submitted XP or completion as authoritative. Locked state is calculated from the node's `unlock_xp` and the user's `player_progress.xp`; completion is read from this table.

## Deferred Tables

Do not create `missions` or a question/node pivot in Phase 4.1. Phase 4.2 should decide whether a mission is one question, an existing quiz attempt, or a multi-question challenge before persisting that relationship. Existing `questions`, `attempts`, and `attempt_answers` remain the learning source of truth.

## Phase 4.1 API Direction

- `GET /api/worlds`: list active worlds with summary/progression state for the authenticated user.
- `GET /api/worlds/{world}`: return ordered nodes with server-calculated `is_unlocked` and `is_completed` values.

The API must derive XP from the authenticated user's stored `player_progress` record. The client can render the result but cannot grant node unlocks, completion, or rewards.
