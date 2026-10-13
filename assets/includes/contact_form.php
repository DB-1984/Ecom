<?php
add_action('init', function () {
    register_block_type(
        get_theme_file_path('/blocks/contact-form/build')
    );
});

add_action('rest_api_init', function () {
    register_rest_route('dbwp/v1', '/contact-form', [
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => 'dbwp_handle_contact_form',
        'permission_callback' => '__return_true',
    ]);
});

function dbwp_handle_contact_form(WP_REST_Request $request)
{
    $first_name = sanitize_text_field($request->get_param('firstName'));
    $last_name  = sanitize_text_field($request->get_param('lastName'));
    $email      = sanitize_email($request->get_param('email'));
    $url        = esc_url_raw($request->get_param('url'));
    $message    = sanitize_textarea_field($request->get_param('message'));
    $honeypot   = sanitize_text_field($request->get_param('company'));

    if ($honeypot !== '') {
        return new WP_REST_Response([
            'success' => true,
            'message' => 'Thank you for your message.',
        ], 200);
    }

    if ($first_name === '' || $message === '' || !is_email($email)) {
        return new WP_Error(
            'invalid_contact_form',
            'Please complete all required fields correctly.',
            ['status' => 400]
        );
    }

    $attachments = [];
    $files       = $request->get_file_params();

    if (!empty($files['file']['name'])) {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $file = $files['file'];

        if ((int) $file['size'] > 5 * MB_IN_BYTES) {
            return new WP_Error(
                'file_too_large',
                'The uploaded file must be no larger than 5MB.',
                ['status' => 400]
            );
        }

        $allowed_mimes = [
            'jpg|jpeg' => 'image/jpeg',
            'png'      => 'image/png',
            'pdf'      => 'application/pdf',
        ];

        $checked_file = wp_check_filetype_and_ext(
            $file['tmp_name'],
            $file['name'],
            $allowed_mimes
        );

        if (empty($checked_file['type']) || empty($checked_file['ext'])) {
            return new WP_Error(
                'invalid_file',
                'Please upload a JPG, PNG or PDF file.',
                ['status' => 400]
            );
        }

        $uploaded_file = wp_handle_upload($file, [
            'test_form' => false,
            'mimes'     => $allowed_mimes,
        ]);

        if (!empty($uploaded_file['error'])) {
            return new WP_Error(
                'upload_failed',
                $uploaded_file['error'],
                ['status' => 400]
            );
        }

        $attachments[] = $uploaded_file['file'];
    }

    $recipient = 'your-email@example.com';
    $subject   = 'New contact form submission';

    $body = implode("\n\n", [
        "Name: {$first_name} {$last_name}",
        "Email: {$email}",
        "URL: {$url}",
        "Message:\n{$message}",
    ]);

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: DBWP Website <website@yourdomain.co.uk>',
        "Reply-To: {$first_name} {$last_name} <{$email}>",
    ];

    $sent = wp_mail(
        $recipient,
        $subject,
        $body,
        $headers,
        $attachments
    );

    /*
     * Uploaded files are only temporary email attachments here.
     * Remove them after wp_mail() has finished with them.
     */
    foreach ($attachments as $attachment) {
        if (is_file($attachment)) {
            wp_delete_file($attachment);
        }
    }

    if (!$sent) {
        return new WP_Error(
            'email_failed',
            'We could not send your message. Please try again later.',
            ['status' => 500]
        );
    }

    return new WP_REST_Response([
        'success' => true,
        'message' => 'Thank you. We will get back to you as soon as possible.',
    ], 200);
}