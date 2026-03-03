<template>
  <!-- Sidebar container -->
  <div id="sidebarContainer" class="offcanvas offcanvas-end show" data-bs-scroll="true" data-bs-backdrop="false" tabindex="-1" aria-labelledby="sidebarContainerLabel">
    <div class="offcanvas-header">
      <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="clock ms-4 me-4">{{ store.currentTime }}</div>

    <div class="offcanvas-body">
      <div id="toolContainer" class="accordion accordion-flush">
        <div class="accordion-item bg-dark">
          <h5 class="accordion-header bg-dark" id="headingOne">
            <button class="accordion-button btn btn-sm bg-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
              Finde
            </button>
          </h5>

          <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#toolContainer" aria-labelledby="headingOne">
            <div class="accordion-body my-4">
              <div id="searchForm">
                <!-- search for runners starting number -->
                <label for="searchNumber">Gehe zu Start-Nr.</label><br>

                <!-- speech input -->
                <div class="position-relative">
                  <input type="number" class="form-control form-control-lg pe-5" id="searchNumber" placeholder="123" autocomplete="off" :value="store.searchNumber" @input="onInputNumber" @keydown.enter="scrollToNumber">
                  <button class="speech-btn btn btn-link position-absolute end-0 top-50 translate-middle-y me-2 p-0" :class="{ listening: store.isListening }" @click="startSpeech('#searchNumber')">
                    <i class="fa-solid fa-microphone fa-2x"></i>
                  </button>
                </div>

                <div class="search-dropdown dropdown suggest" data-key="#">
                  <ul id="searchNumberDropdown" class="dropdown-menu" role="menu" v-if="store.searchForm.showNumberDropdown" v-bind:class="store.searchForm.showNumberDropdown ? 'show' : ''">
                    <li v-for="(runner, index) in store.searchForm.numberSuggests" v-bind:data-index="runner.index" @click="store.openModal(runner.index)" class=""><a href="#" @click.prevent="store.openModal(runner.index)" @click="removeNameDropdownSuggest">{{ runner.number }} {{ runner.fullname }}</a></li>
                  </ul>
                </div>

                <br> <br>

                <!-- search for runners name -->
                <label for="searchName">Suche nach Namen:</label>
                <input type="text" class="form-control form-control-lg" id="searchName" placeholder="Namen eingeben" autocomplete="off" @input="showNameDropdownSuggest">
                <div class="search-dropdown dropdown suggest" data-key="#">
                  <ul id="searchNameDropdown" class="dropdown-menu" role="menu" v-if="store.searchForm.showNameDropdown" v-bind:class="store.searchForm.showNameDropdown ? 'show' : ''">
                    <li v-for="(runner, index) in store.searchForm.nameSuggests" v-bind:data-index="runner.index"><a href="#" @click.prevent="store.openModal(runner.index)" @click="removeNameDropdownSuggest">{{ runner.number }} {{ runner.fullname }}</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="accordion-item bg-dark">
          <h5 class="accordion-header bg-dark" id="headingTwo">
            <button class="accordion-button btn btn-sm bg-dark btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
              Kategorien-Filter
            </button>
          </h5>
          <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#toolContainer" aria-labelledby="headingTwo">
            <div class="accordion-body my-4">
              <select class="form-control form-control-lg" id="filterSelect" @change="applyFilter">
                <option value="0">alle auswählen</option>
                <option v-for="(category, index) in store.categories" v-bind:value="category.id">{{ category.label }}</option>
              </select>
            </div>
          </div>
        </div>

        <div class="accordion-item bg-dark">
          <h5 class="accordion-header bg-dark" id="headingThree">
            <button class="accordion-button btn btn-sm bg-dark btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
              Statistik
            </button>
          </h5>
          <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#toolContainer" aria-labelledby="headingThree">
            <div class="accordion-body my-4">
              <ul class="p-0">
                <li class="total" data-text="Total">Gemeldet: {{ store.stats.total }}</li>
                <li class="runnersTotal" data-text="Gemeldet">Am Start: {{ store.stats.runnersTotal }}</li>
                <li class="dispensed" data-text="Dispensiert">Nicht am Start: {{ store.stats.dispensed }}</li>
                <li class="haveFinished" data-text="Im Ziel">Bereits im Ziel: {{ store.stats.haveFinished }}</li>
                <li class="running" data-text="Noch nicht im Ziel">Noch nicht im Ziel: {{ store.stats.running }}</li>
                <li class="haveGivenUp" data-text="Wettkampf aufgegeben">Wettkampf aufgegeben: {{ store.stats.haveGivenUp }}</li>
              </ul>
            </div>
          </div>
        </div>

        <div class="accordion-item bg-dark">
          <h5 class="accordion-header bg-dark" id="headingFour">
            <button class="accordion-button btn btn-sm bg-dark btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
              Downloads
            </button>
          </h5>
          <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#toolContainer" aria-labelledby="headingFour">
            <div class="accordion-body my-4">
              <form :action="envRequestUrl + '?action=printRankingList'" method="post">
                <input type="hidden" name="REQUEST_TOKEN" :value="store.requestToken">
                <select class="form-control form-control-lg mt-5" id="rankingListSelect" name="printRankingListCat">
                  <option v-for="(category, index) in store.categories" v-bind:value="category.id">{{ category.label }}</option>
                </select>
                <button type="submit" name="rankingListDownload" class="d-block mt-5 w-100 btn btn-lg btn-primary">Rangliste herunterladen</button>
                <button type="submit" name="eternalListOfTheBestDownload" class="d-block mt-5 w-100 btn btn-lg btn-primary">Ewigenbestenliste herunterladen</button>
              </form>

              <a :href="envRequestUrl + '?action=csvExport'" class="d-block mt-5 w-100 btn btn-lg btn-primary">CSV-Export</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useChronometryStore } from '../stores/chronometry';
