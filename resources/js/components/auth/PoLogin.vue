<script setup>
import { computed, defineOptions } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoLayoutAlt from '../common/PoLayoutAlt.vue'

const page = computed(() => usePage())

defineOptions({
  layout: PoLayoutAlt,
})
</script>

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

@media (min-width: 960px) {
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
  <po-head :title="page.props.title" />

  <v-row class="flex-column flex-md-row" style="height: 100%;" no-gutters>
    <v-col class="login-col d-none d-md-block">
      <v-sheet class="d-flex align-center justify-center bg-gradient">
        <div class="py-10">
          <v-img src="/images/logo.svg" class="logo logo-shadow"></v-img>
        </div>
      </v-sheet>
    </v-col>

    <v-col class="login-col">
      <v-sheet class="d-flex align-center justify-center">
        <div>
          <div class="d-flex align-center justify-center py-10 d-md-none">
            <v-img src="/images/logo.svg" class="logo logo-shadow"></v-img>
          </div>

          <p class="text-center text-uppercase font-weight-bold mb-3">
            {{ $t('accounts.welcome-to-hood') }}
          </p>

          <v-list>
            <v-list-item>
              <po-button block color="primary" :href="$route('social.login', 'facebook')"
                prepend-icon="fab fa-facebook-f">
                {{ $t('accounts.continue-with-facebook') }}
              </po-button>
            </v-list-item>

            <v-list-item>
              <po-button block color="primary" :href="$route('social.login', 'twitter')" prepend-icon="fab fa-x-twitter">
                {{ $t('accounts.continue-with-x-twitter') }}
              </po-button>
            </v-list-item>

            <v-list-item>
              <po-button block color="primary" :href="$route('social.login', 'google')" prepend-icon="fab fa-google">
                {{ $t('accounts.continue-with-google') }}
              </po-button>
            </v-list-item>

            <v-list-item>
              <po-button block color="primary" :href="$route('login.email.check')" prepend-icon="fas fa-at"
                @click.prevent="$inertia.get($route('login.email.check'))">
                {{ $t('accounts.continue-with-email') }}
              </po-button>
            </v-list-item>

            <v-list-item>
              <po-button block color="primary" :href="$route('home')" prepend-icon="fas fa-ghost"
                @click.prevent="$inertia.get($route('home'))">
                {{ $t('accounts.continue-as-guest') }}
              </po-button>
            </v-list-item>
          </v-list>
        </div>
      </v-sheet>
    </v-col>
  </v-row>
</template>
