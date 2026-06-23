# SmarTern-Production Instructions

## Automatic Skill Triggers

### Frontend Tasks
When the user's task involves any of the following, automatically load the listed skills:

**Triggers:** HTML, CSS, Tailwind, Blade templates, responsive design, layout, styling, UI changes, component design, visual updates, mobile/tablet responsiveness, dark mode, animations, design system changes, copy/text changes, UX improvements, form styling, button styling, typography, color changes, spacing, grids, modals, cards, navigation, headers, footers, dashboards, data tables, charts, frontend JS/Alpine.js behavior

**Skills to load (all of them):**
- `tailwindcss-responsive` — responsive breakpoints, container queries, mobile-first
- `tailwindcss-development` — Tailwind utility classes, grid/flex, dark mode
- `ui-ux-practices` — visual hierarchy, whitespace, typography, color strategy, interaction states
- `design-taste-frontend` — premium design patterns, layout diversification, anti-default discipline
- `jakobs-law-ux` — familiar UX patterns, navigation flows
- `copywriting-skill` — UI labeling, buttons, system messages

### Backend Tasks
When the user's task involves any of the following, automatically load the listed skills:

**Triggers:** PHP, Laravel, controllers, models, migrations, Eloquent, routes, middleware, form requests, policies, jobs, queues, scheduled tasks, caching, database queries, API endpoints, services, commands, authentication, authorization, validation, testing, Pest/PHPUnit, broadcasting, events, notifications, roles/permissions, business logic

**Skills to load (all of them):**
- `laravel-best-practices` — Eloquent, N+1 prevention, caching, validation, authorization, queues
- `pest-testing` — writing/fixing Pest tests, feature tests, unit tests
- `echo-development` — Laravel Echo, WebSockets, broadcasting, Reverb, Pusher, real-time events
- `cross-role-dependencies` — multi-role business logic consistency (Intern, Supervisor, Head Supervisor, School Coordinator, Admin)
- `karpathy-principles` — engineering discipline, surgical changes, simplicity

### Mixed Frontend + Backend Tasks
If the task touches both layers (e.g., a feature that adds a new page with a controller + view), load **all** skills from both groups.
