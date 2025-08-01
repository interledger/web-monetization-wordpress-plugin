import '../scss/admin.scss';
declare const ajaxurl: string;
function normalizeWAPrefix(pointer: string): string {
  return pointer.startsWith('$') ? 'https://' + pointer.substring(1) : pointer;
}

export function validateWalletAddresses(
  name: string,
  wa: string | null,
): boolean {
  if (!wa) return true;
  if (typeof wa !== 'string') return false;

  if (name === 'wm_wallet_address') {
    const addresses = wa.trim().split(/\s+/); // Split on one or more spaces.
    return addresses.every(validateSingleWalletAddress);
  } else {
    // For post type settings, allow only a single address.
    return validateSingleWalletAddress(wa);
  }
}

function validateSingleWalletAddress(wa: string): boolean {
  if (!wa || typeof wa !== 'string') return false;
  if (wa.includes(' ')) return false;

  // Only allow URL-safe characters
  const allowedChars = /^[a-zA-Z0-9\-._~:/?#[@\]!$&()*+,;=%]+$/;
  if (!allowedChars.test(wa)) return false;

  try {
    const url = new URL(normalizeWAPrefix(wa));
    if (url.protocol !== 'https:') return false;
    if (!url.hostname) return false;
    if (url.pathname && !url.pathname.startsWith('/')) return false;
    if (url.pathname === '/') return false;
    if (url.search || url.hash) return false;

    return true;
  } catch {
    return false;
  }
}

function createElements(input: HTMLInputElement, index: number) {
  const wrapper = document.createElement('div');
  wrapper.className = 'wallet-ui-wrapper';
  wrapper.style.marginTop = '8px';

  const feedback = document.createElement('p');
  feedback.className = 'wallet-feedback';
  feedback.style.fontSize = '0.9em';

  const connectBtn = document.createElement('button');
  connectBtn.textContent = 'Verify Wallet Address';
  connectBtn.type = 'button';
  connectBtn.className = 'button button-secondary';

  const editLink = document.createElement('a');
  editLink.textContent = 'Edit';
  editLink.href = '#';
  editLink.style.marginLeft = '12px';
  editLink.style.display = 'none';

  const check = document.createElement('span');
  check.textContent = '✅ Wallet Verified';
  check.style.marginLeft = '8px';
  check.style.color = 'green';
  check.style.display = 'none';

  wrapper.appendChild(connectBtn);
  wrapper.appendChild(editLink);
  wrapper.appendChild(check);
  wrapper.appendChild(feedback);

  input.insertAdjacentElement('afterend', wrapper);

  return { connectBtn, editLink, check, feedback };
}

function setConnectingState(
  isConnecting: boolean,
  connectBtn: HTMLButtonElement,
) {
  connectBtn.disabled = isConnecting;
  connectBtn.textContent = isConnecting
    ? 'Connecting...'
    : 'Verify Wallet Address';
}

async function fetchWalletDetails(url: string): Promise<any> {
  const response = await fetch(url, {
    headers: { Accept: 'application/json' },
  });
  if (!response.ok) {
    throw new Error('Wallet request failed');
  }
  return { url: response.url, response: await response.json() };
}

function validateWalletData(data: any): void {
  if (
    !data ||
    typeof data !== 'object' ||
    !data.id ||
    !data.authServer?.startsWith('https://') ||
    !data.resourceServer?.startsWith('https://')
  ) {
    throw new Error('Invalid wallet response');
  }
}

async function saveWalletConnection(walletId: string, inputName: string) {
  const walletConnectData = window.walletConnectData || {};

  const response = await fetch(walletConnectData.ajaxUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    },
    body: new URLSearchParams({
      action: 'save_wallet_connection',
      nonce: walletConnectData.nonce || '',
      wallet_field: inputName,
      id: walletId,
    }),
  });

  const result = await response.json();
  if (!result.success) {
    throw new Error('Failed to save wallet connection');
  }
}

function handleUIAfterSuccess(
  input: HTMLInputElement,
  check: HTMLSpanElement,
  editLink: HTMLAnchorElement,
  connectBtn: HTMLButtonElement,
) {
  input.readOnly = true;
  input.classList.add('connected');
  check.style.display = 'inline';
  editLink.style.display = 'inline';
  connectBtn.style.display = 'none';

  if (input.name.startsWith('wm_post_type_settings')) {
    const postType = input.name.split('[')[1].split(']')[0];
    const hiddenInput = document.querySelector(
      `input[name="wm_post_type_settings[${postType}][connected]"]`,
    ) as HTMLInputElement;
    if (hiddenInput) {
      hiddenInput.value = '1';
    }
  } else if (input.name === 'wm_wallet_address') {
    const hiddenInput = document.querySelector(
      'input[name="wm_wallet_address_connected"]',
    ) as HTMLInputElement;
    if (hiddenInput) {
      hiddenInput.value = '1';
    }
  }
}

