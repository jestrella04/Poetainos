<script setup>
import { Link } from '@inertiajs/vue3'
import PoAvatar from '../common/PoAvatar.vue';

defineProps({
  alone: { type: Boolean, default: true },
  data: { type: Object, required: true },
  likers: Object,
})
</script>

<template>
  <v-card border rounded elevation="2">
    <v-img v-if="!$helper.isNull(data.extra_info) && !$helper.strNullOrEmpty(data.extra_info.cover)"
      class="align-end text-white" height="200" :src="$helper.storage(data.extra_info.cover)" cover>
      <div class="text-center py-3">
        <po-avatar size="64" color="deep-purple-lighten-2" :user="data.author" />
      </div>
    </v-img>

    <div v-else class="text-center pt-3">
      <po-avatar size="64" color="deep-purple-lighten-2" :user="data.author" />
    </div>

    <v-card-text>
      <div class="text-center mb-3">
        <p class="text-h5 text-uppercase font-weight-bold">
          <Link :href="$route('writings.show', data.slug)">{{ data.title }}</Link>
        </p>
        <p class="text-caption text-uppercase font-weight-light">
          {{
            `${$t('main.by-name', { name: $helper.userDisplayName(data.author) })}
                    @ ${$helper.toLocaleDate(data.created_at)}`
          }}
        </p>
      </div>

      <template v-if="alone">
        <blockquote class="mb-3">
          {{ data.text }}
        </blockquote>

        <template v-if="!$helper.strNullOrEmpty(data.extra_info.link)">
          <v-btn variant="link" :href="data.extra_info.link" prepend-icon="fas fa-link" target="_blank"
            rel="nofollow noopener" class="mb-4">

            {{ $helper.cropUrl(data.extra_info.link) }}
          </v-btn>
        </template>

        <div v-if="!$helper.isEmpty(data.categories)" class="d-flex mb-2">
          <div class="mr-3">
            <v-icon icon="fas fa-folder-open"></v-icon>
          </div>

          <div>
            <v-btn v-for="category in data.categories" :key="category.slug" color="primary" size="x-small"
              :href="$route('categories.show', category.slug)" class="mr-1"
              @click.prevent="$inertia.get($route('categories.show', category.slug))">
              {{ category.name }}
            </v-btn>
          </div>
        </div>

        <div v-if="!$helper.isEmpty(data.tags)" class="d-flex mb-4">
          <div class="mr-3">
            <v-icon icon="fas fa-hashtag"></v-icon>
          </div>

          <div>
            <v-btn v-for="tag in data.tags" :key="tag.slug" color="success" size="x-small"
              :href="$route('tags.show', tag.slug)" class="mr-1"
              @click.prevent="$inertia.get($route('tags.show', tag.slug))">
              {{ tag.name }}
            </v-btn>
          </div>
        </div>

        <div v-if="!$helper.isEmpty(likers)">
          <p class="mb-3">{{ $t('main.liked-by') }}</p>

          <div v-for="liker in likers" :key="liker.id" class="d-flex flex-wrap">
            <v-btn icon :href="$route('users.show', liker.username)"
              @click.prevent="$inertia.get($route('users.show', liker.username))">
              <po-avatar size="48" color="secondary" :user="liker" />
            </v-btn>
          </div>
        </div>
      </template>

      <template v-else>
        <blockquote>
          {{ $helper.excerpt(data.text) }}
        </blockquote>
      </template>
    </v-card-text>

    <v-card-actions>
      <div class="d-flex flex-fill text-caption text-center">
        <div v-if="!$helper.strNullOrEmpty(data.home_posted_at)" class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-fan" color="amber-accent-4"></v-icon></div>
            <div>:</div>
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-heart"></v-icon></div>
            <div>{{ $helper.readable(data.likes_count) }}</div>
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-comment"></v-icon></div>
            <div>{{ $helper.readable(data.comments_count) }}</div>
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-book-reader"></v-icon></div>
            <div>{{ $helper.readable(data.views) }}</div>
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-bookmark"></v-icon></div>
            <div>{{ $helper.readable(data.shelf_count) }}</div>
          </div>
        </div>

        <div class="flex-grow-1">
          <div class="d-flex flex-column">
            <div><v-icon icon="fas fa-dove"></v-icon></div>
            <div>{{ data.aura }}</div>
          </div>
        </div>
      </div>
    </v-card-actions>
  </v-card>
</template>