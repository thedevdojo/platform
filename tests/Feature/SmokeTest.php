<?php

use App\Enums\TicketStatus;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Customer;
use App\Models\Message;
use App\Models\Tag;
use App\Models\Ticket;
use App\Models\User;
use Devdojo\Blog\Models\Category;
use Devdojo\Blog\Models\Post;
use Devdojo\Changelog\Models\Changelog;
use Livewire\Volt\Volt;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => 'agent', 'guard_name' => 'web']);

    $this->user = User::factory()->create(['username' => 'demo']);
    $this->user->assignRole(['admin', 'agent']);

    $this->customer = Customer::factory()->create();

    $tags = Tag::factory()->count(3)->create();
    $this->ticket = Ticket::factory()->open()->create([
        'customer_id' => $this->customer->id,
        'assignee_id' => $this->user->id,
    ]);
    $this->ticket->tags()->attach($tags->random()->id);

    Message::factory()->create([
        'ticket_id' => $this->ticket->id,
        'customer_id' => $this->customer->id,
    ]);

    $this->ticket->recordEvent('created');

    $kbCategory = ArticleCategory::factory()->create(['slug' => 'getting-started']);
    $this->article = Article::factory()->create([
        'article_category_id' => $kbCategory->id,
        'author_id' => $this->user->id,
        'slug' => 'first-article',
    ]);

    $category = Category::create(['name' => 'Product', 'slug' => 'product', 'order' => 1]);
    $this->post = Post::create([
        'author_id' => $this->user->id,
        'category_id' => $category->id,
        'title' => 'Hello World',
        'slug' => 'hello-world',
        'excerpt' => 'A first post.',
        'body' => '<p>Hello.</p>',
        'status' => 'PUBLISHED',
        'featured' => true,
    ]);

    Changelog::create(['title' => 'v1.0', 'description' => 'First release', 'body' => '<p>Shipped.</p>']);
});

it('shows the marketing landing to guests', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Deskly')
        ->assertSee('chaos');
});

it('renders the core authenticated experience', function (string $route) {
    $this->actingAs($this->user)->get($route)->assertSuccessful();
})->with(function () {
    return [
        'dashboard' => fn () => route('dashboard'),
        'ticket inbox' => fn () => route('tickets.index'),
        'ticket detail' => fn () => route('tickets.show', ['ticket' => $this->ticket->id]),
        'customers index' => fn () => route('customers.index'),
        'customer detail' => fn () => route('customers.show', ['customer' => $this->customer->id]),
        'reports' => fn () => route('reports'),
        'knowledge base' => fn () => route('kb.index'),
        'notifications' => fn () => route('notifications'),
        'settings account' => fn () => route('settings.account'),
        'settings security' => fn () => route('settings.security'),
        'settings replies' => fn () => route('settings.replies'),
        'settings notifications' => fn () => route('settings.notifications'),
        'settings billing' => fn () => route('settings.billing'),
        'settings team' => fn () => route('settings.team'),
    ];
});

it('renders public content pages', function () {
    foreach ([
        route('pricing'),
        route('changelog.index'),
        route('blog.index'),
        route('blog.show', ['post' => $this->post->slug]),
        route('help.index'),
        route('help.category', ['articleCategory' => 'getting-started']),
        route('help.article', ['article' => $this->article->slug]),
        route('profile.show', ['username' => $this->user->username]),
    ] as $url) {
        $this->get($url)->assertSuccessful();
    }
});

it('requires authentication for the app', function () {
    $this->get(route('tickets.index'))->assertRedirect();
    $this->get(route('dashboard'))->assertRedirect();
});

it('sends a reply and records the first response', function () {
    $this->actingAs($this->user);

    Volt::test('ticket-detail', ['ticket' => $this->ticket])
        ->set('body', 'Thanks for reaching out — looking into this now.')
        ->call('send');

    $this->ticket->refresh();

    expect($this->ticket->messages()->whereNotNull('user_id')->count())->toBe(1)
        ->and($this->ticket->first_response_at)->not->toBeNull()
        ->and($this->ticket->status)->toBe(TicketStatus::Pending);
});

it('resolves a ticket from the detail view', function () {
    $this->actingAs($this->user);

    Volt::test('ticket-detail', ['ticket' => $this->ticket])
        ->call('setStatus', 'resolved');

    $this->ticket->refresh();

    expect($this->ticket->status)->toBe(TicketStatus::Resolved)
        ->and($this->ticket->resolved_at)->not->toBeNull();
});

it('hides draft articles from the public help center', function () {
    $draft = Article::factory()->draft()->create([
        'article_category_id' => $this->article->article_category_id,
    ]);

    $this->get(route('help.article', ['article' => $draft->slug]))->assertNotFound();
    $this->actingAs($this->user)->get(route('help.article', ['article' => $draft->slug]))->assertSuccessful();
});
