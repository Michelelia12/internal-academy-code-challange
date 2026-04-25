import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Statistics from '../../Pages/Admin/Statistics.vue';

const mocks = vi.hoisted(() => {
    const mockReload = vi.fn();
    const mockListen = vi.fn();
    const mockChannel = vi.fn(() => ({ listen: mockListen }));
    const mockLeaveChannel = vi.fn();

    return { mockReload, mockListen, mockChannel, mockLeaveChannel };
});

vi.mock('@inertiajs/vue3', () => ({
    router: { reload: mocks.mockReload },
    usePage: () => ({ props: { auth: { user: { name: 'Test', is_admin: false } } } }),
}));

describe('Admin/Statistics.vue', () => {
    beforeEach(() => {
        vi.unstubAllGlobals();
        vi.stubGlobal('Echo', {
            channel: mocks.mockChannel,
            leaveChannel: mocks.mockLeaveChannel,
        });
        mocks.mockReload.mockClear();
        mocks.mockListen.mockClear();
        mocks.mockChannel.mockClear();
        mocks.mockLeaveChannel.mockClear();
    });

    it('renders total_count', () => {
        const wrapper = mount(Statistics, {
            props: { total_count: 42, most_popular: null },
        });

        expect(wrapper.text()).toContain('42');
    });

    it('renders most-popular workshop title and date', () => {
        const wrapper = mount(Statistics, {
            props: {
                total_count: 5,
                most_popular: { title: 'Vue Advanced', starts_at: '2026-06-01 09:00' },
            },
        });

        expect(wrapper.text()).toContain('Vue Advanced');
        expect(wrapper.text()).toContain('2026-06-01 09:00');
    });

    it('shows fallback text when most_popular is null', () => {
        const wrapper = mount(Statistics, {
            props: { total_count: 0, most_popular: null },
        });

        expect(wrapper.text()).toContain('No registrations yet.');
    });

    it('joins the Echo academy channel on mount', () => {
        mount(Statistics, { props: { total_count: 0, most_popular: null } });

        expect(mocks.mockChannel).toHaveBeenCalledWith('academy');
        expect(mocks.mockListen).toHaveBeenCalledWith('RegistrationUpdated', expect.any(Function));
    });

    it('leaves the Echo academy channel on unmount', () => {
        const wrapper = mount(Statistics, { props: { total_count: 0, most_popular: null } });

        wrapper.unmount();

        expect(mocks.mockLeaveChannel).toHaveBeenCalledWith('academy');
    });

    it('does not throw on mount or unmount when window.Echo is not available', () => {
        vi.unstubAllGlobals();

        const wrapper = mount(Statistics, { props: { total_count: 0, most_popular: null } });
        wrapper.unmount();

        expect(mocks.mockChannel).not.toHaveBeenCalled();
    });

    it('RegistrationUpdated callback calls router.reload', () => {
        mount(Statistics, { props: { total_count: 0, most_popular: null } });

        const callback = mocks.mockListen.mock.calls[0][1];
        callback();

        expect(mocks.mockReload).toHaveBeenCalledWith({
            only: ['most_popular', 'total_count'],
        });
    });
});
