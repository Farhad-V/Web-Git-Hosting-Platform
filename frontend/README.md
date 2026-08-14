# Git Hosting Platform - Frontend

React and TypeScript client for the self-hosted Git Hosting Platform.

## Technology

- React 19
- TypeScript
- Vite
- Tailwind CSS
- React Router
- TanStack Query
- Axios
- React Hook Form
- Zod
- Vitest
- Testing Library
- ESLint

## Requirements

- Node.js 24
- npm 11 or later
- Git Hosting Platform backend

## Installation

From the frontend directory, run:

    npm.cmd install

## Development

Start the Vite development server:

    npm.cmd run dev

The application is available at:

    http://127.0.0.1:5173

## Testing

Run the frontend test suite:

    npm.cmd run test

Run tests in watch mode:

    npm.cmd run test:watch

## Code Quality

Run ESLint:

    npm.cmd run lint

Audit npm dependencies:

    npm.cmd audit

## Production Build

Create the production bundle:

    npm.cmd run build

Preview the production bundle:

    npm.cmd run preview

## Current Foundation

The frontend foundation currently includes:

- responsive GitForge landing page
- browser routing
- TanStack Query provider
- Tailwind CSS design foundation
- authentication route placeholders
- Vitest and Testing Library configuration
- GitHub Actions frontend CI

Authentication forms, authenticated navigation, repository management screens,
and live Laravel API integration are implemented in subsequent feature stages.