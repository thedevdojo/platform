<?php

namespace Database\Seeders;

use App\Enums\MessageType;
use App\Enums\TicketStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Customer;
use App\Models\Invite;
use App\Models\SavedReply;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketActivityNotification;
use Devdojo\Billing\Models\Plan;
use Devdojo\Billing\Models\Subscription;
use Devdojo\Blog\Models\Category;
use Devdojo\Blog\Models\Post;
use Devdojo\Changelog\Models\Changelog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $agents = $this->createAgents();
        $alex = $agents['alex'];

        $this->setupBilling($alex);

        $tags = $this->createTags();
        $customers = $this->createCustomers();
        $this->createSavedReplies($agents);
        $this->createTickets($agents, $customers, $tags);
        $this->seedNotifications($alex);
        $this->seedKnowledgeBase($agents);
        $this->seedChangelog();
        $this->seedBlog($alex);

        // A pending invite so Settings → Team demonstrates the flow.
        Invite::firstOrCreate(
            ['email' => 'taylor@nimbus.test'],
            ['token' => Str::random(48), 'role' => 'agent', 'invited_by' => $alex->id, 'expires_at' => now()->addDays(6)],
        );
    }

    /**
     * @return array<string, User>
     */
    protected function createAgents(): array
    {
        $people = [
            'alex' => ['Alex Rivera', 'demo@devdojo.test', 'alex', 'Head of Support', 'Running support at Nimbus. Inbox zero is a lifestyle, not a goal.'],
            'maya' => ['Maya Chen', 'maya@devdojo.test', 'maya', 'Support Engineer', 'Debugging sync issues by day, writing help docs by night.'],
            'dev' => ['Dev Patel', 'dev@devdojo.test', 'dev', 'Technical Support', 'API, webhooks, and the weird edge cases nobody else wants.'],
            'sam' => ['Sam Okafor', 'sam@devdojo.test', 'sam', 'Support Specialist', 'Front of the queue. First reply under an hour or it does not count.'],
            'riley' => ['Riley Brooks', 'riley@devdojo.test', 'riley', 'Customer Success', 'Onboarding, renewals, and turning angry emails into renewals.'],
            'jordan' => ['Jordan Lee', 'free@devdojo.test', 'jordan', 'Founder', 'Still answers tickets on weekends. Sorry, team.'],
        ];

        $agents = [];

        foreach ($people as $key => [$name, $email, $username, $title, $bio]) {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'username' => $username,
                    'title' => $title,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed='.urlencode($name).'&backgroundColor=b6e3f4,c0aede,d1d4f9,ffd5dc,ffdfbf,c9f7d4&radius=50',
                    'social_links' => [
                        'website' => 'https://'.$username.'.deskly.app',
                        'github' => 'https://github.com/'.$username,
                        'twitter' => 'https://x.com/'.$username,
                    ],
                    'privacy_settings' => [
                        'profile_visibility' => 'public',
                        'show_email' => false,
                        'allow_search_engines' => true,
                    ],
                    'notification_preferences' => [
                        'email_notifications' => true,
                        'marketing_emails' => $key === 'riley',
                        'product_updates' => true,
                        'blog_notifications' => false,
                        'security_alerts' => true,
                    ],
                ]
            );

            $user->setProfileKeyValue('about', $bio);
            $user->setProfileKeyValue('location', fake()->randomElement(['San Francisco, CA', 'Brooklyn, NY', 'Austin, TX', 'London, UK', 'Lisbon, PT', 'Toronto, CA']), 'TextInput');

            $user->assignRole('agent');

            $agents[$key] = $user;
        }

        return $agents;
    }

    protected function setupBilling(User $alex): void
    {
        $alex->syncRoles(['admin', 'agent', 'pro']);

        $pro = Plan::where('name', 'Pro')->first();

        if ($pro) {
            Subscription::updateOrCreate(
                ['billable_type' => 'user', 'billable_id' => $alex->id],
                [
                    'plan_id' => $pro->id,
                    'status' => 'active',
                    'cycle' => 'month',
                    'seats' => 1,
                    'vendor_slug' => 'demo',
                ]
            );
        }

        $alex->clearUserCache();
    }

    /**
     * @return Collection<string, Tag>
     */
    protected function createTags(): Collection
    {
        return collect([
            'Billing' => 'amber',
            'Bug' => 'rose',
            'How-to' => 'sky',
            'Feature request' => 'indigo',
            'Account' => 'violet',
            'API' => 'emerald',
        ])->map(fn ($color, $name) => Tag::firstOrCreate(['name' => $name], ['color' => $color]));
    }

    /**
     * @param  array<string, User>  $agents
     */
    protected function createSavedReplies(array $agents): void
    {
        $replies = [
            ['Ask for more details', "Hi {customer},\n\nThanks for reaching out! To help us dig into this, could you share:\n\n1. The email address on the account\n2. Roughly when you first noticed the issue\n3. A screenshot of what you're seeing, if possible\n\nOnce we have that, we'll get right on it.\n\nBest,\n{agent}"],
            ['Refund processed', "Hi {customer},\n\nDone! I've processed the refund — you should see it back on your original payment method within 5–10 business days, depending on your bank.\n\nSorry for the hassle, and thanks for your patience.\n\nBest,\n{agent}"],
            ['Escalated to engineering', "Hi {customer},\n\nThanks for the detailed report — this one needs eyes from our engineering team, so I've escalated it with everything you sent over.\n\nI'll keep this ticket open and follow up as soon as we have an update (usually within one business day).\n\nBest,\n{agent}"],
            ['Password reset steps', "Hi {customer},\n\nNo problem — here's the quickest way to get back in:\n\n1. Head to the sign-in page and choose “Forgot password”\n2. Enter the email on your account\n3. Check your inbox (and spam folder) for the reset link — it's valid for 60 minutes\n\nIf the email never arrives, just reply here and I'll trigger one manually.\n\nBest,\n{agent}"],
            ['Welcome & onboarding', "Hi {customer},\n\nWelcome aboard! 🎉 A few links to get you moving:\n\n• Quick-start guide: takes about 5 minutes\n• Importing your existing data\n• Inviting your team\n\nIf anything feels confusing in the first week, reply here — a real human reads every message.\n\nBest,\n{agent}"],
        ];

        foreach ($replies as [$name, $body]) {
            SavedReply::firstOrCreate(['name' => $name, 'user_id' => null], ['body' => $body]);
        }

        SavedReply::firstOrCreate(
            ['name' => 'My signature sign-off', 'user_id' => $agents['alex']->id],
            ['body' => "Anything else I can help with? Don't hesitate to reach back out.\n\nAlex Rivera\nHead of Support, Nimbus"]
        );
    }

    /**
     * @return array<string, Customer>
     */
    protected function createCustomers(): array
    {
        $people = [
            'priya' => ['Priya Sharma', 'priya@lumenanalytics.io', 'Lumen Analytics', 'Data Lead', 'Pro', 'Bengaluru, IN', 'Asia/Kolkata'],
            'marcus' => ['Marcus Webb', 'marcus@coastalsupply.co', 'Coastal Supply Co.', 'Operations Manager', 'Business', 'Portland, OR', 'America/Los_Angeles'],
            'elena' => ['Elena Vasquez', 'elena@brightpath.app', 'Brightpath', 'Co-founder', 'Pro', 'Barcelona, ES', 'Europe/Madrid'],
            'tom' => ['Tom Nakamura', 'tom@fielddaystudio.com', 'Field Day Studio', 'Creative Director', 'Free', 'Tokyo, JP', 'Asia/Tokyo'],
            'sofia' => ['Sofia Lindgren', 'sofia@nordicgrid.se', 'Nordic Grid', 'IT Administrator', 'Business', 'Stockholm, SE', 'Europe/Stockholm'],
            'james' => ['James Okonkwo', 'james@apexlogistics.ng', 'Apex Logistics', 'CTO', 'Business', 'Lagos, NG', 'Africa/Lagos'],
            'hannah' => ['Hannah Doyle', 'hannah@thimblecraft.ie', 'Thimble & Craft', 'Owner', 'Free', 'Dublin, IE', 'Europe/Dublin'],
            'lucas' => ['Lucas Ferreira', 'lucas@veloztech.br', 'Veloz Tech', 'Engineering Manager', 'Pro', 'São Paulo, BR', 'America/Sao_Paulo'],
            'amara' => ['Amara Diallo', 'amara@solstice.health', 'Solstice Health', 'Product Manager', 'Pro', 'Paris, FR', 'Europe/Paris'],
            'noah' => ['Noah Kim', 'noah@stackline.dev', 'Stackline', 'Indie Developer', 'Free', 'Seoul, KR', 'Asia/Seoul'],
        ];

        $customers = [];

        foreach ($people as $key => [$name, $email, $company, $title, $plan, $location, $timezone]) {
            $customers[$key] = Customer::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'company' => $company,
                    'title' => $title,
                    'plan' => $plan,
                    'location' => $location,
                    'timezone' => $timezone,
                    'avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed='.urlencode($name).'&backgroundColor=ffd5dc,ffdfbf,c9f7d4,b6e3f4,c0aede&radius=50',
                ]
            );
        }

        return $customers;
    }

    /**
     * Conversation lines are [author, body] where author is 'c' (customer),
     * an agent key, or 'note:<agent key>' for an internal note.
     *
     * @param  array<string, User>  $agents
     * @param  array<string, Customer>  $customers
     * @param  Collection<string, Tag>  $tags
     */
    protected function createTickets(array $agents, array $customers, Collection $tags): void
    {
        $blueprints = [
            [
                'Sync stuck at "uploading" for 2 days', 'priya', 'open', 'urgent', 'email', ['Bug'], 'maya', 0.4,
                [
                    ['c', "Hi — our shared workspace has been stuck syncing the same 14 files since Tuesday. The desktop app just says “uploading…” forever. We're a data team and these are the dashboards we present on Friday, so this is getting urgent.\n\nMacOS 15.2, app version 3.8.1. Happy to send logs."],
                    ['maya', "Hi Priya,\n\nSorry about this — that's not the Friday prep you needed. Two quick things while I pull your account's sync history:\n\n1. Could you send the logs? Settings → Help → Export logs.\n2. Are the 14 files all in the same folder, and is any of them larger than 2 GB?\n\nWe shipped 3.8.1 last week and are watching a handful of similar reports closely."],
                    ['c', 'Logs attached (well, sent via the upload link). Yes — all in /Q3-Dashboards, and one of them is a 3.1 GB Tableau extract. The rest are small.'],
                    ['note:maya', 'Confirmed: large-file chunked upload regression in 3.8.1, engineering tracking as NIM-2241. ETA on patch is tomorrow. Keeping customer posted daily.'],
                    ['maya', "Thanks Priya — found it. The 3.1 GB extract is hitting a bug in our new chunked uploader, and it's blocking the queue behind it (that part is on us, queues shouldn't stall like that).\n\nEngineering has a fix in review now. In the meantime: if you move the big extract out of the synced folder, the other 13 files will go through immediately. I'll reply here the moment the patched build (3.8.2) ships — expected tomorrow."],
                ],
            ],
            [
                'Charged twice for the annual plan', 'marcus', 'open', 'high', 'email', ['Billing'], 'sam', 1.2,
                [
                    ['c', "Hello,\n\nWe just renewed our Business annual plan and our card statement shows two charges of $948 on the same day (Dec 2). Invoice #INV-8841 only covers one of them. Please refund the duplicate.\n\nMarcus Webb\nCoastal Supply Co."],
                    ['sam', "Hi Marcus,\n\nYou're right, and I'm sorry — I can see both charges on my end. The second one came from a retried payment that shouldn't have fired after the first succeeded.\n\nI've started the refund for the duplicate $948 just now. It typically lands back on your card in 5–10 business days. I'm also flagging this to our billing engineers so the retry logic gets fixed.\n\nI'll keep this ticket open until you confirm the refund arrived."],
                    ['c', 'Appreciate the fast response. Will confirm once it shows up.'],
                ],
            ],
            [
                'How do I give my accountant read-only access?', 'hannah', 'resolved', 'normal', 'web', ['How-to', 'Account'], 'riley', 3, 5,
                [
                    ['c', "Hi! Small business owner here. My accountant needs to see our invoices and storage usage but I really don't want her to be able to delete anything (she won't, but still). Is there a viewer role or something?"],
                    ['riley', "Hi Hannah,\n\nThere is exactly that! Here's the 60-second version:\n\n1. Go to Settings → Team → Invite\n2. Enter her email and pick the “Viewer” role\n3. Under permissions, tick “Billing & invoices” — viewers don't get it by default\n\nShe'll be able to see everything you described but can't edit, delete, or invite anyone. There's a full breakdown of roles in our help center under “Roles & permissions.”\n\nAnything else you run into, just shout."],
                    ['c', 'Perfect, worked first try. Thanks Riley!'],
                ],
            ],
            [
                'Webhook deliveries failing with 401 since this morning', 'lucas', 'open', 'high', 'web', ['API', 'Bug'], 'dev', 0.25,
                [
                    ['c', "Our webhook endpoint started rejecting Nimbus deliveries at ~06:40 UTC. Signature verification fails on every event. Nothing changed on our side — same secret, same code that's run for 8 months. Did you rotate signing keys?"],
                    ['dev', "Hi Lucas,\n\nGood instinct — we didn't rotate keys, but we did deploy a webhook pipeline change last night, and your timing lines up suspiciously well. Pulling delivery logs for your endpoint now.\n\nCan you confirm which signature scheme your code expects — the `X-Nimbus-Signature` v1 (HMAC-SHA256 of the raw body) or the older v0 header?"],
                    ['c', 'v1, HMAC-SHA256 over raw body, hex-encoded.'],
                    ['note:dev', "Deploy added a charset suffix to Content-Type which some frameworks use to re-serialize the body before verification → mismatch. Rolling back the header change. Affects ~30 endpoints, Lucas's included."],
                    ['dev', "Found it. Last night's deploy started sending `Content-Type: application/json; charset=utf-8`, and some HTTP frameworks re-serialize the body when a charset is present — which breaks raw-body signature checks.\n\nWe've rolled the header back as of 14:20 UTC and re-queued your failed deliveries from the past 8 hours. You should see them arriving now, oldest first. Can you confirm verification is passing again?"],
                ],
            ],
            [
                'Feature request: dark mode for the customer portal', 'noah', 'pending', 'low', 'chat', ['Feature request'], 'sam', 6,
                [
                    ['c', 'hey! love nimbus. one thing — the end-customer portal is blinding white. any chance of dark mode? my users keep asking'],
                    ['sam', "Hey Noah!\n\nYou're not alone — dark mode for the portal is one of our most-requested items and it's on the roadmap for next quarter. I've added your vote (votes genuinely move priorities here).\n\nI'll mark this as pending and ping you the moment it ships. 🌙"],
                ],
            ],
            [
                'SSO login loop after enabling SAML', 'sofia', 'open', 'urgent', 'email', ['Bug', 'Account'], 'dev', 0.1,
                [
                    ['c', "We enabled SAML SSO with Entra ID this morning following your guide. Now every user gets bounced between Nimbus and Microsoft in an endless redirect loop. 240 employees can't log in. We need this fixed or rolled back ASAP.\n\nSofia Lindgren\nIT Administrator, Nordic Grid"],
                    ['sam', "Hi Sofia,\n\nUnderstood — 240 people locked out is an emergency on our end too. I'm escalating this straight to our identity engineer and marking it urgent. First thing he'll need: does your Entra ID SAML config have the “Reply URL” set to https://app.nimbus.io/sso/callback exactly (no trailing slash)?\n\nYou'll hear from us within the hour."],
                    ['note:sam', 'Escalated to Dev. Entra tenant metadata shows clock skew on assertions (issued 4 min in the future) → our validator rejects → loop. Workaround: widen skew tolerance for their tenant.'],
                    ['dev', "Hi Sofia — Dev here, I handle identity at Nimbus.\n\nFound the cause: the SAML assertions from your Entra tenant are time-stamped about 4 minutes in the future, so our validator rejects them and restarts the login — hence the loop. It's a clock-skew issue on the identity provider side, but we should handle it more gracefully.\n\nTwo fixes, both done:\n1. I've widened the skew tolerance for your tenant — logins work right now.\n2. Long term, ask your Entra admin to check the token lifetime/clock settings.\n\nAll 240 accounts can sign in again. Keeping this open until you confirm."],
                ],
            ],
            [
                'Can we export all our files before canceling?', 'tom', 'snoozed', 'normal', 'email', ['Account'], 'riley', 4,
                [
                    ['c', "Hi, we're consolidating tools at the end of the month and will likely cancel our Nimbus account. What's the best way to export everything first? We have about 200 GB across 3 workspaces."],
                    ['riley', "Hi Tom,\n\nSorry to see you go — and of course, your data is yours. The cleanest path for 200 GB:\n\n1. Settings → Workspace → Export → “Full archive” (one per workspace)\n2. We'll email you a download link within a few hours — links stay valid for 7 days\n3. Archives preserve your folder structure and file versions from the last 90 days\n\nNo rush on your end; exports work right up until the moment you cancel. I'll check back in before the end of the month in case anything snags.\n\nAnd if the consolidation doesn't pan out, we'd love to have you back."],
                    ['c', "Great, kicked off the first export. I'll write back if anything fails — you can park this until the 28th."],
                ],
            ],
            [
                'Mobile app crashes when opening shared folders', 'amara', 'open', 'high', 'chat', ['Bug'], 'maya', 0.8,
                [
                    ['c', 'The iOS app (4.2.0) crashes instantly when I tap any *shared* folder. My own folders open fine. iPhone 15 Pro, iOS 18.2. Re-installed twice already.'],
                    ['maya', 'Hi Amara — thanks for the precise repro, that narrows it a lot. Shared-folder metadata went through a migration this week, and 4.2.0 has a parsing bug for folders shared *before* 2023. Does that match the folders crashing for you — older shares?'],
                    ['c', 'Yes actually — both are from 2022. Newer shares open fine. Good catch.'],
                    ['maya', "That's the one. Fix is already merged and ships in 4.2.1, which is in App Store review now — typically clears in 24–48h. Until then the web app handles those folders fine.\n\nI'll message you here when 4.2.1 is live. Sorry for the crashes — this one slipped past our migration tests."],
                ],
            ],
            [
                'API rate limits — what are they exactly?', 'james', 'resolved', 'normal', 'web', ['API', 'How-to'], 'dev', 7, 4,
                [
                    ['c', "We're building an internal tool against your REST API and the docs mention rate limits but not the actual numbers. What are the limits per plan? And do you support bulk endpoints to reduce call volume?"],
                    ['dev', "Hi James,\n\nFair point — we're overdue on documenting the numbers. Current limits:\n\n• Free: 60 requests/min\n• Pro: 600 requests/min\n• Business: 3,000 requests/min, burst to 5,000\n\nEvery response includes `X-RateLimit-Remaining` and `X-RateLimit-Reset` headers. And yes — for bulk work use `POST /v2/files/batch` (up to 100 operations per call); it counts as one request against the limit.\n\nI've also passed your note to whoever owns the docs page. Which, awkwardly, is me. It'll be updated this week."],
                    ['c', 'Ha! Appreciated the honesty. Batch endpoint is exactly what we needed. Cheers.'],
                ],
            ],
            [
                'Onboarding 30 new seats next week — best practices?', 'elena', 'pending', 'normal', 'email', ['Account', 'How-to'], 'riley', 2,
                [
                    ['c', 'Hola! We just closed a funding round and the team is doubling — 30 new people start Monday. Any advice for onboarding them into Nimbus without chaos? Folder structure tips, permission gotchas, that kind of thing.'],
                    ['riley', "Hi Elena — congrats on the round! 🎉\n\nA few things that save teams pain at your size:\n\n1. **Invite via groups, not individuals.** Create “Engineering”, “Design”, etc. first, set folder permissions on the groups, then drop people in.\n2. **Default to “can edit” not “owner”** on shared spaces — fewer accidental deletions.\n3. **Turn on the 30-day trash retention** (Settings → Workspace → Data) before Monday, not after someone needs it.\n4. We have a CSV bulk-invite — happy to walk you through it.\n\nWant me to set up a 30-minute onboarding call this week? It's free on your plan, and honestly it's the highest-leverage half hour of the whole process."],
                    ['c', "This is gold. Yes to the call — Thursday afternoon CET works best. I'll mark this pending until then."],
                ],
            ],
            [
                'Invoice needs our VAT number on it', 'sofia', 'resolved', 'low', 'email', ['Billing'], 'sam', 12, 5,
                [
                    ['c', 'Finance is asking that our VAT number (SE556677889901) appears on future invoices, and ideally on November\'s too. Possible?'],
                    ['sam', "Hi Sofia,\n\nDone and done:\n\n1. Added the VAT number to your billing profile — all future invoices will carry it automatically.\n2. Regenerated November's invoice with it included; the corrected PDF is in Settings → Billing → Invoices.\n\nTell Finance we said hi."],
                    ['c', 'Confirmed on both. Smooth as always — thanks Sam.'],
                ],
            ],
            [
                'Deleted a folder by accident — can I get it back??', 'hannah', 'resolved', 'urgent', 'chat', ['How-to', 'Account'], 'sam', 9, 5,
                [
                    ['c', "PLEASE tell me there's an undo. I just deleted our entire “Client Orders 2025” folder instead of one file inside it. Heart is in my stomach."],
                    ['sam', "Deep breath — you're fine, Hannah. 🙂 Deleted folders sit in your workspace trash for 30 days.\n\nGo to Home → Trash (left sidebar) → find “Client Orders 2025” → Restore. It comes back exactly where it was, sharing settings included.\n\nGive it a try and tell me when your heart has returned to its usual location."],
                    ['c', "IT'S BACK. You are my favorite person this week. Thank you!!"],
                    ['sam', "Happy to hear it! For what it's worth, you can also require a confirmation step for folder deletions under Settings → Preferences → “Confirm before deleting folders.” Might spare the next heart attack. Closing this one out — come back any time."],
                ],
            ],
            [
                'Search returns no results for files I can open directly', 'priya', 'pending', 'normal', 'web', ['Bug'], 'maya', 5,
                [
                    ['c', 'Odd one: searching for “quarterly_summary” returns nothing, but the file definitely exists — I can open it from the folder directly. Same for a few other files added last month. Re-indexing issue?'],
                    ['maya', "Hi Priya — yes, that smells like an indexing gap. Your workspace's search index has a hole roughly matching mid-November uploads.\n\nI've queued a full re-index of your workspace (about 120k files, so it'll take ~6 hours). I'll mark this pending and confirm here once it completes — would you mind re-testing the same searches then?"],
                ],
            ],
            [
                'Does Nimbus have a Linux desktop client?', 'noah', 'closed', 'low', 'web', ['How-to'], 'sam', 20, null,
                [
                    ['c', 'Title says it all — Ubuntu user here. Web app works but native sync would be amazing.'],
                    ['sam', "Hi Noah,\n\nNot yet, honest answer. The team has a Linux client on the long-term roadmap, but there's no date I can responsibly promise.\n\nWhat most Linux users do today: our CLI (`nimbus-cli sync`) covers headless folder sync and works great on Ubuntu — most of the desktop app's value without the GUI. Docs are in the help center under “CLI”.\n\nI'll close this since there's no action pending, but your +1 is recorded on the Linux client request."],
                ],
            ],
            [
                'Storage usage doubled overnight — why?', 'james', 'open', 'normal', 'email', ['Billing', 'Account'], null, 0.05,
                [
                    ['c', "Our dashboard says we're using 4.1 TB as of this morning. Yesterday it was 2.2 TB. Nobody uploaded 2 TB overnight. Is version history counting against us now? Something feels off and I'd like an explanation before our next invoice."],
                ],
            ],
            [
                'CSV import drops rows with accented characters', 'elena', 'open', 'high', 'email', ['Bug'], null, 0.15,
                [
                    ['c', 'Importing our contact CSV (UTF-8, 4,800 rows) silently drops every row containing accented characters — so roughly half our Spanish customer names are missing after import. No error shown. File validates fine elsewhere. Can someone look? Sample file available on request.'],
                ],
            ],
            [
                'Two-factor codes not arriving by SMS', 'marcus', 'open', 'high', 'phone', ['Account', 'Bug'], null, 0.3,
                [
                    ['c', "Called earlier but writing too: my SMS 2FA codes stopped arriving on Friday. Tried three phones on two carriers. I'm locked out of the admin account unless I use backup codes, and I'm down to two of those. Need this sorted before I run out."],
                ],
            ],
            [
                'Praise: your status page saved our standup', 'amara', 'closed', 'low', 'chat', [], 'riley', 15, 5,
                [
                    ['c', "Not a problem, just a note — during yesterday's blip your status page updated within 2 minutes and the incident timeline was genuinely useful. Our whole team noticed. Whoever owns that process: bravo."],
                    ['riley', "Amara, you have no idea how rare and appreciated this kind of ticket is. 💚 I've passed it to our infra team (they're insufferably pleased now). Closing this, but the door's always open — even for compliments."],
                ],
            ],
            [
                'Slow uploads from Brazil region', 'lucas', 'snoozed', 'normal', 'web', ['Bug'], 'dev', 8,
                [
                    ['c', "Uploads from our São Paulo office average ~1.2 MB/s while our US office gets 40+ MB/s on the same files. Feels like we're not hitting a local edge node. Traceroute attached."],
                    ['dev', "Hi Lucas — you're exactly right. Your traffic is currently routing to our US-East ingestion point; our GRU edge node is in deployment and lands in about two weeks.\n\nI'll snooze this ticket until the node is live, then ask you to re-test. Your traceroute is already attached to the infra ticket. Thanks for the legwork — made this a five-minute diagnosis."],
                ],
            ],
            [
                'Update the billing email on our account', 'tom', 'open', 'normal', 'email', ['Account', 'Billing'], 'alex', 0.6,
                [
                    ['c', "Hi — our finance contact changed and invoices should now go to accounts@fielddaystudio.com instead of my address. Can you switch that over? I'm still the account owner."],
                    ['alex', "Hi Tom,\n\nEasy one — but because it changes where billing documents go, I need a quick confirmation: reply with a 👍 (or just the word “confirm”) from this email address and I'll flip it immediately.\n\nOnce done, future invoices go to accounts@, and you'll keep getting product and security notices as the owner."],
                ],
            ],
            [
                'API docs link returns a 404', 'amara', 'open', 'low', 'web', ['Bug'], 'alex', 1.5,
                [
                    ['c', "Small one: the “Webhooks” link in your developer docs sidebar 404s. The page exists if I search for it directly, so it's just the nav link."],
                    ['alex', "Good catch, Amara — confirmed on my end. The docs nav config has a stale path from last week's restructure. I've filed it with the docs owner and it should be fixed in today's deploy; I'll confirm here once it's live."],
                ],
            ],
            [
                'Do you offer non-profit discounts?', 'hannah', 'pending', 'normal', 'chat', ['Billing'], 'alex', 2.2,
                [
                    ['c', "Hi! We're a registered charity in Ireland — do you have non-profit pricing? The Pro plan is exactly what we need but the budget meeting will go better with a discount slide. 😄"],
                    ['alex', "Hi Hannah,\n\nWe do — registered non-profits get 40% off any paid plan, forever.\n\nSend over your charity registration number (CHY or RCN) and I'll apply it to your account the same day. Looking forward to making that budget meeting easy."],
                ],
            ],
            [
                'Need SOC 2 report for vendor review', 'sofia', 'resolved', 'normal', 'email', ['Account'], 'alex', 10, 4,
                [
                    ['c', 'Our security team is running annual vendor reviews and needs your current SOC 2 Type II report plus a summary of your data residency options for EU customers.'],
                    ['alex', "Hi Sofia,\n\nBoth ready for you:\n\n1. **SOC 2 Type II** — I've shared the current report (Oct 2025) through our trust portal; link lands in your inbox separately (NDA click-through, then instant download).\n2. **EU data residency** — Business plans can pin all file storage and processing to our Frankfurt region: Settings → Workspace → Data region. Metadata stays in the EU as well; full details are in the residency whitepaper included in the trust portal.\n\nIf your security team has follow-up questionnaires, send them my way — we turn those around in 2–3 business days."],
                    ['c', 'Report received and residency doc answers everything. Review passed — see you at renewal.'],
                ],
            ],
        ];

        $number = Ticket::nextNumber();

        foreach ($blueprints as $blueprint) {
            [$subject, $customerKey, $status, $priority, $channel, $tagNames, $assigneeKey, $daysAgo] = $blueprint;
            $csat = $blueprint[8] ?? null;
            $conversation = is_array($csat) ? $csat : ($blueprint[9] ?? []);
            $csat = is_array($csat) ? null : $csat;

            $customer = $customers[$customerKey];
            $assignee = $assigneeKey ? $agents[$assigneeKey] : null;
            $createdAt = Carbon::now()->subMinutes((int) ($daysAgo * 1440) + fake()->numberBetween(0, 180));

            $ticket = Ticket::create([
                'number' => $number++,
                'subject' => $subject,
                'customer_id' => $customer->id,
                'assignee_id' => $assignee?->id,
                'status' => $status,
                'priority' => $priority,
                'channel' => $channel,
                'snoozed_until' => $status === 'snoozed' ? Carbon::now()->addDays(fake()->numberBetween(3, 14)) : null,
                'csat_rating' => $csat,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($tagNames as $tagName) {
                if ($tag = $tags->get($tagName)) {
                    $ticket->tags()->attach($tag->id);
                }
            }

            $ticket->recordEvent('created', null, []);
            $ticket->events()->latest('id')->first()?->forceFill(['created_at' => $createdAt, 'updated_at' => $createdAt])->save();

            if ($assignee) {
                $event = $ticket->recordEvent('assigned', $agents['alex']->id, ['to_name' => $assignee->name]);
                $event->forceFill(['created_at' => $createdAt->copy()->addMinutes(5), 'updated_at' => $createdAt->copy()->addMinutes(5)])->save();
            }

            $messageAt = $createdAt;
            $firstResponseAt = null;

            foreach ($conversation as $index => [$author, $body]) {
                $messageAt = $index === 0 ? $createdAt : $messageAt->copy()->addMinutes(fake()->numberBetween(25, 600));

                $isNote = str_starts_with($author, 'note:');
                $agentKey = $isNote ? substr($author, 5) : $author;

                $message = $ticket->messages()->create([
                    'user_id' => $author === 'c' ? null : $agents[$agentKey]->id,
                    'customer_id' => $author === 'c' ? $customer->id : null,
                    'type' => $isNote ? MessageType::Note : MessageType::Reply,
                    'body' => $body,
                ]);

                $message->forceFill(['created_at' => $messageAt, 'updated_at' => $messageAt])->save();

                if (! $firstResponseAt && $author !== 'c' && ! $isNote) {
                    $firstResponseAt = $messageAt;
                }
            }

            $resolvedAt = in_array($status, ['resolved', 'closed'], true)
                ? $messageAt->copy()->addMinutes(fake()->numberBetween(10, 120))
                : null;

            $ticket->forceFill([
                'first_response_at' => $firstResponseAt,
                'resolved_at' => $resolvedAt,
                'last_activity_at' => $messageAt,
                'updated_at' => $resolvedAt ?? $messageAt,
            ])->save();

            if ($resolvedAt) {
                $event = $ticket->recordEvent('status_changed', $ticket->assignee_id, ['to' => TicketStatus::from($status)->label()]);
                $event->forceFill(['created_at' => $resolvedAt, 'updated_at' => $resolvedAt])->save();

                if ($csat) {
                    $event = $ticket->recordEvent('rated', null, ['rating' => $csat]);
                    $event->forceFill(['created_at' => $resolvedAt->copy()->addHours(2), 'updated_at' => $resolvedAt->copy()->addHours(2)])->save();
                }
            }
        }
    }

    protected function seedNotifications(User $alex): void
    {
        $others = User::where('id', '!=', $alex->id)->get();

        $assigned = Ticket::active()->whereNotNull('assignee_id')->with('customer')->latest('last_activity_at')->take(2)->get();
        foreach ($assigned as $ticket) {
            $actor = $others->random();
            $alex->notify(new TicketActivityNotification(
                'ticket_assigned',
                $ticket,
                Str::of($actor->name)->explode(' ')->first().' assigned you '.$ticket->identifier().' · '.$ticket->subject,
            ));
        }

        $waiting = Ticket::where('status', TicketStatus::Open->value)->whereNull('assignee_id')->with('customer')->first();
        if ($waiting) {
            $alex->notify(new TicketActivityNotification(
                'new_reply',
                $waiting,
                $waiting->customer->name.' opened '.$waiting->identifier().' · '.$waiting->subject,
            ));
        }

        $resolved = Ticket::where('status', TicketStatus::Resolved->value)->with('customer')->first();
        if ($resolved) {
            $actor = $others->random();
            $alex->notify(new TicketActivityNotification(
                'ticket_resolved',
                $resolved,
                Str::of($actor->name)->explode(' ')->first().' resolved '.$resolved->identifier().' · '.$resolved->subject,
            ));
        }

        // Mark a couple as read so there's a believable read/unread mix.
        $alex->unreadNotifications()->latest()->skip(2)->take(5)->get()->each->markAsRead();
    }

    /**
     * @param  array<string, User>  $agents
     */
    protected function seedKnowledgeBase(array $agents): void
    {
        $categories = [
            ['Getting started', 'rocket-launch', 'Set up your account and learn the basics.', [
                ['Quick-start guide', 'Everything you need to go from zero to synced in five minutes.', "<p>Welcome to Nimbus! This guide walks you through the essentials.</p><h2>1. Create your workspace</h2><p>Your workspace is home for your team's files. Pick a name, invite at least one teammate, and you're off.</p><h2>2. Install the desktop app</h2><p>Download the app for macOS or Windows, sign in, and choose which folders to sync. Changes flow both ways automatically.</p><h2>3. Share your first folder</h2><p>Right-click any folder → Share. You can share with teammates, groups, or external guests with view-only access.</p><p>That's it — you're syncing. When you're ready, explore version history and selective sync in the articles below.</p>"],
                ['Inviting your team', 'Adding teammates, choosing roles, and bulk-importing via CSV.', '<p>Nimbus is better with your team in it. There are three ways to invite people:</p><h2>One at a time</h2><p>Settings → Team → Invite. Enter an email, pick a role, done.</p><h2>By group</h2><p>Create groups like “Engineering” or “Design” first, set folder permissions on the group, then add people. New members inherit everything automatically.</p><h2>In bulk</h2><p>Upload a CSV with email and role columns — up to 500 invitations at once. Perfect for onboarding whole departments.</p>'],
                ['Understanding roles & permissions', 'Owner, admin, member, and viewer — what each role can and cannot do.', '<p>Every person in your workspace has exactly one role:</p><h2>Owner</h2><p>Full control, including billing and workspace deletion. Every workspace has exactly one.</p><h2>Admin</h2><p>Everything owners can do except deleting the workspace or changing the owner.</p><h2>Member</h2><p>Create, edit, and share files and folders they have access to.</p><h2>Viewer</h2><p>Read-only access. Viewers can preview and download but never modify. Optionally grant them billing visibility for accountants and finance teams.</p>'],
            ]],
            ['Account & billing', 'credit-card', 'Plans, invoices, payments, and account management.', [
                ['Changing your plan', 'Upgrade, downgrade, and what happens to your data when you do.', "<p>You can change plans at any time from Settings → Billing.</p><h2>Upgrading</h2><p>Upgrades take effect immediately. We prorate the difference — you only pay for the remainder of the current cycle.</p><h2>Downgrading</h2><p>Downgrades apply at the end of your current billing period, so you keep what you paid for. If you're over the new plan's limits, we'll warn you first — nothing is ever deleted automatically.</p>"],
                ['Getting invoices with your company details', 'Adding VAT numbers, billing addresses, and custom fields to invoices.', '<p>Finance teams have requirements; we have settings for them.</p><p>Under Settings → Billing → Invoice details you can add:</p><ul><li>Company legal name and address</li><li>VAT / tax ID numbers</li><li>A purchase order reference</li></ul><p>Changes apply to all future invoices. Need a past invoice corrected? Contact support — we can regenerate any invoice from the past 12 months.</p>'],
                ['Refund policy', 'When refunds apply and how long they take.', "<p>We keep this simple:</p><ul><li><strong>Duplicate or erroneous charges</strong> — refunded in full, no questions.</li><li><strong>Annual plans</strong> — full refund within 30 days of purchase or renewal.</li><li><strong>Monthly plans</strong> — we don't prorate partial months, but we'll never charge you again after you cancel.</li></ul><p>Refunds land on the original payment method within 5–10 business days.</p>"],
            ]],
            ['Troubleshooting', 'wrench', 'Fixes for sync, login, and performance issues.', [
                ['Files stuck syncing', 'The checklist that resolves 90% of stuck uploads.', "<p>If a file shows “uploading…” for more than a few minutes, run through this list:</p><ol><li><strong>Check file size.</strong> Files over 10 GB need the desktop app — the web uploader caps at 10 GB.</li><li><strong>Check the filename.</strong> Names containing <code>/ \\ : * ?</code> fail on some platforms.</li><li><strong>Pause and resume syncing</strong> from the menu bar icon. This clears most queue stalls.</li><li><strong>Check the status page</strong> — if we're having a bad day, it'll say so honestly.</li></ol><p>Still stuck? Export your logs (Settings → Help → Export logs) and open a ticket — logs cut our diagnosis time in half.</p>"],
                ['Recovering deleted files', 'The trash keeps everything for 30 days — here is how to use it.', '<p>Deleted something important? You have 30 days to undo it.</p><h2>Restore from trash</h2><p>Open Home → Trash in the sidebar. Find your file or folder, click Restore, and it returns to exactly where it lived — sharing settings intact.</p><h2>Restore a previous version</h2><p>Overwrote a file instead? Right-click it → Version history. Every plan keeps 30 days of versions; Business plans keep 180.</p><p><em>Tip: admins can enable “Confirm before deleting folders” under workspace preferences.</em></p>'],
                ['Two-factor authentication problems', 'Lost devices, missing codes, and backup code best practices.', "<p>Locked out of 2FA? Options in order of speed:</p><ol><li><strong>Backup codes</strong> — each one works exactly once. Store them somewhere that isn't the account they unlock.</li><li><strong>Another enrolled device</strong> — any signed-in session can approve a new device from Settings → Security.</li><li><strong>Account recovery</strong> — if both are gone, contact support from your account email. Identity verification takes about one business day; we'd rather be slow than wrong here.</li></ol>"],
            ]],
            ['API & integrations', 'cube', 'Build on the Nimbus API and connect your tools.', [
                ['API authentication', 'Creating API keys, scopes, and rotating credentials safely.', "<p>All API requests authenticate with a bearer token:</p><pre><code>Authorization: Bearer nim_live_...</code></pre><h2>Creating keys</h2><p>Settings → Developers → API keys. Scope each key to the minimum it needs — read-only keys can't delete anything, ever.</p><h2>Rotation</h2><p>Create the new key, deploy it, then revoke the old one. Keys can overlap indefinitely, so there's no forced downtime.</p>"],
                ['Webhooks', 'Receiving events, verifying signatures, and handling retries.', "<p>Webhooks push events to your endpoint as they happen.</p><h2>Verifying signatures</h2><p>Every delivery includes an <code>X-Nimbus-Signature</code> header — an HMAC-SHA256 of the raw request body using your endpoint's secret. Always verify against the <em>raw</em> body, before any framework parses it.</p><h2>Retries</h2><p>If your endpoint returns anything other than 2xx, we retry with exponential backoff for up to 24 hours. Deliveries are at-least-once: design your handlers to be idempotent.</p>"],
                ['Rate limits', 'Limits per plan and how to work within them.', '<p>Current limits per workspace:</p><ul><li>Free — 60 requests/minute</li><li>Pro — 600 requests/minute</li><li>Business — 3,000 requests/minute (burst 5,000)</li></ul><p>Watch the <code>X-RateLimit-Remaining</code> and <code>X-RateLimit-Reset</code> response headers. For bulk operations use <code>POST /v2/files/batch</code> — 100 operations, one request.</p>'],
            ]],
            ['Security & privacy', 'shield-check', 'How we protect your data, and the controls you have.', [
                ['Data residency options', 'Choosing where your files live: US or EU regions.', '<p>Business workspaces can pin data to a region:</p><p>Settings → Workspace → Data region offers <strong>US (Virginia)</strong> and <strong>EU (Frankfurt)</strong>. The setting covers file storage, processing, and metadata. Moving regions later triggers a managed migration — typically overnight, with zero downtime.</p>'],
                ['Compliance reports', 'SOC 2, GDPR, and how to request documentation.', "<p>Our trust portal hosts the current SOC 2 Type II report, penetration test summaries, and our GDPR data-processing agreement.</p><p>Request access from Settings → Security → Trust portal, or ask support — there's a short NDA click-through, then everything downloads instantly. Security questionnaires get a 2–3 business day turnaround.</p>"],
            ]],
        ];

        foreach ($categories as $position => [$name, $icon, $description, $articles]) {
            $category = ArticleCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'icon' => $icon, 'description' => $description, 'position' => $position],
            );

            foreach ($articles as $articlePosition => [$title, $excerpt, $body]) {
                Article::updateOrCreate(
                    ['slug' => Str::slug($title)],
                    [
                        'article_category_id' => $category->id,
                        'author_id' => fake()->randomElement([$agents['alex']->id, $agents['maya']->id, $agents['riley']->id]),
                        'title' => $title,
                        'excerpt' => $excerpt,
                        'body' => $body,
                        'position' => $articlePosition,
                        'published_at' => now()->subDays(fake()->numberBetween(5, 90)),
                    ],
                );
            }
        }
    }

    protected function seedChangelog(): void
    {
        $entries = [
            ['SLA targets & breach alerts', 'Set first-response targets per priority and never miss one silently.', '<p>Every priority now carries a first-response SLA target. Tickets approaching breach are flagged in the inbox, and breached tickets get a persistent indicator until first reply.</p><p>Targets are sensible out of the box (urgent: 1h, high: 4h) and the whole team sees the same clock.</p>', 16],
            ['Saved replies with placeholders', 'Answer the common 80% in two clicks, personalized automatically.', '<p>Build a library of saved replies for the questions you answer every day. Placeholders like <strong>{customer}</strong> and <strong>{agent}</strong> fill themselves in when inserted, so canned never reads as canned.</p>', 12],
            ['Command palette', 'Jump to any ticket, customer, or page with ⌘K.', '<p>Press <strong>⌘K</strong> from anywhere to search tickets and customers or jump straight to any page. Fully keyboard-navigable, naturally.</p>', 9],
            ['Internal notes', 'Talk to your team privately, right inside the conversation.', '<p>Notes live in the ticket thread but are only visible to agents — amber-tinted so nobody ever mistakes one for a customer reply. Loop in engineering without leaving the conversation.</p>', 6],
            ['CSAT surveys', 'A one-tap rating after every resolved ticket.', '<p>When a ticket is resolved, the customer gets a single-question survey. Scores roll up to the dashboard and reports so you can see how the team is really doing — per agent, per week, per tag.</p>', 3],
        ];

        foreach ($entries as [$title, $description, $body, $daysAgo]) {
            $at = Carbon::now()->subDays($daysAgo);
            Changelog::create(compact('title', 'description', 'body'))
                ->forceFill(['created_at' => $at, 'updated_at' => $at])
                ->save();
        }
    }

    protected function seedBlog(User $author): void
    {
        $product = Category::firstOrCreate(['slug' => 'product'], ['name' => 'Product', 'order' => 1]);
        $support = Category::firstOrCreate(['slug' => 'support'], ['name' => 'Support craft', 'order' => 2]);

        $posts = [
            [
                'Introducing Deskly', $product,
                'The help desk that feels like a product your team chose — not one they were assigned.',
                "<p>Support tools have a reputation: heavy, beige, and built for the people buying them rather than the people living in them eight hours a day.</p><p>Deskly is our answer. A shared inbox that's actually fast. Conversations that read like conversations. SLA clocks that keep everyone honest, saved replies that don't sound canned, and a help center your customers can find answers in before they ever write to you.</p><h2>Opinionated where it counts</h2><p>One queue. Clear ownership. Internal notes beside the conversation instead of in a separate tool. We sweat the defaults so your team doesn't have to configure their way to a good day.</p>",
                18, true,
            ],
            [
                'The case for answering tickets in order', $support,
                'Cherry-picking easy tickets feels productive. It quietly wrecks your response times.',
                "<p>Every support team develops the same bad habit: skimming the queue for quick wins and leaving the hard tickets to age. The metrics look fine — until you check the 95th percentile.</p><p>The fix is structural, not motivational. Deskly's inbox sorts by waiting time within priority, makes the SLA clock visible on every row, and treats “oldest first” as the default path of least resistance. Make the right thing the easy thing and the tail takes care of itself.</p>",
                10, false,
            ],
            [
                'Internal notes are a superpower', $support,
                'The best support conversations have a second, invisible conversation running beside them.',
                "<p>Watch a great support team work and you'll notice the real collaboration happens in the margins: a quick “this looks like the bug from Tuesday” to engineering, a heads-up to the account manager, a draft answer sanity-checked before sending.</p><p>When that margin conversation lives in Slack, context dies in the scroll. Notes in Deskly sit directly in the ticket thread — amber-tinted, agent-only, and permanent. Six months later, the whole story is still in one place.</p>",
                4, false,
            ],
        ];

        foreach ($posts as [$title, $category, $excerpt, $body, $daysAgo, $featured]) {
            $at = Carbon::now()->subDays($daysAgo);
            Post::updateOrCreate(
                ['slug' => Str::slug($title)],
                [
                    'author_id' => $author->id,
                    'category_id' => $category->id,
                    'title' => $title,
                    'excerpt' => $excerpt,
                    'body' => $body,
                    'status' => 'PUBLISHED',
                    'featured' => $featured,
                    'meta_description' => $excerpt,
                    'created_at' => $at,
                    'updated_at' => $at,
                ]
            );
        }

        Category::clearCache();
    }
}
