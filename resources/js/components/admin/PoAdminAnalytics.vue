<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'

defineOptions({
  layout: PoLayoutAdmin,
})

const page = computed(() => usePage())
</script>

<template>
  <po-wrapper class="h-100">
    <v-card-title>{{ $t('admin.analytics') }}</v-card-title>

    <iframe :src="page.props.counter" height="100%" />
  </po-wrapper>
</template>