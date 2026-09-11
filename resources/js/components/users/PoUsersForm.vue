<script setup lang="ts">
import { provide, reactive, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import axios from 'axios'
import { formDataKey, helperKey } from '@/composables/keys'
import { injectStrict } from '@/composables/injectStrict'
import type { InertiaPageProps } from '@/types/inertia'
import type { LaravelValidationErrors, ValidationError } from '@/types/http'

// $user (the raw Eloquent model, not a select()) — a different shape from
// types/models.ts's `User` (which reflects UsersController::show()'s
// flattened extra_info->x AS x columns): here extra_info is still a
// genuine nested object, cast by the model.
interface EditableUser {
  id: number
  username: string
  name?: string | null
  email: string
  role_id?: number | null
  extra_info?: {
    bio?: string
    location?: string
    occupation?: string
    interests?: string
    website?: string
    social?: Record<string, string>
  } | null
}

interface Role {
  id: number
  name: string
}

interface PostedResult {
  url: string
}

const page = usePage<InertiaPageProps<{ user: EditableUser; roles: Role[]; agreement: boolean }>>()
const helper = injectStrict(helperKey)

const user = page.props.user
const formData = reactive({
  avatar: [] as File[],
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
const errors = ref<LaravelValidationErrors>({})
const isPosting = ref(false)
const isPosted = ref<Partial<PostedResult>>({})

provide(formDataKey, formData)

// Init form data
formData.role = user.role_id ?? ''
formData.name = user.name ?? ''
formData.username = user.username ?? ''
formData.email = user.email ?? ''

if (user.extra_info) {
  formData.bio = user.extra_info.bio ?? ''
  formData.location = user.extra_info.location ?? ''
  formData.occupation = user.extra_info.occupation ?? ''
  formData.interests = user.extra_info.interests ?? ''
  formData.website = user.extra_info.website ?? ''

  if (user.extra_info.social) {
    formData.twitter = user.extra_info.social.twitter ?? ''
    formData.threads = user.extra_info.social.threads ?? ''
    formData.instagram = user.extra_info.social.instagram ?? ''
    formData.facebook = user.extra_info.social.facebook ?? ''
    formData.youtube = user.extra_info.social.youtube ?? ''
    formData.goodreads = user.extra_info.social.goodreads ?? ''
  }
}

function clearErrors() {
  errors.value = {}
}

async function submitForm() {
  const form = document.querySelector<HTMLFormElement>('#profile-form')

  clearErrors()

  if (!form || !helper.checkFormValidity(form)) {
    return
  }

  isPosted.value = {}
  isPosting.value = true

  await axios
    .postForm<PostedResult>(form.action, {
      _method: 'PUT',
      avatar: formData.avatar,
      role: formData.role,
      name: formData.name,
      email: formData.email,
      bio: formData.bio,
      location: formData.location,
      occupation: formData.occupation,
      interests: formData.interests,
      website: formData.website,
      twitter: formData.twitter,
      threads: formData.threads,
      instagram: formData.instagram,
      facebook: formData.facebook,
      youtube: formData.youtube,
      goodreads: formData.goodreads,
      service_agreement: formData.serviceAgreement,
      privacy_agreement: formData.privacyAgreement
    })
    .then((response) => {
      clearErrors()
      isPosted.value = response.data
    })
    .catch((error: ValidationError) => {
      errors.value = error.response?.data.errors ?? {}
    })
    .finally(() => {
      setTimeout(() => {
        isPosting.value = false
      }, 1000)
    })
}

function file() {
  document.querySelector<HTMLElement>('#avatar-input')?.click()
}
</script>

<template>
  <po-wrapper class="w-100" style="max-width: 900px">
    <po-head></po-head>
    <v-card :title="$t('accounts.update-profile').toUpperCase()">
      <v-form
        id="profile-form"
        :action="route('users.update', user.username)"
        class="px-5 pb-5"
        @submit.prevent="submitForm"
      >
        <div class="d-flex ga-3 mb-3 align-center">
          <po-avatar :user="user" size="72" color="secondary"></po-avatar>
          <po-button color="primary" variant="tonal" @click="file">{{
            $t('main.choose-image')
          }}</po-button>

          <v-file-input
            id="avatar-input"
            class="d-none"
            v-model="formData.avatar"
            label="avatar"
            hide-details
          ></v-file-input>
        </div>

        <template v-if="$helper.admin()">
          <v-select
            v-model="formData.role"
            :label="$t('main.role')"
            hide-details="auto"
            :error-messages="errors.role"
            :items="page.props.roles"
            item-value="id"
            item-title="name"
            required
          ></v-select>
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
        ></v-text-field>

        <v-text-field
          v-model="formData.username"
          :label="$t('users.username')"
          hide-details="auto"
          :error-messages="errors.username"
          minlength="3"
          maxlength="100"
          required
          readonly
        ></v-text-field>

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
        ></v-text-field>

        <v-textarea
          v-model="formData.bio"
          :label="$t('users.bio')"
          hide-details="auto"
          :error-messages="errors.bio"
          minlength="10"
          maxlength="300"
          clearable
          required
        ></v-textarea>

        <v-text-field
          v-model="formData.location"
          type="text"
          :label="$t('main.location')"
          hide-details="auto"
          :error-messages="errors.location"
          minlength="3"
          maxlength="250"
          clearable
        ></v-text-field>

        <v-text-field
          v-model="formData.occupation"
          type="text"
          :label="$t('main.occupation')"
          hide-details="auto"
          :error-messages="errors.occupation"
          minlength="3"
          maxlength="100"
          clearable
        ></v-text-field>

        <v-text-field
          v-model="formData.interests"
          type="text"
          :label="$t('main.interests')"
          hide-details="auto"
          :error-messages="errors.interests"
          minlength="3"
          maxlength="250"
          clearable
        ></v-text-field>

        <v-text-field
          v-model="formData.website"
          type="url"
          :label="$t('main.website')"
          hide-details="auto"
          :error-messages="errors.website"
          minlength="3"
          maxlength="100"
          clearable
        ></v-text-field>

        <v-text-field
          v-model="formData.twitter"
          type="text"
          label="X (Twitter)"
          hide-details="auto"
          :error-messages="errors.twitter"
          minlength="3"
          maxlength="250"
          clearable
        ></v-text-field>

        <v-text-field
          v-model="formData.threads"
          type="text"
          label="Threads"
          hide-details="auto"
          :error-messages="errors.threads"
          minlength="3"
          maxlength="250"
          clearable
        ></v-text-field>

        <v-text-field
          v-model="formData.instagram"
          type="text"
          label="Instagram"
          hide-details="auto"
          :error-messages="errors.instagram"
          minlength="3"
          maxlength="100"
          clearable
        ></v-text-field>

        <v-text-field
          v-model="formData.facebook"
          type="text"
          label="Facebook"
          hide-details="auto"
          :error-messages="errors.facebook"
          minlength="3"
          maxlength="250"
          clearable
        ></v-text-field>

        <v-text-field
          v-model="formData.youtube"
          type="text"
          label="Youtube"
          hide-details="auto"
          :error-messages="errors.youtube"
          minlength="3"
          maxlength="100"
          clearable
        ></v-text-field>

        <v-text-field
          v-model="formData.goodreads"
          type="text"
          label="Goodreads"
          hide-details="auto"
          :error-messages="errors.goodreads"
          minlength="3"
          maxlength="250"
          clearable
        ></v-text-field>

        <po-agreement
          v-if="!page.props.agreement && user.id === $helper.authUser()!.id"
        ></po-agreement>

        <po-button type="submit" color="primary" size="large" block :disabled="isPosting">
          <template v-if="isPosting"
            ><v-progress-circular indeterminate></v-progress-circular
          ></template>
          <template v-else>{{ $t('main.save') }}</template>
        </po-button>
      </v-form>

      <v-alert
        v-if="!helper.isEmpty(isPosted)"
        type="success"
        variant="tonal"
        class="mb-5 mx-auto"
        style="width: 85%; max-width: 600px"
      >
        {{ $t('accounts.profile-updated') }}
        {{ $t('main.take-a-look') }}
        <po-link :href="isPosted.url" inertia>{{ $t('main.here') }}.</po-link>
      </v-alert>
    </v-card>
  </po-wrapper>
</template>
