<script setup>
import { computed, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'

defineOptions({
  layout: PoLayoutAdmin,
})

const page = computed(() => usePage())
const tab = ref(null)
</script>

<template>
  <po-wrapper class="h-100">
    <v-card-title>{{ $t('admin.tools') }}</v-card-title>

    <v-tabs v-model="tab" class="mb-5">
      <v-tab value="info">{{ $t('admin.php-info') }}</v-tab>
      <v-tab value="log">{{ $t('admin.log-viewer') }}</v-tab>
    </v-tabs>

    <v-window v-model="tab" class="h-100">
      <v-window-item value="info" class="h-100">
        <iframe :srcdoc="page.props.info" frameborder="0" style="height: 100%; width: 100%;"></iframe>
      </v-window-item>

      <v-window-item value="log" class="h-100">
        <v-textarea v-model="page.props.log" :label="$t('admin.log')" rows="20" readonly></v-textarea>
      </v-window-item>
    </v-window>
  </po-wrapper>
</template>