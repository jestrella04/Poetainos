<script setup lang="ts">
import { provide, reactive, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { formDataKey } from '@/composables/keys'
import { useAuth } from '@/composables/useAuth'
import { useTypeGuards } from '@/composables/useTypeGuards'
import { useFormErrors } from '@/composables/useFormErrors'
import { useFormSubmit } from '@/composables/useFormSubmit'
import type { InertiaPageProps } from '@/types/inertia'
import type { LaravelValidationErrors } from '@/types/http'

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

interface PostedResult {
  url: string
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
const { isEmpty, strNullOrEmpty } = useTypeGuards()
const { validationErrors } = useFormErrors()
const { isPosting, errors, submitForm: postForm } = useFormSubmit<LaravelValidationErrors>({})

const user = page.props.user
const formData = reactive({
  avatar: [] as File[],
  avatarRemove: false,
  role: '' as string | number, // eslint-disable-line @typescript-eslint/no-unnecessary-type-assertion -- widens for the later `formData.role = user.role_id` numeric assignment
  name: '',
  username: '',
  email: '',
  bio: '',
  location: '',
  occupation: '',
  interests: '',
  website: '',
  twitter: '',
  threads: '',
  instagram: '',
  facebook: '',
  youtube: '',
  goodreads: '',
  serviceAgreement: false,
  privacyAgreement: false
})
const isPosted = ref<Partial<PostedResult>>({})

provide(formDataKey, formData)

// Init form data
formData.role = user.role_id ?? ''
formData.name = user.name ?? ''
formData.username = user.username ?? ''
formData.email = user.email ?? ''

formData.bio = user.profile?.bio ?? ''
formData.location = user.profile?.location ?? ''
formData.occupation = user.profile?.occupation ?? ''
formData.interests = user.profile?.interests ?? ''
formData.website = user.profile?.website ?? ''

for (const field of socialLinkFields) {
  formData[field.key] = user.profile?.[field.key] ?? ''
}

// PoAvatar reads a flat `avatar`, as every listing sends it
const avatarUser = {
  username: user.username,
  name: user.name,
  avatar: user.profile?.avatar ?? null
}
const hasAvatar = !strNullOrEmpty(avatarUser.avatar)

async function submitForm() {
  isPosted.value = {}

  await postForm<PostedResult>({
    formSelector: '#profile-form',
    multipart: true,
    cooldown: true,
    payload: {
      _method: 'PUT',
      avatar: formData.avatar,
      'avatar-remove': formData.avatarRemove ? 1 : 0,
      role: formData.role,
      name: formData.name,
      email: formData.email,
      bio: formData.bio,
      location: formData.location,
      occupation: formData.occupation,
      interests: formData.interests,
      website: formData.website,
      ...Object.fromEntries(socialLinkFields.map((field) => [field.key, formData[field.key]])),
      service_agreement: formData.serviceAgreement,
      privacy_agreement: formData.privacyAgreement
    },
    onSuccess: (data) => {
      isPosted.value = data
    },
    onError: validationErrors
  })
}

function openAvatarPicker(): void {
  document.querySelector<HTMLElement>('#avatar-input')?.click()
}
</script>

<template>
  <po-wrapper class="w-100" style="max-width: 900px">
    <po-head />
    <v-card :title="$t('accounts.update-profile').toUpperCase()">
      <v-form
        id="profile-form"
        :action="route('users.update', user.username)"
        class="px-5 pb-5"
        @submit.prevent="submitForm"
      >
        <div class="d-flex ga-3 mb-3 align-center">
          <po-avatar :user="avatarUser" size="72" color="secondary" />
          <po-button color="primary" variant="tonal" @click="openAvatarPicker">{{
            $t('main.choose-image')
          }}</po-button>

          <v-file-input
            id="avatar-input"
            class="d-none"
            v-model="formData.avatar"
            :label="$t('main.choose-image')"
            hide-details
          />
        </div>

        <v-checkbox
          v-if="hasAvatar"
          v-model="formData.avatarRemove"
          :label="$t('accounts.remove-current-avatar')"
          hide-details
        />

        <template v-if="isAdmin()">
          <v-select
            v-model="formData.role"
            :label="$t('main.role')"
            hide-details="auto"
            :error-messages="errors.role"
            :items="page.props.roles"
            item-value="id"
            item-title="name"
            required
          />
        </template>

        <v-text-field
          v-model="formData.name"
          type="text"
          :label="$t('main.name')"
          hide-details="auto"
          :error-messages="errors.name"
          minlength="3"
          maxlength="250"
          required
          clearable
        />

        <v-text-field
          v-model="formData.username"
          :label="$t('users.username')"
          hide-details="auto"
          :error-messages="errors.username"
          minlength="3"
          maxlength="100"
          required
          readonly
        />

        <v-text-field
          v-model="formData.email"
          type="text"
          :label="$t('main.email')"
          hide-details="auto"
          :error-messages="errors.email"
          minlength="3"
          maxlength="250"
          required
          clearable
        />

        <v-textarea
          v-model="formData.bio"
          :label="$t('users.bio')"
          hide-details="auto"
          :error-messages="errors.bio"
          minlength="10"
          maxlength="300"
          clearable
          required
        />

        <v-text-field
          v-model="formData.location"
          type="text"
          :label="$t('main.location')"
          hide-details="auto"
          :error-messages="errors.location"
          minlength="3"
          maxlength="250"
          clearable
        />

        <v-text-field
          v-model="formData.occupation"
          type="text"
          :label="$t('main.occupation')"
          hide-details="auto"
          :error-messages="errors.occupation"
          minlength="3"
          maxlength="100"
          clearable
        />

        <v-text-field
          v-model="formData.interests"
          type="text"
          :label="$t('main.interests')"
          hide-details="auto"
          :error-messages="errors.interests"
          minlength="3"
          maxlength="250"
          clearable
        />

        <v-text-field
          v-model="formData.website"
          type="url"
          :label="$t('main.website')"
          hide-details="auto"
          :error-messages="errors.website"
          minlength="3"
          maxlength="250"
          clearable
        />

        <v-text-field
          v-for="field in socialLinkFields"
          :key="field.key"
          v-model="formData[field.key]"
          type="text"
          :label="field.label"
          hide-details="auto"
          :error-messages="errors[field.key]"
          minlength="3"
          :maxlength="field.maxlength"
          clearable
        />

        <po-agreement v-if="!page.props.agreement && user.id === authUser()!.id" />

        <po-button type="submit" color="primary" size="large" block :disabled="isPosting">
          <template v-if="isPosting"><v-progress-circular indeterminate /></template>
          <template v-else>{{ $t('main.save') }}</template>
        </po-button>
      </v-form>

      <v-alert
        v-if="!isEmpty(isPosted)"
        type="success"
        variant="tonal"
        class="mb-5 mx-auto"
        width="85%"
        max-width="600"
      >
        {{ $t('accounts.profile-updated') }}
        {{ $t('main.take-a-look') }}
        <po-link :href="isPosted.url" inertia>{{ $t('main.here') }}.</po-link>
      </v-alert>
    </v-card>
  </po-wrapper>
</template>
