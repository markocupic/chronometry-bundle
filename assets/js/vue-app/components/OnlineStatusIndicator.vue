<template>
  <div v-if="!store.isOnline" id="onlineStatusInfoBox" class="m-b-4 alert alert-warning" role="alert">
    Im Moment kann keine Verbindung zum Server aufgebaut werden. Bitte überprüfen Sie die Verbindung.
  </div>
</template>

<script setup>
import {useChronometryStore} from '../stores/chronometry';

const store = useChronometryStore();
</script>