function handleUIAfterFailure(
  input: HTMLInputElement,
  feedback: HTMLParagraphElement,
) {
  input.classList.remove('connected');
  feedback.textContent = 'Failed to connect to wallet.';
  feedback.style.color = 'red';

  if (input.name.startsWith('wm_post_type_settings')) {
    const postType = input.name.split('[')[1].split(']')[0];
    const hiddenInput = document.querySelector(
      `input[name="wm_post_type_settings[${postType}][connected]"]`,
    ) as HTMLInputElement;
    if (hiddenInput) {
      hiddenInput.value = '0';
    }
  } else if (input.name === 'wm_wallet_address') {
    const hiddenInput = document.querySelector(
      'input[name="wm_wallet_address_connected"]',
    ) as HTMLInputElement;
    if (hiddenInput) {
      hiddenInput.value = '0';
    }
  }
}

function showValidation(
  input: HTMLInputElement,
  feedback: HTMLParagraphElement,
): boolean {
  const value = input.value;
  const isValid = validateWalletAddresses(input.name, input.value);

  if (isValid) {
    input.style.borderColor = '';
    feedback.textContent = '';
  } else {
    input.style.borderColor = 'red';
    feedback.textContent = 'Invalid Wallet Address format.';
    feedback.style.color = 'red';
    feedback.style.fontSize = '0.9em';
  }
  return isValid;
}

function setupWalletField(input: HTMLInputElement, index: number) {
  const { connectBtn, editLink, check, feedback } = createElements(
    input,
    index,
  );

  const validateAndToggleButton = () => {
    const isValid = showValidation(input, feedback);
    connectBtn.style.display =
      isValid && input.value.trim() !== '' ? 'inline-block' : 'none';
  };

  validateAndToggleButton();

  const isConnected = validateConnectedState(input, check, editLink);

  if (isConnected) {
    connectBtn.style.display = 'none';
    editLink.style.display = 'inline';
    check.style.display = 'inline';
  }
  input.addEventListener('input', validateAndToggleButton);

  connectBtn.addEventListener('click', async () => {
    const rawInput = input.value.trim();
    const pointers = rawInput.split(/\s+/); // split on spaces

    setConnectingState(true, connectBtn);
    feedback.textContent = '';

    try {
      const normalizedPointers: string[] = [];

      for (const pointer of pointers) {
        const pointerUrl = normalizeWAPrefix(pointer);
        const { url, response } = await fetchWalletDetails(pointerUrl);
        validateWalletData(response);

        // Save normalized pointer
        normalizedPointers.push(url);

        // Save to server
        await saveWalletConnection(response.id, url);
      }

      input.value = normalizedPointers.join(' ');

      handleUIAfterSuccess(input, check, editLink, connectBtn);
      feedback.textContent = '';
    } catch (err) {
      handleUIAfterFailure(input, feedback);
    } finally {
      setConnectingState(false, connectBtn);
    }
  });

  editLink.addEventListener('click', (e) => {
    e.preventDefault();
    input.readOnly = false;
    check.style.display = 'none';
    editLink.style.display = 'none';
    connectBtn.style.display = 'inline-block';
  });
}

function validateConnectedState(
  input: HTMLInputElement,
  check: HTMLSpanElement,
  editLink: HTMLAnchorElement,
) {
  const isConnected = input.readOnly && input.value.trim() !== '';
  if (isConnected) {
    check.style.display = 'inline';
    editLink.style.display = 'inline';
    input.readOnly = true;
  } else {
    check.style.display = 'none';
    editLink.style.display = 'none';
    input.readOnly = false;
  }
  return isConnected;
}

document.addEventListener('DOMContentLoaded', () => {
  const walletInputs = Array.from(
    document.querySelectorAll<HTMLInputElement>(
      '#wm_wallet_address, input[name^="wm_post_type_settings"][name$="[wallet]"]',
    ),
  );
  walletInputs.forEach((input, index) => {
    setupWalletField(input, index);
  });
});

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector<HTMLFormElement>(
    'form#webmonetization_general_form',
  );
  if (!form) return;

  let initialFormData = new FormData(form);
  let isDirty = false;

  const compareFormData = (): boolean => {
    const currentFormData = new FormData(form);
    for (const [key, value] of currentFormData.entries()) {
      if (initialFormData.get(key) !== value) {
        return true;
      }
    }
    return false;
  };

  const onChange = () => {
    isDirty = compareFormData();
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
      e.returnValue = ''; // Required for cross-browser support
    }
  });

  form.addEventListener('submit', () => {
    isDirty = false;
  });
});
