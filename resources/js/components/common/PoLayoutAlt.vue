<script setup>
import { useTheme } from 'vuetify'

const theme = useTheme()

theme.global.name.value = window.matchMedia("(prefers-color-scheme: dark)").matches ? 'dark' : 'light'
</script>

<style>
html,
body {
  height: 100%;
}

.logo-shadow {
  filter: drop-shadow(0 0 2px rgba(248, 249, 250, .5));
}

.bg-gradient {
  background: linear-gradient(180deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-surface)) 100%) !important;
}
</style>

<template>
  <po-head title="" />

  <v-app>
    <v-main>
      <slot />
    </v-main>
  </v-app>
</template>
