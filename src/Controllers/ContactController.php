<?php
require_once __DIR__ . '/../Models/ContactModel.php';

class ContactController
{
    public function submit(array $data, string $ip, ?string $ua): array
    {
        $errors = [];
        $name    = trim($data['name'] ?? '');
        $email   = trim($data['email'] ?? '');
        $subject = trim($data['subject'] ?? '');
        $message = trim($data['message'] ?? '');
        if ($name === '') {
            $errors[] = 'Bitte geben Sie Ihren Namen an.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Bitte geben Sie eine gültige E-Mail-Adresse an.';
        }
        if ($subject === '') {
            $errors[] = 'Bitte geben Sie einen Betreff an.';
        }
        if ($message === '') {
            $errors[] = 'Bitte geben Sie eine Nachricht ein.';
        }
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }
        $contactId = 'CF' . strtoupper(bin2hex(random_bytes(4)));
        ContactModel::saveRequest($contactId, $name, $email, $subject, $message, $ip, $ua);
        return ['success' => true, 'contactId' => $contactId];
    }
}
