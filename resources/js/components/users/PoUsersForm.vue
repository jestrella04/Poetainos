<script setup lang="ts">
import { reactive, useTemplateRef } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useAuth } from '@/composables/useAuth'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormValidation } from '@/composables/useFormValidation'
import { useRequestFailure } from '@/composables/useRequestFailure'
import { useSocialLinks } from '@/composables/useSocialLinks'
import type { InertiaPageProps } from '@/types/inertia'

// UsersController::edit()'s user with its profile loaded (null until the user fills one in)
interface EditableUser {
  id: number
  username: string
  name?: string | null
  email: string
  role_id?: number | null
  profile:
    | (Partial<Record<string, string | null>> & {
        avatar_url?: string | null
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
  key: string
  label: string
  maxlength: number
}

const page = usePage<InertiaPageProps<{ user: EditableUser; roles: Role[]; agreement: boolean }>>()
const { socialNetworkName } = useSocialLinks()
// The networks and their handles' max lengths come from UserProfile::SOCIAL_NETWORKS
const socialLinkFields: SocialLinkField[] = Object.entries(page.props.site.socialNetworks).map(
  ([key, maxlength]) => ({ key, label: socialNetworkName(key), maxlength })
)
const { authUser, isAdmin } = useAuth()
const { isBlank } = useTypeGuards()
const { isSubmittedFormValid } = useFormValidation()
const { onHttpException, onNetworkError } = useRequestFailure()
const avatarInput = useTemplateRef<{ click: () => void }>('avatarInput')

const user = page.props.user
const form = useForm({
  avatar: null as File | null,
  avatar_remove: false,
  role: user.role_id ?? null,
  name: user.name ?? '',
  email: user.email,
  bio: user.profile?.bio ?? '',
  location: user.profile?.location ?? '',
  occupation: user.profile?.occupation ?? '',
  interests: user.profile?.interests ?? '',
  website: user.profile?.website ?? '',
  service_agreement: false,
  privacy_agreement: false
})
// Handles are keyed by network, which the form's field types can't list, so
// they're kept apart and sent along with the form
const socialHandles = reactive<Record<string, string>>(
  Object.fromEntries(socialLinkFields.map((field) => [field.key, user.profile?.[field.key] ?? '']))
)

function socialHandleError(network: string): string | undefined {
  const errors: Partial<Record<string, string>> = form.errors

  return errors[network]
}

// PoAvatar reads a flat `avatar`, as every listing sends it
const avatarUser = {
  username: user.username,
  name: user.name,
  avatar_url: user.profile?.avatar_url ?? null
}
const hasAvatar = !isBlank(avatarUser.avatar_url)

// The server opens the profile once saved, confirming with a flash message. The
// avatar upload makes this multipart, which PHP only parses on POST, hence the
// method spoofing.
function submitForm(event: Event): void {
  if (isSubmittedFormValid(event) === false) {
    return
  }

  form
    .transform((data) => ({ ...data, ...socialHandles, _method: 'put' }))
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
  <v-responsive class="w-100" max-width="900">
    <po-head />
    <v-card>
      <v-card-title class="text-uppercase">{{ $t('accounts.update-profile') }}</v-card-title>

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
          v-model="form.avatar_remove"
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
          v-model="socialHandles[field.key]"
          type="text"
          :label="field.label"
          hide-details="auto"
          :error-messages="socialHandleError(field.key)"
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
  </v-responsive>
</template>
