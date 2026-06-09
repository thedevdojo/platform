# Deskly — a help desk template for the DevDojo Platform

Deskly is a complete, production-grade help desk: a shared ticket inbox, customer profiles,
SLA targets, internal notes, saved replies, CSAT, reports, and a public knowledge base —
wrapped in a calm, light-first design with full dark mode. Use it as the starting point for
any support tool, ticketing system, or customer-facing service product.

Built on **Laravel 13 + Livewire 4 (Volt) + Folio + Alpine + Tailwind CSS v4**, powered by
the DevDojo Platform packages (`devdojo/foundation`: auth, billing, blog, changelog,
notifications, profiles).

## Quick start

```bash
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite
php artisan migrate:fresh --seed
npm install && npm run build
```

Serve with [Laravel Herd](https://herd.laravel.com) (https://deskly.test) or `composer run dev`.

**Demo login:** `demo@devdojo.test` / `password` (admin, Pro plan). Five more seeded agents
(`maya@`, `dev@`, `sam@`, `riley@`, `free@devdojo.test`) share the same password. In local
environments, `/_demo-login` signs you in as the demo account instantly.

## What's inside

| Area | Where | What |
| --- | --- | --- |
| Marketing site | `/`, `/pricing`, `/blog`, `/changelog` | Landing page with a pure-CSS product preview, billing-backed pricing, blog & changelog from the DevDojo packages |
| Help center | `/help` | Public knowledge base with live search, categories, articles, draft previews |
| Inbox | `/inbox` | Queues (mine, unassigned, urgent, snoozed), search, longest-waiting sort, SLA flags |
| Ticket view | `/tickets/{id}` | Conversation thread, reply vs. internal note composer (⌘↵ to send), saved replies with `{customer}`/`{agent}` placeholders, status/priority/assignee/tags, activity timeline |
| Customers | `/customers` | Directory + per-customer profile with conversation history and CSAT |
| Reports | `/reports` | Volume, median first response, CSAT distribution, agent leaderboard, tags & channels |
| Knowledge base admin | `/kb` | Create, edit, publish/unpublish help center articles |
| Settings | `/settings/*` | Account, security (2FA), saved replies, notification prefs, billing, team |
| Everywhere | ⌘K | Command palette: tickets, customers, navigation |

## Domain model

`Ticket` (status, priority, channel, SLA helpers) → `Customer`, `Message` (reply/note),
`TicketEvent` (audit timeline), `Tag`, `SavedReply`, `Article`/`ArticleCategory`.
Enums in `app/Enums` carry labels, icons, colors, and SLA targets — the UI derives from them.

First-response SLA targets live on `TicketPriority` (urgent 1h · high 4h · normal 8h · low 24h);
`Ticket::isSlaBreached()` drives every breach indicator.

## Conventions

- Pages are Folio files in `resources/views/pages`; interactive components are class-based
  Volt in `resources/views/livewire`.
- Design tokens (warm paper canvas, jade accent) live in `resources/css/app.css` and flip
  with the `.dark` class. Components: `.btn`, `.card`, `.input`, `.nav-item`, `.badge`, `.kbd`.
- Feature flags: toggle auth/billing/blog/changelog/notifications/profiles at `/foundation/setup`
  or via `Foundation::enabled('billing')`.

## Tests

```bash
php artisan test --compact
```

Smoke tests cover every page plus ticket reply/resolve flows and help-center draft privacy.
