<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useSystemTheme } from '@/composables/useSystemTheme'
import type { InertiaPageProps } from '@/types/inertia'

const page = computed(() => usePage<InertiaPageProps<{ title?: string }>>())

const { revealStyle } = useSystemTheme()
</script>

<style scoped>
.logo {
  width: 72px;
  height: 72px;
  max-width: 72px;
  max-height: 72px;
}

/* Caps the form to a comfortable width and centers it; Vuetify's v-form has no width preset. */
.po-login {
  width: 100%;
  max-width: 400px;
  margin-inline: auto;
}
</style>

<template>
  <v-app :style="revealStyle">
    <po-head :title="page.props.title" />

    <v-main>
      <div class="d-flex flex-column align-center justify-center w-100 h-100">
        <v-img src="/images/logo.svg" class="logo mb-6" />

        <div class="po-login">
          <slot />
        </div>
      </div>
    </v-main>
  </v-app>
</template>
