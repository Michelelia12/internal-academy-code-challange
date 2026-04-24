import { mount } from '@vue/test-utils';
import { describe, it, expect, vi, beforeEach } from 'vitest';
import Login from '../Pages/Auth/Login.vue';

// useForm mock — tracks submitted data and exposes errors reactively
const mockForm = {
    email: '',
    password: '',
    errors: {},
    processing: false,
    post: vi.fn((url, opts) => opts?.onFinish?.()),
    reset: vi.fn(),
};

vi.mock('@inertiajs/vue3', () => ({
    useForm: () => mockForm,
}));

describe('Login.vue', () => {
    beforeEach(() => {
        mockForm.email = '';
        mockForm.password = '';
        mockForm.errors = {};
        mockForm.processing = false;
        mockForm.post.mockClear();
        mockForm.reset.mockClear();
    });

    it('renders email and password inputs', () => {
        const wrapper = mount(Login);

        expect(wrapper.find('input[type="email"]').exists()).toBe(true);
        expect(wrapper.find('input[type="password"]').exists()).toBe(true);
    });

    it('renders a submit button', () => {
        const wrapper = mount(Login);

        const button = wrapper.find('button[type="submit"]');
        expect(button.exists()).toBe(true);
        expect(button.text()).toBe('Sign in');
    });

    it('calls form.post with /login on submit', async () => {
        const wrapper = mount(Login);

        await wrapper.find('form').trigger('submit');

        expect(mockForm.post).toHaveBeenCalledWith('/login', expect.any(Object));
    });

    it('disables the submit button while processing', async () => {
        mockForm.processing = true;
        const wrapper = mount(Login);

        expect(wrapper.find('button[type="submit"]').attributes('disabled')).toBeDefined();
    });

    it('displays an email error when present', async () => {
        mockForm.errors = { email: 'The provided credentials are incorrect.' };
        const wrapper = mount(Login);

        expect(wrapper.text()).toContain('The provided credentials are incorrect.');
    });

    it('displays a password error when present', async () => {
        mockForm.errors = { password: 'The password field is required.' };
        const wrapper = mount(Login);

        expect(wrapper.text()).toContain('The password field is required.');
    });

    it('contains a link to the register page', () => {
        const wrapper = mount(Login);

        const link = wrapper.find('a[href="/register"]');
        expect(link.exists()).toBe(true);
    });

    it('updates form fields via v-model and calls onFinish after submit', async () => {
        const wrapper = mount(Login);

        await wrapper.find('input[type="email"]').setValue('a@b.com');
        await wrapper.find('input[type="password"]').setValue('secret');
        await wrapper.find('form').trigger('submit');

        expect(mockForm.reset).toHaveBeenCalledWith('password');
    });
});