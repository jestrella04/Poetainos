<script setup lang="ts">
import { useSystemTheme } from '@/composables/useSystemTheme'
import { useFlashMessages } from '@/composables/useFlashMessages'
import PoAdminMenu from '../admin/PoAdminMenu.vue'

const { revealStyle } = useSystemTheme()

useFlashMessages()
</script>

<style scoped>
.v-main {
  padding-top: 0 !important;
}
</style>

<template>
  <v-app :style="revealStyle">
    <po-head />
    <po-snack-bar />

    <v-main>
      <po-admin-menu />

      <po-wrapper class="admin-wrapper px-8 h-100">
        <slot />
      </po-wrapper>
    </v-main>
  </v-app>
</template>
