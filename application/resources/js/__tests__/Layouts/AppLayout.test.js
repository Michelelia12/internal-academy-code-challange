import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import AppLayout from '../../Layouts/AppLayout.vue';

vi.mock('@inertiajs/vue3', () => ({
    usePage: () => ({ props: { auth: { user: { name: 'Test User', is_admin: false } } } }),
    router: { post: vi.fn() },
}));

describe('AppLayout.vue', () => {
    it('renders AppNav and slot content', () => {
        const wrapper = mount(AppLayout, {
            slots: { default: '<p>Page content</p>' },
        });

        expect(wrapper.text()).toContain('Dashboard');
        expect(wrapper.text()).toContain('Page content');
    });
});
