<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'

const page = computed(() => usePage())
</script>

<template>
  <v-row>
    <v-col cols="12" md="8">
      <po-writings-entry :data="page.props.writing" :likers="page.props.likers"></po-writings-entry>
    </v-col>

    <v-col cols="12" md="4">Sidebar</v-col>
  </v-row>
</template>