import { describe, expect, it } from 'vitest'
import { useSocialLinks } from '../useSocialLinks'

const { socialLink } = useSocialLinks()

describe('socialLink', () => {
  it('builds a profile URL for a known network', () => {
    expect(socialLink('jane', 'twitter')).toBe('https://twitter.com/jane')
  })

  it('returns an empty string for an unknown network', () => {
    expect(socialLink('jane', 'myspace')).toBe('')
  })
})
