<script setup lang="ts">
import { useTemplateRef } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useAuth } from '@/composables/useAuth'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormValidation } from '@/composables/useFormValidation'
import { useRequestFailure } from '@/composables/useRequestFailure'
import type { InertiaPageProps } from '@/types/inertia'

type SocialNetworkKey = 'twitter' | 'threads' | 'instagram' | 'facebook' | 'youtube' | 'goodreads'

// UsersController::edit()'s user with its profile loaded (null until the user fills one in)
interface EditableUser {
  id: number
  username: string
  name?: string | null
  email: string
  role_id?: number | null
  profile:
    | (Partial<Record<SocialNetworkKey, string | null>> & {
        avatar?: string | null
        bio?: string | null
        location?: string | null
        occupation?: string | null
        interests?: string | null
        website?: string | null
      })
    | null
}

interface Role {
  id: number
  name: string
}

interface SocialLinkField {
  key: SocialNetworkKey
  label: string
  maxlength: number
}

const socialLinkFields: SocialLinkField[] = [
  { key: 'twitter', label: 'X (Twitter)', maxlength: 250 },
  { key: 'threads', label: 'Threads', maxlength: 250 },
  { key: 'instagram', label: 'Instagram', maxlength: 100 },
  { key: 'facebook', label: 'Facebook', maxlength: 250 },
  { key: 'youtube', label: 'Youtube', maxlength: 100 },
  { key: 'goodreads', label: 'Goodreads', maxlength: 250 }
]

const page = usePage<InertiaPageProps<{ user: EditableUser; roles: Role[]; agreement: boolean }>>()
const { authUser, isAdmin } = useAuth()
const { strNullOrEmpty } = useTypeGuards()
const { isSubmittedFormValid } = useFormValidation()
const { onHttpException, onNetworkError } = useRequestFailure()
const avatarInput = useTemplateRef<{ click: () => void }>('avatarInput')

const user = page.props.user
const form = useForm({
  avatar: null as File | null,
  'avatar-remove': false,
  role: user.role_id ?? null,
  name: user.name ?? '',
  email: user.email,
  bio: user.profile?.bio ?? '',
  location: user.profile?.location ?? '',
  occupation: user.profile?.occupation ?? '',
  interests: user.profile?.interests ?? '',
  website: user.profile?.website ?? '',
  ...(Object.fromEntries(
    socialLinkFields.map((field) => [field.key, user.profile?.[field.key] ?? ''])
  ) as Record<SocialNetworkKey, string>),
  service_agreement: false,
  privacy_agreement: false
})

// PoAvatar reads a flat `avatar`, as every listing sends it
const avatarUser = {
  username: user.username,
  name: user.name,
  avatar: user.profile?.avatar ?? null
}
const hasAvatar = !strNullOrEmpty(avatarUser.avatar)

// The server opens the profile once saved, confirming with a flash message. The
// avatar upload makes this multipart, which PHP only parses on POST, hence the
// method spoofing.
function submitForm(event: Event): void {
  if (isSubmittedFormValid(event) === false) {
    return
  }

  form
    .transform((data) => ({ ...data, _method: 'put' }))
    .post(route('users.update', user.username), {
      forceFormData: true,
      onHttpException,
      onNetworkError
    })
}

function openAvatarPicker(): void {
  avatarInput.value?.click()
}
</script>

<template>
  <po-wrapper class="w-100" style="max-width: 900px">
    <po-head />
    <v-card :title="$t('accounts.update-profile').toUpperCase()">
      <v-form id="profile-form" class="px-5 pb-5" @submit.prevent="submitForm">
        <div class="d-flex ga-3 mb-3 align-center">
          <po-avatar :user="avatarUser" size="72" color="secondary" />
          <po-button color="primary" variant="tonal" @click="openAvatarPicker">{{
            $t('main.choose-image')
          }}</po-button>

          <v-file-input
            id="avatar-input"
            ref="avatarInput"
            class="d-none"
            v-model="form.avatar"
            :label="$t('main.choose-image')"
            hide-details
          />
        </div>

        <v-checkbox
          v-if="hasAvatar"
          v-model="form['avatar-remove']"
          :label="$t('accounts.remove-current-avatar')"
          hide-details
        />

        <template v-if="isAdmin()">
          <v-select
            v-model="form.role"
            :label="$t('main.role')"
            hide-details="auto"
            :error-messages="form.errors.role"
            :items="page.props.roles"
            item-value="id"
            item-title="name"
            required
          />
        </template>

        <v-text-field
          v-model="form.name"
          type="text"
          :label="$t('main.name')"
          hide-details="auto"
          :error-messages="form.errors.name"
          minlength="3"
          maxlength="250"
          required
          clearable
        />

        <v-text-field
          :model-value="user.username"
          :label="$t('users.username')"
          hide-details="auto"
          minlength="3"
          maxlength="100"
          required
          readonly
        />

        <v-text-field
          v-model="form.email"
          type="text"
          :label="$t('main.email')"
          hide-details="auto"
          :error-messages="form.errors.email"
          minlength="3"
          maxlength="250"
          required
          clearable
        />

        <v-textarea
          v-model="form.bio"
          :label="$t('users.bio')"
          hide-details="auto"
          :error-messages="form.errors.bio"
          minlength="10"
          maxlength="300"
          clearable
          required
        />

        <v-text-field
          v-model="form.location"
          type="text"
          :label="$t('main.location')"
          hide-details="auto"
          :error-messages="form.errors.location"
          minlength="3"
          maxlength="250"
          clearable
        />

        <v-text-field
          v-model="form.occupation"
          type="text"
          :label="$t('main.occupation')"
          hide-details="auto"
          :error-messages="form.errors.occupation"
          minlength="3"
          maxlength="100"
          clearable
        />

        <v-text-field
          v-model="form.interests"
          type="text"
          :label="$t('main.interests')"
          hide-details="auto"
          :error-messages="form.errors.interests"
          minlength="3"
          maxlength="250"
          clearable
        />

        <v-text-field
          v-model="form.website"
          type="url"
          :label="$t('main.website')"
          hide-details="auto"
          :error-messages="form.errors.website"
          minlength="3"
          maxlength="250"
          clearable
        />

        <v-text-field
          v-for="field in socialLinkFields"
          :key="field.key"
          v-model="form[field.key]"
          type="text"
          :label="field.label"
          hide-details="auto"
          :error-messages="form.errors[field.key]"
          minlength="3"
          :maxlength="field.maxlength"
          clearable
        />

        <po-agreement
          v-if="!page.props.agreement && user.id === authUser()!.id"
          v-model:service-agreement="form.service_agreement"
          v-model:privacy-agreement="form.privacy_agreement"
        />

        <po-button type="submit" color="primary" size="large" block :disabled="form.processing">
          <template v-if="form.processing"><v-progress-circular indeterminate /></template>
          <template v-else>{{ $t('main.save') }}</template>
        </po-button>
      </v-form>
    </v-card>
  </po-wrapper>
</template>
