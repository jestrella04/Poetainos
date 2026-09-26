import { onScopeDispose, ref } from 'vue'
import type { Ref } from 'vue'
import { useForm, useHttp } from '@inertiajs/vue3'
import { useRequestFailure } from '@/composables/useRequestFailure'

const RESEND_COOLDOWN_SECONDS = 60
const FLASH_DURATION_MS = 6000

type ResendOutcome = 'success' | 'error'

/**
 * State and actions for a page where the user types an emailed one-time code:
 * submitting it (the server redirects onward once it matches), showing why it
 * was rejected, and resending it with a cooldown.
 */
export function useVerificationCode() {
  const form = useForm({ code: '' })
  const resendRequest = useHttp({})
  const { onHttpException, onNetworkError, whenSettled } = useRequestFailure()
  const resendOutcome: Ref<ResendOutcome | null> = ref(null)
  const resendCountdown = ref(0)

  let countdownTimer: ReturnType<typeof setInterval> | undefined
  let outcomeTimer: ReturnType<typeof setTimeout> | undefined

  function showResendOutcome(outcome: ResendOutcome): void {
    resendOutcome.value = outcome
    clearTimeout(outcomeTimer)
    outcomeTimer = setTimeout(() => {
      resendOutcome.value = null
    }, FLASH_DURATION_MS)
  }

  function startResendCountdown(): void {
    resendCountdown.value = RESEND_COOLDOWN_SECONDS
    clearInterval(countdownTimer)
    countdownTimer = setInterval(() => {
      resendCountdown.value--

      if (resendCountdown.value <= 0) {
        clearInterval(countdownTimer)
      }
    }, 1000)
  }

  function verifyCode(url: string): void {
    form.post(url, {
      onHttpException,
      onNetworkError,
      onError: () => {
        form.reset('code')
      }
    })
  }

  async function resendCode(url: string): Promise<void> {
    await whenSettled(
      resendRequest.post(url, {
        onSuccess: () => {
          form.reset()
          form.clearErrors()
          showResendOutcome('success')
          startResendCountdown()
        },
        onHttpException: () => {
          showResendOutcome('error')
        },
        onNetworkError: () => {
          showResendOutcome('error')
        }
      })
    )
  }

  onScopeDispose(() => {
    clearInterval(countdownTimer)
    clearTimeout(outcomeTimer)
  })

  return { form, resendOutcome, resendCountdown, verifyCode, resendCode }
}
