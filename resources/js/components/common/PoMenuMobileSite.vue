<script setup>
import { inject } from 'vue'

const mobileSiteMenu = inject('mobileSiteMenu', false)
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

          <po-list-item :href="route('pages.show', 'preguntas-frecuentes')" prepend-icon="fas fa-circle-question"
            inertia>
            <span>{{ $t('main.faq') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="route('pages.show', 'sobre-nosotros')" prepend-icon="fas fa-address-card" inertia>
            <span>{{ $t('main.about-us') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="route('pages.show', 'condiciones-de-uso')" prepend-icon="fas fa-pen-ruler" inertia>
            <span>{{ $t('main.terms-of-use') }}</span>
          </po-list-item>
          <v-divider class="my-0"></v-divider>

          <po-list-item :href="route('pages.show', 'politicas-de-privacidad')" variant="text"
            prepend-icon="fas fa-shield-halved" inertia>
            <span>{{ $t('main.privacy-policy') }}</span>
          </po-list-item>
        </v-list>
      </v-card-text>
    </v-card>
  </v-bottom-sheet>
</template>