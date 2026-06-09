# Deskly access model: roles, invites, and customer intake

**Date:** 2026-06-09
**Status:** Approved

## Problem

Deskly currently gates the entire agent app behind `auth` only. Anyone who self-registers
becomes, effectively, an agent: they can read every ticket, reply to customers, and
create/edit/delete knowledge-base articles. A help desk needs a boundary between the
support team and the public, and customers need a way to submit tickets without an account.

## Decisions (made with the user)

1. **Customer side:** public "Submit a request" form + email-style threads. No customer
   accounts or portal in this iteration (data model keeps a portal possible later).
2. **Team side:** public registration is closed; team members join via signed invite links
   generated from Settings → Team.
3. **Role split:** agents do all operational work; admins additionally manage the workspace.

## Roles & access

Two Spatie roles (`admin` role and supporting middleware already exist):

| Capability | agent | admin |
| --- | --- | --- |
| Dashboard, inbox, all tickets (view/reply/assign/resolve/snooze/tag) | ✓ | ✓ |
| Customers (view, create manually) | ✓ | ✓ |
| Reports | ✓ | ✓ |
| KB: create, edit, publish/unpublish articles | ✓ | ✓ |
| KB: delete articles | — | ✓ |
| Saved replies (own + shared) | ✓ | ✓ |
| Settings: account, security, notifications, saved replies | ✓ | ✓ |
| Settings: Team (invite, revoke, promote/demote, remove) | — | ✓ |
| Settings: Billing & plan | — | ✓ |
| `/foundation/setup` feature toggles | — | ✓ |

### Enforcement

- New `App\Http\Middleware\EnsureUserIsAgent`: passes users with `agent` or `admin` role.
  Applied to all app pages: `/dashboard`, `/inbox`, `/tickets/*`, `/customers/*`, `/reports`,
  `/kb`, `/notifications`, `/settings/*`.
- Existing `EnsureUserIsAdmin` (or equivalent checks inside components) guards Team and
  Billing settings pages and the KB `deleteArticle` action. Server-side checks in Volt
  actions, not just hidden buttons.
- An authenticated user with **no role** is redirected to a friendly standalone page
  ("You're not part of this workspace yet — ask an admin for an invite"), not a bare 403.
- Admin-only UI (Team/Billing tabs, KB delete buttons) is hidden from agents.

## Team onboarding (invite links)

- **Close public registration** via the devdojo/auth native setting
  (`registration_enabled = false`), persisted the way the auth package stores settings.
- **Bootstrap exception:** when `User::count() === 0`, registration is permitted and the
  first registered user receives the `admin` role. The template therefore works on a fresh
  install with no seeding.
- **Invites table:** `invites` — `id`, `email`, `token` (hashed), `role` (`agent`|`admin`,
  default `agent`), `invited_by` (FK users), `expires_at` (7 days), `accepted_at`,
  timestamps. Model: `App\Models\Invite` with `isPending()`, `isExpired()` helpers.
- **Create invite (admin, Settings → Team):** enter email, optionally choose role →
  generates a signed URL (`URL::temporarySignedRoute`) embedding the invite token →
  shown with copy-to-clipboard. Production note in code: also send a Mailable here.
- **Accept page (`/invite/{token}`, guest-only):** valid + signature OK → register form
  with email pre-filled and read-only, name + password fields → creates the user with the
  invite's role, marks invite accepted, logs in, redirects to the dashboard.
  Invalid/expired/revoked/already-accepted → clear error state on the same page.
  An existing user hitting a valid invite link is told the email already has an account.
- **Manage invites:** Team settings lists pending invites (email, role, expiry) with
  revoke (delete) buttons; also lists current members with promote/demote (agent ⇄ admin)
  and remove actions. Admins cannot demote or remove themselves if they are the last admin.

## Customer intake

- **Public form** at `/help/contact` (Folio page + Volt component, named `help.contact`):
  name, email, company (optional), subject, message. Honeypot field + simple per-IP/email
  rate limit for spam. Linked from the help center "Contact support" card and the
  marketing footer (replacing the current `mailto:`).
- **On submit:** find-or-create `Customer` by email (update name/company if blank on
  record), create `Ticket` (channel `web`, status `open`, priority `normal`, unassigned),
  create the customer `Message`, record the `created` event, set `last_activity_at`,
  notify admins in-app (`TicketActivityNotification`, event `new_reply`).
- **Confirmation:** success state shows the ticket number and the email we'll reply to.
- **Email seam:** agent replies remain in-thread for the demo; a clearly marked
  comment/seam indicates where a production Mailable would send the reply to
  `customer.email`. No mail is sent in the demo.
- **Manual creation:** "New customer" action on `/customers` (name, email, company,
  location, plan) for agents.

## Demo, seeding & copy

- Seeder: all six demo users get `agent`; Alex additionally keeps `admin`. Seed one
  pending invite (e.g. `taylor@nimbus.test`) so Team settings demonstrates the flow.
- Marketing CTAs that pointed to `/auth/register` ("Start for free", "Get started")
  point to the login/live-demo flow instead, with copy adjusted where needed.
- `/_demo-login` stays (local env only).
- README updated: roles table, invite flow, customer intake, registration-closed note.

## Error handling

- Expired/revoked/used invite → explanatory page with no information leak about accounts.
- Role-less authenticated user → "not part of this workspace" page (with logout link).
- Contact form: validation errors inline; honeypot and rate-limit failures fail silently
  (pretend success to bots, generic throttle message to humans).
- Last-admin protection on demote/remove.

## Testing

Pest feature tests:

- Agents can access every app page; role-less users are redirected to the holding page;
  guests are redirected to login.
- Non-admins: 403 (or hidden + server-rejected) on Team/Billing settings and KB delete.
- Invite flow: create (admin only), accept creates an `agent` and logs in, expired/revoked
  links rejected, accepted links can't be reused, last-admin protections hold.
- Bootstrap: with zero users, registration works and first user is admin; with users
  present, registration is closed.
- Contact form: creates customer + ticket + message + event, dedupes customer by email,
  honeypot rejects, admins get a notification.
- Existing smoke tests updated for the new role requirements.

## Out of scope (explicitly)

- Customer portal / magic-link login (data model already supports adding it later).
- Real email sending (seams marked for production).
- Per-ticket visibility restrictions (shared-inbox model retained).
