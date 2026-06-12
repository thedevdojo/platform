# ☀️ Good morning — meet **Relay**

I built **Relay**, a premium project-management app on the DevDojo Platform, to a
Linear/Vercel-quality bar. It's running, seeded, and ready to log into in one click.

> Product name: you asked to swap **Tempo → Relay**. Done, everywhere.

---

## 🔑 Log in (one click)

The app is served by Herd at **https://platform-project.test**.

| Account | Email | Password | Plan | What it shows |
| --- | --- | --- | --- | --- |
| **Primary demo** | `demo@devdojo.test` | `password` | **Pro** (everything unlocked) + admin | The full app, all 4 projects, `/admin` |
| **Free demo** | `free@devdojo.test` | `password` | **Free** (2-project limit, already at it) | The upgrade-gating experience |

Both are members of the **Northwind** workspace (4 projects, 42 tasks, 6 teammates).

> ℹ️ Served at `platform-project.test` via Herd's parked `~/Sites` directory (no extra setup needed).
> `APP_URL` is set to match. (Earlier I'd temporarily linked `platform.test`; that alias has been removed.)

---

## 🗺️ Take the tour

- **Landing** — https://platform-project.test/  (hero + live in-browser product preview)
- **Pricing** — https://platform-project.test/pricing  (monthly/annual toggle, test-mode activation)
- **Dashboard** — https://platform-project.test/dashboard  (your tasks, stats, activity)
- **Projects** — https://platform-project.test/projects  → open **Website Redesign**
- **The board** — drag cards between columns (optimistic), click a card for the **slide-over**
- **⌘K** — press it anywhere to search/jump (Cmd+K or Ctrl+K)
- **Inbox** — https://platform-project.test/inbox  (the bell in the top bar too)
- **Changelog** — https://platform-project.test/changelog  (unread badge clears on view)
- **Blog** — https://platform-project.test/blog
- **Public profile** — https://platform-project.test/u/alex
- **Settings** — https://platform-project.test/settings/account  (Account, Security/2FA, Notifications, Billing, Team)
- **Admin** — https://platform-project.test/admin  (blog + changelog CRUD, plans) — demo account only
- **Feature toggles** — https://platform-project.test/foundation/setup

**Try the free-plan gate:** log in as `free@devdojo.test`, go to Projects, click **New project** → upgrade prompt.

---

## ▶️ How to run / develop

The built assets are committed-ready (`npm run build` already run). For live dev:

```bash
npm run dev          # Vite for hot CSS/JS
# Herd serves PHP automatically at https://platform-project.test
php artisan test --compact   # 38 passing
php artisan migrate:fresh --seed   # rebuild the demo workspace anytime
```

If a UI change doesn't show up, run `npm run build` (or `npm run dev`).

---

## 🧩 Which DevDojo package powers what

| Package | Role in Relay |
| --- | --- |
| **foundation** | All 6 features enabled; every feature's UI is gated with `Foundation::enabled()` so a disabled feature degrades cleanly. `/foundation/setup` works. |
| **auth** | The whole auth layer — login / register / social / 2FA. Host `User` extends `Devdojo\Auth\Models\User`. Screens branded via the auth *appearance* config. |
| **billing** | Free / Pro / Team plans (DB) with feature **limits**. `HasPlanFeatures` + `config/limits.php` enforce the Free 2-project cap. Pricing + Billing settings are custom Relay UI; activation runs in **test mode**. |
| **blog** | Public `/blog` (index + post) + admin CRUD. 3 seeded posts. |
| **changelog** | `/changelog` timeline + per-user read tracking → unread dot in the sidebar. 5 seeded entries. Admin CRUD. |
| **notifications** | Native DB notifications. Bell + dropdown + `/inbox` + preferences page. Fires on **task assigned, new comment, status → done**. |
| **profiles** | Public profile at `/u/{username}` (bio, social links, privacy) via `HasProfileKeyValues` + JSON columns. Editable in Account settings. |

All integration goes through the **supported surfaces** (User-model traits, named routes, the packages' models) — no querying package tables directly, no editing `vendor/`.

---

## 🎨 Design + product decisions (and why)

- **Aesthetic:** dark-first, monochrome-neutral foundation + a single restrained **indigo/violet** accent. Hairline borders, 8–12px radii, generous whitespace, 150ms motion, staggered enter animations, optimistic DnD, loading/empty/error states everywhere.
- **Type:** **Geist** + **Geist Mono** (Vercel's typeface) via Bunny CDN with a graceful system fallback — decoupled from the build so a missing font never breaks `npm run build`.
- **Design system:** one `app.css` token layer (`bg-canvas/surface/elevated`, `text-fg/muted/subtle`, `border-line`, `bg-accent…`) that flips light/dark via CSS vars + `@theme inline`, plus component classes (`.btn`, `.card`, `.input`, `.badge`, `.nav-item`, `.kbd`) and reusable Blade components (`x-icon`, `x-avatar`, `x-logo`, `x-dot`, `x-label-chip`, `x-due-chip`, `x-toasts`).
- **Icons:** a dependency-free inline-SVG `<x-icon>` set (I renamed blade-icons' default `icon` component to `svg-icon` so Relay owns the tag).
- **Drag & drop:** native HTML5 DnD via a small Alpine component with **optimistic UI**, persisted through a Livewire `moveTask` — exactly as you preferred (no heavy framework). `wire:sort` was avoided because its handler signature couldn't be confirmed offline.
- **Stack:** Livewire 4 + Volt (class-based for the board/slide-over, functional elsewhere) + Folio pages + Tailwind v4. State stays server-side.

---

## ⚠️ Toggled off / stubbed / known gaps

- **Filament admin → replaced with a native Relay-styled `/admin`.** The three package
  Filament resources (Billing/Blog/Changelog) are written against **Filament 4** namespaces;
  installing Filament 5 and porting them overnight, unattended, was high-risk and would have
  threatened the whole build. The native admin (Volt) is cohesive and zero-risk. The package
  Filament plugins are untouched in `vendor/` for anyone who installs Filament 5 and ports them.
- **Billing is test mode.** No Stripe/Paddle keys. Choosing a plan on `/pricing` activates it
  instantly by writing a `Subscription` row + syncing the Spatie role (clearly labelled "test mode").
  The package's real checkout/update views depend on `<x-filament::modal>`; since Relay doesn't
  install Filament I override those two unused views and register a no-op `filament::` stub so
  `view:cache` / `php artisan optimize` stay green. **Don't rely on `/billing/checkout` (package route)** — use `/pricing`.
- **Auth screens** use the package's own Folio/Volt pages, branded via the *appearance* config
  (Relay mark, indigo accent). A deeper pixel-level restyle isn't possible because the package
  doesn't expose a views publish tag (it's commented out upstream).
- **2FA** is enabled and linked from Security settings; it uses the package's own 2FA page.
  Fortify isn't installed, so it relies on the package's built-in Google2FA flow.
- **Social login** buttons render (package), but no OAuth client IDs are configured — add provider
  keys in `config/devdojo/auth/providers.php` (env) to make them live.
- The `members` plan limit is shown in the UI but only the **projects** limit is hard-enforced at
  creation (the headline gate you asked for). Wiring per-project seat enforcement is a small follow-up.

---

## 🐛 Two real bugs I found & fixed (caught by the tests)

1. **Slide-over crash:** opening any task with comments/activity threw `getKey() on array` —
   `Eloquent\Collection::merge()` ran on mapped arrays in the timeline. Fixed with base collections + explicit `wire:key`s.
2. **Billing cache:** `Plan::getActivePlans()` returned a `__PHP_Incomplete_Class` from the DB cache;
   I query plans directly in the pricing/admin views instead.

---

## ✅ Definition of done

- [x] `php artisan migrate:fresh --seed` runs clean and populates the demo
- [x] `npm run build` succeeds; app boots at https://platform-project.test with no errors
- [x] Both demo logins work and land in a full, populated app
- [x] Landing, board (working drag-and-drop), slide-over, ⌘K, pricing, changelog, blog, profile, notifications, settings, `/admin` all function
- [x] Every DevDojo package is visibly used
- [x] Free-plan project limit enforced with an upgrade prompt
- [x] **38 Pest tests pass**; `pint` clean
- [x] Looks like a real product

---

## 🔭 What I'd build next

1. **Filament 5 admin** (port the package resources) if you want the packaged admin back.
2. **Real Stripe test checkout** wired to the package's gateway (add `pk_test`/`sk_test`).
3. **Saved filters & sorting** on the board (by assignee/label/priority) + a "My issues" view.
4. **Keyboard-create from ⌘K** (e.g. "Create task in WEB…") — palette is ready for actions.
5. **Realtime** task updates across clients (Laravel Echo/Reverb) so boards sync live.
6. **Per-project labels** (currently workspace-global) + a label manager.
7. **Browser/visual tests** (Pest 4 `visit()` + Playwright) for the DnD and slide-over.

Enjoy — I'm proud of this one. 🚀
