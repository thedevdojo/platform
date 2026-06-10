<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Product;
use App\Models\Topic;
use App\Models\User;
use App\Models\Vote;
use Carbon\Carbon;
use Devdojo\Blog\Models\Category;
use Devdojo\Blog\Models\Post;
use Devdojo\Changelog\Models\Changelog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /** @var array<string, User> */
    protected array $users = [];

    /** @var Collection<int, User> */
    protected $voterPool;

    /** @var array<string, Topic> */
    protected array $topics = [];

    public function run(): void
    {
        File::ensureDirectoryExists(public_path('images/demo'));

        $this->seedUsers();
        $this->seedTopics();
        $this->seedProducts();
        $this->seedChangelog();
        $this->seedBlog();
        $this->seedNotifications();
    }

    protected function seedUsers(): void
    {
        $named = [
            // [name, email, username, title, about]
            ['Skyler Reed', 'demo@devdojo.test', 'skyler', 'Indie maker · 3 launches', 'Serial launcher. I ship something every quarter and write about what sticks.'],
            ['Avery Stone', 'admin@devdojo.test', 'avery', 'Community lead at Hunted', 'Keeping the front page honest. Say hi if your launch needs a boost.'],
            ['Mara Lindqvist', 'mara@example.test', 'mara', 'Design engineer', 'Pixels and TypeScript. Building calm software in Malmö.'],
            ['Theo Okafor', 'theo@example.test', 'theo', 'Founder, Glasspane', 'Recovering agency owner. Now making infrastructure boring (in a good way).'],
            ['June Castellanos', 'june@example.test', 'june', 'Product designer', 'I hunt tools that respect your attention.'],
            ['Felix Braun', 'felix@example.test', 'felix', 'Full-stack developer', 'Laravel by day, woodworking by weekend.'],
            ['Priya Raghavan', 'priya@example.test', 'priya', 'AI researcher turned founder', 'Making LLMs less mysterious, one eval at a time.'],
            ['Dario Mancini', 'dario@example.test', 'dario', 'Growth marketer', 'Launched 14 products. Upvoted thousands.'],
            ['Imani Walker', 'imani@example.test', 'imani', 'No-code educator', 'Teaching 40k students to build without engineers.'],
            ['Kenji Mori', 'kenji@example.test', 'kenji', 'Indie hacker', 'One-person SaaS studio. Coffee-powered.'],
            ['Lotte Visser', 'lotte@example.test', 'lotte', 'Frontend lead', 'CSS is my love language.'],
            ['Aisha Khan', 'aisha@example.test', 'aisha', 'Fintech PM', 'Money tools should feel like magic, not math homework.'],
            ['Oscar Nilsen', 'oscar@example.test', 'oscar', 'DevRel at heart', 'If your docs are good, I will tell everyone.'],
            ['Wren Hollis', 'wren@example.test', 'wren', 'Solo founder', 'Bootstrapping in public since 2021.'],
            ['Marcus Webb', 'marcus@example.test', 'marcus', 'Data engineer', 'I make dashboards people actually open.'],
            ['Elif Demir', 'elif@example.test', 'elif', 'UX researcher', 'I ask "why" professionally.'],
            ['Joaquin Vega', 'joaquin@example.test', 'joaquin', 'Mobile developer', 'Shipping delightful little apps.'],
            ['Hana Suzuki', 'hana@example.test', 'hana', 'Health-tech founder', 'Building tech that helps you log off.'],
            ['Greta Holm', 'greta@example.test', 'greta', 'Open-source maintainer', '500 merged PRs and counting.'],
            ['Ravi Patel', 'ravi@example.test', 'ravi', 'Bootstrapped founder', 'MRR screenshots are my weakness.'],
        ];

        foreach ($named as $i => [$name, $email, $username, $title, $about]) {
            $user = User::factory()->create([
                'name' => $name,
                'email' => $email,
                'username' => $username,
                'title' => $title,
                'avatar' => 'https://api.dicebear.com/9.x/notionists/svg?seed='.urlencode($name).'&backgroundColor=ffdfbf,ffd5dc,c9f7d4,b6e3f4,c0aede&radius=50',
                'social_links' => [
                    'website' => $i % 3 === 0 ? 'https://'.$username.'.dev' : null,
                    'github' => $i % 2 === 0 ? 'https://github.com/'.$username : null,
                    'twitter' => 'https://x.com/'.$username,
                ],
                'created_at' => now()->subDays(40 + $i * 7),
            ]);

            $user->setProfileKeyValue('about', $about);
            $user->syncRoles(['registered']);

            $this->users[$username] = $user;
        }

        $this->users['avery']->syncRoles(['admin']);
        $this->users['skyler']->syncRoles(['registered', 'pro']);

        // A wider pool of quieter community members so vote counts feel real.
        $extras = User::factory()->count(38)->create();

        $this->voterPool = collect($this->users)->values()->concat($extras);
    }

    protected function seedTopics(): void
    {
        $topics = [
            ['Artificial Intelligence', 'ai', 'Models, agents and the tools that tame them', 'sparkles'],
            ['Developer Tools', 'developer-tools', 'Everything that makes shipping software faster', 'command'],
            ['Design Tools', 'design-tools', 'For the pixel-obsessed and typography-curious', 'palette'],
            ['Productivity', 'productivity', 'Do more of what matters, less of everything else', 'zap'],
            ['SaaS', 'saas', 'Software businesses, subscriptions and all', 'cube'],
            ['Marketing', 'marketing', 'Get your thing in front of the right people', 'megaphone'],
            ['Analytics', 'analytics', 'Numbers that tell you what to do next', 'dashboard'],
            ['No-Code', 'no-code', 'Build real products without writing code', 'layers'],
            ['Open Source', 'open-source', 'Free as in freedom (and usually as in beer)', 'github'],
            ['Fintech', 'fintech', 'Money, moved and managed beautifully', 'credit-card'],
            ['Health & Wellness', 'health', 'Tech that takes care of the human running it', 'flame'],
            ['Education', 'education', 'Tools for learning anything, faster', 'book'],
        ];

        foreach ($topics as [$name, $slug, $tagline, $icon]) {
            $this->topics[$slug] = Topic::create(compact('name', 'slug', 'tagline', 'icon'));
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function productCatalog(): array
    {
        // [name, tagline, topics, pricing, hunter, votes, when, about]
        return [
            // ---- Today ----
            ['Loopdesk', 'Async standups your team will actually read', ['productivity', 'saas'], 'freemium', 'skyler', 46, 'today',
                'Loopdesk replaces the 9:15am standup with a two-minute async loop. Everyone answers three sharp prompts, Loopdesk weaves the answers into a single digest, and blockers get flagged before they become fires.'],
            ['Tracelight', 'Debug production AI agents with full replay', ['ai', 'developer-tools'], 'paid', 'priya', 38, 'today',
                'When an agent goes off the rails in production, Tracelight lets you scrub through every step it took — every tool call, every token, every branch — and replay the run with edits until you find the fix.'],
            ['Brevoir', 'Turn meeting recordings into crisp decision docs', ['ai', 'productivity'], 'freemium', 'june', 31, 'today',
                'Brevoir listens to your meetings and produces something better than a transcript: a one-page decision doc with owners, deadlines and the "why" behind every call. Your future self will thank you.'],
            ['Quillshot', 'A screenshot API that styles itself', ['developer-tools', 'design-tools'], 'paid', 'felix', 27, 'today',
                'One HTTP call, one gorgeous screenshot. Quillshot renders any URL with smart framing, device chrome, and brand-matched backgrounds — perfect for OG images, changelogs and docs.'],
            ['Fernwave', 'White-noise that adapts to your calendar', ['health', 'productivity'], 'free', 'hana', 19, 'today',
                'Fernwave reads your calendar and shapes your soundscape around it: deep-focus textures during maker time, gentle wind-downs before meetings, and silence when you actually need to listen.'],
            ['Stackmark', 'Bookmarks for code, synced to your repos', ['developer-tools', 'open-source'], 'free', 'greta', 14, 'today',
                'Stackmark lets you bookmark lines of code the way you bookmark articles. Marks live in your repo, survive refactors, and become a shared map of the codebase for your whole team.'],
            ['Formgrove', 'Forms that feel like conversations', ['no-code', 'saas'], 'freemium', 'imani', 11, 'today',
                'Formgrove turns dull forms into friendly back-and-forth conversations. Build with drag-and-drop blocks, branch on any answer, and watch completion rates climb.'],
            ['Nimbusly', 'One dashboard for all your side-project costs', ['fintech', 'analytics'], 'free', 'kenji', 7, 'today',
                'Nimbusly pulls in every invoice from your hosting, domains, APIs and subscriptions, then shows you exactly what each side project costs to keep alive — and which ones deserve to.'],

            // ---- Yesterday ----
            ['Glasspane', 'A beautiful status page in 60 seconds', ['saas', 'developer-tools'], 'freemium', 'theo', 52, 'yesterday',
                'Point Glasspane at your endpoints and get a status page your customers will actually trust: live uptime, honest incident timelines, and subscriber alerts — all on your own domain.'],
            ['Murmur', 'Voice notes that transcribe into your second brain', ['ai', 'productivity'], 'freemium', 'wren', 41, 'yesterday',
                'Capture thoughts the moment they happen. Murmur transcribes your voice notes, tags them automatically, and files them into Notion, Obsidian or wherever your second brain lives.'],
            ['Pixelpond', 'A community marketplace for tiny design assets', ['design-tools', 'marketing'], 'free', 'mara', 33, 'yesterday',
                'Not another bloated asset store. Pixelpond is a curated pond of small, perfect things — icons, textures, cursors, sounds — made by independent designers and priced like coffee.'],
            ['Cronpilot', 'Cron jobs with a conscience — alerts before failures', ['developer-tools'], 'paid', 'oscar', 26, 'yesterday',
                'Cronpilot watches your scheduled jobs and learns their rhythms. When a job runs late, slow, or suspiciously fast, you hear about it before your users do.'],
            ['Vitalon', 'Habit tracking driven by your wearable data', ['health'], 'freemium', 'hana', 18, 'yesterday',
                'Stop logging habits by hand. Vitalon reads your wearable and verifies the habits that matter — sleep, movement, recovery — and nudges you only when the data says you need it.'],
            ['Inkwise', 'Annotate any PDF with your team in realtime', ['productivity', 'education'], 'freemium', 'elif', 13, 'yesterday',
                'Inkwise makes PDF review feel like a shared whiteboard: live cursors, threaded margin notes, and a single source of truth for every version.'],
            ['Coinframe', 'Crypto portfolio rebalancing on autopilot', ['fintech'], 'paid', 'aisha', 8, 'yesterday',
                'Set your target allocation once. Coinframe watches the market and rebalances across exchanges automatically, with full audit logs and zero custody of your keys.'],

            // ---- This week ----
            ['Launchloom', 'Plan, schedule and announce your product launches', ['marketing', 'saas'], 'freemium', 'dario', 55, '-2',
                'Launchloom is mission control for launch day: a single timeline for your announcement posts, emails, and community pings — with templates from a hundred successful launches baked in.'],
            ['Tablecraft', 'The spreadsheet-database hybrid for ops teams', ['no-code', 'productivity'], 'freemium', 'imani', 49, '-3',
                'Tablecraft gives ops teams the comfort of a spreadsheet with the backbone of a database: views, automations, approvals and an API, without a single migration.'],
            ['Sketchlink', 'Hand off design files with living specs', ['design-tools', 'developer-tools'], 'paid', 'mara', 44, '-3',
                'Sketchlink turns your design files into living specs that update themselves. Developers get tokens, measurements and copy that never drift out of date.'],
            ['Promptforge', 'Version control for your LLM prompts', ['ai', 'developer-tools'], 'freemium', 'priya', 40, '-4',
                'Treat prompts like code. Promptforge gives you diffs, evals and rollbacks for every prompt in production, so "it got worse" becomes a bug report, not a feeling.'],
            ['Meadow', 'Mental-health check-ins for remote teams', ['health', 'saas'], 'paid', 'june', 35, '-4',
                'Meadow asks your team one thoughtful question a week and turns the answers into an anonymous pulse. Managers see trends, not names — people stay people.'],
            ['Fontaine', 'Discover font pairings that actually work', ['design-tools'], 'free', 'lotte', 31, '-5',
                'Fontaine pairs typefaces the way a sommelier pairs wine. Pick a hero font and get curated companions with live previews on real layouts, not lorem ipsum.'],
            ['Queryhawk', 'Explain and optimize slow SQL in one click', ['developer-tools', 'analytics'], 'paid', 'marcus', 28, '-5',
                'Paste a slow query and Queryhawk shows you the plan in plain English, the index you are missing, and the rewrite that makes it fly. Works with Postgres and MySQL.'],
            ['Podlift', 'Grow your podcast with listener-driven clips', ['marketing'], 'freemium', 'dario', 24, '-6',
                'Podlift finds the moments your listeners replay most and turns them into share-ready clips with captions. Your best marketing was already in the episode.'],
            ['Ledgerly', 'Bookkeeping for indie hackers, automated', ['fintech', 'saas'], 'paid', 'ravi', 21, '-6',
                'Ledgerly connects to Stripe and your bank, categorizes everything, and hands your accountant a clean ledger at year end. Bookkeeping you never think about.'],
            ['Tutorwing', 'AI study plans that adapt to every student', ['education', 'ai'], 'freemium', 'elif', 18, '-7',
                'Tutorwing builds a personal study plan from any syllabus, then adapts it daily based on what the student actually retained. Like a tutor with infinite patience.'],
            ['Webhookery', 'Inspect, replay and fan out webhooks', ['developer-tools', 'open-source'], 'free', 'greta', 16, '-7',
                'Webhookery is the missing dev tool for webhooks: capture everything, inspect payloads, replay failures, and fan one event out to many consumers. Open source, self-hostable.'],
            ['Palettine', 'Generate brand palettes from a single photo', ['design-tools', 'ai'], 'free', 'lotte', 13, '-7',
                'Feed Palettine one photo that feels like your brand and get a full palette with accessible contrast pairs, dark-mode variants and ready-to-paste design tokens.'],

            // ---- This month ----
            ['Compasso', 'OKRs that update themselves from your data', ['analytics', 'saas'], 'paid', 'marcus', 54, '-9',
                'Compasso wires your OKRs straight to the metrics behind them. Progress updates itself, sandbagging gets harder, and Friday check-ins take five minutes.'],
            ['Helio UI', 'An open-source component library with taste', ['open-source', 'design-tools'], 'free', 'felix', 58, '-11',
                'Helio UI is a hand-finished set of interface components that look designed, not assembled. Accessible by default, themable in minutes, MIT for life.'],
            ['Sprintlite', 'Two-week sprints for teams of one', ['productivity'], 'freemium', 'kenji', 47, '-12',
                'Sprintlite scales agile down to a single human: pick a goal, timebox the fortnight, and let the app protect your scope from your own ambition.'],
            ['Mailgrove', 'Newsletters with built-in referral loops', ['marketing', 'saas'], 'freemium', 'dario', 43, '-14',
                'Mailgrove turns every subscriber into a recruiter with referral rewards, milestone gifts and shareable issues — growth mechanics that respect your readers.'],
            ['Datapeek', 'Ask your database questions in plain English', ['ai', 'analytics'], 'paid', 'priya', 39, '-15',
                'Datapeek sits on top of your warehouse and answers questions like a sharp analyst: real SQL you can audit, charts you can share, caveats you should know.'],
            ['Docsmith', 'Generate API docs your users will love', ['developer-tools'], 'freemium', 'oscar', 36, '-17',
                'Docsmith reads your OpenAPI spec and produces docs with working examples in six languages, a live playground, and search that actually finds things.'],
            ['Plantry', "Meal planning from what's already in your fridge", ['health', 'ai'], 'free', 'hana', 32, '-18',
                'Snap your fridge and pantry, and Plantry plans the week: recipes ranked by what expires first, a shopping list for only what is missing, zero waste.'],
            ['Budgetbee', 'Envelope budgeting for couples', ['fintech'], 'freemium', 'aisha', 28, '-20',
                'Budgetbee makes money a team sport: shared envelopes, private allowances, and a weekly money date agenda that keeps the conversation kind.'],
            ['Lexikon', 'Learn languages through the news you read', ['education'], 'freemium', 'joaquin', 25, '-22',
                'Lexikon rewrites today\'s news at exactly your reading level, glosses the words you do not know yet, and quietly levels you up article by article.'],
            ['Teamtide', 'Detect burnout signals before they become churn', ['analytics', 'health'], 'paid', 'june', 22, '-24',
                'Teamtide watches the rhythms of work — after-hours commits, meeting creep, response times — and warns managers about burnout weeks before a resignation letter does.'],
            ['Craftpage', 'Landing pages assembled from proven sections', ['no-code', 'marketing'], 'freemium', 'imani', 19, '-25',
                'Craftpage is a library of landing-page sections that have already converted for real products. Pick, customize, publish — your page is live before lunch.'],
            ['Gitgazer', 'A beautiful changelog generated from your commits', ['developer-tools', 'open-source'], 'free', 'skyler', 17, '-27',
                'Gitgazer reads your merge history and writes a human changelog: grouped, summarized and illustrated with the right emoji. Your users finally know what shipped.'],
            ['Chordcraft', 'Music theory practice that feels like a game', ['education'], 'paid', 'joaquin', 14, '-28',
                'Chordcraft drills intervals, chords and progressions through tiny daily quests. Streaks, ear-training boss fights, and real progress you can hear.'],
            ['Adweaver', 'Generate ad variants and auto-kill the losers', ['marketing', 'ai'], 'paid', 'dario', 12, '-29',
                'Adweaver spins one ad into fifty honest variants, launches them in batches, and reallocates budget to winners automatically. Creative testing without the spreadsheet.'],
            ['Shelfspace', 'Track and lend your home library', ['productivity'], 'free', 'wren', 9, '-30',
                'Scan a barcode, shelve it digitally, and never lose a lent book again. Shelfspace remembers who borrowed your favorite novel even when you do not.'],

            // ---- Scheduled ----
            ['Cloudloom', 'Deploy previews for your data pipelines', ['developer-tools', 'analytics'], 'paid', 'skyler', 0, '+1',
                'Cloudloom gives every pull request a sandboxed preview of your data pipeline: real transformations on sampled data, diffed outputs, zero risk to prod.'],
            ['Brightform', 'Accessibility audits built into your CI', ['developer-tools', 'open-source'], 'freemium', 'greta', 0, '+3',
                'Brightform fails the build when your UI fails real users: automated WCAG checks, screen-reader smoke tests, and fix-it hints inline in the PR.'],
            ['Postcardly', 'Send real postcards from your CRM', ['marketing', 'saas'], 'paid', 'wren', 0, '+6',
                'Postcardly turns customer milestones into physical mail: handwritten-style postcards triggered by your CRM, delivered anywhere, tracked like email.'],

            // ---- Draft ----
            ['Sidequest', 'A gentle to-do app for your side projects', ['productivity'], 'free', 'skyler', 0, 'draft',
                'Sidequest is a to-do app that knows you have a day job: tiny quests, guilt-free snoozing, and a weekly digest that celebrates whatever you did manage.'],
        ];
    }

    protected function seedProducts(): void
    {
        $closers = [
            'Built by a small team that sweats the details, it launched on Hunted today to find its first true believers. The roadmap is public, feedback shapes it weekly, and the makers answer every comment.',
            'It started as an internal tool, escaped, and got polished into a real product. Early users describe it as "the thing I did not know I needed" — the makers are in the comments and genuinely want your hardest questions.',
            'The team spent six months on the details most people never notice — and you can feel it. Pricing is honest, onboarding takes minutes, and there is a generous free tier to kick the tires.',
            'No venture capital, no growth hacks — just a sharp tool built for a real problem the makers had themselves. They are hanging out in the discussion below all day.',
        ];

        $logoMotifs = ['letter', 'rings', 'split', 'spark', 'dots', 'diamond'];

        foreach ($this->productCatalog() as $index => [$name, $tagline, $topicSlugs, $pricing, $hunter, $voteTarget, $when, $about]) {
            $slug = Str::slug($name);
            $hue = crc32($slug) % 360;

            [$status, $launchedAt] = match (true) {
                $when === 'today' => ['live', now()->startOfDay()->addMinutes(61 + $index * 7)],
                $when === 'yesterday' => ['live', now()->subDay()->startOfDay()->addMinutes(61 + $index * 11)],
                $when === 'draft' => ['draft', null],
                str_starts_with($when, '+') => ['scheduled', now()->addDays((int) ltrim($when, '+'))->startOfDay()->addMinute()],
                default => ['live', now()->subDays((int) ltrim($when, '-'))->startOfDay()->addMinutes(61 + $index * 13)],
            };

            // Generate a brand mark for most products; the rest use the
            // generated monogram tile so the feed shows both treatments.
            $logo = null;
            if ($index % 4 !== 3) {
                $logo = '/images/demo/'.$slug.'-logo.svg';
                File::put(public_path($logo), $this->makeLogo($name, $hue, $logoMotifs[$index % count($logoMotifs)]));
            }

            $screenshots = [];
            if ($status !== 'draft') {
                foreach ([1, 2] as $variant) {
                    $path = '/images/demo/'.$slug.'-shot-'.$variant.'.svg';
                    File::put(public_path($path), $this->makeScreenshot($name, $tagline, $hue, $variant));
                    $screenshots[] = $path;
                }
            }

            $product = Product::create([
                'user_id' => $this->users[$hunter]->id,
                'name' => $name,
                'slug' => $slug,
                'tagline' => $tagline,
                'description' => $about."\n\n".$closers[$index % count($closers)],
                'url' => 'https://'.$slug.'.app',
                'logo' => $logo,
                'screenshots' => $screenshots,
                'pricing' => $pricing,
                'status' => $status,
                'featured' => in_array($name, ['Loopdesk', 'Glasspane', 'Helio UI']),
                'launched_at' => $launchedAt,
                'created_at' => $launchedAt ? $launchedAt->copy()->subDays(2) : now()->subDays(3),
                'updated_at' => $launchedAt ?? now(),
            ]);

            $product->topics()->sync(collect($topicSlugs)->map(fn ($s) => $this->topics[$s]->id));

            // Makers: the hunter plus up to two teammates.
            $teammates = collect($this->users)->except([$hunter, 'avery'])->values()
                ->shuffle()->take($index % 3)->pluck('id');
            $product->makers()->sync($teammates->push($this->users[$hunter]->id)->unique());

            if ($status === 'live') {
                $this->seedVotes($product, $voteTarget);
                $this->seedComments($product);
            }
        }
    }

    protected function seedVotes(Product $product, int $target): void
    {
        $voters = $this->voterPool->shuffle()->take(min($target, $this->voterPool->count()));
        $windowEnd = $product->launched_at->copy()->addDays(3)->min(now());

        $rows = $voters->map(fn (User $voter) => [
            'user_id' => $voter->id,
            'product_id' => $product->id,
            'created_at' => Carbon::createFromTimestamp(
                random_int($product->launched_at->timestamp, max($windowEnd->timestamp, $product->launched_at->timestamp + 60))
            ),
            'updated_at' => now(),
        ])->all();

        Vote::insert($rows);
        $product->update(['votes_count' => count($rows)]);
    }

    protected function seedComments(Product $product): void
    {
        $openers = [
            'Congrats on the launch! Been waiting for something exactly like this — the onboarding was genuinely smooth.',
            'This is the kind of product I wish I had built. Curious: what was the hardest part to get right?',
            'Tried it this morning and it already replaced two tools in my stack. The %s angle is really clever.',
            'Beautiful landing page and an even better product. How are you thinking about pricing long-term?',
            'I have been burned by tools in this space before, but the demo won me over. Bookmarked and upvoted.',
            'How does this handle teams? Would love to roll it out to my squad of six.',
            'The attention to detail here is wild. Even the empty states are thoughtful.',
            'Honest question: what stops me from doing this with a spreadsheet? (Asking because my spreadsheet is crying.)',
            'Solid launch. The free tier is generous enough to actually evaluate it, which I appreciate.',
            'Found a tiny bug in the signup flow but support fixed it within the hour. That alone earned my upvote.',
        ];

        $makerReplies = [
            'Thank you! Honestly the hardest part was saying no to features — we cut half the roadmap to keep it fast.',
            'Appreciate the kind words! Teams support is in private beta right now, ping me and I will get you in.',
            'Great question — pricing stays simple: free for individuals, paid when it makes you money. No surprise tiers.',
            'That means a lot. We obsessed over the small stuff, glad somebody noticed the empty states!',
            'Fair challenge! The spreadsheet works until it does not — we exist for the day it does not. 😄',
        ];

        $followUps = [
            'Came back to say it is still great a few hours later. The details hold up.',
            'Upvoted! Also shared it with my team — two of them signed up already.',
            'Seconding this. The execution level here is rare for a v1.',
        ];

        // Comment volume scales with vote count so popular launches feel alive.
        $count = min(intdiv($product->votes_count, 8) + ($product->votes_count > 25 ? 3 : 1), 8);
        $commenters = $this->voterPool->shuffle()->take($count + 3)->values();
        $makerId = $product->user_id;
        $total = 0;

        for ($i = 0; $i < $count; $i++) {
            $body = $openers[($product->id + $i * 3) % count($openers)];
            $body = str_contains($body, '%s') ? sprintf($body, strtolower($product->topics->first()?->name ?? 'product')) : $body;

            $at = $product->launched_at->copy()->addMinutes(40 + $i * 95)->min(now()->subMinutes(5));

            $comment = Comment::create([
                'user_id' => $commenters[$i]->id,
                'product_id' => $product->id,
                'body' => $body,
                'created_at' => $at,
                'updated_at' => $at,
            ]);
            $total++;

            // Makers reply to the first few comments; community piles on once.
            if ($i < 3 && $product->votes_count > 15) {
                $replyAt = $at->copy()->addMinutes(30 + $i * 12)->min(now());
                Comment::create([
                    'user_id' => $makerId,
                    'product_id' => $product->id,
                    'parent_id' => $comment->id,
                    'body' => $makerReplies[($product->id + $i) % count($makerReplies)],
                    'created_at' => $replyAt,
                    'updated_at' => $replyAt,
                ]);
                $total++;
            } elseif ($i === 3) {
                $replyAt = $at->copy()->addMinutes(50)->min(now());
                Comment::create([
                    'user_id' => $commenters[$count + 1]->id,
                    'product_id' => $product->id,
                    'parent_id' => $comment->id,
                    'body' => $followUps[$product->id % count($followUps)],
                    'created_at' => $replyAt,
                    'updated_at' => $replyAt,
                ]);
                $total++;
            }
        }

        $product->update(['comments_count' => $total]);
    }

    /**
     * A simple generated brand mark: gradient tile + geometric motif.
     */
    protected function makeLogo(string $name, int $hue, string $motif): string
    {
        $h2 = ($hue + 42) % 360;
        $initial = strtoupper(mb_substr($name, 0, 1));

        $shape = match ($motif) {
            'rings' => '<circle cx="64" cy="64" r="34" fill="none" stroke="#fff" stroke-width="9" opacity="0.95"/><circle cx="64" cy="64" r="14" fill="#fff"/>',
            'split' => '<path d="M20 108 108 20v50a38 38 0 0 1-38 38H20Z" fill="#fff" opacity="0.92"/><circle cx="44" cy="44" r="13" fill="#fff"/>',
            'spark' => '<path d="M64 18 76 52l34 12-34 12-12 34-12-34-34-12 34-12Z" fill="#fff" opacity="0.95"/>',
            'dots' => '<circle cx="46" cy="46" r="15" fill="#fff"/><circle cx="82" cy="46" r="15" fill="#fff" opacity="0.55"/><circle cx="46" cy="82" r="15" fill="#fff" opacity="0.55"/><circle cx="82" cy="82" r="15" fill="#fff"/>',
            'diamond' => '<rect x="38" y="38" width="52" height="52" rx="10" fill="#fff" opacity="0.95" transform="rotate(45 64 64)"/><circle cx="64" cy="64" r="10" fill="hsl('.$hue.' 70% 45%)"/>',
            default => '<text x="64" y="88" font-family="Georgia, serif" font-size="68" font-weight="700" fill="#fff" text-anchor="middle">'.$initial.'</text>',
        };

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128">
  <defs>
    <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="hsl({$hue} 72% 52%)"/>
      <stop offset="1" stop-color="hsl({$h2} 70% 38%)"/>
    </linearGradient>
  </defs>
  <rect width="128" height="128" rx="28" fill="url(#g)"/>
  {$shape}
</svg>
SVG;
    }

    /**
     * A stylized browser-window product shot in the brand hue.
     */
    protected function makeScreenshot(string $name, string $tagline, int $hue, int $variant): string
    {
        $safeName = htmlspecialchars($name, ENT_XML1);
        $safeTagline = htmlspecialchars($tagline, ENT_XML1);
        $accent = "hsl({$hue} 70% 50%)";
        $soft = "hsl({$hue} 65% 93%)";
        $ink = "hsl({$hue} 30% 18%)";

        $bodyContent = $variant === 1
            ? <<<BODY
  <text x="90" y="270" font-family="Helvetica, Arial, sans-serif" font-size="46" font-weight="700" fill="{$ink}">{$safeName}</text>
  <text x="90" y="320" font-family="Helvetica, Arial, sans-serif" font-size="24" fill="hsl({$hue} 15% 45%)">{$safeTagline}</text>
  <rect x="90" y="360" width="170" height="48" rx="24" fill="{$accent}"/>
  <text x="175" y="390" font-family="Helvetica, Arial, sans-serif" font-size="18" font-weight="600" fill="#fff" text-anchor="middle">Get started</text>
  <rect x="280" y="360" width="150" height="48" rx="24" fill="none" stroke="hsl({$hue} 20% 80%)" stroke-width="2"/>
  <rect x="640" y="200" width="470" height="320" rx="18" fill="{$soft}"/>
  <rect x="680" y="240" width="390" height="22" rx="11" fill="hsl({$hue} 50% 78%)"/>
  <rect x="680" y="282" width="320" height="22" rx="11" fill="hsl({$hue} 50% 84%)"/>
  <rect x="680" y="324" width="350" height="22" rx="11" fill="hsl({$hue} 50% 84%)"/>
  <rect x="680" y="386" width="180" height="94" rx="12" fill="#fff"/>
  <rect x="880" y="386" width="180" height="94" rx="12" fill="#fff"/>
BODY
            : <<<BODY
  <rect x="60" y="170" width="250" height="430" rx="16" fill="{$soft}"/>
  <rect x="90" y="210" width="190" height="18" rx="9" fill="hsl({$hue} 50% 76%)"/>
  <rect x="90" y="252" width="150" height="14" rx="7" fill="hsl({$hue} 45% 84%)"/>
  <rect x="90" y="286" width="170" height="14" rx="7" fill="hsl({$hue} 45% 84%)"/>
  <rect x="90" y="320" width="130" height="14" rx="7" fill="hsl({$hue} 45% 84%)"/>
  <rect x="350" y="170" width="370" height="200" rx="16" fill="#fff" stroke="hsl({$hue} 25% 88%)"/>
  <rect x="740" y="170" width="370" height="200" rx="16" fill="#fff" stroke="hsl({$hue} 25% 88%)"/>
  <path d="M380 330 Q 450 240 520 290 T 690 230" fill="none" stroke="{$accent}" stroke-width="5" stroke-linecap="round"/>
  <path d="M770 330 Q 840 260 910 300 T 1080 250" fill="none" stroke="hsl({$hue} 50% 70%)" stroke-width="5" stroke-linecap="round"/>
  <rect x="350" y="400" width="760 " height="200" rx="16" fill="#fff" stroke="hsl({$hue} 25% 88%)"/>
  <rect x="385" y="440" width="500" height="16" rx="8" fill="hsl({$hue} 30% 88%)"/>
  <rect x="385" y="478" width="430" height="16" rx="8" fill="hsl({$hue} 30% 90%)"/>
  <rect x="385" y="516" width="470" height="16" rx="8" fill="hsl({$hue} 30% 90%)"/>
  <circle cx="1040" cy="490" r="44" fill="{$accent}" opacity="0.9"/>
BODY;

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 700">
  <rect width="1200" height="700" rx="20" fill="hsl({$hue} 30% 98%)"/>
  <rect width="1200" height="64" rx="20" fill="hsl({$hue} 25% 94%)"/>
  <rect y="40" width="1200" height="24" fill="hsl({$hue} 25% 94%)"/>
  <circle cx="42" cy="32" r="7" fill="#f87171"/>
  <circle cx="68" cy="32" r="7" fill="#fbbf24"/>
  <circle cx="94" cy="32" r="7" fill="#34d399"/>
  <rect x="420" y="16" width="360" height="32" rx="16" fill="hsl({$hue} 20% 99%)"/>
  <text x="600" y="37" font-family="Helvetica, Arial, sans-serif" font-size="15" fill="hsl({$hue} 15% 60%)" text-anchor="middle">{$safeName}.app</text>
{$bodyContent}
</svg>
SVG;
    }

    protected function seedChangelog(): void
    {
        $entries = [
            ['Launch scheduling', 'Pick your day, go live at 12:01am.', '<p>Makers can now schedule launches up to 30 days ahead. Your product goes live at 12:01am on launch day, giving you the full 24 hours to climb the front page.</p>', 21],
            ['Maker badges in discussions', 'Know when the maker is talking.', '<p>Comments from a product\'s makers now carry a vermilion <strong>Maker</strong> badge, so launch-day Q&amp;A is easy to follow.</p>', 16],
            ['Topic pages, rebuilt', 'Every territory got a glow-up.', '<p>Topic pages now show popular and newest views, live launch counts, and the products defining each space.</p>', 11],
            ['The Leaderboard', 'Glory, ranked weekly and monthly.', '<p>A new leaderboard celebrates the makers earning the community\'s upvotes — with a podium for the top three, naturally.</p>', 6],
            ['Dark mode', 'For the night hunters.', '<p>Hunted now ships with a full dark theme. Your preference is remembered across visits, and it is one click away in the navbar.</p>', 2],
        ];

        foreach ($entries as [$title, $description, $body, $daysAgo]) {
            $at = Carbon::now()->subDays($daysAgo);
            Changelog::create(compact('title', 'description', 'body'))
                ->forceFill(['created_at' => $at, 'updated_at' => $at])
                ->save();
        }
    }

    protected function seedBlog(): void
    {
        $author = $this->users['avery'];

        $launches = Category::firstOrCreate(['slug' => 'launch-guides'], ['name' => 'Launch Guides', 'order' => 1]);
        $community = Category::firstOrCreate(['slug' => 'community'], ['name' => 'Community', 'order' => 2]);

        $posts = [
            [
                'The anatomy of a great launch day', $launches,
                'What the top 1% of launches on Hunted do differently — from tagline to last comment.',
                "<p>We studied a year of front pages and the pattern is unmistakable: great launches are won before they begin. The tagline is sharpened to a single sentence. The gallery shows the product doing its job, not posing. And the makers camp out in the comments all day.</p><h2>Your tagline is the product</h2><p>Most hunters decide in the four seconds it takes to read your name and one-liner. Write twenty taglines, cut the clever ones, and keep the clearest.</p><h2>Show up in the discussion</h2><p>Products whose makers reply within an hour earn roughly twice the comments. Conversation is compounding — every reply bumps you back into someone's feed.</p>",
                14, true,
            ],
            [
                'How the Hunted ranking works', $community,
                'No dark magic: a plain-English explanation of the daily leaderboard.',
                "<p>Every product that launches today competes on today's page, ranked by community upvotes. At midnight the page is archived and a new hunt begins.</p><p>We deliberately keep the formula boring: real votes from real accounts, no follower multipliers, no pay-to-rank. The featured ribbon highlights editorially-chosen launches but never changes the order.</p>",
                8, false,
            ],
            [
                'Twenty questions to ask every maker', $community,
                'Better comments make a better community. Steal these conversation starters.',
                '<p>The best thing you can give a maker on launch day is not an upvote — it is a sharp question. Ask about the hardest tradeoff, the user they built it for, the feature they cut. Specific questions get specific answers, and specific answers are why the discussion section is the best part of this site.</p>',
                3, false,
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

    protected function seedNotifications(): void
    {
        $demo = $this->users['skyler'];
        $loopdesk = Product::where('slug', 'loopdesk')->first();

        if (! $loopdesk) {
            return;
        }

        $events = [
            ['product_voted', 'Your launch <strong>Loopdesk</strong> passed 25 upvotes 🎉', 0.5],
            ['new_comment', '<strong>June Castellanos</strong> commented on <strong>Loopdesk</strong>', 2],
            ['product_voted', '<strong>Theo Okafor</strong> and 11 others upvoted <strong>Loopdesk</strong>', 4],
            ['new_comment', '<strong>Dario Mancini</strong> replied to your comment on <strong>Glasspane</strong>', 9],
            ['product_live', 'Reminder: <strong>Cloudloom</strong> goes live tomorrow at 12:01am', 20],
        ];

        foreach ($events as $i => [$event, $message, $hoursAgo]) {
            $at = now()->subHours($hoursAgo);
            $demo->notifications()->create([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\LaunchActivity',
                'data' => [
                    'event' => $event,
                    'message' => $message,
                    'url' => route('products.show', ['product' => $loopdesk]),
                ],
                'read_at' => $i > 2 ? $at : null,
                'created_at' => $at,
                'updated_at' => $at,
            ]);
        }
    }
}
