import 'sortable-tablesort/dist/sortable.auto.min.js';
import { createApp, onMounted } from 'vue';
import { createPinia } from 'pinia';
import { useChronometryStore } from './stores/chronometry';

// Components
import LoadingSpinner from './components/LoadingSpinner.vue';
import OnlineStatusIndicator from './components/OnlineStatusIndicator.vue';
import Sidepanel from './components/Sidepanel.vue';
import RunnersTable from './components/RunnersTable.vue';
import Modal from './components/Modal.vue';

const pinia = createPinia();

window.chronometryApp = createApp({
    components: {
        LoadingSpinner,
        OnlineStatusIndicator,
        Sidepanel,
        RunnersTable,
        Modal,
    },

    setup() {
        const store = useChronometryStore();

        const setTime = () => {
            let now = new Date();
            let h = now.getHours();
            let m = now.getMinutes();
            let s = now.getSeconds();
            if (h < 10) h = '0' + h;
            if (m < 10) m = '0' + m;
            if (s < 10) s = '0' + s;
            store.currentTime = h + ":" + m + ":" + s;
        };

        // Lifecycle
        onMounted(() => {
            store.$patch({
                requestToken: CHRONOMETRY.requestToken,
                modalId: CHRONOMETRY.modalId
            });

            window.setTimeout(() => {
                store.isReady = true;
                document.getElementById('chronometry-app').classList.add('is-ready');
            }, 2000);

            window.setInterval(() => {
                setTime();
            }, 1000);

            store.checkIsOnline();

            window.setInterval(() => {
                store.checkIsOnline();
            }, 15000);

            store.fetchAppData();

            window.setInterval(() => {
                store.fetchAppData();
            }, 15000);
        });

        return {
            store
        };
    }
});

// Mount the app and expose the instance globally
window.chronometryApp.use(pinia);
window.chronometryApp.config.compilerOptions.delimiters = ['[[ ', ' ]]'];
window.chronometryApp.mount('#chronometry-app');
