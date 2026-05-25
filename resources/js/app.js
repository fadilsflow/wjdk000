import './bootstrap';
import * as Turbo from '@hotwired/turbo';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
window.Turbo = Turbo;

Alpine.start();

// Re-dispatch DOMContentLoaded-style event for inline scripts after Turbo navigation
document.addEventListener('turbo:load', () => {
    window.dispatchEvent(new Event('turbo:page-ready'));
});
