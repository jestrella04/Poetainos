<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = computed(() => usePage())
</script>

<template>
  <v-footer :elevation="2">
    <div class="d-flex align-center justify-space-around w-100 pa-2 ga-2 text-caption text-center">
      <div>&copy; 2020 {{ page.props.site.name }}</div>

      <div v-if="$helper.isEmpty(relatedApps)" class="d-inline-flex ga-3">
        <template v-for="(app, store) in page.props.site.stores" :key="app">
          <po-button v-if="'' !== app.value" :href="app.value" :prepend-icon="app.icon" color="secondary" size="x-small">
            {{ store }}
          </po-button>
        </template>
      </div>

      <div class="d-inline-flex ga-3">
        <template v-for="(user, social) in page.props.site.social" :key="social">
          <po-button icon color="primary" size="x-small" :href="$helper.socialLink(user.value, social)"
            :title="$t('main.follow-on', { app: social })">
            <v-icon :icon="$helper.socialIcon()[social]"></v-icon>
          </po-button>
        </template>
      </div>
    </div>
  </v-footer>
</template>