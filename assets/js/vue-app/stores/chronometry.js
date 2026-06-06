import {defineStore} from 'pinia';
import {ref, reactive} from 'vue';

export const useChronometryStore = defineStore('chronometry', () => {
  // Shared State
  const isReady = ref(false);
  const isOnline = ref(false);
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
    runnerStatus: '',
    lastChange: '',
    endTime: '',
    runnerNotice: '',
    runnerHasNotice: false
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
    finishers: 0,
    unranked: 0,
    running: 0,
    dnf: 0,
    runnersTotal: 0,
  });

  // Actions
  const getFormatedTime = (d) => {
    const hh = d.getHours() < 10 ? '0' + d.getHours() : d.getHours();
    const mm = d.getMinutes() < 10 ? '0' + d.getMinutes() : d.getMinutes();
    const ss = d.getSeconds() < 10 ? '0' + d.getSeconds() : d.getSeconds();
    return hh + ":" + mm + ":" + ss;
  };

  const fetchAppData = () => {
    fetch(window.location.href + '?action=fetchAppData', {
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
    modal.runnerStatus = runner.status;
    modal.runnerNotice = runner.notice.replace(/&amp;/g, '&');
    modal.runnerId = runner.id;

    const d = new Date(runner.tstamp * 1000);
    modal.lastChange = getFormatedTime(d);

    modal.endTime = runner.status === 'dnf' || runner.status === 'notstarted'
        ? ''
        : (runner.endtime || currentTime.value);

    // Set the default status if none is set
    if (modal.endTime && !modal.runnerStatus) {
      modal.runnerStatus = 'finisher';
    }

    const modalElement = document.getElementById(modalId.value);

    // Wait for the next tick to ensure the modal is updated
    const bsModal = bootstrap.Modal.getOrCreateInstance(modalElement, {
      keyboard: true
    });

    bsModal.show();
  };

  const speakNumber = (number) => {
    if (!number) return;

    try {
      const utterance = new SpeechSynthesisUtterance(number);
      utterance.lang = 'de-DE';
      speechSynthesis.speak(utterance);
    } catch (error) {
      console.log('SpeechSynthesisUtterance is not supported in this browser.');
    }
  };

  const checkOnlineState = () => {
    fetch(window.location.href + '?action=checkOnlineState', {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })
        .then(response => response.json())
        .then(data => {
          isOnline.value = data.status === 'success';
        })
        .catch(() => {
          isOnline.value = false;
        });
  };

  const findRunnerById = (id) => {
    for (const runner of runners.value) {
      if (runner.id === id) {
        return runner;
      }
    }

    return null;
  };

  return {
    isReady,
    isOnline,
    requestToken,
    modalId,
    currentTime,
    runners,
    categories,
    searchNumber,
    isListening,
    modal,
    searchForm,
    stats,
    getFormatedTime,
    fetchAppData,
    openModal,
    speakNumber,
    checkOnlineState,
    findRunnerById,
  };
});
