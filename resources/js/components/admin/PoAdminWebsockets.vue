<script setup>
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'

defineOptions({
  layout: PoLayoutAdmin,
})
</script>

<template>
  <po-wrapper class="h-100">
    <v-card-title>{{ $t('admin.websockets') }}</v-card-title>

    <iframe src="/admin/websockets-dashboard" height="100%" />
  </po-wrapper>
</template>