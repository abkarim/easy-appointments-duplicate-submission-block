<?php
/*
 * Plugin Name:       Easy appointments duplicate submission block
 * Plugin URI:        https://github.com/abkarim/easy-appointments-duplicate-submission-block
 * Description:       Blocks duplicate submission in a specific date range
 * Version:           0.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            Karim
 * Author URI:        https://github.com/abkarim
 * License:           GPL-3.0 license
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.en.html
 * Update URI:
 * Text Domain:       easy-appointments-duplicate-submission-block
 * Domain Path:       /languages
 */

/**
 * !Prevent direct access
 */

use Easy_Appointments_Duplicate_Submission_block\Ajax;

if (!defined("ABSPATH")) {
    exit();
}

if (!class_exists("Easy_Appointments_Duplicate_Submission_block")) {
    class Easy_Appointments_Duplicate_Submission_block
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            $this->define_constants();

            /**
             * Load plugin
             */
            add_action("plugins_loaded", [$this, "init"]);
        }

        /**
         * Define constant
         * required in plugin
         *
         * @access private
         * @since 0.1.0
         */
        private function define_constants()
        {
            /**
             * Define essentials constant
             */

            /**
             * Get plugin data defined in header
             */
            if (!function_exists("get_plugin_data")) {
                require_once ABSPATH . "wp-admin/includes/plugin.php";
            }
            $plugin_data = get_plugin_data(__FILE__);

            /**
             * Plugin textdomain
             * @var string
             */
            define(
                "EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN",
                $plugin_data["TextDomain"]
            );

            /**
             * Plugin path from root
             * @var string
             */
            define(
                "EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_PATH",
                rtrim(plugin_dir_path(__FILE__), "/")
            );

            /**
             * Plugin url from root
             * @var string
             */
            define(
                "EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_URL",
                rtrim(plugin_dir_url(__FILE__), "/")
            );

            /**
             * Plugin basename from root
             * @var string
             */
            define(
                "EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_BASENAME",
                plugin_basename(__FILE__)
            );

            /**
             * File path from root
             * @var string
             */
            define(
                "EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_FILE",
                __FILE__
            );

            /**
             * Directory from root
             * @var string
             */
            define("EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_DIR", __DIR__);

            /**
             * Nonce
             * @var string
             */
            define(
                "EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_NONCE",
                "2abd9731S07S1b7e9f1DSD2f4E5912e523bj4c80255e3e"
            );
        }

        /**
         * Initialize plugin
         *
         * Called by plugins_loaded hook
         *
         * @access public
         * @since 0.1.0
         */
        public function init()
        {
            require_once EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_DIR .
                "/includes/Dashboard.php";
            new Easy_Appointments_Duplicate_Submission_block\Dashboard();

            require_once EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_DIR .
                "/includes/DB.php";
            require_once EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_DIR .
                "/includes/Util.php";
            require_once EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_DIR .
                "/includes/Ajax.php";
            new Easy_Appointments_Duplicate_Submission_block\Ajax();
        }
    }

    new Easy_Appointments_Duplicate_Submission_block();
}
