<template>
  <div v-if="!store.isReady" class="text-center align-middle text-light mt-5">
    <i class="fa-solid fa-sync fa-spin fa-3x"></i>
    <br>
    <span class="mt-5">...Loading...</span>
  </div>
</template>

<script setup>
import {useChronometryStore} from '../stores/chronometry';

const store = useChronometryStore();
</script>
