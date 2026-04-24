import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Index from '../../Pages/Workshops/Index.vue';

const mocks = vi.hoisted(() => {
    const mockPost = vi.fn();
    const mockPut = vi.fn();
    const mockDelete = vi.fn();
    let callCount = 0;
    let createErrors = {};
    let editErrors = {};

    const useForm = (initial) => {
        const isCreate = callCount % 2 === 0;
        callCount++;
        return isCreate
            ? { ...initial, post: mockPost, reset: vi.fn(), errors: createErrors, processing: false }
            : { ...initial, put: mockPut, reset: vi.fn(), errors: editErrors, processing: false };
    };

    return {
        useForm,
        mockPost,
        mockPut,
        mockDelete,
        setCreateErrors: (e) => { createErrors = e; },
        setEditErrors: (e) => { editErrors = e; },
        reset() {
            callCount = 0;
            createErrors = {};
            editErrors = {};
            mockPost.mockReset();
            mockPut.mockReset();
            mockDelete.mockReset();
        },
    };
});

vi.mock('@inertiajs/vue3', () => ({
    useForm: mocks.useForm,
    router: { delete: mocks.mockDelete },
}));

const makeWorkshop = (overrides = {}) => ({
    id: 1,
    title: 'Vue Fundamentals',
    description: 'Intro to Vue 3',
    starts_at: '2026-05-01 09:00',
    ends_at: '2026-05-01 17:00',
    capacity: 10,
    ...overrides,
});

