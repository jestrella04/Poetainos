<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

const page = usePage<InertiaPageProps<{ counter: string }>>()
</script>

<template>
  <po-wrapper class="h-100">
    <v-card-title>{{ $t('admin.analytics') }}</v-card-title>

    <iframe :src="page.props.counter" height="100%" />
  </po-wrapper>
</template>
