/**
 * @vitest-environment jsdom
 */

import React from 'react';
import { test, expect, beforeEach, afterEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor, cleanup } from '@testing-library/react';
import PostIndex from '../react/controllers/PostIndex.jsx';

const apiUrl = '/api/posts';

const samplePosts = [
  {
    id: 1,
    authorId: 10,
    author: 'Bob',
    avatar: null,
    createdAt: '2025-06-01T10:00:00Z',
    title: 'Título de prueba',
    snippet: 'Este es un snippet de prueba que excede los 150 caracteres. '.repeat(4),
    likes: 5,
    comments: 2,
    commentsCount: 2,  
    media: null
  }
];

beforeEach(() => {
  cleanup();
  window.isSecureContext = true;
  navigator.clipboard = { writeText: vi.fn().mockResolvedValue() };
});

afterEach(() => {
  vi.resetAllMocks();
});

test('smoke: renderiza sin errores', () => {
  render(<PostIndex apiUrl={apiUrl} initialData={{ view: 'recent', posts: [] }} />);
});

test('muestra botones Recientes y Siguiendo y asigna clase activa', () => {
  render(<PostIndex apiUrl={apiUrl} initialData={{ view: 'following', posts: [] }} />);
  const btnRecientes = screen.getByText('Recientes');
  const btnSiguiendo = screen.getByText('Siguiendo');
  expect(btnRecientes).toBeTruthy();
  expect(btnSiguiendo).toBeTruthy();
  // Siguiendo debe tener clase bg-blue-500
  expect(btnSiguiendo.className.includes('bg-blue-500')).toBe(true);
  // Recientes no activa
  expect(btnRecientes.className.includes('bg-gray-700')).toBe(true);
});

test('muestra encabezado adecuado según vista', () => {
  const { rerender } = render(<PostIndex apiUrl={apiUrl} initialData={{ view: 'recent', posts: [] }} />);
  expect(screen.getByText('Publicaciones recientes')).toBeTruthy();
  rerender(<PostIndex apiUrl={apiUrl} initialData={{ view: 'following', posts: [] }} />);
  expect(screen.getByText('Publicaciones de a quienes sigues')).toBeTruthy();
});

test('cuando no hay posts muestra mensaje', () => {
  render(<PostIndex apiUrl={apiUrl} initialData={{ view: 'recent', posts: [] }} />);
  expect(screen.getByText('No hay publicaciones para mostrar.')).toBeTruthy();
});

test('renderiza artículo con datos correctos', () => {
  render(<PostIndex apiUrl={apiUrl} initialData={{ view: 'recent', posts: samplePosts }} />);
  // Título con enlace
  const titleLink = screen.getByText('Título de prueba');
  expect(titleLink.tagName).toBe('A');
  expect(titleLink.getAttribute('href')).toBe('/posts/1');
  // Autor con enlace
  const authorLink = screen.getByText('Bob');
  expect(authorLink.tagName).toBe('A');
  expect(authorLink.getAttribute('href')).toBe('/user/10');
  // Fecha formateada
  expect(screen.getByText('1/6/2025')).toBeTruthy();
  // Likes y comments
  expect(screen.getByText('5')).toBeTruthy();
  expect(screen.getByText('2')).toBeTruthy();
  // Snippet truncado
  const snippet = samplePosts[0].snippet;
  const expected = snippet.slice(0, 150) + '…';
  expect(screen.getByText(expected)).toBeTruthy();
});

test('copiar snippet usa Clipboard API y muestra notificación', async () => {
  render(<PostIndex apiUrl={apiUrl} initialData={{ view: 'recent', posts: samplePosts }} />);
  const copyBtn = screen.getByLabelText('Copiar snippet');
  fireEvent.click(copyBtn);
  await waitFor(() => expect(navigator.clipboard.writeText).toHaveBeenCalledWith(samplePosts[0].snippet));
  expect(screen.getByText('Texto copiado')).toBeTruthy();
});
