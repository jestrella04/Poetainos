<script setup>
import { useRegisterSW } from 'virtual:pwa-register/vue'

// replaced dynamically
const reloadSW = '__RELOAD_SW__'
const intervalMS = 60 * 60 * 1000

const { needRefresh, updateServiceWorker } = useRegisterSW({
  onRegisteredSW(swUrl, r) {
    console.log(`Service Worker at: ${swUrl}`)

    if (reloadSW === 'true') {
      r && setInterval(async () => {
        console.log('Checking for sw update')
        await r.update()
      }, intervalMS)
    } else {
      console.log(`SW Registered: ${r}`)
    }
  },
})
</script>

<style scoped>
.v-snackbar {
  margin: 0 auto;
}
</style>

<template>
  <div v-if="needRefresh" class="d-flex w-100 justify-center">
    <v-snackbar :model-value="true" color="secondary" elevation="5" :timeout="-1" location="bottom" width="100%"
      max-width="600">
      <div class="w-100 d-flex align-center ga-5">
        <div>
          <v-avatar size="48">
            <v-img src="/images/logo.svg" alt=""></v-img>
          </v-avatar>
        </div>

        <div class="flex-grow-1">
          <span>{{ $t('main.app-update-available') }}</span>
        </div>

        <div>
          <v-btn @click="updateServiceWorker()">{{ $t('main.reload') }}</v-btn>
        </div>
      </div>
    </v-snackbar>
  </div>
</template>

