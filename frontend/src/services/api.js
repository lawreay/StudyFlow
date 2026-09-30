const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

async function request(path, options = {}) {
  const { headers: requestHeaders = {}, ...requestOptions } = options;
  const headers = { ...requestHeaders };

  if (!(requestOptions.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
  }

  const response = await fetch(`${API_BASE_URL}${path}`, {
    ...requestOptions,
    headers,
  });

  const payload = await response.json().catch(() => ({}));

  if (!response.ok) {
    const message = payload.message || 'Request failed';
    const errors = payload.errors || {};
    throw { message, errors, status: response.status };
  }

  return payload;
}

export const api = {
  get: (path, token) =>
    request(path, {
      method: 'GET',
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    }),
  post: (path, data, token) =>
    request(path, {
      method: 'POST',
      body: JSON.stringify(data),
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    }),
  put: (path, data, token) =>
    request(path, {
      method: 'PUT',
      body: JSON.stringify(data),
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    }),
  patch: (path, data, token) =>
    request(path, {
      method: 'PATCH',
      body: JSON.stringify(data),
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    }),
  delete: (path, token) =>
    request(path, {
      method: 'DELETE',
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    }),
  upload: (path, file, token) => {
    const body = new FormData();
    body.append('file', file);

    return request(path, {
      method: 'POST',
      body,
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    });
  },
};
