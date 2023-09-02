<?php
namespace Easy_Appointments_Duplicate_Submission_block;

/**
 * !Prevent direct access
 */
if (!defined("ABSPATH")) {
    exit();
}

class Util
{
    /**
     * Get value if present
     *
     * use's isset to check if the value is present or not
     * if present returns value
     *
     * defaults to default value
     *
     * @param object
     * @param mixed - default to return
     * @since 0.1.0
     * @access public
     */
    public static function get_value_if_present_in_stdClass(
        $object,
        $value,
        $default
    ) {
        if (isset($object->$value)) {
            return $object->$value;
        }

        return $default;
    }
}
