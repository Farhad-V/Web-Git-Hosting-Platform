# Shared visual system

The interface uses one light/dark token palette for text, semantic colors, surfaces, borders, and shadows. Text and code sit on calmer surfaces; atmospheric color remains behind the workspace. Glass uses 85% white in light mode and 80% slate in dark mode, with a restrained edge highlight and glow. Header and floating navigation surfaces use higher opacity so content underneath does not compete with labels.

Repository navigation stays below the application header while scrolling and uses labeled buttons with distinct accents for branches, pull requests, Actions, and tags. Clone information is available through the keyboard-accessible **Clone & repository details** disclosure, collapsed initially on Code to prioritize browsing. Settings continues to show the clone information directly. The file sidebar retains its current-file marker.

Repository cards lift slightly on hover or keyboard focus. Primary controls have a blue/violet gradient. Status badges retain text and a border so color is not their only signal. The floating appearance control reserves space at the page end and accounts for device safe areas. Mobile navigation scrolls horizontally, inputs use 16px text, and decorative repository icons are omitted on small screens.

Blur fallback, reduced-motion, reduced-transparency, and forced-color styles cover the shared panel and navigation surfaces. There are no continuous decorative animations. Color preferences continue to persist and apply before React renders.

Verification includes the frontend regression suite, build and lint, native disclosure keyboard use, mobile overflow checks, and browser inspection of the landing page, sign-in, dashboard, repository browsing, collaboration, and creation dialog using an isolated demo database. Shared text and semantic tokens were checked against page, panel, field, and header surfaces with the atmospheric tints composited together: minimum measured ratios were 4.57:1 in light mode and 5.79:1 in dark mode. White text on the primary gradient endpoints measured at least 6.01:1. These are token-level measurements, not a certification of every rendered pixel or third-party PDF content.
