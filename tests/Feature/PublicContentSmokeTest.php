<?php

use App\Models\User;
use Devdojo\Blog\Models\Category;
use Devdojo\Blog\Models\Post;
use Devdojo\Changelog\Models\Changelog;

it('renders changelog index with entries', function () {
    Changelog::create([
        'title' => 'Drag and drop board',
        'description' => 'Move work without friction.',
        'body' => '<h2>What changed</h2><p>You can now <strong>drag</strong> cards.</p><ul><li>Faster</li></ul>',
    ]);

    $this->get('/changelog')
        ->assertOk()
        ->assertSee('Drag and drop board')
        ->assertSee('Changelog');
});

it('renders changelog empty state', function () {
    $this->get('/changelog')->assertOk()->assertSee('Nothing shipped yet');
});

it('renders blog index with a featured post and grid', function () {
    $author = User::factory()->create(['name' => 'Alex Rivers']);
    $category = Category::create(['name' => 'Product', 'slug' => 'product']);

    Post::create([
        'title' => 'Introducing Relay',
        'slug' => 'introducing-relay',
        'excerpt' => 'Say hello to Relay.',
        'body' => '<p>Welcome.</p>',
        'status' => 'PUBLISHED',
        'featured' => true,
        'author_id' => $author->id,
        'category_id' => $category->id,
    ]);
    Post::create([
        'title' => 'Shipping faster',
        'slug' => 'shipping-faster',
        'excerpt' => 'Ship more, sit in tools less.',
        'body' => '<p>Speed.</p>',
        'status' => 'PUBLISHED',
        'featured' => false,
        'author_id' => $author->id,
        'category_id' => $category->id,
    ]);

    $this->get('/blog')
        ->assertOk()
        ->assertSee('The Relay Blog')
        ->assertSee('Introducing Relay')
        ->assertSee('Shipping faster')
        ->assertSee('Featured');
});

it('renders blog empty state', function () {
    $this->get('/blog')->assertOk()->assertSee('No posts yet');
});

it('renders a blog post by slug', function () {
    $author = User::factory()->create(['name' => 'Alex Rivers', 'title' => 'Founder']);
    $category = Category::create(['name' => 'Engineering', 'slug' => 'engineering']);

    $post = Post::create([
        'title' => 'How we build fast',
        'slug' => 'how-we-build-fast',
        'excerpt' => 'Notes on speed.',
        'status' => 'PUBLISHED',
        'featured' => false,
        'author_id' => $author->id,
        'category_id' => $category->id,
        'body' => '<h2>Speed</h2><p>We obsess over it.</p>',
    ]);

    $this->get('/blog/'.$post->slug)
        ->assertOk()
        ->assertSee('How we build fast')
        ->assertSee('Written by')
        ->assertSee('Alex Rivers');
});

it('renders a public profile', function () {
    User::factory()->create([
        'name' => 'Jordan Blake',
        'username' => 'jordan',
        'title' => 'Designer',
    ]);

    $this->get('/u/jordan')
        ->assertOk()
        ->assertSee('Jordan Blake')
        ->assertSee('jordan')
        ->assertSee('Recent activity');
});

it('hides a private profile from other users', function () {
    User::factory()->create([
        'name' => 'Secret Sam',
        'username' => 'sam',
        'privacy_settings' => ['profile_visibility' => 'private'],
    ]);

    $this->get('/u/sam')
        ->assertOk()
        ->assertSee('This profile is private')
        ->assertDontSee('Recent activity');
});

it('404s for an unknown profile', function () {
    $this->get('/u/nobody-here')->assertNotFound();
});
