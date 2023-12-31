<script setup>
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = computed(() => usePage())

</script>

<template>
    <po-head :title="page.props.title" />

    <v-row>
        <v-col cols="12" lg="4">
            Categories
        </v-col>

        <v-col cols="12" lg="4">
            Tags
        </v-col>

        <v-col cols="12" lg="4">
            Users
        </v-col>
    </v-row>
</template>
