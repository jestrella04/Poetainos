<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import PoAdminTitle from './partials/PoAdminTitle.vue'
import type { InertiaPageProps } from '@/types/inertia'

defineOptions({
  layout: PoLayoutAdmin
})

const page = usePage<InertiaPageProps<{ counter: string }>>()
</script>

<template>
  <po-wrapper class="h-100 d-flex flex-column">
    <po-admin-title :title="$t('admin.analytics')" />

    <iframe :src="page.props.counter" class="w-100 flex-grow-1 border-0" />
  </po-wrapper>
</template>
