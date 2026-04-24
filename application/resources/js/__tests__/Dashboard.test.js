import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Dashboard from '../Pages/Dashboard.vue';

const mockPost = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    useForm: () => ({ post: mockPost }),
}));

const makeWorkshop = (overrides = {}) => ({
    id: 1,
    title: 'Vue Fundamentals',
    description: 'Learn Vue 3 from scratch.',
    starts_at: '2026-05-01',
    ends_at: '2026-05-02',
    capacity: 10,
    available_seats: 5,
    ...overrides,
});

describe('Dashboard.vue', () => {
    beforeEach(() => {
        mockPost.mockClear();
    });

    it('renders workshop titles and descriptions', () => {
        const workshops = [
            makeWorkshop({ id: 1, title: 'Workshop A', description: 'Desc A' }),
            makeWorkshop({ id: 2, title: 'Workshop B', description: 'Desc B' }),
        ];
        const wrapper = mount(Dashboard, { props: { workshops } });

        expect(wrapper.text()).toContain('Workshop A');
        expect(wrapper.text()).toContain('Desc A');
        expect(wrapper.text()).toContain('Workshop B');
        expect(wrapper.text()).toContain('Desc B');
    });

    it('shows empty-state message when workshops list is empty', () => {
        const wrapper = mount(Dashboard, { props: { workshops: [] } });

        expect(wrapper.text()).toContain('No upcoming workshops available.');
    });

    it('shows Register button when available_seats > 0', () => {
        const wrapper = mount(Dashboard, {
            props: { workshops: [makeWorkshop({ available_seats: 3 })] },
        });

        const buttons = wrapper.findAll('button');
        expect(buttons.some(b => b.text() === 'Register')).toBe(true);
        expect(buttons.some(b => b.text() === 'Join Waiting List')).toBe(false);
    });

    it('shows Join Waiting List button when available_seats is 0', () => {
        const wrapper = mount(Dashboard, {
            props: { workshops: [makeWorkshop({ available_seats: 0 })] },
        });

        const buttons = wrapper.findAll('button');
        expect(buttons.some(b => b.text() === 'Join Waiting List')).toBe(true);
        expect(buttons.some(b => b.text() === 'Register')).toBe(false);
    });

    it('calls useForm().post with the correct URL when Register is clicked', async () => {
        const workshop = makeWorkshop({ id: 42, available_seats: 5 });
        const wrapper = mount(Dashboard, { props: { workshops: [workshop] } });

        await wrapper.find('button').trigger('click');

        expect(mockPost).toHaveBeenCalledOnce();
        expect(mockPost).toHaveBeenCalledWith('/workshops/42/registrations');
    });

    it('calls useForm().post with the correct URL when Join Waiting List is clicked', async () => {
        const workshop = makeWorkshop({ id: 7, available_seats: 0 });
        const wrapper = mount(Dashboard, { props: { workshops: [workshop] } });

        await wrapper.find('button').trigger('click');

        expect(mockPost).toHaveBeenCalledOnce();
        expect(mockPost).toHaveBeenCalledWith('/workshops/7/registrations');
    });

    it('uses capacity as fallback for available_seats when null', () => {
        const wrapper = mount(Dashboard, {
            props: { workshops: [makeWorkshop({ available_seats: null, capacity: 8 })] },
        });

        expect(wrapper.text()).toContain('8 / 8');
        expect(wrapper.findAll('button').some(b => b.text() === 'Register')).toBe(true);
    });
});
