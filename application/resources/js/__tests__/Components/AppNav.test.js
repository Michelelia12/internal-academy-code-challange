import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import AppNav from '../../Components/AppNav.vue';

const mocks = vi.hoisted(() => {
    const mockPost = vi.fn();
    let currentUser = { name: 'Jane Employee', is_admin: false };

    return {
        mockPost,
        setUser: (user) => { currentUser = user; },
        usePage: () => ({ props: { auth: { user: currentUser } } }),
        reset() {
            currentUser = { name: 'Jane Employee', is_admin: false };
            mockPost.mockReset();
        },
    };
});

vi.mock('@inertiajs/vue3', () => ({
    usePage: mocks.usePage,
    router: { post: mocks.mockPost },
}));

describe('AppNav.vue', () => {
    beforeEach(() => mocks.reset());

    it('renders Dashboard link for all users', () => {
        const wrapper = mount(AppNav);

        expect(wrapper.text()).toContain('Dashboard');
    });

    it('renders Logout button for all users', () => {
        const wrapper = mount(AppNav);

        expect(wrapper.findAll('button').some(b => b.text() === 'Logout')).toBe(true);
    });

    it('does not render Workshops or Statistics links for non-admin users', () => {
        const wrapper = mount(AppNav);

        expect(wrapper.text()).not.toContain('Workshops');
        expect(wrapper.text()).not.toContain('Statistics');
    });

    it('renders Workshops and Statistics links for admin users', () => {
        mocks.setUser({ name: 'Admin User', is_admin: true });
        const wrapper = mount(AppNav);

        expect(wrapper.text()).toContain('Workshops');
        expect(wrapper.text()).toContain('Statistics');
    });

    it('clicking Logout calls router.post with the logout URL', async () => {
        const wrapper = mount(AppNav);

        await wrapper.find('button').trigger('click');

        expect(mocks.mockPost).toHaveBeenCalledWith('/logout');
    });
});
