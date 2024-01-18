<script setup>
import { ref, provide } from 'vue'
import PoCommentsIndex from '../comments/PoCommentsIndex.vue'
import PoWritingStats from './partials/PoWritingStats.vue'
import PoWritingDropdown from './partials/PoWritingDropdown.vue';

const props = defineProps({
  alone: { type: Boolean, default: true },
  data: { type: Object, required: true },
  likers: Object,
})

const loadingComments = ref(true)

provide('loadingComments', loadingComments)
provide('writing', props.data)

</script>

<style scoped>
.writing-body {
  white-space: pre-wrap !important;
}
</style>

<template>
  <po-wrapper>
    <v-card class="pos-relative" elevation="2" rounded>
      <po-writing-dropdown></po-writing-dropdown>
      <template v-if="!$helper.isEmpty(data.extra_info) && !$helper.strNullOrEmpty(data.extra_info.cover)">
        <v-img class="align-end text-white" height="200" :src="$helper.storage(data.extra_info.cover)" alt="" cover>
          <div class="text-center py-3">
            <po-link :href="$route('users.show', data.author.username)" inertia>
              <po-avatar size="64" color="secondary" :user="data.author" />
            </po-link>
          </div>
        </v-img>
      </template>

      <div v-else class="text-center pt-3">
        <po-link :href="$route('users.show', data.author.username)" inertia>
          <po-avatar size="64" color="secondary" :user="data.author" />
        </po-link>
      </div>

      <v-card-text class="pos-relative">
        <div class="text-center mb-3">
          <p class="text-h5 text-uppercase font-weight-bold">
            <po-link v-if="!alone" :href="$route('writings.show', data.slug)" class="stretched" inertia>{{ data.title
            }}</po-link>
            <span v-else>{{ data.title }}</span>
          </p>
          <p class="text-caption text-uppercase font-weight-light">
            {{
              `${$t('main.by-name', { name: $helper.userDisplayName(data.author) })}
                        ${$helper.relativeDate(data.created_at)}`
            }}
          </p>
        </div>

        <template v-if="alone">
          <blockquote class="writing-body mb-3">
            {{ data.text }}
          </blockquote>

          <template v-if="!$helper.isEmpty(data.extra_info) && !$helper.strNullOrEmpty(data.extra_info.link)">
            <po-button variant="text" :href="data.extra_info.link" prepend-icon="fas fa-link" target="_blank"
              rel="nofollow noopener" class="mb-4">

              {{ $helper.cropUrl(data.extra_info.link) }}
            </po-button>
          </template>

          <div v-if="!$helper.isEmpty(data.categories)" class="d-flex mb-2">
            <div class="mr-3">
              <v-icon icon="fas fa-folder-open"></v-icon>
            </div>

            <div class="d-inline-flex ga-1">
              <po-chip v-for="category in data.categories" :key="category.slug" color="secondary" variant="elevated"
                size="small" :href="$route('categories.show', category.slug)" inertia>
                {{ category.name }}
              </po-chip>
            </div>
          </div>

          <div v-if="!$helper.isEmpty(data.tags)" class="d-flex mb-4">
            <div class="mr-3">
              <v-icon icon="fas fa-hashtag"></v-icon>
            </div>

            <div class="d-inline-flex ga-1">
              <po-chip v-for="tag in data.tags" :key="tag.slug" color="secondary" variant="elevated" size="small"
                :href="$route('tags.show', tag.slug)" inertia>
                {{ tag.name }}
              </po-chip>
            </div>
          </div>

          <div v-if="!$helper.isEmpty(likers)">
            <p class="mb-3">{{ $t('main.liked-by') }}</p>

            <div class="d-inline-flex flex-wrap ga-1">
              <div v-for="liker in likers" :key="liker.id">
                <po-button icon :href="$route('users.show', liker.username)" :title="$helper.userDisplayName(liker)"
                  inertia>
                  <po-avatar size="48" color="secondary" :user="liker" />
                </po-button>
              </div>
              <div v-if="likers.length > 5">
                <v-avatar size="48" color="secondary" text="+" />
              </div>
            </div>
          </div>
        </template>

        <template v-else>
          <blockquote class="writing-body">
            {{ $helper.excerpt(data.text) }}
          </blockquote>
        </template>
      </v-card-text>

      <v-divider></v-divider>
      <v-card-actions>
        <po-writing-stats></po-writing-stats>
      </v-card-actions>
    </v-card>

    <template v-if="alone">
      <v-skeleton-loader v-if="loadingComments" :elevation="3" type="list-item-avatar" class="mb-2"></v-skeleton-loader>
      <v-skeleton-loader v-if="loadingComments" :elevation="3" type="list-item-avatar" class="mb-2"></v-skeleton-loader>
      <v-skeleton-loader v-if="loadingComments" :elevation="3" type="list-item-avatar" class="mb-2"></v-skeleton-loader>

      <po-comments-index />
    </template>
  </po-wrapper>
</template>