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

  it('does not post and keeps errors empty when the form is natively invalid', async () => {
    // Given
    document.body.innerHTML =
      '<form id="test-form" action="/things/1"><input name="title" required></form>'
    const post = vi.spyOn(axios, 'post')
    const onSuccess = vi.fn()
    const { isPosting, submitForm } = useFormSubmit(false)

    // When
    await submitForm({
      formSelector: '#test-form',
      payload: {},
      onSuccess,
      onError: () => true
    })

    // Then
    expect(post).not.toHaveBeenCalled()
    expect(onSuccess).not.toHaveBeenCalled()
    expect(isPosting.value).toBe(false)
  })

  it('posts an invalid form anyway when validation is turned off', async () => {
    // Given
    document.body.innerHTML =
      '<form id="test-form" action="/things/1"><input name="title" required></form>'
    const post = vi.spyOn(axios, 'post').mockResolvedValueOnce({})
    const { submitForm } = useFormSubmit(false)

    // When
    await submitForm({
      formSelector: '#test-form',
      payload: {},
      validate: false,
      onSuccess: vi.fn(),
      onError: () => true
    })

    // Then
    expect(post).toHaveBeenCalledOnce()
  })

  it('posts to the given url instead of the form action', async () => {
    // Given
    const post = vi.spyOn(axios, 'post').mockResolvedValueOnce({})
    const { submitForm } = useFormSubmit(false)

    // When
    await submitForm({
      formSelector: '#test-form',
      url: '/elsewhere',
      payload: { a: 1 },
      onSuccess: vi.fn(),
      onError: () => true
    })

    // Then
    expect(post).toHaveBeenCalledWith('/elsewhere', { a: 1 })
  })

  it('sends multipart form data when asked to', async () => {
    // Given
    const post = vi.spyOn(axios, 'post')
    const postForm = vi.spyOn(axios, 'postForm').mockResolvedValueOnce({})
    const { submitForm } = useFormSubmit(false)

    // When
    await submitForm({
      formSelector: '#test-form',
      payload: { cover: 'file' },
      multipart: true,
      onSuccess: vi.fn(),
      onError: () => true
    })

    // Then
    expect(postForm).toHaveBeenCalledWith('http://localhost:3000/things/1', { cover: 'file' })
    expect(post).not.toHaveBeenCalled()
  })

  it('hands the response body to onSuccess', async () => {
    // Given
    vi.spyOn(axios, 'post').mockResolvedValueOnce({ data: { url: '/writings/a' } })
    const onSuccess = vi.fn()
    const { submitForm } = useFormSubmit(false)

    // When
    await submitForm<{ url: string }>({
      formSelector: '#test-form',
      payload: {},
      onSuccess,
      onError: () => true
    })

    // Then
    expect(onSuccess).toHaveBeenCalledWith({ url: '/writings/a' })
  })

  it('clears previous errors when a new submit starts', async () => {
    // Given
    vi.spyOn(axios, 'post').mockRejectedValueOnce(new Error('boom'))
    const { errors, submitForm } = useFormSubmit<string[]>([])
    await submitForm({
      formSelector: '#test-form',
      payload: {},
      onSuccess: vi.fn(),
      onError: () => ['failed']
    })
    expect(errors.value).toEqual(['failed'])
    vi.spyOn(axios, 'post').mockResolvedValueOnce({})

    // When
    await submitForm({
      formSelector: '#test-form',
      payload: {},
      onSuccess: vi.fn(),
      onError: () => ['failed']
    })

    // Then
    expect(errors.value).toEqual([])
  })

  it('keeps isPosting on until the cooldown has passed', async () => {
    // Given
    vi.useFakeTimers()
    vi.spyOn(axios, 'post').mockResolvedValueOnce({})
    const { isPosting, submitForm } = useFormSubmit(false)

    // When
    await submitForm({
      formSelector: '#test-form',
      payload: {},
      cooldown: true,
      onSuccess: vi.fn(),
      onError: () => true
    })

    // Then
    expect(isPosting.value).toBe(true)

    vi.advanceTimersByTime(1000)

    expect(isPosting.value).toBe(false)

    vi.useRealTimers()
  })
})
