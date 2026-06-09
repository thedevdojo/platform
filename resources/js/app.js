/**
 * Relay front-end glue. Livewire 4 bundles Alpine, so we hook `alpine:init`
 * to register shared stores (theme, command palette, toasts).
 */

const THEME_KEY = 'relay-theme';

function applyTheme(theme) {
    const root = document.documentElement;
    root.classList.toggle('dark', theme === 'dark');
    root.style.colorScheme = theme;
    try {
        localStorage.setItem(THEME_KEY, theme);
    } catch (e) {
        // ignore storage failures (private mode)
    }
}

function currentTheme() {
    try {
        return localStorage.getItem(THEME_KEY) || 'dark';
    } catch (e) {
        return 'dark';
    }
}

document.addEventListener('alpine:init', () => {
    window.Alpine.store('theme', {
        current: currentTheme(),
        get isDark() {
            return this.current === 'dark';
        },
        toggle() {
            this.current = this.current === 'dark' ? 'light' : 'dark';
            applyTheme(this.current);
        },
    });

    window.Alpine.store('palette', {
        open: false,
        toggle() {
            this.open = !this.open;
        },
        show() {
            this.open = true;
        },
        hide() {
            this.open = false;
        },
    });

    window.Alpine.store('toasts', {
        items: [],
        push(toast) {
            const id = Date.now() + Math.floor(performance.now());
            const item = {
                id,
                type: toast.type || 'success',
                message: toast.message || '',
                title: toast.title || null,
            };
            this.items.push(item);
            setTimeout(() => this.dismiss(id), toast.duration || 4000);
        },
        dismiss(id) {
            this.items = this.items.filter((t) => t.id !== id);
        },
    });
});

// Kanban drag-and-drop (native HTML5 DnD, optimistic UI, persisted via Livewire).
document.addEventListener('alpine:init', () => {
    window.Alpine.data('board', () => ({
        draggingId: null,

        cardOf(e) {
            return e.target.closest('[data-task-id]');
        },

        onDragStart(e) {
            const card = this.cardOf(e);
            if (!card) return;
            this.draggingId = card.dataset.taskId;
            card.classList.add('dragging', 'opacity-40');
            e.dataTransfer.effectAllowed = 'move';
            try { e.dataTransfer.setData('text/plain', this.draggingId); } catch (err) {}
        },

        onDragEnd(e) {
            const card = this.cardOf(e);
            card?.classList.remove('dragging', 'opacity-40');
            this.$root.querySelectorAll('[data-dropzone]').forEach((z) => z.classList.remove('ring-2', 'ring-accent-line', 'bg-accent-soft/30'));
            this.draggingId = null;
        },

        afterElement(container, y) {
            const els = [...container.querySelectorAll('[data-task-id]:not(.dragging)')];
            return els.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) {
                    return { offset, element: child };
                }
                return closest;
            }, { offset: Number.NEGATIVE_INFINITY, element: null }).element;
        },

        onDragOver(e) {
            e.preventDefault();
            const zone = e.currentTarget;
            zone.classList.add('ring-2', 'ring-accent-line', 'bg-accent-soft/30');
            const container = zone.querySelector('[data-cards]');
            const dragging = container && document.querySelector('.dragging');
            if (!container || !dragging) return;
            const after = this.afterElement(container, e.clientY);
            if (after == null) {
                container.appendChild(dragging);
            } else {
                container.insertBefore(dragging, after);
            }
        },

        onDragLeave(e) {
            if (!e.currentTarget.contains(e.relatedTarget)) {
                e.currentTarget.classList.remove('ring-2', 'ring-accent-line', 'bg-accent-soft/30');
            }
        },

        onDrop(e, status) {
            e.preventDefault();
            const zone = e.currentTarget;
            zone.classList.remove('ring-2', 'ring-accent-line', 'bg-accent-soft/30');
            if (!this.draggingId) return;
            const container = zone.querySelector('[data-cards]');
            const ids = [...container.querySelectorAll('[data-task-id]')].map((el) => el.dataset.taskId);
            this.$wire.moveTask(this.draggingId, status, ids);
            this.draggingId = null;
        },
    }));
});

// Global ⌘K / Ctrl+K shortcut for the command palette.
document.addEventListener('keydown', (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault();
        if (window.Alpine && window.Alpine.store('palette')) {
            window.Alpine.store('palette').toggle();
        }
    }
});

// Bridge Livewire toast events to the Alpine toast store.
document.addEventListener('livewire:init', () => {
    window.Livewire.on('toast', (payload) => {
        const data = Array.isArray(payload) ? payload[0] : payload;
        if (window.Alpine && window.Alpine.store('toasts')) {
            window.Alpine.store('toasts').push(data || {});
        }
    });
});
