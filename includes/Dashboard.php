<?php
namespace Easy_Appointments_Duplicate_Submission_block;

/**
 * !Prevent direct access
 */
if (!defined("ABSPATH")) {
    exit();
}

class Dashboard
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action("admin_menu", [$this, "add_page"]);

        // Load JavaScripts
        add_action("wp_enqueue_scripts", [$this, "load_javascript"]);
    }

    /**
     * Load javascript
     *
     * Called by admin_enqueue_scripts from Constructor
     *
     * @since 0.1.0
     * @access public
     */
    public function load_javascript($hook)
    {
        wp_register_script(
            "easy-appointments-duplicate-submission-block--submission-block-handler",
            EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_URL .
                "/assets/js/handle_appointment_submission.js",
            [],
            "0.1.0",
            true
        );

        /**
         * Only load this script when ea's shortcode available
         */
        global $post;
        if (
            is_a($post, "WP_Post") &&
            has_shortcode($post->post_content, "ea_bootstrap")
        ) {
            wp_enqueue_script(
                "easy-appointments-duplicate-submission-block--submission-block-handler"
            );

            /**
             * Pass data to JavaScript to use in frontend
             *
             * @since 0.1.0
             */
            wp_localize_script(
                "easy-appointments-duplicate-submission-block--submission-block-handler",
                "plugin_info_from_backend",
                [
                    "ajax_nonce" => wp_create_nonce(
                        EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_NONCE
                    ),
                    "ajax_url" => admin_url("admin-ajax.php"),
                    "days_duration" => get_option(
                        EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN .
                            "-appointments_session_range",
                        30
                    ),
                ]
            );
        }
    }

    /**
     * Admin success notice
     *
     * Show success on admin dashboard
     *
     * @since 0.1.0
     * @param string
     * @access public
     */
    public function show_admin_success_message($massage)
    {
        ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php _e($massage); ?>
                </p>
            </div>
        <?php
    }

    /**
     * Admin warning notice
     *
     * Show warning on admin dashboard
     *
     * @since 0.1.0
     * @param string
     * @access public
     */
    public function show_admin_warning_message($massage)
    {
        ?>
            <div class="notice notice-warning is-dismissible">
                <p>
                    <?php _e($massage); ?>
                </p>
            </div>
        <?php
    }

    /**
     * Admin error notice
     *
     * Show error on admin dashboard
     *
     * @since 0.1.0
     * @param string
     * @access public
     */
    public function show_admin_error_message($massage)
    {
        ?>
            <div class="notice notice-error is-dismissible">
                <p>
                    <?php _e($massage); ?>
                </p>
            </div>
        <?php
    }

    /**
     * Add page
     * adds page in dashboard
     *
     * @since 0.1.0
     * @access public
     */
    public function add_page()
    {
        /**
         * Add list submenu
         *
         * @since 0.1.0
         */
        add_submenu_page(
            "easy_app_top_level",
            "Submission Whitelist List",
            "Submission Whitelist",
            "manage_options",
            "easy_submission_blocks_whitelist",
            [$this, "render_list_page_element"]
        );

        /**
         * Add settings submenu
         *
         * @since 0.1.0
         */
        add_submenu_page(
            "easy_app_top_level",
            "Submission Block Settings",
            "Submission Block Settings",
            "manage_options",
            "easy_submission_blocks_settings",
            [$this, "render_settings_page_element"]
        );
    }

    /**
     * Render whitelist page
     *
     * called by add_submenu_page hook
     * @access public
     * @since 0.1.0
     */
    public function render_list_page_element()
    {
        $page_title = get_admin_page_title();
        $data_type = isset($_POST["type"]) ? $_POST["type"] : "email";
        $table_data = "";

        /**
         * Get table data
         */
        $data = DB::get_whitelist_data($data_type);

        /**
         * Add new data
         */
        if (isset($_POST["add_new"])) {
            $input_value = isset($_POST["value"]) ? trim($_POST["value"]) : "";
            if ($input_value !== "") {
                $invalid_data = false;
                if ($data_type === "email") {
                    if (!filter_var($input_value, FILTER_VALIDATE_EMAIL)) {
                        $invalid_data = true;
                    }
                }

                if ($data_type === "phone") {
                    if (!preg_match('/^[+0-9]+$/', $input_value)) {
                        $invalid_data = true;
                    }
                }

                if ($data_type === "ip") {
                    if (!filter_var($input_value, FILTER_VALIDATE_IP)) {
                        $invalid_data = true;
                    }
                }

                if ($invalid_data === false) {
                    /**
                     * Add into previous data
                     */
                    array_push($data, $input_value);
                    DB::add_whitelist_data($data_type, $input_value);
                } else {
                    add_action("admin_notices", function ($data_type) {
                        $this->show_admin_error_message(
                            "Please input a valid $data_type"
                        );
                    });
                }
            }
        }

        /**
         * Delete data
         */
        if (isset($_POST["delete"])) {
            $data_to_delete = isset($_POST["delete_value"])
                ? trim($_POST["delete_value"])
                : "";
            if ($data_to_delete !== "") {
                $data = DB::delete_whitelist_data($data_type, $data_to_delete);
            }
        }

        /**
         * Prepare table data
         */
        $index = 1;
        foreach ($data as $value) {
            $table_data .= "<tr>
                                <td style='border: 1px solid black; border-collapse: collapse;'>$index</td>
                                <td style='border: 1px solid black; border-collapse: collapse;'>$value</td>
                                <td style='border: 1px solid black; border-collapse: collapse;'>
                                                                                                <form method='POST'>
                                                                                                    <input type='hidden' name='type' value='$data_type' />
                                                                                                    <input type='hidden' name='delete_value' value='$value' />
                                                                                                    <button type='submit' name='delete'>Delete</button>
                                                                                                </form>
                                </td>
                            </tr>";
            $index += 1;
        }

        $selection_data = "<section style='margin-bottom: 1rem;'>
                            <form method='POST' style='display:flex; align-items: stretch; gap: 0;'>
                                <select name='type' required>
                                    <option disabled selected>Select Item type</option>
                                    <option value='email'>Email</option>
                                    <option value='phone'>Phone</option>
                                    <option value='ip'>IP Address</option>
                                </select>
                                <button type='submit' name='data_type'>Select</button>
                            </form>
                        </section>";

        $input_type = $data_type === "email" ? "email" : "text";
        $add_field = "<section>
                        <form style='display:flex; align-items: stretch; gap: 0; margin-bottom: 1rem;' method='POST'>
                            <input type='$input_type' name='value' placeholder='$data_type' />
                            <input type='hidden' name='type' value='$data_type' />
                            <button type='submit' name='add_new'>Add $data_type</button>
                        </from>
                    </section>";

        $html = "<main id='whitelist_settings'> <h1>$page_title</h1>
                    $selection_data
                    $add_field
                    <table  style='border: 1px solid black; border-collapse: collapse; width: 100%; border: 1px solid black; background-color: white;'>
                        <thead>
                            <tr>
                                <th  style='border: 1px solid black; border-collapse: collapse;'>
                                    #
                                </th >
                                <th  style='border: 1px solid black; border-collapse: collapse;'>
                                    $data_type
                                </th >
                                <th  style='border: 1px solid black; border-collapse: collapse;'>
                                    Action
                                </th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            $table_data
                        </tbody>
                    </table>
            </main>";

        echo $html;
    }

    /**
     * Render whitelist page
     *
     * called by add_submenu_page hook
     * @access public
     * @since 0.1.0
     */
    public function render_settings_page_element()
    {
        // check user capabilities
        if (!current_user_can("manage_options")) {
            return;
        }

        $page_title = get_admin_page_title();
        $appointments_block_session_range = get_option(
            EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN .
                "-appointments_session_range",
            30
        );

        /**
         * UPDATE options
         */
        if (isset($_POST["submit"])) {
            $range = $_POST["range"];
            if ($range >= 1) {
                $appointments_block_session_range = $range;
                update_option(
                    EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN .
                        "-appointments_session_range",
                    $range
                );
            }
        }

        $html = "<main id='whitelist_settings'>
                    <h1>$page_title</h1>
                    <section>
                        <form method='post'>
                            <h4>Appointments block session range (days)</h4>
                            <input name='range' type='number' step='1' min='0' value='$appointments_block_session_range' />
                            <br />
                            <br />
                            <button type='submit' name='submit'>Save</button>
                        </form>
                    </section>    
                </main>";
        echo $html;
    }
}
