# 8-Ball Pool School

## Overview

An application for managing an 8-ball pool school.

The application is initially designed for **teachers only**. Teachers use it to manage their pupils, define training exercises, plan weekly training sessions, and record pupil results.

In a later version, pupils will have their own access to view their sessions, exercises, progress, and results.

## Initial Users

### Teacher

The teacher is the only user type in the first version.

A teacher can:

- Manage pupils
- Create and manage exercises
- Create weekly training sessions
- Assign exercises to pupils for a session
- Record the results of exercises
- Review a pupil's history and progress

### Pupil

Pupils do not have accounts in the first version.

Pupil accounts and a pupil-facing interface will be added later.

## Core Concepts

### School

A school is the central organizational unit. It represents a pool school managed by one or more teachers.

Users and schools have a many-to-many relationship. The user who creates a school is its admin and can invite other users to join. Pupils and sessions belong to a school. Exercises are global and shared across schools.

### Pupil

A person who is learning 8-ball pool.

A pupil has basic information such as:

- Name
- Optional contact information
- Notes

### Exercise

A training exercise designed to improve a specific pool skill.

An exercise should contain:

- Name
- Description/instructions
- Optional category
- Optional difficulty
- Notes

Exercises are reusable and can be assigned to many pupils across different sessions.

### Session

A weekly training session involving one or more pupils.

A session contains:

- Date
- One or more pupils
- Exercises assigned to each pupil
- Results recorded for each exercise

A session represents a specific training occasion, not a recurring schedule.

## Main Workflow

The core workflow is:

1. Teacher creates or selects a pupil.
2. Teacher creates a new weekly session.
3. Teacher selects the pupils participating in the session.
4. Teacher assigns exercises to each pupil.
5. During or after the session, teacher records the result of each exercise.
6. Results are stored as part of the pupil's history.
7. Teacher can review previous sessions and results.

## Product Principles

- Keep the workflow fast enough to use while teaching.
- Avoid unnecessary complexity.
- Make recording results require as few clicks as possible.
- Exercises should be reusable.
- A pupil's history should be easy to understand.
- The application should work well on a tablet or laptop.
- The first version should focus on teachers rather than trying to support pupils too early.

## Future Direction

Later versions may include:

- Pupil accounts
- Pupil dashboard
- Pupil progress tracking
- Exercise recommendations
- Statistics and charts
- Training plans
- Goals
- Teacher notes and feedback visible to pupils
- Multiple teachers
- Multiple schools per teacher
- Shared schools between teachers
