import { describe, expect, it, vi, beforeEach } from 'vitest'
import axios from 'axios'
import { useFormSubmit } from '../useFormSubmit'

beforeEach(() => {
  document.body.innerHTML = '<form id="test-form" action="/things/1"></form>'
  vi.restoreAllMocks()
})

describe('useFormSubmit', () => {
  it('does nothing when the form is not found', async () => {
    // Given
    const post = vi.spyOn(axios, 'post')
    const { submitForm } = useFormSubmit(false)

    // When
    await submitForm({
      formSelector: '#missing-form',
      payload: {},
      onSuccess: vi.fn(),
      onError: () => true
    })

    // Then
    expect(post).not.toHaveBeenCalled()
  })

  it('posts the payload to the form action and calls onSuccess', async () => {
    // Given
    const post = vi.spyOn(axios, 'post').mockResolvedValueOnce({})
    const onSuccess = vi.fn()
    const { isPosting, errors, submitForm } = useFormSubmit(false)

    // When
    const promise = submitForm({
      formSelector: '#test-form',
      payload: { _method: 'DELETE' },
      onSuccess,
      onError: () => true
    })

    // Then
    expect(isPosting.value).toBe(true)

    await promise

    expect(post).toHaveBeenCalledWith('http://localhost:3000/things/1', { _method: 'DELETE' })
    expect(onSuccess).toHaveBeenCalledOnce()
    expect(errors.value).toBe(false)
    expect(isPosting.value).toBe(false)
  })

  it('sets errors from onError and skips onSuccess when the post fails', async () => {
    // Given
    vi.spyOn(axios, 'post').mockRejectedValueOnce(new Error('network error'))
    const onSuccess = vi.fn()
    const { isPosting, errors, submitForm } = useFormSubmit(false)

    // When
    await submitForm({
      formSelector: '#test-form',
      payload: {},
      onSuccess,
      onError: () => true
    })

    // Then
    expect(onSuccess).not.toHaveBeenCalled()
    expect(errors.value).toBe(true)
    expect(isPosting.value).toBe(false)
  })

  it('runs preSubmit before the main post and stops on preSubmit failure', async () => {
    // Given
    const post = vi.spyOn(axios, 'post').mockResolvedValue({})
    const preSubmit = vi.fn().mockRejectedValueOnce(new Error('bad password'))
    const onSuccess = vi.fn()
    const { errors, submitForm } = useFormSubmit(false)

    // When
    await submitForm({
      formSelector: '#test-form',
      payload: { _method: 'DELETE' },
      preSubmit,
      onSuccess,
      onError: () => true
    })

    // Then
    expect(preSubmit).toHaveBeenCalledOnce()
    expect(post).not.toHaveBeenCalled()
    expect(onSuccess).not.toHaveBeenCalled()
    expect(errors.value).toBe(true)
  })

  it('calls the main post after preSubmit resolves', async () => {
    // Given
    const post = vi.spyOn(axios, 'post').mockResolvedValue({})
    const preSubmit = vi.fn().mockResolvedValueOnce(undefined)
    const onSuccess = vi.fn()
    const { submitForm } = useFormSubmit(false)

    // When
    await submitForm({
      formSelector: '#test-form',
      payload: { _method: 'DELETE' },
      preSubmit,
      onSuccess,
      onError: () => true
    })

    // Then
    expect(preSubmit).toHaveBeenCalledOnce()
    expect(post).toHaveBeenCalledWith('http://localhost:3000/things/1', { _method: 'DELETE' })
    expect(onSuccess).toHaveBeenCalledOnce()
  })
})
