<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoUsersEntry from './PoUsersEntry.vue'

const page = computed(() => usePage())
</script>

<template>
  <v-row>
    <v-col cols="12" md="8">
      <po-users-entry :data="page.props.user" />
    </v-col>

    <v-col cols="12" md="4">Sidebar</v-col>
  </v-row>
</template>