# Third-party licensing notes

The repository-level [LICENSE](LICENSE) reserves rights in Farhad Valizadegan's original project materials. It does not relicense third-party code, dependencies, framework scaffolding, fonts or other separately licensed assets.

- Laravel framework and Laravel-origin scaffold materials retain their applicable upstream MIT terms and copyright notices. The backend's proprietary package metadata describes the original application; it does not override those upstream terms.
- PHP dependencies are recorded in [composer.lock](backend/composer.lock). Review each package's supplied license and notices, including installed package license files, when distributing any permitted copy.
- JavaScript dependencies are recorded in [package-lock.json](frontend/package-lock.json). Their individual license entries and distributed notices remain applicable; the root `UNLICENSED` value refers only to the original frontend application.
- Third-party notices already present in individual files or distributed assets must be retained as required by their licenses.

This document is a scope clarification, not an exhaustive inventory or an assertion of ownership over dependencies. Any permission to redistribute the original project must also account for applicable third-party obligations. Permissions previously granted under another license remain governed by that grant; this notice does not retroactively cancel them.
