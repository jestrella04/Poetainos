<script setup>
import { computed, inject } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = computed(() => usePage())
const helper = inject('helper')
const karma = page.value.props.author.karma
const label = helper.karmaLabel(karma)
</script>

<style scoped>
.karma-icon {
  top: 1rem;
  right: 1rem;
}
</style>

<template>
  <div class="karma-icon position-absolute">
    <v-tooltip location="bottom" max-width="400px">
      <template v-slot:activator="{ props }">
        <v-icon icon="fas fa-yin-yang" :color="label" v-bind="props"></v-icon>
      </template>

      <p>
        <span>{{ $t('main.karma-points', { karma: karma }) }}</span>
        <span v-if="'error' === label">&nbsp;{{ $t('main.karma-low') }}</span>
        <span v-else-if="'warning' === label">&nbsp;{{ $t('main.karma-mid') }}</span>
        <span v-else-if="'success' === label">&nbsp;{{ $t('main.karma-high') }}</span>
      </p>
    </v-tooltip>
  </div>
</template>
