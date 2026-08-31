# Exercises

## Goal

Allow teachers to create and maintain a reusable library of 8-ball pool training exercises.

## Requirements

A teacher can:

* View all exercises.
* Create an exercise.
* View an exercise.
* Edit an exercise.
* Archive an exercise.

An exercise should have:

* Name
* Category
* Description/instructions
* Image
* Optional difficulty
* Optional notes
* Active/archived status

## Exercise Categories

The application should require a fixed set of categories initially.
Categories can be added and removed by the administrator.

The teacher should be able to categorize exercises in a way that makes sense for the school.

Possible examples:

* Basic potting
* Potting down the rail
* Back spin
* Top spin
* Stop shot
* Break
* Pattern play

## Reusability

An exercise is a reusable definition.

Assigning an exercise to a pupil in a session must not modify the original exercise.

If an exercise is later edited, historical session results must still make sense.

## Behaviour

Archived exercises should not be available for new session assignments by default.

Previously completed exercises and their results must remain visible in historical sessions.

## Acceptance Criteria

* [ ] Teacher can create an exercise.
* [ ] Teacher can edit an exercise.
* [ ] Teacher can view an exercise.
* [ ] Teacher can archive an exercise.
* [ ] Active exercises are available when creating a session.
* [ ] Archived exercises are excluded from new assignments.
* [ ] Historical assignments remain accessible.
