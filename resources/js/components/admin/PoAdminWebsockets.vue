<script setup lang="ts">
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'

defineOptions({
  layout: PoLayoutAdmin
})
</script>

<template>
  <po-wrapper class="h-100 d-flex flex-column">
    <v-card-title>{{ $t('admin.websockets') }}</v-card-title>

    <iframe src="/admin/websockets-dashboard" class="w-100 flex-grow-1 border-0" />
  </po-wrapper>
</template>
