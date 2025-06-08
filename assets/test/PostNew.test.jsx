/**
 * @vitest-environment jsdom
 */

import React from 'react';
import { test, expect, beforeEach, afterEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor, act, cleanup } from '@testing-library/react';
import PostNew from '../react/controllers/PostNew.jsx';

const apiUrl = '/api/posts';
const initialData = {};

beforeEach(() => {
  // No hay fetch en useEffect aquí
});

afterEach(() => {
  cleanup();
  vi.resetAllMocks();
  delete window.location;
  window.location = { href: '' };
});

test('smoke: renderiza sin errores', () => {
  render(<PostNew apiUrl={apiUrl} initialData={initialData} />);
});

test('tiene select de lenguaje y textarea de código', () => {
  render(<PostNew apiUrl={apiUrl} initialData={initialData} />);
  expect(screen.getByRole('combobox')).toBeTruthy();
  const textboxes = screen.getAllByRole('textbox');
  expect(textboxes.length).toBeGreaterThanOrEqual(1);
});

test('preview inicial muestra placeholder', () => {
  render(<PostNew apiUrl={apiUrl} initialData={initialData} />);
  expect(screen.getByText('// Preview your code here', { selector: 'pre' })).toBeTruthy();
});

test('al modificar textarea se actualiza solo el <pre>', () => {
  render(<PostNew apiUrl={apiUrl} initialData={initialData} />);
  const textarea = screen.getByRole('textbox');
  act(() => {
    fireEvent.change(textarea, { target: { value: 'console.log("Hi")' } });
  });
  // comprobamos solo el PRE
  expect(screen.getByText('console.log("Hi")', { selector: 'pre' })).toBeTruthy();
});

test('selección de fichero y vista previa', async () => {
  render(<PostNew apiUrl={apiUrl} initialData={initialData} />);
  const file = new File(['dummy'], 'test.png', { type: 'image/png' });
  const dataURL = 'data:image/png;base64,foo';
  vi.stubGlobal('FileReader', class {
    onload = null;
    readAsDataURL() {
      this.result = dataURL;
      this.onload();
    }
  });

  const input = document.querySelector('#media-upload');
  await act(async () => {
    fireEvent.change(input, { target: { files: [file] } });
  });
  expect(await screen.findByAltText('Preview')).toBeTruthy();
});

test('submit exitoso redirige', async () => {
  const redirectUrl = '/posts/123';
  global.fetch = vi.fn().mockResolvedValue({ json: async () => ({ success: true, redirectUrl }) });

  render(<PostNew apiUrl={apiUrl} initialData={initialData} />);
  const btn = screen.getByRole('button', { name: 'Create Post' });
  fireEvent.click(btn);
  await waitFor(() => expect(window.location.href).toBe(redirectUrl));
});

test('submit con errores muestra lista', async () => {
  const errors = ['Err1', 'Err2'];
  global.fetch = vi.fn().mockResolvedValue({ json: async () => ({ success: false, errors }) });

  render(<PostNew apiUrl={apiUrl} initialData={initialData} />);
  const btn = screen.getByRole('button', { name: 'Create Post' });
  fireEvent.click(btn);
  for (const err of errors) {
    expect(await screen.findByText(err, { selector: 'ul li' })).toBeTruthy();
  }
});

test('error de red muestra mensaje global', async () => {
  global.fetch = vi.fn().mockRejectedValue(new Error('fail'));

  render(<PostNew apiUrl={apiUrl} initialData={initialData} />);
  const btn = screen.getByRole('button', { name: 'Create Post' });
  fireEvent.click(btn);
  expect(await screen.findByText('Error de red o respuesta inválida', { selector: 'ul li' })).toBeTruthy();
});
