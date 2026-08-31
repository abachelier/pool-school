# Training Sessions

## Goal

Allow teachers to organize weekly training sessions and record what each pupil achieved during the session.

## Scope

Sessions belong to a school. All session management happens within the context of a school. See [schools.md](schools.md) for details.

## Session

A session represents one specific training occasion.

A session has:

* Date
* One or more pupils
* Exercise assignments

The application does not need recurring session scheduling in the first version.

## Creating a Session

A teacher can:

1. Create a new session.
2. Select the session date.
3. Select one or more pupils.
4. Assign exercises to each pupil.

Different pupils in the same session may have different exercises.

For example:

Session: Monday, March 9

* Alice

    * Exercise A
    * Exercise B
* Bob

    * Exercise A
    * Exercise C

## Exercise Assignment

An exercise assignment connects:

* A session
* A pupil
* An exercise

The assignment should allow the teacher to record the result achieved by the pupil.

## Recording Results

During or after a session, the teacher can record a result for an assigned exercise.

The exact result format should remain flexible in the first version because different exercises may use different scoring methods.

An exercise result may contain:

* Result/value
* Optional notes
* Completion status

The application should not assume that every exercise uses the same scoring system.

For example, one exercise might be:

> Pot 10 balls: 8/10

while another might be:

> Safety exercise: Completed

The data model should therefore allow different types of results to be introduced later.

## Session Status

A session can be:

* Planned
* In progress
* Completed

The teacher should be able to complete a session after results have been recorded.

## Session History

A teacher can view previous sessions.

A session detail page should show:

* Date
* Participating pupils
* Exercises assigned to each pupil
* Recorded results
* Teacher notes

## Pupil History

From a pupil's profile, the teacher should be able to see their previous sessions and exercise results.

This provides the foundation for future progress tracking.

## Acceptance Criteria

* [ ] Teacher can create a session.
* [ ] Teacher can select the session date.
* [ ] Teacher can select multiple pupils.
* [ ] Teacher can assign exercises to pupils.
* [ ] Different pupils can have different exercises.
* [ ] Teacher can record a result for an assigned exercise.
* [ ] Teacher can add notes to a result.
* [ ] Teacher can mark a session as completed.
* [ ] Teacher can view previous sessions.
* [ ] Teacher can view a pupil's session history.
* [ ] Historical results remain available even if a pupil or exercise is archived.
