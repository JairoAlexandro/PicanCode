/**
 * @vitest-environment jsdom
 */

import React from 'react';
import { test, expect, beforeEach, afterEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor, act, cleanup } from '@testing-library/react';
import ProfileEdit from '../react/controllers/ProfileEdit.jsx';

const apiUrl = '/api/profile/edit';

beforeEach(() => {
  cleanup();
  delete window.location;
  window.location = { href: '' };
});

afterEach(() => {
  vi.resetAllMocks();
});

test('smoke: renderiza sin errores', () => {
  render(<ProfileEdit initialData={{ bio: '', avatar: null }} apiUrl={apiUrl} />);
});

test('muestra avatar inicial cuando existe', () => {
  render(<ProfileEdit initialData={{ bio: '', avatar: 'foo.png' }} apiUrl={apiUrl} />);
  const img = screen.getByAltText('Avatar Preview');
  expect(img).toBeTruthy();
  expect(img.src).toContain('foo.png');
});

test('cambio de archivo actualiza preview', async () => {
  render(<ProfileEdit initialData={{ bio: '', avatar: null }} apiUrl={apiUrl} />);
  const file = new File(['x'], 'a.png', { type: 'image/png' });
  const dataURL = 'data:image/png;base64,xyz';
  vi.stubGlobal('FileReader', class {
    onload = null;
    readAsDataURL() { this.result = dataURL; this.onload(); }
  });
  // obtenemos el input directamente por su id
  const input = document.querySelector('#avatar-upload');
  await act(async () => fireEvent.change(input, { target: { files: [file] } }));
  const img = await screen.findByAltText('Avatar Preview');
  expect(img.src).toBe(dataURL);
});

test('editar bio actualiza textarea', () => {
  render(<ProfileEdit initialData={{ bio: 'Hola', avatar: null }} apiUrl={apiUrl} />);
  const textarea = screen.getByPlaceholderText('Escribe tu biografía...');
  expect(textarea.value).toBe('Hola');
  fireEvent.change(textarea, { target: { value: 'Nuevo bio' } });
  expect(textarea.value).toBe('Nuevo bio');
});

test('submit exitoso redirige', async () => {
  const redirectUrl = '/profile/1';
  global.fetch = vi.fn().mockResolvedValue({
    json: async () => ({ success: true, redirectUrl })
  });
  render(<ProfileEdit initialData={{ bio: '', avatar: null }} apiUrl={apiUrl} />);
  fireEvent.click(screen.getByRole('button', { name: 'Guardar cambios' }));
  await waitFor(() => expect(window.location.href).toBe(redirectUrl));
});

test('submit con errores muestra lista', async () => {
  const errors = ['Err1', 'Err2'];
  global.fetch = vi.fn().mockResolvedValue({
    json: async () => ({ success: false, errors })
  });
  render(<ProfileEdit initialData={{ bio: '', avatar: null }} apiUrl={apiUrl} />);
  fireEvent.click(screen.getByRole('button', { name: 'Guardar cambios' }));
  for (const err of errors) {
    expect(await screen.findByText(err)).toBeTruthy();
  }
});

test('error de red muestra mensaje genérico', async () => {
  global.fetch = vi.fn().mockRejectedValue(new Error('fail'));
  render(<ProfileEdit initialData={{ bio: '', avatar: null }} apiUrl={apiUrl} />);
  fireEvent.click(screen.getByRole('button', { name: 'Guardar cambios' }));
  expect(await screen.findByText('Error de red o respuesta inválida.')).toBeTruthy();
});
