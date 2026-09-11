<?php

declare(strict_types=1);

/**
 * Contact form back-end: read the POST, screen for bots, validate, deliver.
 * The form markup lives in components/ContactForm.php (footer, every page);
 * the POST is routed here from public/index.php, which redirects back to
 * #contact afterwards (PRG) — this file never renders a page.
 */

/** Fields the form sends and we care about. */
const CONTACT_FIELDS = ['first_name', 'last_name', 'email', 'subject', 'message'];

/** A human takes longer than this to read and fill the form. */
const CONTACT_MIN_SECONDS = 2;

/** Shortest message we treat as a real enquiry. */
const CONTACT_MESSAGE_MIN = 10;

/**
 * The five fields, trimmed. Missing keys become ''.
 */
function contactInput(): array
{
    $data = [];

    foreach (CONTACT_FIELDS as $field) {
        $data[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    return $data;
}

/**
 * Two cheap bot signals: a hidden field a human never sees (so never fills),
 * and a submit that lands faster than a person could type. Either one = spam.
 * The caller then *pretends* the send worked — a silent failure tells the bot
 * nothing about what tripped it.
 */
function contactLooksLikeSpam(): bool
{
    $honeypot = trim((string) ($_POST['website'] ?? ''));
    $elapsed  = time() - (int) ($_POST['loaded_at'] ?? 0);

    return $honeypot !== '' || $elapsed < CONTACT_MIN_SECONDS;
}

/**
 * Validate the trimmed fields. Return a map of field-name => error-message;
 * an empty array means the submission is good.
 *
 * Your task (see the marker in the body). Things to decide:
 *   - which fields are required (the form marks email, subject, message with *)
 *   - email must pass filter_var(..., FILTER_VALIDATE_EMAIL)
 *   - message must be at least CONTACT_MESSAGE_MIN characters
 *   - HEADER INJECTION: email and subject end up in real mail headers
 *     (Reply-To:, Subject:). A value containing "\r" or "\n" lets an attacker
 *     bolt on extra headers (Bcc:, a fake From:, spam payloads). Reject any
 *     field that contains a carriage return or a line feed.
 *   - keep the messages short and in English (they show next to the field).
 */
function validateContact(array $data): array
{
    $errors = [];

    if ($data['email'] === '') {
        $errors['email'] = "Email can't be empty";
    } elseif (str_contains($data['email'], "\n") || str_contains($data['email'], "\r")) {
        $errors['email'] = 'Email must not contain line breaks';
    } elseif (filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = 'Enter a valid email address';
    }

    if ($data['subject'] === '') {
        $errors['subject'] = "Subject can't be empty";
    } elseif (str_contains($data['subject'], "\n") || str_contains($data['subject'], "\r")) {
        $errors['subject'] = 'Subject must not contain line breaks';
    }

    if (mb_strlen($data['message']) < CONTACT_MESSAGE_MIN) {
        $errors['message'] = 'Message must be at least ' . CONTACT_MESSAGE_MIN . ' characters';
    }

    return $errors;
}

/**
 * Stash errors + what the visitor typed, so the redirected page can redraw the
 * form with both. One-shot: read once by takeContactState(), then gone.
 */
function contactFail(array $errors, array $old): void
{
    $_SESSION['contact'] = ['errors' => $errors, 'old' => $old];
}

/**
 * Mark the submission as accepted (real send, or a silently-swallowed bot).
 */
function contactSucceed(): void
{
    $_SESSION['contact'] = ['sent' => true];
}

/**
 * Read the one-shot contact state and clear it. Always returns the three keys
 * so the form can use them without isset() gymnastics.
 */
function takeContactState(): array
{
    $state = $_SESSION['contact'] ?? [];
    unset($_SESSION['contact']);

    return [
        'errors' => $state['errors'] ?? [],
        'old'    => $state['old'] ?? [],
        'sent'   => $state['sent'] ?? false,
    ];
}

/**
 * Deliver the message. In production: mail(). Locally (no MTA under MAMP):
 * append it to logs/contact.log so the flow is still testable. Returns false
 * only if mail() itself refuses the message.
 */
function sendContactEmail(array $data): bool
{
    $name    = trim($data['first_name'] . ' ' . $data['last_name']);
    $subject = '[Portfolio] ' . $data['subject'];

    $body = 'From: ' . ($name !== '' ? $name : '(no name)') . ' <' . $data['email'] . ">\n\n"
          . $data['message'] . "\n";

    // From: stays on our own domain so the host's mail server accepts it (SPF).
    // Reply-To: carries the visitor's address, so "Reply" reaches them.
    $headers = 'From: ' . CONTACT_FROM . "\r\n"
             . 'Reply-To: ' . $data['email'] . "\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n";

    if (APP_ENV === 'local') {
        $dir = dirname(__DIR__) . '/logs';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $entry = '=== ' . date('c') . " ===\n"
               . 'To: ' . CONTACT_TO . "\n"
               . 'Subject: ' . $subject . "\n"
               . $headers . "\n"
               . $body . "\n";
        error_log($entry, 3, $dir . '/contact.log');

        return true;
    }

    return mail(CONTACT_TO, $subject, $body, $headers);
}
