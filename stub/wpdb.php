<?php
if (class_exists('wbdb')) {
    return;
}

/**
 * Class wpdb
 *
 * This is just an IDE stub of the actual class.
 *
 * @property bool $show_errors
 * @property bool $suppress_errors
 * @property string $last_error
 * @property int $num_queries
 * @property int $num_rows
 * @property int $rows_affected
 * @property int $insert_id
 * @property string $last_query
 * @property array|null $last_result
 * @property resource|bool $result
 * @property array $col_meta
 * @property array $table_charset
 * @property bool $check_current_query
 * @property bool $checking_collation
 * @property array|null $col_info
 * @property array[] $queries
 * @property int $reconnect_retries
 * @property string $prefix
 * @property string $base_prefix
 * @property bool $ready
 * @property int $blogid
 * @property int $siteid
 * @property string[] $tables
 * @property string[] $old_tables
 * @property string[] $global_tables
 * @property string[] $ms_global_tables
 * @property string $comments
 * @property string $commentmeta
 * @property string $links
 * @property string $options
 * @property string $postmeta
 * @property string $posts
 * @property string $terms
 * @property string $term_relationships
 * @property string $term_taxonomy
 * @property string $termmeta
 * @property string $usermeta
 * @property string $users
 * @property string $blogs
 * @property string $blogmeta
 * @property string $registration_log
 * @property string $signups
 * @property string $site
 * @property string $sitecategories
 * @property string $sitemeta
 * @property array $field_types
 * @property string $charset
 * @property string $collate
 * @property string $dbuser
 * @property string $dbpassword
 * @property string $dbname
 * @property string $dbhost
 * @property string $dbh
 * @property string $func_call
 * @property bool|null $is_mysql
 * @property string[] $incompatible_modes
 * @property bool $use_mysqli
 * @property bool $has_connected
 *
 * @method wpdb    __construct(string $dbuser, string $dbpassword, string $dbname, string $dbhost)
 * @method bool    __destruct()
 * @method mixed   __get(string $name)
 * @method void    __set(string $name, mixed $value)
 * @method bool    __isset(string $name)
 * @method void    __unset(string $name)
 * @method void    init_charset()
 * @method array   determine_charset(string $charset, string $collate)
 * @method void    set_charset(resource $dbh, ?string $charset = null, ?string $collate = null)
 * @method void    set_sql_mode(array $modes = [])
 * @method string  set_prefix(string $prefix, bool $set_table_names = true)
 * @method int     set_blog_id(int $blog_id, int $network_id = 0)
 * @method string  get_blog_prefix(int|null $blog_id = null)
 * @method array   tables(string $scope = 'all', bool $prefix = true, int $blog_id = 0)
 * @method void    select(string $db, resource|null $dbh = null)
 * @method string  _real_escape(string $string)
 * @method string|array  _escape(string|array $data)
 * @method void   escape_by_ref(string &$string)
 * @method string  esc_like(string $text)
 * @method void|false  print_error(string $str)
 * @method bool    show_errors(bool $show = false)
 * @method bool    hide_errors()
 * @method bool    suppress_errors(bool $suppress = true)
 * @method void    flush()
 * @method bool    db_connect(bool $allow_bail = true)
 * @method array|bool  parse_db_host(string $host)
 * @method bool|void  check_connection(bool $allow_bail = true)
 * @method int|bool  query(string $query)
 * @method int|bool  _do_query(string $query)
 * @method void    log_query(string $query, float $query_time, string $query_callstack, float $query_start, array $query_data)
 * @method string  placeholder_escape()
 * @method string  add_placeholder_escape(string $query)
 * @method string  remove_placeholder_escape(string $query)
 * @method int|bool  replace(string $table, array $data, array|string|null $format = null)
 * @method int|bool  _insert_replace_helper(string $table, array $data, array|string|null $format = null, string $type = 'INSERT')
 * @method int|bool  delete(string $table, array $where, array|string|null $where_format = null)
 * @method array|bool  process_fields(string $table, array $data, mixed $format)
 * @method array   process_field_formats(array $data, mixed $format)
 * @method array|bool  process_field_charsets(array $data, string $table)
 * @method array|bool  process_field_lengths(array $data, string $table)
 * @method array|object|null|void  get_row(string|null $query, string $output = '', int $y = 0)
 * @method array|object|null  get_results(string|null $query = null, string $output = '')
 * @method string  get_table_charset(string $table)
 * @method string|bool  get_col_charset(string $table, string $column)
 * @method array|bool get_col_length(string $table, string $column)
 * @method bool    check_ascii(string $string)
 * @method bool    check_safe_collation(string $query)
 * @method array   strip_invalid_text(array $data)
 * @method string  strip_invalid_text_from_query(string $query)
 * @method string  strip_invalid_text_for_column(string $table, string $column, string $value)
 * @method string  get_table_from_query(string $query)
 * @method void    load_col_info()
 * @method mixed   get_col_info(string $info_type = 'name', int $col_offset = -1)
 * @method bool    timer_start()
 * @method float   timer_stop()
 * @method bool|void bail(string $message, string $error_code = '500')
 * @method bool    close()
 * @method void    check_database_version()
 * @method bool    supports_collation()
 * @method string  get_charset_collate()
 * @method int|false  has_cap(string $db_cap)
 * @method string  get_caller()
 * @method string|null db_version()
 */
abstract class wpdb
{
    /**
     * @var int
     */
    public $insert_id;

    /**
     * @param string $query
     * @param mixed ...$args
     * @return string|void
     */
    abstract public function prepare($query, ...$args);

    /**
     * @param string|null $query
     * @param int $x
     * @return array
     */
    abstract public function get_col($query = null, $x = 0);

    /**
     * @param string|null $query
     * @param int $x
     * @param int $y
     * @return string|null
     */
    abstract public function get_var($query = null, $x = 0, $y = 0);

    /**
     * @param string $table
     * @param array $data
     * @param array $where
     * @param array|string|null $format
     * @param array|string|null $where_format
     * @return int|bool
     */
    abstract public function update($table, $data, $where, $format = null, $where_format = null);

    /**
     * @param string $table
     * @param array $data
     * @param array|string|null $format
     * @return int|bool
     */
    abstract public function insert($table, $data, $format = null);
}
