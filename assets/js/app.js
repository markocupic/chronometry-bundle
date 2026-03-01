import 'sortable-tablesort/dist/sortable.auto.min.js';
import {createApp, ref, reactive, onMounted} from 'vue';

window.chronometryApp = createApp({

    setup() {
        // Reactive state
        const isReady = ref(false);
        const isOnline = ref('');
        const requestToken = ref('');
        const modalId = ref(null);
        const currentTime = ref('');
        const runners = ref(null);
        const categories = ref(null);
        const searchNumber = ref('');
        const isListening = ref(false); // SpeechRecognition


        const modal = reactive({
            runnerIndex: null,
            runnerNumber: '',
            runnerFullname: '',
            runnerId: null,
            runnerIsFinisher: false,
            runnerdnf: false,
            lastChange: '',
            endTime: '',
        });

        const searchForm = reactive({
            showNumberDropdown: false,
            numberSuggests: [],
            showNameDropdown: false,
            nameSuggests: [],
        });

        const stats = reactive({
            total: 0,
            dispensed: 0,
            haveFinished: 0,
            running: 0,
            haveGivenUp: 0,
            runnersTotal: 0,
        });

        const getDataAll = () => {
            fetch(window.location.href + '?action=getDataAll', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    runners.value = data.runners;
                    Object.assign(stats, data.stats);
                    categories.value = data.categories;
                })
                .catch((error) => {
                    console.error('XHR-request failed!!!:', error);
                });
        };

        const openModal = (index) => {
            const runner = runners.value[index];

            speakNumber(runner.number + ' ' + runner.fullname);

            modal.runnerIndex = index;
            modal.runnerNumber = runner.number;
            modal.runnerFullname = runner.fullname;
            modal.runnerIsFinisher = runner.endtime !== '';
            modal.runnerHasNotice = runner.notice !== '';
            modal.runnerdnf = runner.dnf === 1;
            modal.runnerNotice = runner.notice.replace(/&\am\p;/g, '&');
            modal.runnerId = runner.id;

            const d = new Date(runner.tstamp * 1000);
            modal.lastChange = 'Letzte Änderung: ' + getFormatedTime(d);

            modal.endTime = runner.dnf === '1'
                ? ''
                : (runner.endtime || currentTime.value);

            document.getElementById('runnerDnfCtrl').checked = runner.dnf === '1';

            const modalElement = document.getElementById(modalId.value);

            // Event‑Handler nur einmal registrieren
            if (!modalElement.dataset.handlersAttached) {
                modalElement.dataset.handlersAttached = '1';

                modalElement.addEventListener('hidden.bs.modal', () => {
                    searchNumber.value = '';
                    document.querySelector('#searchName').value = '';

                    searchForm.numberSuggests = [];
                    searchForm.nameSuggests = [];
                    searchForm.showNumberDropdown = false;
                    searchForm.showNameDropdown = false;
                    getDataAll();
                    //document.querySelector('#searchNumber').focus();
                });

                modalElement.addEventListener('shown.bs.modal', () => {
                    document.querySelector('#endtimeCtrl').blur();
                });

                document.querySelector('#inputClear').addEventListener('click', () => {
                    modal.endTime = '';
                });
            }

            const bsModalWindow = bootstrap.Modal.getOrCreateInstance(modalElement, {
                keyboard: false
            });

            bsModalWindow.show();
        };

        const startSpeech = (inputSelector) => {
            // Chrome / Android unterstützt webkitSpeechRecognition
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

            if (!SpeechRecognition) {
                alert("Spracherkennung wird auf diesem Gerät nicht unterstützt.");
                return;
            }

            const recognition = new SpeechRecognition();
            recognition.lang = "de-DE";
            recognition.interimResults = false;

            recognition.onstart = () => {
                isListening.value = true;
            }

            recognition.onend = () => {
                isListening.value = false;
            }

            recognition.onresult = (event) => {
                const spoken = event.results[0][0].transcript;

                // Optional: Nur Zahlen extrahieren
                searchNumber.value = spoken.replace(/[^0-9]/g, '');
                if (searchNumber.value.length > 0) {
                    showNumberDropdownSuggest();
                    if (searchForm.numberSuggests.length === 1) {
                        const tr = document.querySelector("tr[data-number='" + searchNumber.value + "']");
                        openModal(tr.dataset.index);
                        // Scrollen (Vanilla JS)
                        window.scrollTo({
                            top: tr.offsetTop - 40,
                            behavior: 'smooth'
                        });
                    }
                }
            };

            recognition.start();
        };

        const speakNumber = (number) => {
            if (!number) return;

            const utterance = new SpeechSynthesisUtterance(number);
            utterance.lang = 'de-DE';
            speechSynthesis.speak(utterance);
        };

        const scrollToNumber = (event) => {
            const input = event.target;

            if (input.value > 1) {
                const tr = document.querySelector("tr[data-number='" + input.value + "']");

                if (tr) {
                    // Scrollen (Vanilla JS)
                    window.scrollTo({
                        top: tr.offsetTop - 40,
                        behavior: 'smooth'
                    });

                    const index = tr.dataset.index;
                    openModal(index);
                }
            }
        };

        const checkOnlineStatus = () => {
            fetch(window.location.href + '?action=checkOnlineStatus', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    isOnline.value = data.status === 'success';
                })
                .catch((error) => {
                    isOnline.value = false;
                });
        };

        const saveRow = (index) => {
            const runner = runners.value[index];
            const id = modal.runnerId;

            const endtime = document.querySelector('#endtimeCtrl').value;
            const dnf = document.querySelector('.modal #runnerDnfCtrl').checked ? 1 : '';

            const regex = /^(([0|1][0-9])|([2][0-3])):([0-5][0-9]):([0-5][0-9])$/;

            if (regex.test(endtime) || endtime === '') {

                const modalElement = document.getElementById(modalId.value);
                const bsModalWindow = bootstrap.Modal.getOrCreateInstance(modalElement, {
                    keyboard: false
                });
                bsModalWindow.hide();

                const form = new FormData();
                form.append('REQUEST_TOKEN', requestToken.value);
                form.append('id', id);
                form.append('index', index);
                form.append('endtime', endtime);
                form.append('dnf', dnf);

                fetch(window.location.href + '?action=saveRow', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                    },
                    body: form,
                })
                    .then(response => response.json())
                    .then(data => {
                        runners.value = data.runners;
                        Object.assign(stats, data.stats);
                        categories.value = data.categories;
                    })
                    .catch((error) => {
                        console.error('Upload error. Could not save data.', error);
                    });

            } else {
                alert('Invalid input format for "endtime": ' + endtime);
            }
        };

        const setTime = () => {
            let now = new Date();
            let h = now.getHours();
            let m = now.getMinutes();
            let s = now.getSeconds();
            if (h < 10) h = '0' + h;
            if (m < 10) m = '0' + m;
            if (s < 10) s = '0' + s;
            currentTime.value = h + ":" + m + ":" + s;
        };

        const setEndTimeFromCurrentTime = () => {
            if (!confirm('Soll die Endzeit die Endzeit wirklich neu gesetzt werden?')) {
                return;
            }
            const d = new Date();
            modal.endTime = getFormatedTime(d);
        };

        const clearEndTime = () => {
            if (!confirm('Wollen Sie die Endzeit wirklich löschen?')) {
                return;
            }
            modal.endTime = '';
        };

        const onInputNumber = (event) => {
            searchNumber.value = event.target.value.replace(/[^0-9]/g, '');
            showNumberDropdownSuggest();
        };

        const showNumberDropdownSuggest = async () => {

            if (searchNumber.value === '') {
                searchForm.numberSuggests = [];
                searchForm.showNumberDropdown = false;
                return;
            }

            document.querySelector('#searchName').value = '';
            searchForm.nameSuggests = [];
            searchForm.showNameDropdown = false;

            const rows = document.querySelectorAll('#startlistTable tbody tr');
            const regex = new RegExp('^' + searchNumber.value + '(.*)', 'i');

            searchForm.numberSuggests = [];

            for (const row of rows) {
                if (regex.test(row.getAttribute('data-number'))) {
                    const runner = {
                        index: row.getAttribute('data-index'),
                        number: row.getAttribute('data-number'),
                        fullname: row.getAttribute('data-fullname')
                    };
                    searchForm.numberSuggests.push(runner);
                    searchForm.showNumberDropdown = true;
                }
            }
        };

        const removeNumberDropdownSuggest = (event) => {
            const input = event.target;
            window.setTimeout(() => {
                input.value = '';
                searchForm.numberSuggests = [];
                searchForm.showNumberDropdown = false;
            }, 50);
        };

        const showNameDropdownSuggest = (event) => {
            const input = event.target;
            const value = input.value.trim();

            if (value === '') {
                searchForm.nameSuggests = [];
                searchForm.showNameDropdown = false;
                return;
            }

            searchNumber.value = '';
            searchForm.numberSuggests = [];
            searchForm.showNumberDropdown = false;

            const rows = document.querySelectorAll('#startlistTable tbody tr');
            const regex = new RegExp(value + '(.*)', 'i');

            const results = [];

            rows.forEach(row => {
                if (regex.test(row.dataset.fullname)) {
                    results.push({
                        index: row.dataset.index,
                        number: row.dataset.number,
                        fullname: row.dataset.fullname
                    });
                }
            });

            searchForm.nameSuggests = results;
            searchForm.showNameDropdown = results.length > 0;
        };

        const removeNameDropdownSuggest = (event) => {
            const input = event.target;
            window.setTimeout(function () {
                input.value = '';
                searchForm.nameSuggests = [];
                searchForm.showNameDropdown = false;
            }, 50);
        };

        const applyFilter = (event) => {
            const select = event.target;
            const filterCat = select.value;

            const rows = document.querySelectorAll('.startlist-table tbody tr');

            // Alle Zeilen wieder einblenden
            rows.forEach(row => row.classList.remove('d-none'));

            if (filterCat === '0') return;

            rows.forEach(row => {
                if (row.getAttribute('data-category') !== filterCat) {
                    row.classList.add('d-none');
                }
            });
        };

        const getFormatedTime = (d) => {
            const hours = d.getHours() < 10 ? '0' + d.getHours() : d.getHours();
            const minutes = d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes();
            const seconds = d.getSeconds() < 10 ? '0' + d.getSeconds() : d.getSeconds();
            return hours + ":" + minutes + ":" + seconds;
        };

        // Lifecycle
        onMounted(() => {
            requestToken.value = CHRONOMETRY.requestToken;
            modalId.value = CHRONOMETRY.modalId;

            window.setTimeout(() => {
                isReady.value = true;
                document.getElementById('chronometry-app').classList.add('is-ready');
            }, 2000);

            window.setInterval(() => {
                setTime();
            }, 1000);

            checkOnlineStatus();

            window.setInterval(() => {
                checkOnlineStatus();
            }, 15000);

            getDataAll();

            window.setInterval(() => {
                getDataAll();
            }, 60000);

            // This will activate the sortable table
            document.querySelector('#chronometry-app #startlistTable').classList.add('sortable');
        });

        return {
            isListening,
            isReady,
            isOnline,
            requestToken,
            modalId,
            currentTime,
            runners,
            categories,
            modal,
            searchForm,
            stats,
            getDataAll,
            openModal,
            searchNumber,
            startSpeech,
            scrollToNumber,
            checkOnlineStatus,
            saveRow,
            setTime,
            setEndTimeFromCurrentTime,
            clearEndTime,
            onInputNumber,
            showNumberDropdownSuggest,
            removeNumberDropdownSuggest,
            showNameDropdownSuggest,
            removeNameDropdownSuggest,
            applyFilter,
            getFormatedTime,
        };
    }
});

// Mount the app and expose the instance globally
window.chronometryApp.config.compilerOptions.delimiters = ['[[ ', ' ]]'];
window.chronometryApp.mount('#chronometry-app');
