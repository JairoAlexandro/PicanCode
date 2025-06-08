/**
 * @vitest-environment jsdom
 */

import React from 'react';
import { test, expect, beforeEach, afterEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor, cleanup } from '@testing-library/react';
import Profile from '../react/controllers/Profile.jsx';

const initialData = {
  user: {
    id: 1,
    username: 'alice',
    avatar: null,
    bio: '',
    createdAt: '2020-01-15T00:00:00Z',
    followers: 5,
    following: 3
  },
  posts: [],
  isFollowing: false,
  csrfToken: 'token123',
  canFollow: true
};
const apiUrl = '/api/user/1/follow';

beforeEach(() => {
  cleanup();
  global.alert = () => {};
});

afterEach(() => {
  vi.resetAllMocks();
});

test('smoke: renderiza sin errores', () => {
  render(<Profile initialData={initialData} apiUrl={apiUrl} />);
});

test('muestra usuario y fecha de registro formateada', () => {
  render(<Profile initialData={initialData} apiUrl={apiUrl} />);
  expect(screen.getByText('alice')).toBeTruthy();
  expect(screen.getByText(/Miembro desde 15\/1\/2020/)).toBeTruthy();
});

test('avatar fallback muestra inicial', () => {
  render(<Profile initialData={initialData} apiUrl={apiUrl} />);
  expect(screen.getByText('A')).toBeTruthy();
});

test('botón Seguir y su estado inicial', () => {
  render(<Profile initialData={initialData} apiUrl={apiUrl} />);
  const btn = screen.getByRole('button', { name: 'Seguir' });
  expect(btn).toBeTruthy();
  // Verificamos que la clase de fondo azul esté presente
  expect(btn.className.includes('bg-blue-500')).toBe(true);
});

test('toggleFollow hace fetch y cambia texto del botón', async () => {
  const mockResponse = { success: true, isFollowing: true, csrfToken: 'newToken' };
  global.fetch = vi.fn().mockResolvedValue({
    json: async () => mockResponse
  });
  render(<Profile initialData={initialData} apiUrl={apiUrl} />);
  const btn = screen.getByRole('button', { name: 'Seguir' });
  fireEvent.click(btn);
  await waitFor(() => expect(global.fetch).toHaveBeenCalledWith(
    apiUrl,
    expect.objectContaining({ method: 'POST' })
  ));
  expect(screen.getByRole('button', { name: 'Dejar de seguir' })).toBeTruthy();
});

test('muestra biografía por defecto cuando está vacía', () => {
  render(<Profile initialData={initialData} apiUrl={apiUrl} />);
  expect(screen.getByText('Este usuario no ha puesto biografía.')).toBeTruthy();
});

test('muestra estadísticas de posts, seguidores y siguiendo', () => {
  render(<Profile initialData={initialData} apiUrl={apiUrl} />);
  expect(screen.getByText('0')).toBeTruthy();    // posts
  expect(screen.getByText('5')).toBeTruthy();    // seguidores
  expect(screen.getByText('3')).toBeTruthy();    // siguiendo
});

test('cuando no hay posts muestra mensaje adecuado', () => {
  render(<Profile initialData={initialData} apiUrl={apiUrl} />);
  expect(screen.getByText('No ha publicado nada aún.')).toBeTruthy();
});