describe('Workshops/Index.vue', () => {
    beforeEach(() => {
        mocks.reset();
        vi.unstubAllGlobals();
    });

    it('renders the create form', () => {
        const wrapper = mount(Index, { props: { workshops: [] } });

        expect(wrapper.text()).toContain('New Workshop');
        expect(wrapper.find('form').exists()).toBe(true);
    });

    it('calls createForm.post with the correct URL on submit and invokes onSuccess', async () => {
        mocks.mockPost.mockImplementationOnce((_url, { onSuccess }) => onSuccess());
        const wrapper = mount(Index, { props: { workshops: [] } });

        await wrapper.find('input[type="text"]').setValue('Test Workshop');
        await wrapper.find('input[type="number"]').setValue('10');
        await wrapper.find('textarea').setValue('A description');
        const dateInputs = wrapper.findAll('input[type="datetime-local"]');
        await dateInputs[0].setValue('2026-06-01T09:00');
        await dateInputs[1].setValue('2026-06-01T17:00');
        await wrapper.find('form').trigger('submit');

        expect(mocks.mockPost).toHaveBeenCalledWith('/workshops', expect.objectContaining({ onSuccess: expect.any(Function) }));
    });

    it('shows create-form validation errors', () => {
        mocks.setCreateErrors({
            title: 'Title is required',
            capacity: 'Capacity is required',
            description: 'Description is required',
            starts_at: 'Start date is required',
            ends_at: 'End date is required',
        });
        const wrapper = mount(Index, { props: { workshops: [] } });

        expect(wrapper.text()).toContain('Title is required');
        expect(wrapper.text()).toContain('Capacity is required');
        expect(wrapper.text()).toContain('Description is required');
        expect(wrapper.text()).toContain('Start date is required');
        expect(wrapper.text()).toContain('End date is required');
    });

    it('shows empty-state message when workshops list is empty', () => {
        const wrapper = mount(Index, { props: { workshops: [] } });

        expect(wrapper.text()).toContain('No workshops yet.');
    });

    it('renders workshop data in table rows', () => {
        const workshops = [
            makeWorkshop({ id: 1, title: 'Workshop A' }),
            makeWorkshop({ id: 2, title: 'Workshop B' }),
        ];
        const wrapper = mount(Index, { props: { workshops } });

        expect(wrapper.text()).toContain('Workshop A');
        expect(wrapper.text()).toContain('Workshop B');
    });

    it('clicking Edit shows the edit row with pre-filled inputs', async () => {
        const workshop = makeWorkshop({ id: 5, title: 'My Workshop', capacity: 20 });
        const wrapper = mount(Index, { props: { workshops: [workshop] } });

        await wrapper.findAll('button').find(b => b.text() === 'Edit').trigger('click');

        expect(wrapper.find('input[placeholder="Title"]').element.value).toBe('My Workshop');
        expect(wrapper.find('input[placeholder="Capacity"]').element.value).toBe('20');
    });

    it('handles null dates in startEdit using empty-string fallback', async () => {
        const workshop = makeWorkshop({ id: 6, starts_at: null, ends_at: null });
        const wrapper = mount(Index, { props: { workshops: [workshop] } });

        await wrapper.findAll('button').find(b => b.text() === 'Edit').trigger('click');

        expect(wrapper.find('input[placeholder="Title"]').exists()).toBe(true);
    });

    it('shows edit-form validation errors', async () => {
        mocks.setEditErrors({
            title: 'Title required',
            capacity: 'Capacity required',
            description: 'Description required',
            starts_at: 'Start required',
            ends_at: 'End required',
        });
        const workshop = makeWorkshop({ id: 3 });
        const wrapper = mount(Index, { props: { workshops: [workshop] } });

        await wrapper.findAll('button').find(b => b.text() === 'Edit').trigger('click');

        expect(wrapper.text()).toContain('Title required');
        expect(wrapper.text()).toContain('Capacity required');
        expect(wrapper.text()).toContain('Description required');
        expect(wrapper.text()).toContain('Start required');
        expect(wrapper.text()).toContain('End required');
    });

    it('submitting the edit form calls editForm.put with the correct URL and invokes onSuccess', async () => {
        mocks.mockPut.mockImplementationOnce((_url, { onSuccess }) => onSuccess());
        const workshop = makeWorkshop({ id: 7 });
        const wrapper = mount(Index, { props: { workshops: [workshop] } });

        await wrapper.findAll('button').find(b => b.text() === 'Edit').trigger('click');

        await wrapper.find('input[placeholder="Title"]').setValue('Updated Title');
        await wrapper.find('input[placeholder="Capacity"]').setValue('15');
        await wrapper.find('textarea[placeholder="Description"]').setValue('Updated desc');
        const dateInputs = wrapper.findAll('input[type="datetime-local"]');
        await dateInputs[2].setValue('2026-07-01T09:00');
        await dateInputs[3].setValue('2026-07-01T17:00');
        await wrapper.findAll('form')[1].trigger('submit');

        expect(mocks.mockPut).toHaveBeenCalledWith('/workshops/7', expect.objectContaining({ onSuccess: expect.any(Function) }));
    });

    it('clicking Cancel exits edit mode', async () => {
        const workshop = makeWorkshop({ id: 3 });
        const wrapper = mount(Index, { props: { workshops: [workshop] } });

        await wrapper.findAll('button').find(b => b.text() === 'Edit').trigger('click');
        expect(wrapper.findAll('button').some(b => b.text() === 'Cancel')).toBe(true);

        await wrapper.findAll('button').find(b => b.text() === 'Cancel').trigger('click');

        expect(wrapper.findAll('button').some(b => b.text() === 'Edit')).toBe(true);
        expect(wrapper.findAll('button').some(b => b.text() === 'Cancel')).toBe(false);
    });

    it('calls router.delete when confirm is accepted', async () => {
        vi.stubGlobal('confirm', vi.fn().mockReturnValue(true));
        const workshop = makeWorkshop({ id: 99, title: 'To Delete' });
        const wrapper = mount(Index, { props: { workshops: [workshop] } });

        await wrapper.findAll('button').find(b => b.text() === 'Delete').trigger('click');

        expect(window.confirm).toHaveBeenCalledWith('Delete "To Delete"?');
        expect(mocks.mockDelete).toHaveBeenCalledWith('/workshops/99');
    });

    it('does not call router.delete when confirm is cancelled', async () => {
        vi.stubGlobal('confirm', vi.fn().mockReturnValue(false));
        const workshop = makeWorkshop({ id: 99 });
        const wrapper = mount(Index, { props: { workshops: [workshop] } });

        await wrapper.findAll('button').find(b => b.text() === 'Delete').trigger('click');

        expect(mocks.mockDelete).not.toHaveBeenCalled();
    });
});
