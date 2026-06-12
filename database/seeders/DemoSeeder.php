<?php

namespace Database\Seeders;

use App\Enums\TaskStatus;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Label;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskActivityNotification;
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
        $members = $this->createMembers();
        [$alex, $jordan, $maya, $dev, $sam, $riley] = array_values($members);

        $this->setupBilling($alex);
        $this->createTeam($members);

        $labels = $this->createLabels();
        $projects = $this->createProjects($alex, $jordan, collect($members));
        $this->createTasks($projects, collect($members), $labels, $alex);
        $this->seedNotifications($alex);
        $this->seedChangelog();
        $this->seedBlog($alex);
    }

    /**
     * @return array<string, User>
     */
    protected function createMembers(): array
    {
        $people = [
            'alex' => ['Alex Rivera', 'demo@devdojo.test', 'alex', 'Head of Product', 'Building Relay. Product-minded engineer who loves a clean board and an empty inbox.'],
            'jordan' => ['Jordan Lee', 'free@devdojo.test', 'jordan', 'Founder', 'Solo founder kicking the tires. Big fan of shipping small and often.'],
            'maya' => ['Maya Chen', 'maya@devdojo.test', 'maya', 'Lead Designer', 'Design systems, dark mode, and the perfect 8px grid.'],
            'dev' => ['Dev Patel', 'dev@devdojo.test', 'dev', 'Staff Engineer', 'Backend & infrastructure. If it scales, I am happy.'],
            'sam' => ['Sam Okafor', 'sam@devdojo.test', 'sam', 'Mobile Engineer', 'iOS & Android. Pixel-perfect, offline-first.'],
            'riley' => ['Riley Brooks', 'riley@devdojo.test', 'riley', 'Growth Marketer', 'Launches, lifecycle, and a good story.'],
        ];

        $members = [];

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
                        'website' => 'https://'.$username.'.relay.app',
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

            $members[$key] = $user;
        }

        return $members;
    }

    /**
     * Create the shared Northwind team and put every demo member on it.
     *
     * @param  array<string, User>  $members
     */
    protected function createTeam(array $members): void
    {
        $alex = $members['alex'];

        $team = $alex->ownedTeams()->firstOrCreate(
            ['name' => 'Northwind'],
            ['personal_team' => false]
        );

        $roles = ['jordan' => 'member', 'maya' => 'admin', 'dev' => 'editor', 'sam' => 'editor', 'riley' => 'member'];

        foreach ($roles as $key => $role) {
            $team->users()->syncWithoutDetaching([$members[$key]->id => ['role' => $role]]);
        }

        foreach ($members as $member) {
            $member->forceFill(['current_team_id' => $team->id])->save();
        }
    }

    protected function setupBilling(User $alex): void
    {
        $alex->syncRoles(['admin', 'pro']);

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
     * @return Collection<string, Label>
     */
    protected function createLabels(): Collection
    {
        return collect([
            'Bug' => 'rose',
            'Feature' => 'indigo',
            'Design' => 'violet',
            'Infra' => 'emerald',
            'Docs' => 'amber',
        ])->map(fn ($color, $name) => Label::firstOrCreate(['name' => $name], ['color' => $color]));
    }

    /**
     * @return Collection<int, Project>
     */
    protected function createProjects(User $alex, User $jordan, Collection $members): Collection
    {
        $definitions = [
            ['Website Redesign', 'WEB', 'indigo', 'browser', 'Rebuild the marketing site with a faster, cleaner design system.', $alex],
            ['API Platform', 'API', 'emerald', 'cube', 'The public API, SDKs, and developer experience.', $alex],
            ['Mobile App v2', 'MOB', 'rose', 'device-mobile', 'A ground-up rewrite of the iOS and Android apps.', $jordan],
            ['Q3 Marketing', 'MKT', 'amber', 'megaphone', 'Campaigns, launches, and content for the third quarter.', $jordan],
        ];

        $projects = collect();

        foreach ($definitions as [$name, $key, $color, $icon, $description, $owner]) {
            $project = Project::create([
                'owner_id' => $owner->id,
                'name' => $name,
                'key' => $key,
                'color' => $color,
                'icon' => $icon,
                'description' => $description,
                'status' => 'active',
            ]);

            foreach ($members as $member) {
                $project->members()->attach($member->id, [
                    'role' => $member->id === $owner->id ? 'owner' : 'member',
                ]);
            }

            $projects->push($project);
        }

        return $projects;
    }

    protected function createTasks(Collection $projects, Collection $members, Collection $labels, User $alex): void
    {
        $blueprints = [
            'WEB' => [
                ['Design the new marketing homepage', 'in_review', 'high', ['Design', 'Feature']],
                ['Implement responsive navigation', 'done', 'medium', ['Feature']],
                ['Dark mode polish pass across pages', 'in_progress', 'medium', ['Design']],
                ['Audit the site for accessibility (WCAG AA)', 'todo', 'high', ['Design']],
                ['Optimize hero image LCP under 1.2s', 'in_progress', 'urgent', ['Bug', 'Infra']],
                ['Add a pricing page A/B test', 'backlog', 'low', ['Feature']],
                ['Cookie consent banner', 'todo', 'low', []],
                ['Rebuild the footer with sitemap links', 'done', 'low', ['Design']],
                ['SEO meta tags + Open Graph pass', 'backlog', 'medium', ['Docs']],
                ['404 page illustration', 'backlog', 'none', ['Design']],
                ['Newsletter signup flow', 'todo', 'medium', ['Feature']],
                ['Fix layout shift on the testimonials carousel', 'in_review', 'high', ['Bug']],
            ],
            'API' => [
                ['Rate-limit the public ingest endpoint', 'in_progress', 'urgent', ['Infra', 'Bug']],
                ['Webhook signature verification', 'done', 'high', ['Infra', 'Feature']],
                ['Publish the OpenAPI 3.1 spec', 'todo', 'medium', ['Docs']],
                ['Cursor-based pagination for list endpoints', 'in_progress', 'high', ['Feature']],
                ['API key rotation + scopes', 'backlog', 'medium', ['Feature', 'Infra']],
                ['Idempotency keys for write endpoints', 'todo', 'high', ['Infra']],
                ['Audit log export (CSV + JSON)', 'backlog', 'low', ['Feature']],
                ['TypeScript SDK client', 'in_review', 'medium', ['Feature', 'Docs']],
                ['Deprecate v1 endpoints with sunset headers', 'backlog', 'low', ['Docs']],
                ['Investigate 504s on the search endpoint', 'todo', 'urgent', ['Bug']],
                ['Add request tracing with OpenTelemetry', 'done', 'medium', ['Infra']],
            ],
            'MOB' => [
                ['Offline draft sync for tasks', 'in_progress', 'urgent', ['Feature', 'Bug']],
                ['Push notification opt-in flow', 'todo', 'high', ['Feature']],
                ['Biometric (Face ID) login', 'backlog', 'medium', ['Feature']],
                ['Redesign the onboarding carousel', 'in_review', 'medium', ['Design']],
                ['Fix crash on Android 14 cold start', 'todo', 'urgent', ['Bug']],
                ['Image upload compression', 'done', 'low', ['Infra']],
                ['Deep link handling for /projects', 'backlog', 'medium', ['Feature']],
                ['Refresh App Store screenshots', 'backlog', 'low', ['Design']],
                ['Add haptics to key interactions', 'todo', 'low', ['Design']],
                ['Dark mode parity with web', 'in_progress', 'medium', ['Design']],
            ],
            'MKT' => [
                ['Write the v2 launch announcement', 'in_progress', 'high', ['Docs']],
                ['Customer case study: Vertex', 'todo', 'medium', ['Docs']],
                ['Plan the Q3 webinar series', 'backlog', 'low', []],
                ['Refresh the brand guidelines', 'in_review', 'medium', ['Design']],
                ['Launch the paid search campaign', 'todo', 'high', []],
                ['Lifecycle email sequence (7 emails)', 'backlog', 'medium', ['Docs']],
                ['Design the conference booth', 'done', 'low', ['Design']],
                ['Influencer outreach shortlist', 'todo', 'low', []],
                ['Update the press kit', 'backlog', 'none', ['Docs']],
            ],
        ];

        $sampleComments = [
            'Picking this up now — should have a draft by EOD.',
            'Left a few notes in Figma, take a look when you get a sec.',
            'Blocked on the API change, will circle back tomorrow.',
            'Nice work on this! One small nit on spacing, otherwise 👍',
            'Moving this to review — ready for a second pair of eyes.',
            'Can we scope this down for v1 and follow up later?',
            'Confirmed this reproduces on staging. Adding repro steps.',
            'Shipped 🎉 closing this out.',
        ];

        foreach ($projects as $project) {
            $blueprint = $blueprints[$project->key] ?? [];
            $number = 0;
            $positions = [];

            foreach ($blueprint as [$title, $status, $priority, $labelNames]) {
                $number++;
                $positions[$status] = ($positions[$status] ?? -1) + 1;

                $createdAt = Carbon::now()->subDays(fake()->numberBetween(1, 24))->subHours(fake()->numberBetween(0, 23));
                $assignee = fake()->boolean(85) ? $members->random() : null;
                $isDone = $status === 'done';

                // Spread due dates; make a few overdue, some upcoming.
                $dueDate = null;
                if (! $isDone && fake()->boolean(60)) {
                    $dueDate = fake()->boolean(35)
                        ? Carbon::now()->subDays(fake()->numberBetween(1, 6))      // overdue
                        : Carbon::now()->addDays(fake()->numberBetween(1, 18));     // upcoming
                }

                $task = $project->tasks()->create([
                    'assignee_id' => $assignee?->id,
                    'creator_id' => $members->random()->id,
                    'number' => $number,
                    'title' => $title,
                    'description' => $this->description($title),
                    'status' => $status,
                    'priority' => $priority,
                    'due_date' => $dueDate?->format('Y-m-d'),
                    'position' => $positions[$status],
                    'completed_at' => $isDone ? $createdAt->copy()->addDays(fake()->numberBetween(1, 4)) : null,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                // Labels
                foreach ($labelNames as $labelName) {
                    if ($label = $labels->get($labelName)) {
                        $task->labels()->attach($label->id);
                    }
                }

                // Created activity
                Activity::create([
                    'task_id' => $task->id,
                    'user_id' => $task->creator_id,
                    'type' => 'created',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($assignee) {
                    Activity::create([
                        'task_id' => $task->id,
                        'user_id' => $task->creator_id,
                        'type' => 'assigned',
                        'meta' => ['to_name' => $assignee->name],
                        'created_at' => $createdAt->copy()->addMinutes(3),
                        'updated_at' => $createdAt->copy()->addMinutes(3),
                    ]);
                }

                // Comments on a subset
                if (fake()->boolean(40)) {
                    foreach (range(1, fake()->numberBetween(1, 3)) as $i) {
                        $author = $members->random();
                        $commentAt = $createdAt->copy()->addHours(fake()->numberBetween(1, 40));
                        Comment::create([
                            'task_id' => $task->id,
                            'user_id' => $author->id,
                            'body' => fake()->randomElement($sampleComments),
                            'created_at' => $commentAt,
                            'updated_at' => $commentAt,
                        ]);
                        Activity::create([
                            'task_id' => $task->id,
                            'user_id' => $author->id,
                            'type' => 'commented',
                            'created_at' => $commentAt,
                            'updated_at' => $commentAt,
                        ]);
                    }
                }
            }
        }
    }

    protected function description(string $title): ?string
    {
        return fake()->boolean(75)
            ? "## Context\n\n".fake()->paragraph(3)."\n\n## Acceptance criteria\n\n- ".fake()->sentence(6)."\n- ".fake()->sentence(5)."\n- ".fake()->sentence(7)
            : null;
    }

    protected function seedNotifications(User $alex): void
    {
        // A few real, recent notifications for the primary demo account.
        $alexTasks = Task::where('assignee_id', $alex->id)->with('project')->latest()->take(3)->get();
        $others = User::where('id', '!=', $alex->id)->get();

        foreach ($alexTasks as $i => $task) {
            $actor = $others->random();
            $alex->notify(new TaskActivityNotification(
                'task_assigned',
                $task,
                Str::of($actor->name)->explode(' ')->first().' assigned you · '.$task->identifier().' '.$task->title,
            ));
        }

        $commentTask = Task::with('project')->inRandomOrder()->first();
        if ($commentTask) {
            $actor = $others->random();
            $alex->notify(new TaskActivityNotification(
                'new_comment',
                $commentTask,
                Str::of($actor->name)->explode(' ')->first().' commented on '.$commentTask->identifier().' '.$commentTask->title,
            ));
        }

        $doneTask = Task::where('status', TaskStatus::Done->value)->with('project')->inRandomOrder()->first();
        if ($doneTask) {
            $actor = $others->random();
            $alex->notify(new TaskActivityNotification(
                'status_done',
                $doneTask,
                Str::of($actor->name)->explode(' ')->first().' completed '.$doneTask->identifier().' '.$doneTask->title,
            ));
        }

        // Mark the oldest as read so there's a believable read/unread mix.
        $alex->unreadNotifications()->latest()->skip(2)->take(5)->get()->each->markAsRead();
    }

    protected function seedChangelog(): void
    {
        $entries = [
            ['Drag-and-drop board', 'Move tasks across your pipeline with a buttery, optimistic board.', '<p>The board is here — and it is fast. Drag cards between Backlog, In Progress, In Review and Done with instant, optimistic updates that persist in the background.</p><p>We also added per-column counts and inline quick-add so you can capture work without breaking flow.</p>', 18],
            ['Command palette', 'Jump anywhere with ⌘K.', '<p>Press <strong>⌘K</strong> (or Ctrl+K) from anywhere to search projects and tasks, or jump to any page in a keystroke. Fully keyboard-navigable.</p>', 13],
            ['Task slide-over', 'A focused panel for every task.', '<p>Open any task in a Linear-style slide-over to edit status, priority, assignee, due date and labels — plus comments and a full activity timeline — without losing your place.</p>', 9],
            ['In-app notifications', 'Never miss an assignment again.', '<p>Get notified the moment a task is assigned to you, someone comments, or work ships. Tune exactly what you hear about in notification preferences.</p>', 5],
            ['Dark mode, done right', 'Easy on the eyes, all day.', '<p>Relay is dark-first with a crisp light theme one click away. Your preference is remembered across sessions.</p>', 2],
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
        $eng = Category::firstOrCreate(['slug' => 'engineering'], ['name' => 'Engineering', 'order' => 2]);

        $posts = [
            [
                'Introducing Relay', $product,
                'The project tracker we always wanted: fast, focused, and genuinely nice to look at.',
                "<p>We built Relay because we were tired of project tools that get in the way. Bloated sidebars, slow boards, and forms that feel like paperwork.</p><p>Relay is different. It's keyboard-first, dark-mode-first, and obsessively fast. The board moves the instant you do. The command palette gets you anywhere in a keystroke. And every screen is designed, not assembled.</p><h2>Built for momentum</h2><p>Your team doesn't need more process. It needs less friction. Relay keeps the surface area small so the work stays front and center.</p>",
                'rocket-launch', 18, true,
            ],
            [
                'How we built an optimistic kanban board', $eng,
                'A look under the hood at the drag-and-drop board, and why it feels instant.',
                '<p>The board is the heart of Relay, so it had to feel perfect. That meant optimistic updates: when you drop a card, it lands instantly — the server catches up in the background.</p><p>We render the board server-side with Livewire for a single source of truth, then layer a thin Alpine drag-and-drop on top. The result is the responsiveness of a single-page app with the simplicity of server-rendered HTML.</p>',
                'cube', 11, false,
            ],
            [
                'Designing for focus', $product,
                'Why we chose restraint: one accent color, hairline borders, and a lot of whitespace.',
                '<p>Good tools disappear. We made deliberate choices to keep Relay calm: a monochrome foundation, a single restrained accent, and typography that gets out of the way.</p><p>Constraints are a feature. Fewer colors, fewer chrome, fewer decisions — so you can spend your attention on the work that matters.</p>',
                'sparkle', 4, false,
            ],
        ];

        foreach ($posts as [$title, $category, $excerpt, $body, $icon, $daysAgo, $featured]) {
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
