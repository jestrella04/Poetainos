import { afterEach, describe, expect, it } from 'vitest'
import { themeStylesheetHead, vuetify } from '../vuetify'

describe('themeStylesheetHead', () => {
  afterEach(async () => {
    await vuetify.theme.change('light')
  })

  it("renders Vuetify's theme CSS as a single style element under Vuetify's stylesheet id", () => {
    // When
    const elements = themeStylesheetHead()

    // Then
    expect(elements).toHaveLength(1)
    expect(elements[0]).toMatch(/^<style id="vuetify-theme-stylesheet">/)
    expect(elements[0]).toContain('--v-theme-primary:')
    expect(elements[0]).toContain('.v-theme--light')
    expect(elements[0]).toContain('.v-theme--dark')
  })

  it('reflects the active theme so a client-side re-render never reverts it', async () => {
    // Given
    await vuetify.theme.change('dark')

    // When
    const [stylesheet] = themeStylesheetHead()

    // Then
    expect(stylesheet).toContain('color-scheme: dark')
  })
})
