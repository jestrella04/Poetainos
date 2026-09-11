import type { AxiosError } from 'axios'

// Laravel's default validation-failure (422) response shape, used across
// every form component's axios `.catch()` handler.
export type LaravelValidationErrors = Record<string, string[]>
export type ValidationError = AxiosError<{ errors: LaravelValidationErrors }>
