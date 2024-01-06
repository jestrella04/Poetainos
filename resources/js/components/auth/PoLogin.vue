<script setup>
import { computed, defineOptions, ref, reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import PoLayoutLogin from '../layouts/PoLayoutLogin.vue'
import axios from 'axios'
import { provide } from 'vue';

defineOptions({
  layout: PoLayoutLogin,
})

const page = computed(() => usePage())
const isLoading = ref(false)
const isEmail = ref(false)
const shouldLogin = ref(false)
const shouldRegister = ref(false)
const formData = reactive({
  email: '',
  username: '',
  password: '',
  confirmPassword: '',
  serviceAgreement: false,
  privacyAgreement: false
})

provide('serviceAgreement', formData.serviceAgreement)
provide('privacyAgreement', formData.privacyAgreement)

function resetForm() {
  setTimeout(() => {
    isEmail.value = false
    shouldLogin.value = false
    shouldRegister.value = false

    formData.email = ''
    formData.username = ''
    formData.password = ''
    formData.confirmPassword = ''
  }, 500)
}

async function submitForm() {
  const form = document.querySelector('#login-form')

  if (!form.checkValidity()) {
    form.reportValidity()
    return
  }

  // Check if email exists
  if (!shouldLogin.value && !shouldRegister.value) {
    isLoading.value = true

    await axios
      .post(window.route('login.email.post'), { email: formData.email })
      .then((response) => {
        if (response.data.exists) {
          shouldLogin.value = true
          shouldRegister.value = false
        } else {
          shouldLogin.value = false
          shouldRegister.value = true
        }
      }).catch((error) => {
        console.log(error)
      }).finally(() => {
        isLoading.value = false
      })

    // Prevent entering following if statement
    return
  }

  // Attempt to login
  else if (shouldLogin.value) {
    isLoading.value = true

    await axios
      .post(window.route('login'), { email: formData.email, password: formData.password })
      .then((response) => {
        if (204 === response.status) {
          router.visit(window.route('home'))
        }
      })
      .catch((error) => {
        console.log(error)
      })
      .finally(() => {
        isLoading.value = false
      })

    // Prevent entering following if statement
    return
  }

  // Attempt to register
  else if (shouldRegister.value) {
    isLoading.value = true

    await axios
      .post(window.route('register'), {
        email: formData.email,
        username: formData.username,
        password: formData.password,
        password_confirmation: formData.confirmPassword,
        service_agreement: formData.serviceAgreement,
        privacy_agreement: formData.privacyAgreement
      })
      .then((response) => {

      })
      .catch((error) => {
        console.log(error)
      })
      .finally(() => {
        isLoading.value = false
      })

    // Prevent entering following if statement
    return
  }
}
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

          <template v-if="isEmail">
            <v-form id="login-form" class="w-100" @submit.prevent="submitForm()" @reset.prevent="resetForm()">
              <v-text-field v-model="formData.email" type="email" :label="$t('main.email')"
                :placeholder="$t('main.enter-your-email')" persistent-placeholder clearable required>
              </v-text-field>

              <template v-if="shouldRegister">
                <v-text-field v-model="formData.username" type="text" :label="$t('users.user')"
                  pattern="^(?!.*\.\.)(?!.*\.$)[^\W][\w.]{0,44}$" :placeholder="$t('main.enter-your-user')"
                  persistent-placeholder clearable required>
                </v-text-field>

                <v-text-field v-model="formData.password" type="password" :label="$t('main.password')"
                  pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
                  :placeholder="$t('main.enter-your-password')" persistent-placeholder clearable required>
                </v-text-field>

                <v-text-field v-model="formData.confirmPassword" type="password" :label="$t('accounts.confirm-password')"
                  pattern="(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$"
                  :placeholder="$t('main.enter-your-password')" persistent-placeholder clearable>
                </v-text-field>

                <po-agreement></po-agreement>
              </template>

              <template v-if="shouldLogin">
                <v-text-field v-model="formData.password" type="password" :label="$t('main.password')"
                  :placeholder="$t('main.enter-your-password')" persistent-placeholder clearable required>
                </v-text-field>
              </template>

              <po-button type="submit" color="primary" size="large" block :disabled="isLoading">
                <span v-if="!isLoading">{{ $t('main.continue') }}</span>
                <v-progress-circular v-else indeterminate></v-progress-circular>
              </po-button>
              <po-button type="reset" color="secondary" size="large" variant="text" class="mt-5" block>
                <v-icon icon="fas fa-arrow-left"></v-icon>
              </po-button>
            </v-form>
          </template>

          <template v-else>
            <v-list tag="po-social">
              <v-list-item>
                <po-button block color="primary" :href="$route('social.login', 'facebook')"
                  prepend-icon="fab fa-facebook-f">
                  {{ $t('accounts.continue-with-facebook') }}
                </po-button>
              </v-list-item>

              <v-list-item>
                <po-button block color="primary" :href="$route('social.login', 'twitter')"
                  prepend-icon="fab fa-x-twitter">
                  {{ $t('accounts.continue-with-x-twitter') }}
                </po-button>
              </v-list-item>

              <v-list-item>
                <po-button block color="primary" :href="$route('social.login', 'google')" prepend-icon="fab fa-google">
                  {{ $t('accounts.continue-with-google') }}
                </po-button>
              </v-list-item>

              <v-list-item>
                <po-button block color="primary" prepend-icon="fas fa-at" @click.prevent="isEmail = true">
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
          </template>
        </div>
      </v-sheet>
    </v-col>
  </v-row>
</template>
