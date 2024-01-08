<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = computed(() => usePage())

</script>

<template>
    <po-head />

    <v-card>
        <v-card-text>
            <v-row>
                <v-col cols="12" lg="4">
                    <p class="text-uppercase text-disabled mb-3">{{ $t('categories.main-categories') }}</p>
                    <div class="d-inline-flex flex-wrap ga-2 mb-10">
                        <template v-for="cat in page.props.categories.main" :key="cat.id">
                            <po-chip :href="$route('categories.show', cat.slug)" color="primary" inertia>
                                {{ cat.name }}
                                <span>&nbsp;({{ cat.writings_count }})</span>
                            </po-chip>
                        </template>
                    </div>

                    <p class="text-uppercase text-disabled mb-3">{{ $t('categories.alt-categories') }}</p>
                    <div class="d-inline-flex flex-wrap ga-2 mb-10">
                        <template v-for="cat in page.props.categories.alt" :key="cat.id">
                            <po-chip :href="$route('categories.show', cat.slug)" color="primary" inertia>
                                {{ cat.name }}
                                <span>&nbsp;({{ cat.writings_count }})</span>
                            </po-chip>
                        </template>
                    </div>
                </v-col>

                <v-col cols="12" lg="4">
                    <p class="text-uppercase text-disabled mb-3">{{ $t('tags.tags') }}</p>
                    <div class="d-inline-flex flex-wrap ga-2 mb-10">
                        <template v-for="tag in page.props.tags" :key="tag.id">
                            <po-chip :href="$route('tags.show', tag.slug)" color="primary" variant="tonal" inertia>
                                {{ tag.name }}
                                <span>&nbsp;({{ tag.writings_count }})</span>
                            </po-chip>
                        </template>
                    </div>
                </v-col>

                <v-col cols="12" lg="4">
                    <p class="text-uppercase text-disabled mb-3">{{ $t('main.featured-authors') }}</p>
                    <div class="d-inline-flex flex-wrap ga-2 mb-10">
                        <template v-for="author in page.props.authors" :key="author.id">
                            <po-link :href="$route('users.show', author.username)" inertia>
                                <po-avatar size="64" color="secondary" :user="author" />
                            </po-link>
                        </template>
                    </div>
                </v-col>
            </v-row>
        </v-card-text>
    </v-card>
</template>
