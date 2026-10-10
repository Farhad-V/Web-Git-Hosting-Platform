# GitForge Frontend

React and TypeScript client for GitForge, the self-hosted Web Git Hosting Platform.

## Stack

- React 19
- TypeScript 6
- Vite 8
- Tailwind CSS 4
- React Router 7
- TanStack Query 5
- Axios
- React Hook Form
- Zod
- Vitest
- Testing Library
- ESLint

## Requirements

- Node.js 24
- npm 11 or later
- GitForge backend

## Installation

From the `frontend` directory:

```bash
npm ci
```

## Development

```bash
npm run dev
```

Default Vite URL:

```text
http://127.0.0.1:5173
```

## Implemented V1.0.0 UI

The frontend currently includes:

- responsive GitForge landing page;
- registration and login flows;
- guest and protected routing;
- authenticated dashboard;
- repository creation and listing;
- repository detail and visibility controls;
- repository browser navigation;
- branch selection, commit display, tree navigation, breadcrumbs, and text-file viewing;
- personal access token management;
- live integration with the Laravel API;
- TanStack Query data management;
- form validation and controlled error states.

The V1.0.0 UI intentionally reflects the implemented owner-centric repository model. Organization/team collaboration features are not claimed as implemented.

## Testing

Run the frontend suite:

```bash
npm run test
```

Watch mode:

```bash
npm run test:watch
```

## Code Quality

```bash
npm run lint
npm audit --omit=dev
```

## Production Build

```bash
npm run build
```

Preview the production bundle:

```bash
npm run preview
```

GitHub Actions validates dependency installation, linting, tests, the production build, and the production dependency audit.

## Backend Integration

The backend lives in `../backend` and exposes versioned APIs under `/api/v1`. Production routing is defined in `../deployment/nginx/gitforge.conf`.

## Documentation

Project-wide lifecycle and verification documentation is maintained in `../docs/`.

## Git Clone URLs

Development uses the Laravel server at http://127.0.0.1:8000. Production uses the current site's origin by default. Set VITE_GIT_BASE_URL at build time when Git HTTP is served from a different origin or base path.
