<script setup lang="ts">
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import PoLayoutAdmin from '../layouts/PoLayoutAdmin.vue'
import { useServerTable } from '@/composables/useServerTable'
import { useFormatting } from '@/composables/useFormatting'
import type { DataTableHeader } from 'vuetify'
import type { InertiaPageProps } from '@/types/inertia'
import type { UserLike } from '@/types/models'

defineOptions({
  layout: PoLayoutAdmin
})

type ActivityKind =
  | 'joined'
  | 'published'
  | 'commented'
  | 'liked_writing'
  | 'liked_comment'
  | 'bookmarked'
  | 'writing_of_the_day'
  | 'reported_writing'
  | 'reported_comment'
  | 'reported_user'

interface ActivityRow {
  kind: ActivityKind
  subject_id: number
  created_at: string
  user: UserLike | null
  writing: { id: number; title: string; slug: string } | null
}

interface ActivityPresentation {
  icon: string
  color: string
  message: string
}

const { t } = useI18n()
const { userDisplayName, relativeDate, toLocaleDate } = useFormatting()
const page = usePage<InertiaPageProps<{ total: number }>>()
const headers: DataTableHeader[] = [
  { title: t('admin.event'), align: 'start', sortable: false, key: 'kind' },
  { title: t('users.user'), align: 'start', sortable: false, key: 'user' },
  { title: t('main.title'), align: 'start', sortable: false, key: 'writing' },
  { title: t('main.date'), align: 'start', sortable: false, key: 'created_at' }
]
const presentations: Record<ActivityKind, ActivityPresentation> = {
  joined: { icon: 'fas fa-user', color: 'primary', message: 'admin.activity-joined' },
  published: { icon: 'fas fa-feather', color: 'primary', message: 'admin.activity-published' },
  commented: { icon: 'fas fa-comment', color: 'secondary', message: 'admin.activity-commented' },
  liked_writing: {
    icon: 'fas fa-heart',
    color: 'secondary',
    message: 'admin.activity-liked-writing'
  },
  liked_comment: {
    icon: 'fas fa-heart',
    color: 'secondary',
    message: 'admin.activity-liked-comment'
  },
  bookmarked: { icon: 'fas fa-bookmark', color: 'secondary', message: 'admin.activity-bookmarked' },
  writing_of_the_day: {
    icon: 'fas fa-award',
    color: 'success',
    message: 'admin.activity-writing-of-the-day'
  },
  reported_writing: {
    icon: 'fas fa-flag',
    color: 'error',
    message: 'admin.activity-reported-writing'
  },
  reported_comment: {
    icon: 'fas fa-flag',
    color: 'error',
    message: 'admin.activity-reported-comment'
  },
  reported_user: { icon: 'fas fa-flag', color: 'error', message: 'admin.activity-reported-user' }
}
const { items, totalItems, isLoading, loadItems } = useServerTable<ActivityRow>(
  'admin.activity',
  page.props.total
)
</script>

<template>
  <po-wrapper>
    <v-card-title>{{ $t('admin.activity') }}</v-card-title>

    <v-data-table-server
      v-model:items-per-page="page.props.site.pagination"
      :headers="headers"
      :items-length="totalItems"
      :items="items"
      :loading="isLoading"
      :item-value="(item: ActivityRow) => `${item.kind}-${item.subject_id}`"
      @update:options="loadItems"
    >
      <template v-slot:item.kind="{ item }">
        <div class="d-flex align-center ga-3">
          <v-icon
            :icon="presentations[item.kind].icon"
            :color="presentations[item.kind].color"
            size="small"
          />
          <span>{{ $t(presentations[item.kind].message) }}</span>
        </div>
      </template>

      <template v-slot:item.user="{ item }">
        <po-link v-if="item.user !== null" :href="route('users.show', item.user.username)" inertia>
          {{ userDisplayName(item.user) }}
        </po-link>
      </template>

      <template v-slot:item.writing="{ item }">
        <po-link
          v-if="item.writing !== null"
          :href="route('writings.show', item.writing.slug)"
          inertia
        >
          {{ item.writing.title }}
        </po-link>
      </template>

      <template v-slot:item.created_at="{ item }">
        <span :title="toLocaleDate(item.created_at)">{{ relativeDate(item.created_at) }}</span>
      </template>
    </v-data-table-server>
  </po-wrapper>
</template>
