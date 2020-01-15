<?php

if (class_exists('WP_Error')) {
    return;
}

/**
 * Class WP_Error
 *
 * @property array $errors = []
 * @property array $error_data = []
 *
 * @method array get_error_codes()
 * @method string|int get_error_code()
 * @method array get_error_messages(string|int $code = '')
 * @method string get_error_message(string|int $code = '')
 * @method mixed get_error_data(string $code = '')
 * @method bool has_errors()
 * @method mixed add(string|int $code, string $message, mixed $data)
 * @method void add_data(mixed $data, string|int $code = '')
 * @method void remove(string|int $code)
 */
class WP_Error {
    /**
     * @param string|int $code Error code
     * @param string $message Error message
     * @param mixed $data Optional. Error data.
     */
    public function __construct( $code = '', $message = '', $data = '' ) {
        // Dummy
    }
}
