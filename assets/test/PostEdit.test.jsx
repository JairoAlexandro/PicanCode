/**
 * @vitest-environment jsdom
 */

import React from 'react';
import { test, expect, beforeEach, afterEach, vi } from 'vitest';
import { render, screen, fireEvent, waitFor, act, cleanup } from '@testing-library/react';
import PostEdit from '../react/controllers/PostEdit.jsx';

const initialData = { id: 1, title: 'Python', content: 'print("Hello")', media: null };
const apiUrl = '/api/posts/1';

beforeEach(() => {
  // Stub fetch para useEffect inicial
  global.fetch = vi.fn().mockResolvedValue({ json: async () => ({ data: {} }) });
});

afterEach(() => {
  cleanup();
  vi.resetAllMocks();
  delete window.location;
  window.location = { href: '' };
});

test('smoke: renderiza sin errores', () => {
  render(<PostEdit initialData={initialData} apiUrl={apiUrl} />);
});

test('tiene select de lenguaje y textarea de código', () => {
  render(<PostEdit initialData={initialData} apiUrl={apiUrl} />);
  expect(screen.getByRole('combobox')).toBeTruthy();
  const textboxes = screen.getAllByRole('textbox');
  expect(textboxes.length).toBeGreaterThanOrEqual(1);
});

test('muestra el contenido inicial en preview', () => {
  render(<PostEdit initialData={initialData} apiUrl={apiUrl} />);
  // Solo comprobamos el <pre>, no el textarea
  const pre = screen.getByText('print("Hello")', { selector: 'pre' });
  expect(pre).toBeTruthy();
});

test('fetch media cuando no hay media inicial', async () => {
  const mediaUrl = 'http://example.com/image.png';
  global.fetch = vi.fn().mockResolvedValue({ json: async () => ({ data: { media: mediaUrl } }) });

  render(<PostEdit initialData={initialData} apiUrl={apiUrl} />);
  await waitFor(() =>
    expect(global.fetch).toHaveBeenCalledWith(
      apiUrl,
      expect.objectContaining({ headers: expect.any(Object) })
    )
  );
  expect(await screen.findByAltText('Preview existente')).toBeTruthy();
});

test('selección de fichero y vista previa', async () => {
  render(<PostEdit initialData={initialData} apiUrl={apiUrl} />);
  const file = new File(['dummy'], 'test.png', { type: 'image/png' });
  const dataURL = 'data:image/png;base64,dummydata';
  vi.stubGlobal('FileReader', class {
    onload = null;
    readAsDataURL() {
      this.result = dataURL;
      this.onload();
    }
  });

  // Seleccionamos por id
  const input = document.querySelector('#media-upload');
  await act(async () =>
    fireEvent.change(input, { target: { files: [file] } })
  );
  expect(await screen.findByAltText('Preview')).toBeTruthy();
});

test('submit exitoso y redirección', async () => {
  const redirectUrl = '/posts/1';
  global.fetch = vi.fn().mockResolvedValue({ json: async () => ({ success: true, redirectUrl }) });

  render(<PostEdit initialData={initialData} apiUrl={apiUrl} />);
  fireEvent.click(screen.getByText('Guardar Cambios'));
  await waitFor(() => expect(window.location.href).toBe(redirectUrl));
});

test('muestra errores en submit fallido', async () => {
  const errors = ['Error 1', 'Error 2'];
  global.fetch = vi.fn().mockResolvedValue({ json: async () => ({ success: false, errors }) });

  render(<PostEdit initialData={initialData} apiUrl={apiUrl} />);
  fireEvent.click(screen.getByText('Guardar Cambios'));
  for (const err of errors) {
    expect(await screen.findByText(err)).toBeTruthy();
  }
});

test('muestra error de red en excepción', async () => {
  global.fetch = vi.fn().mockRejectedValue(new Error('fail'));

  render(<PostEdit initialData={initialData} apiUrl={apiUrl} />);
  fireEvent.click(screen.getByText('Guardar Cambios'));
  expect(await screen.findByText('Error de red o respuesta inválida.')).toBeTruthy();
});
