<?php
namespace Easy_Appointments_Duplicate_Submission_block;

/**
 * !Prevent direct access
 */
if (!defined("ABSPATH")) {
    exit();
}

class DB
{
    /**
     * Get whitelist data
     *
     * @param string type
     * @return array whitelist array
     * @since 0.1.0
     * @access public
     * @static
     */
    public static function get_whitelist_data($type)
    {
        return json_decode(
            get_option(
                EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN .
                    "_" .
                    $type .
                    "_whitelist",
                "[]"
            ),
            true
        );
    }

    /**
     * Add whitelist data
     *
     * @param string type
     * @param string data
     * @since 0.1.0
     * @access public
     * @static
     */
    public static function add_whitelist_data($type, $data)
    {
        $previous_data = self::get_whitelist_data($type);
        array_push($previous_data, $data);
        update_option(
            EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN .
                "_" .
                $type .
                "_whitelist",
            json_encode($previous_data)
        );
    }

    /**
     * Delete whitelist data
     *
     * @param string type
     * @param string data
     * @return array new data
     * @since 0.1.0
     * @access public
     * @static
     */
    public static function delete_whitelist_data($type, $data)
    {
        $previous_data = self::get_whitelist_data($type);

        /**
         * Get selected item
         */
        $key = array_search($data, $previous_data);
        if ($key !== false) {
            /**
             * Remove item
             */
            unset($previous_data[$key]);
        }

        /**
         * Update in database
         */
        update_option(
            EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN .
                "_" .
                $type .
                "_whitelist",
            json_encode($previous_data)
        );

        return $previous_data;
    }

    /**
     * Is exists in whitelist
     *
     * @param string type
     * @param string value
     * @return bool is_found
     * @access public
     * @static
     * @since 0.1.0
     */
    public static function is_exists_in_whitelist($type, $value)
    {
        $data = self::get_whitelist_data($type);
        $is_found = array_search($value, $data);
        if ($is_found === false) {
            return false;
        }
        return true;
    }

    /**
     * Get appointments table name
     *
     * @return string table_name
     * @access private
     * @since 0.1.0
     * @static
     */
    private static function get_appointments_table_name()
    {
        global $wpdb;
        $table_name = "ea_appointments";
        return $wpdb->prefix . $table_name;
    }

    /**
     * Get fields table name
     *
     * @return string table_name
     * @access private
     * @since 0.1.0
     * @static
     */
    private static function get_fields_table_name()
    {
        global $wpdb;
        $table_name = "ea_fields";
        return $wpdb->prefix . $table_name;
    }

    /**
     * Appointments exists in database
     * within selected days
     *
     * @param string IP
     * @return bool is_exists
     * @static
     * @access public
     * @since 0.1.0
     */
    public static function is_appointments_exists_within_selected_range($ip)
    {
        global $wpdb;
        $table_name = self::get_appointments_table_name();
        $block_session_range_in_Days = get_option(
            EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN .
                "-appointments_session_range",
            30
        );

        $query = "
                    SELECT * FROM $table_name 
                    WHERE ip='$ip' 
                    ORDER BY id DESC
                    LIMIT 1
                ";

        $data = $wpdb->get_row($query);

        if ($data) {
            /**
             * The date when appointments row is created
             */
            $date = $data->created;

            // Convert the date string to a DateTime object
            $dateObject = new \DateTime($date);

            // Get the current date
            $currentDate = new \DateTime();

            // Calculate the difference in days
            $interval = $currentDate->diff($dateObject);

            // Is exists
            if ($interval->days >= $block_session_range_in_Days) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Is appointment exists within selected range
     *
     * @param int id
     * @return bool
     * @since 0.1.0
     * @access public
     * @static
     */
    public static function is_appointment_exists_within_selected_range($id)
    {
        global $wpdb;
        $table_name = self::get_appointments_table_name();
        $block_session_range_in_Days = get_option(
            EASY_APPOINTMENTS_DUPLICATE_SUBMISSION_BLOCK_TEXTDOMAIN .
                "-appointments_session_range",
            30
        );

        $query = "
                    SELECT * FROM $table_name 
                    WHERE id='$id' 
                    ORDER BY id DESC
                    LIMIT 1
                ";

        $data = $wpdb->get_row($query);

        if ($data) {
            /**
             * The date when appointments row is created
             */
            $date = $data->created;

            // Convert the date string to a DateTime object
            $dateObject = new \DateTime($date);

            // Get the current date
            $currentDate = new \DateTime();

            // Calculate the difference in days
            $interval = $currentDate->diff($dateObject);

            // Is exists
            if ($interval->days >= $block_session_range_in_Days) {
                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * Appointments exists in database
     * within selected days
     *
     * @param string email
     * @return bool is_exists
     * @static
     * @access public
     * @since 0.1.0
     */
    public static function is_email_exists_within_selected_range($email)
    {
        global $wpdb;
        $table_name = self::get_fields_table_name();

        $query = "
                    SELECT * FROM $table_name 
                    WHERE field_id=1 AND value='$email' 
                    ORDER BY id DESC
                    LIMIT 1
                ";

        $data = $wpdb->get_row($query);

        if ($data) {
            // Appointments ID
            $app_id = $data->app_id;

            if (self::is_appointment_exists_within_selected_range($app_id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Appointments exists in database
     * within selected days
     *
     * @param string email
     * @return bool is_exists
     * @static
     * @access public
     * @since 0.1.0
     */
    public static function is_phone_exists_within_selected_range($phone)
    {
        global $wpdb;
        $table_name = self::get_fields_table_name();

        $query = "
                    SELECT * FROM $table_name 
                    WHERE field_id=3 AND value='$phone' 
                    ORDER BY id DESC
                    LIMIT 1
                ";

        $data = $wpdb->get_row($query);

        if ($data) {
            // Appointments ID
            $app_id = $data->app_id;

            if (self::is_appointment_exists_within_selected_range($app_id)) {
                return true;
            }
        }

        return false;
    }
}
