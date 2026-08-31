# Schools

## Goal

Introduce the concept of a school as the central organizational unit. A school groups pupils and sessions together under a single entity managed by a teacher.

## Overview

A school represents a pool school run by a teacher. All pupils and training sessions belong to a school rather than directly to a user.

A teacher creates a school and manages everything within it. In the first version, a teacher has one school.

## Requirements

A teacher can:

* Create a school.
* View and edit the school's details.
* Manage pupils within the school.
* Manage sessions within the school.

A school should have:

* Name
* Optional description

## Ownership & Scope

### Users and Schools (Many-to-Many)

A school is connected to users through a many-to-many relationship with roles. The user who creates a school becomes its **admin**. Other users can be invited to join as **members**.

See [users.md](users.md) for full details on roles, invitations, and membership.

### Pupils belong to a School

Pupils are scoped to a school, not directly to a user. A pupil exists within the context of a single school.

All pupil routes and operations are nested under the school.

### Sessions belong to a School

Training sessions are scoped to a school. A session is created within a school and involves that school's pupils.

All session routes and operations are nested under the school.

### Exercises remain global

Exercises are a reusable library available across the entire application. They are not scoped to a school.

Any teacher can browse and use any exercise when assigning work to pupils during a session.

## Routing

Pupils and sessions should be nested under the school in the URL structure:

* `/schools/{school}/pupils`
* `/schools/{school}/pupils/{pupil}`
* `/schools/{school}/sessions`
* `/schools/{school}/sessions/{session}`

Exercises remain at the top level:

* `/exercises`
* `/exercises/{exercise}`

## Future Direction

Later versions may support:

* School-level settings and customisation.
* Role-based permissions beyond admin/member.

## Acceptance Criteria

* [ ] Teacher can create a school.
* [ ] Teacher can view and edit school details.
* [ ] Pupils belong to a school.
* [ ] Sessions belong to a school.
* [ ] Exercises remain global and are not scoped to a school.
* [ ] Pupil and session routes are nested under the school.
