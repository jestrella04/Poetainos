import { describe, expect, it, vi, afterEach } from 'vitest'
import { useNativeShare } from '../useNativeShare'

afterEach(() => {
  vi.unstubAllGlobals()
})

describe('useNativeShare', () => {
  it('calls navigator.share when available instead of the fallback', () => {
    const shareMock = vi.fn()
    vi.stubGlobal('navigator', { share: shareMock })
    const onUnsupported = vi.fn()

    const { share } = useNativeShare()
    share('Title', '/some/url', onUnsupported)

    expect(shareMock).toHaveBeenCalledWith({ title: 'Title', url: '/some/url' })
    expect(onUnsupported).not.toHaveBeenCalled()
  })

  it('falls back when navigator.share is unavailable', () => {
    vi.stubGlobal('navigator', {})
    const onUnsupported = vi.fn()

    const { share } = useNativeShare()
    share('Title', '/some/url', onUnsupported)

    expect(onUnsupported).toHaveBeenCalledOnce()
  })
})
