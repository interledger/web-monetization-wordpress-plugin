import '../scss/admin.scss';
function normalizeWAPrefix(pointer: string): string {
  return pointer.startsWith('$') ? 'https://' + pointer.substring(1) : pointer;
}

export function validateWalletAddress(wa: string | null): boolean {
  if (!wa) return true;
  if (typeof wa !== 'string') return false;

  if (wa.includes(' ')) {
    return false;
  }

  // Only allow URL-safe characters
  const allowedChars = /^[a-zA-Z0-9\-._~:/?#[@\]!$&()*+,;=%]+$/;
  if (!allowedChars.test(wa)) {
    return false;
  }

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

function createValidationFeedback(
  input: HTMLInputElement,
  index: number,
): HTMLParagraphElement {
  const feedbackId = input.id
    ? `${input.id}_feedback`
    : `wallet_feedback_${index}`;

  let feedback = document.getElementById(
    feedbackId,
  ) as HTMLParagraphElement | null;
  if (!feedback) {
    feedback = document.createElement('p');
    feedback.id = feedbackId;
    feedback.style.marginTop = '4px';
    input.insertAdjacentElement('afterend', feedback);
  }
  return feedback;
}

function showValidation(
  input: HTMLInputElement,
  feedback: HTMLParagraphElement,
): void {
  const value = input.value;
  const isValid = validateWalletAddress(value);

  if (isValid) {
    input.style.borderColor = '';
    feedback.textContent = '';
  } else {
    input.style.borderColor = 'red';
    feedback.textContent = 'Invalid Wallet Address format.';
    feedback.style.color = 'red';
    feedback.style.fontSize = '0.9em';
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const walletInputs = Array.from(
    document.querySelectorAll<HTMLInputElement>(
      '#wm_wallet_address, input[name^="wm_post_type_settings"][name$="[wallet]"]',
    ),
  );

  walletInputs.forEach((input, index) => {
    const feedback = createValidationFeedback(input, index);
    input.addEventListener('input', () => showValidation(input, feedback));
  });
});
