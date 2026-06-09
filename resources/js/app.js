/**
 * Deskly front-end glue. Livewire 4 bundles Alpine, so we hook `alpine:init`
 * to register shared stores (theme, command palette, toasts).
 */

const THEME_KEY = 'deskly-theme';

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
        return localStorage.getItem(THEME_KEY) || 'light';
    } catch (e) {
        return 'light';
    }
}

// Re-apply the saved theme on every Livewire `wire:navigate` page swap.
//
// During SPA navigation Livewire copies the freshly-fetched page's <html>
// attributes onto the live document, and every page is server-rendered with
// the light default. The anti-flash <script> in <head> only runs on the
// first load (head scripts run once with wire:navigate), so without this a
// navigation would clobber a stored "dark" preference and snap back to
// light. The onSwap callback runs right after the new HTML is swapped in
// but before the browser paints, so the correction happens without flicker.
document.addEventListener('livewire:navigating', (event) => {
    const reapply = () => applyTheme(currentTheme());
    if (event.detail && typeof event.detail.onSwap === 'function') {
        event.detail.onSwap(reapply);
    } else {
        reapply();
    }
});

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
