# Relay — Build Notes (working scratchpad; superseded by WAKE-UP.md at the end)

Product: **Relay** — premium project-management app on the DevDojo Platform.
Stack: Laravel 13, Livewire 4, Volt, Folio, Tailwind v4, PHP 8.4, SQLite. Served by Herd at https://platform.test.

## Key decisions (documented for WAKE-UP.md)
- **Name:** Relay (user changed from "Tempo").
- **Accent:** Indigo/violet (`--accent`), monochrome neutral foundation. Dark-mode-first app shell; light marketing.
- **Font:** Inter (Geist-like grotesk) via bunny fonts.
- **Icons:** dependency-free inline-SVG `<x-icon name=.../>` Blade component (Phosphor/Heroicons style).
- **Drag & drop:** Livewire 4 native `wire:sort` with optimistic UI.
- **Filament admin:** REPLACED with a native Relay-styled `/admin` (Volt) for blog/changelog/plans.
  Reason: all 3 package Filament resources (Billing/Blog/Changelog) are authored against Filament 4
  namespaces; installing Filament 5 + porting v4→v5 resources overnight unattended is high-risk and
  would threaten the whole build. Native admin keeps design cohesive + zero risk. Package Filament
  plugins remain in vendor for anyone who installs Filament 5 and ports them.
- **Billing checkout:** package checkout needs Filament Blade components + live Stripe keys. Built a
  custom Relay pricing page + a clearly-labelled TEST-MODE "activate plan" flow that creates a
  Subscription row directly (no real Stripe call). Limit enforcement via HasPlanFeatures + config/limits.php.
- **Auth:** uses the devdojo/auth package pages (Folio/Volt) — the package has NO views publish tag
  (it's commented out), so deep view restyle isn't possible via publish. Tuned via auth appearance
  config (accent, dark, brand=Relay) + published assets. redirect_after_auth => /dashboard.
- **Profiles URL:** /u/{username} (Folio), package is data-layer only — public profile page built by us.

## Package → role in Relay
- auth: login/register/social/2FA gate. billing: Free/Pro/Team plans + limits. blog: /blog.
- changelog: /changelog + unread badge. notifications: bell + prefs + fire on task events.
- profiles: /u/{username} public profile + privacy. foundation: feature flags + /foundation/setup.

## Wave-style user columns needed
username, avatar, trial_ends_at (host migration) + two_factor_* (auth) + notification_preferences
(notifications) + social_links, privacy_settings (profiles). notifications adds after('avatar');
profiles adds after('notification_preferences') — ordering matters.

## Demo accounts
- demo@devdojo.test / password — Pro plan (everything unlocked)
- free@devdojo.test / password — Free plan (limit gating visible)
