import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Register from '../Pages/Auth/Register.vue';

const mockForm = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    errors: {},
    processing: false,
    post: vi.fn((url, opts) => opts?.onFinish?.()),
    reset: vi.fn(),
};

vi.mock('@inertiajs/vue3', () => ({
    useForm: () => mockForm,
}));

describe('Register.vue', () => {
    beforeEach(() => {
        mockForm.name = '';
        mockForm.email = '';
        mockForm.password = '';
        mockForm.password_confirmation = '';
        mockForm.errors = {};
        mockForm.processing = false;
        mockForm.post.mockClear();
        mockForm.reset.mockClear();
    });

    it('renders name, email, password and confirm-password inputs', () => {
        const wrapper = mount(Register);

        expect(wrapper.find('input#name').exists()).toBe(true);
        expect(wrapper.find('input#email').exists()).toBe(true);
        expect(wrapper.find('input#password').exists()).toBe(true);
        expect(wrapper.find('input#password_confirmation').exists()).toBe(true);
    });

    it('renders a submit button', () => {
        const wrapper = mount(Register);

        const button = wrapper.find('button[type="submit"]');
        expect(button.exists()).toBe(true);
        expect(button.text()).toBe('Create account');
    });

    it('calls form.post with /register on submit', async () => {
        const wrapper = mount(Register);

        await wrapper.find('form').trigger('submit');

        expect(mockForm.post).toHaveBeenCalledWith('/register', expect.any(Object));
    });

    it('disables the submit button while processing', () => {
        mockForm.processing = true;
        const wrapper = mount(Register);

        expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined();
    });

    it('displays a name error when present', () => {
        mockForm.errors = { name: 'The name field is required.' };
        const wrapper = mount(Register);

        expect(wrapper.text()).toContain('The name field is required.');
    });

    it('displays an email error when present', () => {
        mockForm.errors = { email: 'The email has already been taken.' };
        const wrapper = mount(Register);

        expect(wrapper.text()).toContain('The email has already been taken.');
    });

    it('displays a password error when present', () => {
        mockForm.errors = { password: 'The password must be at least 8 characters.' };
        const wrapper = mount(Register);

        expect(wrapper.text()).toContain('The password must be at least 8 characters.');
    });

    it('contains a link to the login page', () => {
        const wrapper = mount(Register);

        const link = wrapper.find('a[href="/login"]');
        expect(link.exists()).toBe(true);
    });

    it('updates form fields via v-model and calls onFinish after submit', async () => {
        const wrapper = mount(Register);

        await wrapper.find('input#name').setValue('Alice');
        await wrapper.find('input#email').setValue('a@b.com');
        await wrapper.find('input#password').setValue('secret');
        await wrapper.find('input#password_confirmation').setValue('secret');
        await wrapper.find('form').trigger('submit');

        expect(mockForm.reset).toHaveBeenCalledWith('password', 'password_confirmation');
    });
});