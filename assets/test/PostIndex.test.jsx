/**
 * @vitest-environment jsdom
 */

import React from 'react'
import { test, expect, afterEach } from 'vitest'
import { render, screen, cleanup } from '@testing-library/react'
import PostIndex from '../react/controllers/PostIndex.jsx'

afterEach(() => {
  cleanup()
})

const apiUrl = '/api/posts'

test('smoke: renderiza sin errores', () => {
  render(<PostIndex apiUrl={apiUrl} initialData={{ view: 'recent', posts: [] }} />)
})

test('muestra los botones Recientes y Siguiendo', () => {
  render(<PostIndex apiUrl={apiUrl} initialData={{ view: 'recent', posts: [] }} />)
  const recientes = screen.getByText('Recientes')
  const siguiendo = screen.getByText('Siguiendo')
  expect(recientes).toBeDefined()
  expect(siguiendo).toBeDefined()
})

test('cuando no hay posts muestra mensaje “No hay publicaciones”', () => {
  render(<PostIndex apiUrl={apiUrl} initialData={{ view: 'recent', posts: [] }} />)
  const mensaje = screen.getByText(/No hay publicaciones para mostrar/i)
  expect(mensaje).toBeDefined()
})
