<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useTheme } from 'vuetify'

const page = computed(() => usePage())
const theme = useTheme()

theme.global.name.value = window.matchMedia("(prefers-color-scheme: dark)").matches ? 'dark' : 'light'
</script>

<style>
html,
body {
  height: 100%;
}

.logo-shadow {
  filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.2));
}

.logo-shadow:focus,
.logo-shadow:active,
.logo-shadow:hover {
  filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
}

.bg-gradient {
  background: linear-gradient(180deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-surface)) 100%) !important;
}
</style>

<style scoped>
.login-col {
  width: 100%;
  height: 100%;
}

.login-col .v-sheet {
  height: 100%;
}

.logo {
  width: 72px;
  height: 72px;
  max-width: 72px;
  max-height: 72px;
}

@media screen and (max-width: 960px) {
  .login-col .v-sheet {
    background: linear-gradient(180deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-surface)) 100%) !important;
  }

  login-btns {
    background-color: transparent !important;
  }
}

@media screen and (min-width: 960px) {
  .login-col {
    min-height: 100vh;
  }

  .logo {
    width: 256px;
    height: 256px;
    max-width: 256px;
    max-height: 256px;
  }
}
</style>

<template>
  <v-app>
    <po-head />

    <v-main>
      <v-row class="flex-column flex-md-row" style="height: 100%;" no-gutters>
        <po-head :title="page.props.title" />
        <v-col class="login-col d-none d-md-block">
          <v-sheet class="d-flex align-center justify-center bg-gradient">
            <v-img src="/images/logo.svg" class="logo logo-shadow"></v-img>
          </v-sheet>
        </v-col>

        <v-col class="login-col">
          <v-sheet class="d-flex align-center justify-center">
            <div style="width: 100%; max-width: 100%;">
              <div class="d-flex align-center justify-center pb-10 d-md-none">
                <v-img src="/images/logo.svg" class="logo logo-shadow"></v-img>
              </div>
              <slot />
            </div>
          </v-sheet>
        </v-col>
      </v-row>
    </v-main>
  </v-app>
</template>
