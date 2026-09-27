<script setup lang="ts">
import { useSystemTheme } from '@/composables/useSystemTheme'
import { useFlashMessages } from '@/composables/useFlashMessages'
import PoAdminMenu from '../admin/PoAdminMenu.vue'

const { revealStyle } = useSystemTheme()

useFlashMessages()
</script>

<style>
/* Clears space for the fixed-width side nav (PoAdminMenu), which isn't part of Vuetify's layout system. */
.admin-wrapper {
  margin-left: 60px;
}

@media screen and (min-width: 960px) {
  .admin-wrapper {
    margin-left: 250px;
  }
}

iframe {
  border: 0 !important;
  border-radius: 5px !important;
  overflow: hidden;
}
</style>

<template>
  <v-app :style="revealStyle">
    <po-head />
    <po-snack-bar />

    <v-main>
      <po-admin-menu />

      <po-wrapper class="admin-wrapper pa-5 h-100">
        <v-card class="pa-5 h-100">
          <slot />
        </v-card>
      </po-wrapper>
    </v-main>
  </v-app>
</template>