import { inject } from 'vue';

const store = useChronometryStore();
const envRequestUrl = window.location.href.split('?')[0];

const onInputNumber = (event) => {
  store.searchNumber = event.target.value.replace(/[^0-9]/g, '');
  showNumberDropdownSuggest();
};

const showNumberDropdownSuggest = () => {
  if (store.searchNumber === '') {
    store.searchForm.numberSuggests = [];
    store.searchForm.showNumberDropdown = false;
    return;
  }

  document.querySelector('#searchName').value = '';
  store.searchForm.nameSuggests = [];
  store.searchForm.showNameDropdown = false;

  const rows = document.querySelectorAll('#startlistTable tbody tr');
  const regex = new RegExp('^' + store.searchNumber + '(.*)', 'i');

  store.searchForm.numberSuggests = [];

  for (const row of rows) {
    if (regex.test(row.getAttribute('data-number'))) {
      const runner = {
        index: row.getAttribute('data-index'),
        number: row.getAttribute('data-number'),
        fullname: row.getAttribute('data-fullname')
      };
      store.searchForm.numberSuggests.push(runner);
      store.searchForm.showNumberDropdown = true;
    }
  }
};

const scrollToNumber = (event) => {
  const input = event.target;
  if (input.value > 1) {
    const tr = document.querySelector("tr[data-number='" + input.value + "']");
    if (tr) {
      window.scrollTo({
        top: tr.offsetTop - 40,
        behavior: 'smooth'
      });
      store.openModal(tr.dataset.index);
    }
  }
};

const startSpeech = (inputSelector) => {
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SpeechRecognition) {
    alert("Spracherkennung wird auf diesem Gerät nicht unterstützt.");
    return;
  }
  const recognition = new SpeechRecognition();
  recognition.lang = "de-DE";
  recognition.interimResults = false;
  recognition.onstart = () => { store.isListening = true; }
  recognition.onend = () => { store.isListening = false; }
  recognition.onresult = (event) => {
    const spoken = event.results[0][0].transcript;
    store.searchNumber = spoken.replace(/[^0-9]/g, '');
    if (store.searchNumber.length > 0) {
      showNumberDropdownSuggest();
      if (store.searchForm.numberSuggests.length === 1) {
        const tr = document.querySelector("tr[data-number='" + store.searchNumber + "']");
        store.openModal(tr.dataset.index);
        window.scrollTo({
          top: tr.offsetTop - 40,
          behavior: 'smooth'
        });
      }
    }
  };
  recognition.start();
};

const showNameDropdownSuggest = (event) => {
  const input = event.target;
  const value = input.value.trim();
  if (value === '') {
    store.searchForm.nameSuggests = [];
    store.searchForm.showNameDropdown = false;
    return;
  }
  store.searchNumber = '';
  store.searchForm.numberSuggests = [];
  store.searchForm.showNumberDropdown = false;
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
  store.searchForm.nameSuggests = results;
  store.searchForm.showNameDropdown = results.length > 0;
};

const removeNameDropdownSuggest = (event) => {
  const input = document.querySelector('#searchName');
  window.setTimeout(function () {
    input.value = '';
    store.searchForm.nameSuggests = [];
    store.searchForm.showNameDropdown = false;
  }, 50);
};

const applyFilter = (event) => {
  const select = event.target;
  const filterCat = select.value;
  const rows = document.querySelectorAll('.startlist-table tbody tr');
  rows.forEach(row => row.classList.remove('d-none'));
  if (filterCat === '0') return;
  rows.forEach(row => {
    if (row.getAttribute('data-category') !== filterCat) {
      row.classList.add('d-none');
    }
  });
};
</script>
