<template>
  <!-- Modal window -->
  <div id="chronometryModal" class="modal fade" :class="store.modal.runnerIsFinisher ? 'runner-is-finisher' : ''">
    <div class="modal-dialog modal-xl modal-fullscreen-sm-down" role="document">
      <div class="modal-content bg-dark">
        <div class="modal-header">
          <h4 class="modal-title text-light">Startnr. {{ store.modal.runnerNumber }}</h4>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <h1 class="mb-4 text-uppercase text-red">{{ store.modal.runnerFullname }}</h1>

          <div id="infoRunnerHasFinished" class="info-runner-has-finished mb-3 text-green" v-if="store.modal.runnerIsFinisher">
            <i class="fa-solid fa-check text-green blinking-runner-has-finished me-3"></i>
            <span class="blinking-runner-has-finished">Athlet bereits im Ziel!</span>
          </div>

          <div class="modal-endtime input-group mb-3">
            <button class="btn btn-lg btn-primary btnGetTime" @click.stop="setEndTimeFromCurrentTime" title="Zielzeit setzen" type="button">
              <i class="large-icon fa-solid fa-stopwatch"></i>
            </button>
            <input type="text" class="form-control" id="endtimeCtrl" maxlength="8" v-model="store.modal.endTime" @keydown.enter="saveRow(store.modal.runnerIndex)" placeholder="00:00:00"/>
            <button id="inputClear" class="btn btn-lg btn-secondary btn-danger" @click.stop="clearEndTime()" title="Zeit zurücksetzen" type="button">
              <i class="large-icon fa fa-times-circle"></i>
            </button>
          </div>

          <div class="modal-dnf form-check mt-5">
            <input class="form-check-input" type="checkbox" value="1" id="runnerDnfCtrl" :checked="store.modal.runnerDnf === true">
            <label class="form-check-label text-light" for="runnerDnfCtrl">
              Wettkampf aufgegeben
            </label>
          </div>

          <div v-if="store.modal.runnerHasNotice" class="mt-5 runnerNotice alert alert-info">
            <span v-html="store.modal.runnerNotice"></span>
          </div>

          <div id="lastChange" class="mt-3 text-right lastChange text-light">Letzte Änderung: {{ store.modal.lastChange }}</div>
          <div id="clockDisplay" class="mt-3 clockStyle text-light">{{ store.currentTime }}</div>
        </div>

        <div class="modal-footer">
          <a :href="certificateUrl" title="Zertifikat drucken" class="btn btn-lg btn-success">
            <i class="me-2 fa-solid fa-trophy"></i>Zertif.
          </a>
          <button id="saveChanges" @click="saveRow(store.modal.runnerIndex)" type="button" class="btn btn-lg btn-primary">
            <i class="me-2 fa-solid fa-save"></i>Save
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useChronometryStore } from '../stores/chronometry';
import { computed, onMounted } from 'vue';

const store = useChronometryStore();

const url = window.location.href.split('?')[0];

const certificateUrl = computed(() => `${url}?action=printCertificate&id=${store.modal.runnerId}`);

const setEndTimeFromCurrentTime = () => {
  if (!confirm('Soll die Endzeit wirklich neu gesetzt werden?')) return;
  if (!confirm('Ganz sicher? Aktion kann nicht rückgängig gemacht werden?')) return;
  store.modal.endTime = store.getFormatedTime(new Date());
};

const clearEndTime = () => {
  if (!confirm('Wollen Sie die Endzeit wirklich löschen?')) return;
  if (!confirm('Ganz sicher? Aktion kann nicht rückgängig gemacht werden?')) return;
  store.modal.endTime = '';
};

const saveRow = (index) => {
  const id = store.modal.runnerId;
  const endtime = document.querySelector('#endtimeCtrl').value;
  const dnf = document.querySelector('.modal #runnerDnfCtrl').checked ? 1 : '';

  const regex = /^(([0|1][0-9])|([2][0-3])):([0-5][0-9]):([0-5][0-9])$/;

  if (regex.test(endtime) || endtime === '') {
    const modalElement = document.getElementById(store.modalId);
    const bsModal = bootstrap.Modal.getOrCreateInstance(modalElement);
    bsModal.hide();

    const form = new FormData();
    form.append('REQUEST_TOKEN', store.requestToken);
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
        store.$patch({
          runners: data.runners,
          categories: data.categories,
          stats: data.stats
        });
      })
      .catch((error) => {
        console.error('Upload error. Could not save data.', error);
      });
  } else {
    alert('Invalid input format for "endtime": ' + endtime);
  }
};

onMounted(() => {
    const modalElement = document.getElementById('chronometryModal');
    if (modalElement) {
        modalElement.addEventListener('hidden.bs.modal', () => {
            store.searchNumber = '';
            document.querySelector('#searchName').value = '';
            store.searchForm.numberSuggests = [];
            store.searchForm.nameSuggests = [];
            store.searchForm.showNumberDropdown = false;
            store.searchForm.showNameDropdown = false;
            store.fetchAppData();
        });
    }
});
</script>
