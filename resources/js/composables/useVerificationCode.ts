import { onScopeDispose, ref } from 'vue'
import type { Ref } from 'vue'
import axios from 'axios'
import { useI18n } from 'vue-i18n'
import type { ValidationError } from '@/types/http'

const RESEND_COOLDOWN_SECONDS = 60
const FLASH_DURATION_MS = 6000

type ResendOutcome = 'success' | 'error'

/**
 * State and actions for a page where the user types an emailed one-time code:
 * submitting it, showing why it was rejected, and resending it with a cooldown.
 */
export function useVerificationCode(): {
  code: Ref<string>
  isVerifying: Ref<boolean>
  codeError: Ref<string>
  resendOutcome: Ref<ResendOutcome | null>
  resendCountdown: Ref<number>
  verifyCode: (url: string, onVerified: (redirectUrl: string) => void) => Promise<void>
  resendCode: (url: string) => Promise<void>
} {
  const { t } = useI18n()
  const code = ref('')
  const isVerifying = ref(false)
  const codeError = ref('')
  const resendOutcome = ref<ResendOutcome | null>(null)
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

  async function verifyCode(url: string, onVerified: (redirectUrl: string) => void): Promise<void> {
    isVerifying.value = true
    codeError.value = ''

    await axios
      .post<{ url: string }>(url, { code: code.value })
      .then((response) => {
        onVerified(response.data.url)
      })
      .catch((error: ValidationError) => {
        code.value = ''
        codeError.value = error.response?.data.errors?.code?.[0] ?? t('main.error-try-again')
      })
      .finally(() => {
        isVerifying.value = false
      })
  }

  async function resendCode(url: string): Promise<void> {
    await axios
      .post(url)
      .then(() => {
        code.value = ''
        codeError.value = ''
        showResendOutcome('success')
        startResendCountdown()
      })
      .catch(() => {
        showResendOutcome('error')
      })
  }

  onScopeDispose(() => {
    clearInterval(countdownTimer)
    clearTimeout(outcomeTimer)
  })

  return { code, isVerifying, codeError, resendOutcome, resendCountdown, verifyCode, resendCode }
}
