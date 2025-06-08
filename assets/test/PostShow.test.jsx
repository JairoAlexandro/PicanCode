/**
 * @vitest-environment jsdom
 */

import React from 'react';
import { test, expect, beforeEach, afterEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor, cleanup } from '@testing-library/react';
import PostShow from '../react/controllers/PostShow.jsx';

const sampleData = {
  id: 42,
  authorId: 7,
  author: 'Alice',
  avatar: null,
  createdAt: '2025-06-01T12:00:00Z',
  title: 'Hola Mundo',
  content: 'console.log("Hola Mundo")',
  media: null,
  comments: [],
  likes: 0,
  likedByCurrentUser: false
};

const user = { id: 7 };
const anon = null;
const commentUrl = '/api/posts/42/comments';
const likeUrl = '/api/posts/42/like';

beforeEach(() => {
  cleanup();
  global.confirm = () => true;
});

afterEach(() => {
  vi.resetAllMocks();
});

test('smoke: renderiza sin errores con datos mínimos', () => {
  render(<PostShow data={sampleData} apiUrl={commentUrl} user={anon} />);
});

test('muestra título, autor y fecha formateada', () => {
  render(<PostShow data={sampleData} apiUrl={commentUrl} user={anon} />);
  expect(screen.getByText('Hola Mundo')).toBeTruthy();
  expect(screen.getByText('Alice')).toBeTruthy();
  // el componente muestra "1/6/2025"
  expect(screen.getByText('1/6/2025')).toBeTruthy();
});

test('pre muestra el contenido del post', () => {
  render(<PostShow data={sampleData} apiUrl={commentUrl} user={anon} />);
  expect(screen.getByText('console.log("Hola Mundo")', { selector: 'pre' })).toBeTruthy();
});

test('like hace fetch y actualiza estado internamente', async () => {
  const fetchMock = vi.fn().mockResolvedValue({ ok: true, json: async () => ({ liked: true }) });
  global.fetch = fetchMock;

  const { container } = render(<PostShow data={sampleData} apiUrl={likeUrl} user={anon} />);
  // el primer div clickabe del área de like
  const likeDiv = container.querySelector('.cursor-pointer');
  fireEvent.click(likeDiv);

  await waitFor(() => expect(fetchMock).toHaveBeenCalledWith(likeUrl, expect.any(Object)));
});

test('comentarios iniciales muestra "No hay comentarios aún."', () => {
  render(<PostShow data={sampleData} apiUrl={commentUrl} user={anon} />);
  expect(screen.getByText('No hay comentarios aún.')).toBeTruthy();
});

test('usuario logueado puede enviar comentario y aparece en la lista', async () => {
  const newComment = { id: 100, author: 'Bob', createdAt: '2025-06-08', content: 'Buen post!' };
  const fetchMock = vi.fn().mockResolvedValue({ ok: true, json: async () => ({ comment: newComment }) });
  global.fetch = fetchMock;

  render(<PostShow data={sampleData} apiUrl={commentUrl} user={user} />);
  const textarea = screen.getByLabelText('Tu comentario');
  fireEvent.change(textarea, { target: { value: 'Buen post!' } });
  fireEvent.click(screen.getByRole('button', { name: 'Comentar' }));

  await waitFor(() => expect(fetchMock).toHaveBeenCalledWith(commentUrl, expect.any(Object)));
  // tras enviar, comprobamos value manualmente
  expect(textarea.value).toBe('');
  // y aparece el nuevo comentario
  expect(screen.getByText('Buen post!')).toBeTruthy();
  expect(screen.getByText('Bob')).toBeTruthy();
});

test('click en copiar texto muestra notificación temporal', async () => {
  window.isSecureContext = true;
  navigator.clipboard = { writeText: vi.fn().mockResolvedValue() };

  render(<PostShow data={sampleData} apiUrl={commentUrl} user={anon} />);
  fireEvent.click(screen.getByLabelText('Copiar contenido completo'));

  await waitFor(() => expect(navigator.clipboard.writeText).toHaveBeenCalledWith(sampleData.content));
  expect(screen.getByText('Texto copiado')).toBeTruthy();
});

test('imagen media abre modal al hacer click (si media existe)', () => {
  const withMedia = { ...sampleData, media: 'foto.png' };
  render(<PostShow data={withMedia} apiUrl={commentUrl} user={anon} />);
  fireEvent.click(screen.getByAltText('Hola Mundo'));
  expect(screen.getByAltText('Imagen ampliada')).toBeTruthy();
});
