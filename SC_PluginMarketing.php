<?php
/**
 * Plugin Name: Smartcat Plugin Marketing
 * Author: Smartcat
 * Version: 1.0.0
 *
 * @since 1.0.0
 * @package global
 */
if ( !class_exists( 'SC_PluginMarketing' ) ) :

    /**
     * Plugin for adding remotely adding notifications to the WordPress admin.
     *
     * @singleton
     * @since 1.0.0
     */
    class SC_PluginMarketing {

        /**
         * Plugin operation mode.
         *
         * @since 1.0.0
         */
        const MODE_PLUGIN = 'plugin';

        /**
         * Embedded library operation mode.
         *
         * @since 1.0.0
         */
        const MODE_EMBEDDED = 'embedded';

        /**
         * The operating mode of the plugin
         *
         * @since 1.0.0
         * @var string
         */
        protected static $mode = self::MODE_EMBEDDED;

        /**
         * Whether or not set_mode() has been called.
         *
         * @since 1.0.0
         * @var bool
         */
        protected static $mode_set = false;

        /**
         * The plugin instance.
         *
         * @since 1.0.0
         * @var null|SC_PluginMarketing
         */
        protected static $instance = null;

        /**
         * The current delegate function of the plugin.
         *
         * @since 1.0.0
         * @var null
         */
        protected $function = null;

        /**
         * Prevent calling __construct()
         *
         * @since 1.0.0
         */
        private function __constructor() {}

        /**
         * Get the plugin instance.
         *
         * @param mixed $config
         *
         * @since 1.0.0
         * @return SC_PluginMarketing
         */
        public static function instance( $config = '' ) {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
                self::$instance->initialize( $config );
            }
            return self::$instance;
        }

        /**
         * Set the plugin operation mode.
         *
         * @param string $mode
         *
         * @internal
         * @since 1.0.0
         * @return void
         */
        public static function _set_mode( $mode ) {
            if ( !self::$mode_set && in_array( $mode, array( self::MODE_PLUGIN, self::MODE_EMBEDDED ) ) ) {
                self::$mode = $mode;
                self::$mode_set = true;
            }
        }

        /**
         * Initializes the plugin.
         *
         * @param string|array $config
         *
         * @since 1.0.0
         * @return void
         */
        public function initialize( $config = '' ) {
            if ( self::$mode === self::MODE_PLUGIN ) {
                $this->function = new SC_MarketingFunctionPlugin();
            } else {
                $this->function = new SC_MarketingFunctionEmbedded( $config );
            }
        }

    }

    /**
     * Get the plugin instance.
     *
     * @action plugins_loaded
     *
     * @param mixed $config
     *
     * @since 1.0.0
     * @return SC_PluginMarketing
     */
    function sc_plugin_marketing( $config = '' ) {
        return SC_PluginMarketing::instance( $config );
    }

    /**
     * Load the plugin.
     *
     * @since 1.0.0
     */
    function sc_plugin_marketing_init() {
        if ( !in_array( __FILE__, wp_get_active_and_valid_plugins() ) ) {
            return;
        }

        SC_PluginMarketing::_set_mode( SC_PluginMarketing::MODE_PLUGIN ); // Set the mode to plugin if active

        // Boot the plugin
        add_action( 'plugins_loaded', 'sc_plugin_marketing' );
    }

    if ( defined( 'ABSPATH' ) ) {
        sc_plugin_marketing_init(); // Check environment early
    }

endif;


if ( !class_exists( 'SC_MarketingFunctionPlugin' ) ) :
/**
 * Handles plugin functionality when running in plugin mode.
 *
 * @since 1.0.0
 */
class SC_MarketingFunctionPlugin {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize the plugin.
     *
     * @since 1.0.0
     * @return void
     */
    protected function init() {
        add_action( 'init', array( $this, 'register_post_type' ) );
    }

    /**
     * Register the message post type.
     *
     * @since 1.0.0
     * @return void
     */
    public function register_post_type() {
        $labels = array(
            'name'                  => _x( 'Marketing Messages', 'Post Type General Name' ),
            'singular_name'         => _x( 'Marketing Message', 'Post Type Singular Name' ),
            'menu_name'             => __( 'Marketing' ),
            'name_admin_bar'        => __( 'Marketing Message' ),
            'archives'              => __( 'Item Archives' ),
            'parent_item_colon'     => __( 'Parent Item:' ),
            'all_items'             => __( 'Message List' ),
            'add_new_item'          => __( 'Create Message' ),
            'add_new'               => __( 'Create Message' ),
            'new_item'              => __( 'Create Message' ),
            'edit_item'             => __( 'Edit Message' ),
            'update_item'           => __( 'Update Message' ),
            'view_item'             => __( 'View Message' ),
            'search_items'          => __( 'Search Message' ),
            'not_found'             => __( 'Message Not found' ),
            'not_found_in_trash'    => __( 'Message Not found in Trash' ),
            'featured_image'        => __( 'Featured Image' ),
            'set_featured_image'    => __( 'Set featured image' ),
            'remove_featured_image' => __( 'Remove featured image' ),
            'use_featured_image'    => __( 'Use as featured image' ),
            'insert_into_item'      => __( 'Insert into message' ),
            'uploaded_to_this_item' => __( 'Uploaded to this message' ),
            'items_list'            => __( 'Messages list' ),
            'items_list_navigation' => __( 'Messages list navigation' ),
            'filter_items_list'     => __( 'Filter messages list' )
        );
        $args = array(
            'labels'               => $labels,
            'description'          => __( 'Remotely managed marketing messages for plugins' ),
            'supports'             => array( 'editor', 'title' ),
            'hierarchical'         => false,
            'public'               => false,
            'show_ui'              => true,
            'show_in_menu'         => true,
            'menu_position'        => 10,
            'menu_icon'            => 'dashicons-megaphone',
            'show_in_admin_bar'    => false,
            'show_in_nav_menus'    => false,
            'can_export'           => true,
            'has_archive'          => false,
            'exclude_from_search'  => true,
            'publicly_queryable'   => false,
            'capability_type'      => 'post',
            'feeds'                => null,
            'show_in_rest'         => true,
            'rest_base'            => 'marketing-messages'
        );

        register_post_type( 'marketing_message', $args );
    }

}

endif;


if ( !class_exists( 'SC_MarketingFunctionEmbedded' ) ) :
/**
 * Handles plugin functionality when running in embedded mode.
 *
 * @since 1.0.0
 */
class SC_MarketingFunctionEmbedded {

    /**
     * The url of the server to pull messages from.
     *
     * @since 1.0.0
     * @var string
     */
    public $url = '';

    /**
     * Constructor.
     *
     * @param string|array $args
     *
     * @since 1.0.0
     */
    public function __construct( $args = '' ) {
        $default = array(
            'url' => ''
        );

        foreach ( wp_parse_args( $args, $default ) as $key => $value ) {
            if ( in_array( $key, array( 'url' ) ) ) {
                $this->$key = $value;
            }
        }

        $this->init();
    }

    /**
     * Initialize the embedded library.
     *
     * @since 1.0.0
     * @return void
     */
    protected function init() {

    }

}

endif;
