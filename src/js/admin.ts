import '../scss/admin.scss';
declare const ajaxurl: string;
type WalletAddress = {
  id: string;
  authServer: string;
  resourceServer: string;
};

function isWalletAddress(x: unknown): x is WalletAddress {
  if (!x || typeof x !== 'object') return false;
  const o = x as Record<string, unknown>;
  return (
    typeof o.id === 'string' &&
    typeof o.authServer === 'string' &&
    o.authServer.startsWith('https://') &&
    typeof o.resourceServer === 'string' &&
    o.resourceServer.startsWith('https://')
  );
}
type WalletConnectData = {
  ajaxUrl: string;
  nonce: string;
};

declare global {
  interface Window {
    walletConnectData?: Partial<WalletConnectData>;
  }
}

function normalizeWAPrefix(pointer: string): string {
  const s = pointer.trim();
  return s.startsWith('$') ? 'https://' + s.slice(1) : s;
}

export function validateWalletAddresses(
  name: string,
  wa: string | null,
): boolean {
  if (!wa) return true;
  if (typeof wa !== 'string') return false;
  if (wa.length > 1000) return false; // arbitrary max length
  const addresses = wa.trim().split(/\s+/);
  return addresses.every(validateSingleWalletAddress);
}

function validateSingleWalletAddress(wa: string): boolean {
  if (!wa || typeof wa !== 'string') return false;
  if (wa.includes(' ')) return false;

  // Allow conservative URL-safe chars; `$` is OK pre-normalization
  const allowedChars = /^[a-zA-Z0-9\-._~:/?#[@\]!$&()*+,;=%]+$/;
  if (!allowedChars.test(wa)) return false;

  try {
    const url = new URL(normalizeWAPrefix(wa));

    if (url.protocol !== 'https:') return false;
    if (!url.hostname) return false;
    if (url.port) return false; // optional: disallow ports
    if (url.search || url.hash) return false;
    if (url.pathname && !url.pathname.startsWith('/')) return false;

    const hostnameRegex =
      /^(?=.{1,253}$)(?!.*\.\.)([a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)(\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
    if (!hostnameRegex.test(url.hostname)) return false;

    return true;
  } catch {
    return false;
  }
}

function createElements(input: HTMLInputElement) {
  const wrapper = document.createElement('div');
  wrapper.className = 'wallet-ui-wrapper mt-2';

  const feedback = document.createElement('p');
  feedback.className = 'wallet-feedback';
  feedback.setAttribute('aria-live', 'polite');

  const connectBtn = document.createElement('button');
  connectBtn.textContent = 'Verify Wallet Address';
  connectBtn.type = 'button';
  connectBtn.className = 'button button-secondary wallet-verify';

  const editLink = document.createElement('a');
  editLink.textContent = 'Edit';
  editLink.href = '#';
  editLink.className = 'wallet-edit hidden ml-3';

  const check = document.createElement('span');
  check.textContent = '✅ Wallet Verified';
  check.className = 'wallet-check hidden ml-2';

  wrapper.append(connectBtn, editLink, check, feedback);
  input.insertAdjacentElement('afterend', wrapper);
  return { connectBtn, editLink, check, feedback, wrapper };
}

function setConnectingState(isConnecting: boolean, btn: HTMLButtonElement) {
  btn.disabled = isConnecting;
  btn.setAttribute('aria-busy', String(isConnecting));
  btn.textContent = isConnecting ? 'Connecting…' : 'Verify Wallet Address';
}
function toggle(el: HTMLElement, show: boolean) {
  el.classList.toggle('hidden', !show);
}

function setConnectedHiddenFlag(input: HTMLInputElement, value: '0' | '1') {
  const byName = (selector: string) =>
    document.querySelector<HTMLInputElement>(selector);
  if (input.name.startsWith('wm_wallet_address_overrides')) {
    const country = input.name.match(/\[(.*?)\]/)?.[1];
    const hidden = byName(
      `input[name="wm_wallet_address_overrides[${country}][connected]"]`,
    );
    if (hidden) hidden.value = value;
  } else if (input.name.startsWith('wm_post_type_settings')) {
    const postType = input.name.match(/\[(.*?)\]/)?.[1];
    const hidden = byName(
      `input[name="wm_post_type_settings[${postType}][connected]"]`,
    );
    if (hidden) hidden.value = value;
  } else if (input.name === 'wm_wallet_address') {
    const hidden = byName('input[name="wm_wallet_address_connected"]');
    if (hidden) hidden.value = value;
  }
}
async function fetchWithTimeout(
  input: RequestInfo,
  init: RequestInit = {},
  ms = 10000,
) {
  const controller = new AbortController();
  const t = setTimeout(() => controller.abort(), ms);
  try {
    return await fetch(input, { ...init, signal: controller.signal });
  } finally {
    clearTimeout(t);
  }
}

async function fetchWalletDetails(
  url: string,
): Promise<{ url: string; response: WalletAddress }> {
  const res = await fetchWithTimeout(url, {
    headers: { Accept: 'application/json' },
  });
  if (!res.ok) throw new Error('Wallet verification failed for ' + url);

  const json = await res.json();
  if (!isWalletAddress(json))
    throw new Error('Invalid wallet response for ' + url);

  return { url: res.url, response: json };
}

async function saveWalletConnection(walletId: string, inputName: string) {
  const cfg: WalletConnectData = {
    ajaxUrl: window.walletConnectData?.ajaxUrl ?? ajaxurl, // fallback to WP ajaxurl
    nonce: window.walletConnectData?.nonce ?? '',
  };

  const res = await fetchWithTimeout(cfg.ajaxUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    },
    body: new URLSearchParams({
      action: 'save_wallet_connection',
      nonce: cfg.nonce,
      wallet_field: inputName,
      id: walletId,
    }),
  });

  const result = await res.json();
  if (!result?.success) throw new Error('Failed to save wallet connection');
}

function handleUIAfterSuccess(
  input: HTMLInputElement,
  check: HTMLSpanElement,
  editLink: HTMLAnchorElement,
  connectBtn: HTMLButtonElement,
) {
  input.readOnly = true;
  input.classList.add('connected');
  toggle(check, true);
  toggle(editLink, true);
  toggle(connectBtn, false);
  setConnectedHiddenFlag(input, '1');
}

function handleUIAfterFailure(
  input: HTMLInputElement,
  feedback: HTMLParagraphElement,
  message = 'Failed to connect to wallet.',
) {
  input.classList.remove('connected');
  feedback.textContent = message;
  feedback.classList.add('text-danger');
  setConnectedHiddenFlag(input, '0');
}

function debounce<T extends (...a: any[]) => void>(fn: T, ms = 250) {
  let t: number | undefined;
  return (...args: Parameters<T>) => {
    if (t) window.clearTimeout(t);
    t = window.setTimeout(() => fn(...args), ms);
  };
}

function showValidation(
  input: HTMLInputElement,
  feedback: HTMLParagraphElement,
): boolean {
  const isValid = validateWalletAddresses(input.name, input.value);
  input.style.borderColor = isValid ? '' : 'red';
  feedback.textContent = isValid ? '' : 'Invalid Wallet Address format.';
  if (!isValid) feedback.classList.add('text-danger');
  return isValid;
}

function setupWalletField(input: HTMLInputElement) {
  const { connectBtn, editLink, check, feedback } = createElements(input);

  const validateAndToggleButton = () => {
    const ok = showValidation(input, feedback);
    toggle(connectBtn, ok && input.value.trim() !== '');
  };

  validateAndToggleButton();

  const isConnected = validateConnectedState(input, check, editLink);
  if (isConnected) {
    console.log('Wallet is connected for ', input.value);
    toggle(connectBtn, false);
    toggle(editLink, true);
    toggle(check, true);
  }

  input.addEventListener('input', validateAndToggleButton);

  connectBtn.addEventListener('click', async () => {
    const pointers = input.value.trim().split(/\s+/).filter(Boolean);
    setConnectingState(true, connectBtn);
    feedback.textContent = '';

    try {
      // verify all in parallel for speed (or keep sequential if rate-limiting)
      const results = await Promise.all(
        pointers.map(async (p) => {
          const pointerUrl = normalizeWAPrefix(p);
          const { url, response } = await fetchWalletDetails(pointerUrl);
          await saveWalletConnection(response.id, url);
          return url; // normalized
        }),
      );

      input.value = results.join(' ');
      handleUIAfterSuccess(input, check, editLink, connectBtn);
      feedback.textContent = '';
    } catch (err) {
      handleUIAfterFailure(
        input,
        feedback,
        err instanceof Error ? err.message : undefined,
      );
    } finally {
      setConnectingState(false, connectBtn);
    }
  });

  editLink.addEventListener('click', (e) => {
    e.preventDefault();
    input.readOnly = false;
    toggle(check, false);
    toggle(editLink, false);
    toggle(connectBtn, true);
    setConnectedHiddenFlag(input, '0');
  });
}

function validateConnectedState(
  input: HTMLInputElement,
  check: HTMLSpanElement,
  editLink: HTMLAnchorElement,
) {
  const isConnected = input.readOnly && input.value.trim() !== '';
  if (isConnected) {
    check.classList.toggle('hidden', false);
    editLink.classList.toggle('hidden', false);
    input.readOnly = true;
  } else {
    check.classList.toggle('hidden', true);
    editLink.classList.toggle('hidden', true);
    input.readOnly = false;
  }
  return isConnected;
}

document.addEventListener('DOMContentLoaded', () => {
  // Wallet fields
  const walletInputs = Array.from(
    document.querySelectorAll<HTMLInputElement>(
      '#wm_wallet_address, input[name^="wm_post_type_settings"][name$="[wallet]"], input[name^="wm_wallet_address_overrides"][name$="[wallet]"]',
    ),
  );
  walletInputs.forEach(setupWalletField);

  // “Dirty form” guard
  const form = document.querySelector<HTMLFormElement>(
    'form#webmonetization_general_form',
  );
  if (form) {
    let initial = new FormData(form);
    let isDirty = false;

    const compare = () => {
      const current = new FormData(form);
      if (current.entries().next().done && initial.entries().next().done)
        return false;
      // Simple diff
      for (const [k, v] of current.entries()) {
        if (initial.get(k) !== v) return true;
      }
      for (const [k, v] of initial.entries()) {
        if (current.get(k) !== v) return true;
      }
      return false;
    };
    const onChange = () => {
      isDirty = compare();
    };

    form
      .querySelectorAll<
        HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement
      >('input, select, textarea')
      .forEach((el) => {
        el.addEventListener('change', onChange);
        el.addEventListener('input', onChange);
      });

    window.addEventListener('beforeunload', (e) => {
      if (isDirty) {
        e.preventDefault();
        e.returnValue = '';
      }
    });
    form.addEventListener('submit', () => {
      isDirty = false;
      initial = new FormData(form);
    });
  }

  // Toggle country wallets section
  const checkbox = document.querySelector<HTMLInputElement>(
    'input[name="wm_enable_country_wallets"]',
  );
  const wrapper = document.getElementById('wm_country_wallets_wrapper');
  if (checkbox && wrapper) {
    const toggleWrapper = () => {
      wrapper.style.display = checkbox.checked ? 'block' : 'none';
    };
    checkbox.addEventListener('change', toggleWrapper);
    toggleWrapper();
  }

  // Table add/remove
  const table = document.getElementById(
    'wallet-country-table',
  ) as HTMLTableElement | null;
  const addBtn = document.getElementById(
    'add-wallet-country-row',
  ) as HTMLButtonElement | null;
  if (table && addBtn) {
    addBtn.addEventListener('click', () => {
      const rows = table.querySelectorAll<HTMLTableRowElement>('tbody tr');
      const firstRow = rows[1];
      const newRow = firstRow.cloneNode(true) as HTMLTableRowElement;
      newRow.style.display = 'table-row';
      newRow.querySelectorAll<HTMLInputElement>('input').forEach((i, idx) => {
        i.value = '';
        i.name = i.name.replace(/\[\]/, `[NEW_${rows.length}]`);
      });
      const par = newRow.querySelector<HTMLElement>('.wallet-ui-wrapper');
      if (par) par.innerHTML = '';
      newRow
        .querySelectorAll<HTMLInputElement>('input[name$="[wallet]"]')
        .forEach(setupWalletField);
      table.querySelector('tbody')?.appendChild(newRow);
    });

    table.addEventListener('click', (e: MouseEvent) => {
      const target = e.target as HTMLElement | null;
      if (!target?.classList.contains('remove-row')) return;
      const row = target.closest('tr') as HTMLTableRowElement | null;
      const rows = table.querySelectorAll('tbody tr');
      if (row && rows.length > 1) row.remove();
    });
  }
});
