<template>
  <div class="table-container table-responsive">
    <table id="startlistTable" class="startlist-table table table-dark table-hover">
      <thead>
        <tr>
          <th class="text-center align-middle align-middle no-sort" data-field="status">Status</th>
          <th class="align-middle" data-field="number">Start-Nr.</th>
          <th class="align-middle" data-field="firstname">Vorname</th>
          <th class="align-middle" data-field="lastname">Nachname</th>
          <th class="align-middle" data-field="stufe">Klasse</th>
          <th class="align-middle" data-field="teachername">KLP</th>
          <th class="align-middle" data-field="starttime">Start</th>
          <th class="align-middle" data-field="endtime">Ziel</th>
          <th class="align-middle" data-field="rank">Rang</th>
          <th class="align-middle" data-field="runningtime">Laufzeit</th>
          <th class="text-center align-middle no-sort"></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(runner, index) in store.runners" :key="runner.id" :data-index="index" :id="runner.id" :data-number="runner.number" :data-fullname="runner.fullname" :data-category="runner.category" :data-runnerDnfRace="runner.dnf === 1 ? 'true' : 'false'" :data-runnerHasFinishedRace="runner.endtime != '' ? true : false">
          <td class="text-center align-middle number">
            <span><i class="fa-solid fa-circle runner-status-icon"></i></span>
          </td>
          <td class="align-middle number">
            <span role="button" @click="store.openModal(index)" title="Datensatz bearbeiten">{{ runner.number }}</span>
          </td>
          <td class="align-middle firstname"><span role="button" @click="store.openModal(index)" title="Datensatz bearbeiten">{{ runner.firstname }}</span></td>
          <td class="align-middle lastname"><span role="button" @click="store.openModal(index)" title="Datensatz bearbeiten">{{ runner.lastname }}</span></td>
          <td class="align-middle stufe">{{ runner.stufe }}</td>
          <td class="align-middle teachername">{{ runner.teachername }}</td>
          <td class="align-middle starttime" :data-sort="runner.starttimeUnix">{{ runner.starttime }}</td>
          <td class="align-middle endtime" :data-sort="runner.endtimeUnix"><span role="button" @click="store.openModal(index)" title="Datensatz bearbeiten">{{ runner.endtime }}</span></td>
          <td class="align-middle rank" :data-sort="runner.rank > 0 ? runner.rank : 999999999999999">{{ runner.rank > 0 ? runner.rank : 'd.n.f' }}</td>
          <td class="align-middle runningtime" :data-sort="runner.runningtimeUnix == 0 ? 999999999999999 : runner.runningtimeUnix"><span role="button" @click="store.openModal(index)" title="Datensatz bearbeiten">{{ runner.runningtime }}</span></td>
          <td class="text-center align-middle edit-button">
            <button @click="store.openModal(index)" title="Datensatz bearbeiten" class="btn btn-primary editButton"><i class="fa-regular fa-clock"></i></button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { useChronometryStore } from '../stores/chronometry';
import { onMounted } from 'vue';

const store = useChronometryStore();

onMounted(() => {
    // This will activate the sortable table
    // Wait a bit for the table to be rendered
    setTimeout(() => {
        const table = document.querySelector('#chronometry-app #startlistTable');

        if (table) {
            table.classList.add('sortable');
        }
    }, 500);
});
</script>
