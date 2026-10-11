# Project overview

GitForge provides self-hosted Git repository hosting with a browser interface and real Git Smart HTTP transport. It is also a software-engineering portfolio project with documented requirements, design, testing, deployment, and maintenance.

## Architecture

React and TypeScript provide the web interface. Laravel provides versioned APIs, authentication, authorization, Git process coordination, and queued repository checks. PostgreSQL stores users, repository metadata, tokens, and workflow records. Repository contents live in managed bare Git repositories, separately from database metadata and public web files.

## Core journeys

1. Register or sign in and create a repository.
2. Choose visibility and issue a personal access token for Git access.
3. Clone, fetch, and push using a Git client.
4. Browse branches, commits, tags, directories, text files, and supported PDFs.
5. Propose and discuss a same-repository pull request; an owner reviews and merges it.
6. Run built-in repository checks and inspect their results in Actions.

Local development uses frontend port 5173 and backend port 8000. Actions requires a dedicated queue worker. See [setup](../../README.md), [collaboration contracts](../SDLC/05-implementation/Features/repository-collaboration.md), and [context diagram](project-context-diagram.md).
