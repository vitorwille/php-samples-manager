const API_URL = process.env.NEXT_PUBLIC_API_URL;

export interface Sample {
  id: number;
  sampleCode: string;
  sampleType: string;
  sampleStatus: string;
  sampleTechnician: string | null;
  sampleReceivalDate: string;
  sampleConclusionDate: string | null;
}

export async function checkSession(): Promise<boolean> {
  const res = await fetch(`${API_URL}/api/users`, { credentials: 'include' });
  return res.ok;
}

export async function login(email: string, password: string): Promise<boolean> {
  const res = await fetch(`${API_URL}/api/login`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ email, password }),
  });
  return res.ok;
}

export async function logout(): Promise<void> {
  await fetch(`${API_URL}/api/logout`, {
    method: 'POST',
    credentials: 'include',
  });
}

export async function fetchSamples(params?: {
  code?: string;
  search?: string;
  type?: string;
}): Promise<Sample[]> {
  const query = new URLSearchParams();
  if (params?.code) query.set('code', params.code);
  if (params?.search) query.set('search', params.search);
  if (params?.type) query.set('type', params.type);

  const qs = query.toString();
  const res = await fetch(`${API_URL}/api/samples${qs ? '?' + qs : ''}`, {
    credentials: 'include',
  });

  if (!res.ok) throw new Error('Failed to fetch samples');
  return res.json();
}

export async function updateSample(data: {
  sampleCode: string;
  sampleStatus?: string;
  sampleTechnician?: string;
  sampleConclusionDate?: string;
}): Promise<Sample> {
  const res = await fetch(`${API_URL}/api/samples`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify(data),
  });

  if (!res.ok) {
    const err = await res.json();
    throw new Error(err.error || 'Failed to update sample');
  }
  return res.json();
}
