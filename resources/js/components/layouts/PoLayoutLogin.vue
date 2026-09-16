<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useTheme } from 'vuetify'
import type { InertiaPageProps } from '@/types/inertia'

const page = computed(() => usePage<InertiaPageProps<{ title?: string }>>())
const theme = useTheme()

void theme.change(window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
</script>

<style>
/* Vuetify's height:100% isn't applied to html/body by default; needed for the full-height split login layout. */
html,
body {
  height: 100%;
}
</style>

<style scoped>
/* Split login layout (image column + form column), each filling the viewport height. No Vuetify layout primitive for this. */
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
      <v-row class="flex-column flex-md-row" style="height: 100%; gap: 0">
        <po-head :title="page.props.title" />
        <v-col class="login-col d-none d-md-block">
          <v-sheet class="d-flex align-center justify-center">
            <v-img src="/images/logo.svg" class="logo" />
          </v-sheet>
        </v-col>

        <v-col class="login-col">
          <v-sheet class="d-flex align-center justify-center">
            <div style="width: 100%; max-width: 100%">
              <div class="d-flex align-center justify-center pb-10 d-md-none">
                <v-img src="/images/logo.svg" class="logo" />
              </div>
              <slot />
            </div>
          </v-sheet>
        </v-col>
      </v-row>
    </v-main>
  </v-app>
</template>
