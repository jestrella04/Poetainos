<script setup>
import { useTheme } from 'vuetify'
import PoAdminMenu from '../admin/PoAdminMenu.vue';

const theme = useTheme()
theme.global.name.value = window.matchMedia("(prefers-color-scheme: dark)").matches ? 'dark' : 'light'
</script>

<style>
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
  <v-app>
    <po-head />

    <v-main>
      <po-admin-menu></po-admin-menu>

      <po-wrapper class="admin-wrapper pa-5">
        <v-card class="pa-5 h-100">
          <slot />
        </v-card>
      </po-wrapper>
    </v-main>
  </v-app>
</template>
