# Users

## Goal

Define how users relate to schools and to each other within the application.

## Overview

A user is a teacher who manages one or more schools. The relationship between users and schools is many-to-many: a user can belong to multiple schools, and a school can have multiple users.

Each user–school membership has a role that determines what the user can do within that school.

## Roles

### Admin

The user who creates a school is automatically assigned the **admin** role. An admin can:

- Edit the school's details.
- Manage pupils within the school.
- Manage sessions within the school.
- Invite other users to join the school.

### Member

A user who is invited to a school receives the **member** role. A member can:

- View the school's details.
- Manage pupils within the school.
- Manage sessions within the school.

Members cannot edit school details or invite other users.

## School Membership

### Creating a School

When a user creates a school, they are automatically added as an admin of that school.

### Joining a School

A user can be invited to join an existing school by an admin of that school. The invited user joins as a member.

Invitation is by email. If the invited email belongs to an existing user, they are added immediately. If not, a placeholder invitation is stored and the user is added when they register.

### Choosing a School

If a user belongs to multiple schools, they select which school to work in. The active school context determines which pupils and sessions are visible.

## Acceptance Criteria

- [ ] A user can create a school and becomes its admin.
- [ ] A user can belong to multiple schools.
- [ ] A school can have multiple users.
- [ ] Each user–school membership has a role (admin or member).
- [ ] An admin can invite other users to join the school.
- [ ] A member can manage pupils and sessions but cannot edit school details or invite users.
