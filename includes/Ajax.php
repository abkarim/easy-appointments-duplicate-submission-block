<?php
namespace Easy_Appointments_Duplicate_Submission_block;

/**
 * !Prevent direct access
 */
if (!defined("ABSPATH")) {
    exit();
}

class Ajax
{
    /**
     * Constructor
     */
    public function __construct()
    {
        /**
         * Plugin settings
         *
         * @since 0.1.0
         */
        add_action(
            "wp_ajax_easy_appointments_duplicate_submission_block__should_allow_submission",
            [$this, "allow_form_submission"]
        );
    }

    /**
     * Handle form submission
     *
     * @return bool
     * @access public
     * @since 0.1.0
     * @static
     */
    public function allow_form_submission()
    {
        [$data, $decoded_data] = $this->get_request_data("POST");
        $message = "";

        /**
         * Get data
         */
        $user_ip_address = $_SERVER["REMOTE_ADDR"];

        $user_email = Util::get_value_if_present_in_stdClass(
            $decoded_data,
            "email",
            ""
        );

        /**
         * Validate email
         */
        if (
            $user_email !== "" &&
            !filter_var($user_email, FILTER_VALIDATE_EMAIL)
        ) {
            $this->send_response_and_close_request([
                "message" => "please input a valid email",
                "success" => false,
            ]);
        }

        $user_phone = Util::get_value_if_present_in_stdClass(
            $decoded_data,
            "phone",
            ""
        );

        /**
         * Validate phone
         */
        if ($user_phone !== "" && !preg_match('/^[+0-9]+$/', $user_phone)) {
            $this->send_response_and_close_request([
                "message" => "please input a valid phone number",
                "success" => false,
            ]);
        }

        $prevent_booking = false;
        $white_listed_found = false;

        /**
         * Is appointments booked within selected range
         * contains current IP address
         */
        $is_exists_booking_by_ip = DB::is_appointments_exists_within_selected_range(
            $user_ip_address
        );
        if ($is_exists_booking_by_ip) {
            $prevent_booking = true;
            $message = "duplicate ip found";
            /**
             * Is ip address exists in whitelist
             */
            if (DB::is_exists_in_whitelist("ip", $user_ip_address)) {
                $prevent_booking = false;
                $white_listed_found = true;
                $message = "";
            }
        }

        /**
         * Check email
         */
        if ($white_listed_found === false && $prevent_booking === false) {
            /**
             * Is in whitelist
             */
            if (DB::is_exists_in_whitelist("email", $user_email)) {
                $prevent_booking = false;
                $white_listed_found = true;
            } else {
                /**
                 * Is email exist in appointments that are created within selected range
                 */
                $prevent_booking = DB::is_email_exists_within_selected_range(
                    $user_email
                );
                if ($prevent_booking) {
                    $message = "duplicate email found";
                }
            }
        }

        /**
         * Check phone
         */
        if ($white_listed_found === false && $prevent_booking === false) {
            /**
             * Is in whitelist
             */
            if (DB::is_exists_in_whitelist("phone", $user_phone)) {
                $prevent_booking = false;
                $white_listed_found = true;
            } else {
                /**
                 * Is phone exist in appointments that are created within selected range
                 */
                $prevent_booking = DB::is_phone_exists_within_selected_range(
                    $user_phone
                );
                if ($prevent_booking) {
                    $message = "duplicate phone number found";
                }
            }
        }

        $this->send_response_and_close_request(
            [
                "success" => true,
                "data" => !$prevent_booking,
                "message" => $message,
            ],
            200
        );
    }

    /**
     * Send response and close request
     *
     * @param mixed data
     * @param int status-code default 200
     * @return void
     * @access private
     * @since 0.1.0
     */
    private function send_response_and_close_request($data, $status_code = 200)
    {
        wp_send_json_success($data, $status_code);
        wp_die();
    }
    /**
     * Get URL parameter
     *
     * @return array - action excluded
     * @since 0.1.0
     * @access private
     */
    private function get_url_parameter()
    {
        // Get all parameter
        $data = $_GET;

        // Remove action parameter
        unset($data["action"]);

        return $data;
    }

    /**
     * Get request data
     * validate and returns data
     *
     * @param string request_type Default GET
     * @return array [$data, $decodedData]
     * @since 0.1.0
     * @access private
     */
    private function get_request_data($request_type = "GET")
    {
        $this->block_incoming_request_if_invalid($request_type);

        $data = null;
        $decoded_data = null;

        if ($request_type === "GET") {
            $data = $this->get_url_parameter();
        } elseif ($request_type === "POST") {
            // Get form data
            $data = file_get_contents("php://input");

            if (!($decoded_data = json_decode($data))) {
                wp_send_json_error("data is not valid json", 400);
                return wp_die();
            }
        }

        return [$data, $decoded_data];
    }

    /**
     * Validate request
     *
     * @param string request_type
     * @since 0.1.0
     * @access private
     */
    private function block_incoming_request_if_invalid($request_type)
    {
        if (!isset($_SERVER["HTTP_X_WP_NONCE"])) {
            wp_send_json_error("unauthorized request", 403);
            return wp_die();
        }

        // Validate the nonce
        $nonce = $_SERVER["HTTP_X_WP_NONCE"];

        if (
            wp_verify_nonce(
                $nonce,
                EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_NONCE
            ) === false
        ) {
            wp_send_json_error("unauthorized request", 403);
            return wp_die();
        }

        if ($request_type === "POST") {
            if ($_SERVER["REQUEST_METHOD"] !== "POST") {
                wp_send_json_error("method not allowed", 405);
                wp_die();
            }

            if (
                !isset($_SERVER["CONTENT_TYPE"]) ||
                $_SERVER["CONTENT_TYPE"] != "application/json"
            ) {
                wp_send_json_error(
                    "content type must be application/json",
                    400
                );
                wp_die();
            }
        }

        if ($request_type === "GET") {
            if ($_SERVER["REQUEST_METHOD"] !== "GET") {
                wp_send_json_error("method not allowed", 405);
                wp_die();
            }
        }
    }
}
