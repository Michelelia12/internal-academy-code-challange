import { mount } from '@vue/test-utils';
import { describe, it, expect, beforeEach, afterEach, vi } from 'vitest';
import { nextTick } from 'vue';
import ToastNotification from '../../Components/ToastNotification.vue';
import { useToast } from '../../composables/useToast.js';

describe('ToastNotification.vue', () => {
    let wrapper;

    beforeEach(() => {
        useToast().hide();
        wrapper = mount(ToastNotification);
    });

    afterEach(() => {
        wrapper.unmount();
        vi.useRealTimers();
    });

    it('renders nothing when the toast is hidden', () => {
        expect(wrapper.find('[role="alert"]').exists()).toBe(false);
    });

    it('renders the message when show() is called', async () => {
        useToast().show('Overlap error');
        await nextTick();
        expect(wrapper.find('[role="alert"]').exists()).toBe(true);
        expect(wrapper.text()).toContain('Overlap error');
    });

    it('hides the toast when the close button is clicked', async () => {
        useToast().show('Click to close');
        await nextTick();
        await wrapper.find('button').trigger('click');
        expect(wrapper.find('[role="alert"]').exists()).toBe(false);
    });

    it('auto-dismisses after 4 seconds', async () => {
        vi.useFakeTimers();
        useToast().show('Auto dismiss');
        await nextTick();
        expect(wrapper.find('[role="alert"]').exists()).toBe(true);
        vi.advanceTimersByTime(4000);
        await nextTick();
        expect(wrapper.find('[role="alert"]').exists()).toBe(false);
    });
});
