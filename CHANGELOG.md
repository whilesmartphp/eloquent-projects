# Changelog

All notable changes to `eloquent-projects` will be documented in this file.

## [1.0.0] - 2026-03-16

### Added
- Initial release
- Polymorphic Project model with owner and creator relationships
- ProjectStatus model for configurable lifecycle states
- ProjectAssignment model for polymorphic entity assignments with roles
- HasProjects trait for project owners (workspaces, teams, orgs, etc.)
- BelongsToProject trait for child models (versions, tasks, etc.)
- AssignableToProject trait for assignable entities (users, agents, etc.)
- Project archiving with archive/unarchive support
- Flexible JSON metadata on projects and assignments
- Query scopes: ownedBy, createdBy, withStatus, active, archived
- Events: ProjectCreated, ProjectUpdated, ProjectDeleted, ProjectArchived,
  ProjectUnarchived, ProjectAssigned, ProjectUnassigned
- Before/after middleware hooks on all controller actions
- Configurable models, routes, and middleware via config
- API routes for project CRUD, archive, and unarchive
- Docker + Makefile development setup
- Laravel 10, 11, and 12 support
