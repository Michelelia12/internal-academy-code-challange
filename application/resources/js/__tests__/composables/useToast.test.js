import { describe, it, expect, beforeEach } from 'vitest';
import { useToast } from '../../composables/useToast.js';

describe('useToast', () => {
    beforeEach(() => {
        useToast().hide();
    });

    it('starts hidden', () => {
        const { visible } = useToast();
        expect(visible.value).toBe(false);
    });

    it('show() sets message and makes toast visible', () => {
        const { show, message, visible } = useToast();
        show('Something went wrong');
        expect(message.value).toBe('Something went wrong');
        expect(visible.value).toBe(true);
    });

    it('hide() hides the toast', () => {
        const { show, hide, visible } = useToast();
        show('Test message');
        hide();
        expect(visible.value).toBe(false);
    });
});
