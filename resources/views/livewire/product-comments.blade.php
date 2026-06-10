<?php

use App\Models\Comment;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component
{
    public Product $product;

    #[Validate('required|string|min:2|max:2000')]
    public string $body = '';

    public ?int $replyingTo = null;

    #[Validate('required|string|min:2|max:2000')]
    public string $replyBody = '';

    public function postComment(): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $this->validateOnly('body');

        $this->product->comments()->create([
            'user_id' => auth()->id(),
            'body' => trim($this->body),
        ]);

        $this->product->increment('comments_count');
        $this->reset('body');
        unset($this->comments);

        $this->dispatch('toast', type: 'success', message: 'Comment posted.');
    }

    public function startReply(int $commentId): void
    {
        if (! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        $this->replyingTo = $this->replyingTo === $commentId ? null : $commentId;
        $this->reset('replyBody');
    }

    public function postReply(): void
    {
        if (! auth()->check() || ! $this->replyingTo) {
            return;
        }

        $this->validateOnly('replyBody');

        $parent = $this->product->comments()->findOrFail($this->replyingTo);

        $this->product->comments()->create([
            'user_id' => auth()->id(),
            'parent_id' => $parent->id,
            'body' => trim($this->replyBody),
        ]);

        $this->product->increment('comments_count');
        $this->reset('replyBody', 'replyingTo');
        unset($this->comments);

        $this->dispatch('toast', type: 'success', message: 'Reply posted.');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->product->comments()->findOrFail($commentId);

        if ($comment->user_id !== auth()->id() && ! auth()->user()?->isAdmin()) {
            return;
        }

        $removed = 1 + $comment->replies()->count();
        $comment->delete();
        $this->product->decrement('comments_count', min($removed, $this->product->comments_count));
        unset($this->comments);

        $this->dispatch('toast', type: 'success', message: 'Comment removed.');
    }

    #[Computed]
    public function comments()
    {
        return $this->product->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();
    }
};

?>

<section id="comments" class="scroll-mt-24">
    <h2 class="flex items-center gap-2.5 text-xl font-extrabold tracking-tight text-fg">
        Discussion
        <span class="badge font-mono text-muted">{{ $product->comments_count }}</span>
    </h2>

    {{-- Composer --}}
    <div class="card mt-5 p-4">
        @auth
            <div class="flex items-start gap-3">
                <x-avatar :name="auth()->user()->name" :src="auth()->user()->avatar" size="lg" class="mt-1" />
                <form wire:submit="postComment" class="flex-1">
                    <textarea
                        wire:model="body"
                        rows="3"
                        class="input"
                        placeholder="What do you think of {{ $product->name }}? Ask the makers anything…"
                    ></textarea>
                    @error('body')
                        <p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>
                    @enderror
                    <div class="mt-3 flex justify-end">
                        <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled" wire:target="postComment">
                            <span wire:loading.remove wire:target="postComment">Post comment</span>
                            <span wire:loading wire:target="postComment">Posting…</span>
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="flex flex-col items-center gap-3 py-4 text-center sm:flex-row sm:justify-between sm:text-left">
                <p class="text-sm text-muted">Join the discussion — tell the makers what you think.</p>
                <a href="{{ route('login') }}" class="btn btn-secondary btn-sm shrink-0">Sign in to comment</a>
            </div>
        @endauth
    </div>

    {{-- Thread --}}
    <div class="mt-6 space-y-6">
        @forelse ($this->comments as $comment)
            <div class="animate-enter-up" wire:key="comment-{{ $comment->id }}">
                <div class="flex items-start gap-3">
                    <a href="{{ $comment->user->profileUrl() }}" wire:navigate>
                        <x-avatar :name="$comment->user->name" :src="$comment->user->avatar" size="lg" />
                    </a>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                            <a href="{{ $comment->user->profileUrl() }}" wire:navigate class="text-[13.5px] font-bold text-fg hover:text-accent">{{ $comment->user->name }}</a>
                            @if ($comment->isFromMaker())
                                <span class="badge !border-accent-line bg-accent-soft !px-2 text-[10.5px] font-bold uppercase tracking-wide text-accent">Maker</span>
                            @endif
                            <span class="font-mono text-[11.5px] text-subtle">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1.5 whitespace-pre-line text-[14px] leading-relaxed text-fg/90 text-pretty">{{ $comment->body }}</p>
                        <div class="mt-2 flex items-center gap-4 text-[12.5px] font-medium text-subtle">
                            <button type="button" wire:click="startReply({{ $comment->id }})" class="transition-colors hover:text-fg">
                                {{ $replyingTo === $comment->id ? 'Cancel' : 'Reply' }}
                            </button>
                            @if (auth()->id() === $comment->user_id || auth()->user()?->isAdmin())
                                <button type="button" wire:click="deleteComment({{ $comment->id }})" wire:confirm="Delete this comment?" class="transition-colors hover:text-accent">
                                    Delete
                                </button>
                            @endif
                        </div>

                        @if ($replyingTo === $comment->id)
                            <form wire:submit="postReply" class="mt-3">
                                <textarea wire:model="replyBody" rows="2" class="input" placeholder="Reply to {{ $comment->user->name }}…" x-init="$el.focus()"></textarea>
                                @error('replyBody')
                                    <p class="mt-1.5 text-[12.5px] text-accent">{{ $message }}</p>
                                @enderror
                                <div class="mt-2 flex justify-end gap-2">
                                    <button type="button" wire:click="startReply({{ $comment->id }})" class="btn btn-ghost btn-sm">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Reply</button>
                                </div>
                            </form>
                        @endif

                        {{-- Replies --}}
                        @if ($comment->replies->isNotEmpty())
                            <div class="mt-4 space-y-4 border-l-2 border-line pl-4 sm:pl-5">
                                @foreach ($comment->replies as $reply)
                                    <div class="flex items-start gap-2.5" wire:key="reply-{{ $reply->id }}">
                                        <a href="{{ $reply->user->profileUrl() }}" wire:navigate>
                                            <x-avatar :name="$reply->user->name" :src="$reply->user->avatar" size="md" />
                                        </a>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                <a href="{{ $reply->user->profileUrl() }}" wire:navigate class="text-[13px] font-bold text-fg hover:text-accent">{{ $reply->user->name }}</a>
                                                @if ($reply->isFromMaker())
                                                    <span class="badge !border-accent-line bg-accent-soft !px-2 text-[10px] font-bold uppercase tracking-wide text-accent">Maker</span>
                                                @endif
                                                <span class="font-mono text-[11px] text-subtle">{{ $reply->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="mt-1 whitespace-pre-line text-[13.5px] leading-relaxed text-fg/90 text-pretty">{{ $reply->body }}</p>
                                            @if (auth()->id() === $reply->user_id || auth()->user()?->isAdmin())
                                                <button type="button" wire:click="deleteComment({{ $reply->id }})" wire:confirm="Delete this reply?" class="mt-1.5 text-[12px] font-medium text-subtle transition-colors hover:text-accent">
                                                    Delete
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="card px-6 py-12 text-center">
                <p class="font-serif text-lg italic text-muted">No comments yet.</p>
                <p class="mt-1 text-[13px] text-subtle">Be the first to start the conversation.</p>
            </div>
        @endforelse
    </div>
</section>
