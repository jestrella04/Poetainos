<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoUsersEntry from './PoUsersEntry.vue'

const page = computed(() => usePage())
</script>

<template>
  <po-wrapper>
    <po-head></po-head>

    <template v-if="page.props.isAuthorBlocked">
      <div class="d-flex align-center mx-auto" style="height: 500px; width: 500px;">
        <po-msg-block :msg-title="$t('users.user-is-blocked')" :msg-body="$t('main.author-blocked')"
          icon="fas fa-ban"></po-msg-block>
      </div>
    </template>
    <template v-else>
      <v-row>
        <v-col cols="12" md="8">
          <po-users-entry :data="page.props.user" />
        </v-col>

        <v-col cols="12" md="4">
          <v-card :title="$t('main.explore-more').toLocaleUpperCase()">
            <v-card-text class="mt-3">
              <div v-if="!$helper.isEmpty(page.props.writings.from_author)">
                <p class="text-uppercase font-medium-weight mb-5">{{ $t('main.from-author') }}</p>

                <template v-for="writing in page.props.writings.from_author" :key="writing.id">
                  <div class="mb-2 pos-relative">
                    <po-link :href="route('writings.show', writing.slug)" class="text-bold stretched" inertia>
                      {{ writing.title }}
                    </po-link>

                    <p class="text-caption text-disabled">
                      {{ $t('main.by-name', { name: $helper.userDisplayName(writing.author) }) }}
                      {{ $helper.relativeDate(writing.created_at) }}
                    </p>
                  </div>
                </template>

                <v-divider class="my-8"></v-divider>
              </div>

              <div v-if="!$helper.isEmpty(page.props.writings.from_shelf)">
                <p class="text-uppercase font-medium-weight mb-5">{{ $t('main.from-shelf') }}</p>
                <template v-for="writing in page.props.writings.from_shelf" :key="writing.id">
                  <div class="mb-2 pos-relative">
                    <po-link :href="route('writings.show', writing.slug)" class="text-bold stretched" inertia>
                      {{ writing.title }}
                    </po-link>

                    <p class="text-caption text-disabled">
                      {{ $t('main.by-name', { name: $helper.userDisplayName(writing.author) }) }}
                      {{ $helper.relativeDate(writing.created_at) }}
                    </p>
                  </div>
                </template>

                <v-divider class="my-8"></v-divider>
              </div>

              <div v-if="!$helper.isEmpty(page.props.writings.from_liked)">
                <p class="text-uppercase font-medium-weight mb-5">{{ $t('main.from-liked') }}</p>
                <template v-for="writing in page.props.writings.from_liked" :key="writing.id">
                  <div class="mb-2 pos-relative">
                    <po-link :href="route('writings.show', writing.slug)" class="text-bold stretched" inertia>
                      {{ writing.title }}
                    </po-link>

                    <p class="text-caption text-disabled">
                      {{ $t('main.by-name', { name: $helper.userDisplayName(writing.author) }) }}
                      {{ $helper.relativeDate(writing.created_at) }}
                    </p>
                  </div>
                </template>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </template>
  </po-wrapper>
</template>