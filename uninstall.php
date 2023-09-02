<?php

// if uninstall.php is not called by WordPress, die
if (!defined("WP_UNINSTALL_PLUGIN")) {
    die();
}

/**
 * Plugin textdomain
 * @var string
 */
define(
    "EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN",
    "easy-appointments-duplicate-submission-block"
);

delete_option(
    EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN . "_email_whitelist"
);
delete_option(
    EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN . "_phone_whitelist"
);
delete_option(
    EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN . "_ip_whitelist"
);
