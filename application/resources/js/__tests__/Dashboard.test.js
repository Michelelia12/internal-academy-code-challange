import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Dashboard from '../Pages/Dashboard.vue';

const mockPost = vi.fn();
const mockDelete = vi.fn();

vi.mock('@inertiajs/vue3', () => ({
    useForm: () => ({ post: mockPost }),
    usePage: () => ({ props: { auth: { user: { name: 'Test', is_admin: false } } } }),
    router: { delete: (...args) => mockDelete(...args) },
}));

const makeWorkshop = (overrides = {}) => ({
    id: 1,
    title: 'Vue Fundamentals',
    description: 'Learn Vue 3 from scratch.',
    starts_at: '2026-05-01',
    ends_at: '2026-05-02',
    capacity: 10,
    available_seats: 5,
    user_registration: null,
    ...overrides,
});

describe('Dashboard.vue', () => {
    beforeEach(() => {
        mockPost.mockClear();
        mockDelete.mockClear();
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

    it('shows Confirmed badge and Unregister button when user has a confirmed registration', () => {
        const wrapper = mount(Dashboard, {
            props: { workshops: [makeWorkshop({ user_registration: { status: 'confirmed' } })] },
        });

        expect(wrapper.text()).toContain('Confirmed');
        expect(wrapper.findAll('button').some(b => b.text() === 'Unregister')).toBe(true);
        expect(wrapper.findAll('button').some(b => b.text() === 'Register')).toBe(false);
        expect(wrapper.findAll('button').some(b => b.text() === 'Join Waiting List')).toBe(false);
    });

    it('shows Waiting List badge and Unregister button when user is on the waiting list', () => {
        const wrapper = mount(Dashboard, {
            props: { workshops: [makeWorkshop({ available_seats: 0, user_registration: { status: 'waiting' } })] },
        });

        expect(wrapper.text()).toContain('Waiting List');
        expect(wrapper.findAll('button').some(b => b.text() === 'Unregister')).toBe(true);
        expect(wrapper.findAll('button').some(b => b.text() === 'Register')).toBe(false);
        expect(wrapper.findAll('button').some(b => b.text() === 'Join Waiting List')).toBe(false);
    });

    it('calls router.delete with the correct URL when Unregister is clicked', async () => {
        const workshop = makeWorkshop({ id: 5, user_registration: { status: 'confirmed' } });
        const wrapper = mount(Dashboard, { props: { workshops: [workshop] } });

        await wrapper.find('button').trigger('click');

        expect(mockDelete).toHaveBeenCalledOnce();
        expect(mockDelete).toHaveBeenCalledWith('/workshops/5/registrations');
    });

    it('shows Register and Join Waiting List buttons when user_registration is null', () => {
        const withSeats = mount(Dashboard, {
            props: { workshops: [makeWorkshop({ user_registration: null, available_seats: 5 })] },
        });
        expect(withSeats.findAll('button').some(b => b.text() === 'Register')).toBe(true);
        expect(withSeats.findAll('button').some(b => b.text() === 'Unregister')).toBe(false);

        const noSeats = mount(Dashboard, {
            props: { workshops: [makeWorkshop({ user_registration: null, available_seats: 0 })] },
        });
        expect(noSeats.findAll('button').some(b => b.text() === 'Join Waiting List')).toBe(true);
        expect(noSeats.findAll('button').some(b => b.text() === 'Unregister')).toBe(false);
    });

    it('uses capacity as fallback for available_seats when null', () => {
        const wrapper = mount(Dashboard, {
            props: { workshops: [makeWorkshop({ available_seats: null, capacity: 8 })] },
        });

        expect(wrapper.text()).toContain('8 / 8');
        expect(wrapper.findAll('button').some(b => b.text() === 'Register')).toBe(true);
    });
});
