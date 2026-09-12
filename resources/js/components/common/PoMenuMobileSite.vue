<script setup lang="ts">
import { mobileSiteMenuKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import { useStaticPages } from '@/composables/useStaticPages'

const mobileSiteMenu = injectStrict(mobileSiteMenuKey)
const { faqPath, aboutPath, termsPath, privacyPath } = useStaticPages()
</script>

<template>
  <v-bottom-sheet v-model="mobileSiteMenu" inset close-on-content-click>
    <v-card>
      <v-card-text>
        <v-list>
          <po-list-item :href="route('users.index')" prepend-icon="fas fa-users" inertia>
            <span>{{ $t('users.authors') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="route('writings.awards')" prepend-icon="fas fa-fan" inertia>
            <span>{{ $t('main.awards') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="route('writings.random')" prepend-icon="fas fa-random" inertia>
            <span>{{ $t('main.random') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="route('contact.create')" prepend-icon="fas fa-envelope" inertia>
            <span>{{ $t('main.contact-us') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="faqPath()" prepend-icon="fas fa-circle-question" inertia>
            <span>{{ $t('main.faq') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="aboutPath()" prepend-icon="fas fa-address-card" inertia>
            <span>{{ $t('main.about-us') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="termsPath()" prepend-icon="fas fa-pen-ruler" inertia>
            <span>{{ $t('main.terms-of-use') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="privacyPath()" variant="text" prepend-icon="fas fa-shield-halved" inertia>
            <span>{{ $t('main.privacy-policy') }}</span>
          </po-list-item>
        </v-list>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
</template>
