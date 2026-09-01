# Pupils

## Goal

Allow teachers to create and manage pupils within their school.

## Scope

Pupils belong to a school. All pupil management happens within the context of a school. See [schools.md](schools.md) for details.

## Requirements

A teacher can:

- View a list of pupils.
- Create a pupil.
- View a pupil's details.
- Edit a pupil.
- Archive a pupil who is no longer active.

A pupil should have:

- Name
- Optional email
- Optional phone number
- Optional notes
- Active/inactive status

## Pupil Details

The pupil detail page should eventually provide an overview of:

- Basic information
- Recent sessions
- Exercises completed
- Exercise results
- Progress over time

For the first version, the focus is on displaying the pupil's session history and results.

## Behaviour

Archived pupils should not appear in the default active pupil list.

Historical sessions and results must remain available for archived pupils.

## Acceptance Criteria

- [ ] Teacher can create a pupil.
- [ ] Teacher can edit a pupil.
- [ ] Teacher can view a pupil.
- [ ] Teacher can archive a pupil.
- [ ] Active pupils are shown by default.
- [ ] Archived pupils remain accessible.
- [ ] A pupil's sessions can be accessed from their details.
