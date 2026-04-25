import { ref } from 'vue';

const message = ref('');
const visible = ref(false);

export function useToast() {
    function show(msg) {
        message.value = msg;
        visible.value = true;
    }

    function hide() {
        visible.value = false;
    }

    return { message, visible, show, hide };
}
