<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import PoWritingsEntry from './PoWritingsEntry.vue'

const page = computed(() => usePage())
</script>

<template>
  <po-wrapper>
    <po-head></po-head>

    <template v-if="page.props.isAuthorBlocked">
      <div class="d-flex align-center mx-auto" style="height: 500px; width: 500px">
        <po-msg-block :msg-title="$t('users.user-is-blocked')" :msg-body="$t('main.author-blocked')"
          icon="fas fa-ban"></po-msg-block>
      </div>
    </template>
    <template v-else>
      <v-row>
        <v-col cols="12" md="8">
          <po-writings-entry :data="page.props.writing" :likers="page.props.likers" />
        </v-col>

        <v-col cols="12" md="4">
          <v-card class="mb-6">
            <v-card-text>
              <div v-if="!$helper.isEmpty(page.props.related.from_author)">
                <p class="text-uppercase text-caption mb-5">{{ $t('main.more-from-author') }}</p>

                <template v-for="writing in page.props.related.from_author" :key="writing.id">
                  <div class="mb-2 pos-relative">
                    <po-link :href="route('writings.show', writing.slug)" class="text-bold stretched" inertia>
                      {{ writing.title }}
                    </po-link>

                    <p class="text-caption text-disabled">
                      {{
                        $t('main.by-name', {
                          name: $helper.userDisplayName(page.props.writing.author)
                        })
                      }}
                      {{ $helper.relativeDate(writing.created_at) }}
                    </p>
                  </div>
                </template>
              </div>
            </v-card-text>
          </v-card>

          <v-card>
            <v-card-text>
              <div v-if="!$helper.isEmpty(page.props.related.from_category)">
                <p class="text-uppercase text-caption mb-5">{{ $t('main.related-writings') }}</p>
                <template v-for="writing in page.props.related.from_category" :key="writing.id">
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
