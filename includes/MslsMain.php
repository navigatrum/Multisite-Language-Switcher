<?php

/**
 * Main
 *
 * @package Msls
 */

/**
 * MslsMain requests a instance of MslsOptions in his constructor
 */
require_once dirname( __FILE__ ) . '/MslsOptions.php';

/**
 * MslsMain requests a instance of MslsBlogCollection in his constructor
 */
require_once dirname( __FILE__ ) . '/MslsBlogs.php';

/**
 * Interface for hook classes
 *
 * @package Msls
 */
interface IMslsMain {

    /**
     * A class which implements IMslsMain must define such a init-method
     */
    public static function init();

}

/**
 * Generic hook class
 *
 * @package Msls
 */
class MslsMain {

    /**
     * @var MslsOptions
     */
    protected $options;

    /**
     * @var MslsBlogCollection
     */
    protected $blogs;

    /**
     * Constructor
     */
    public function __construct() {
        load_plugin_textdomain(
            'msls',
            false,
            dirname( MSLS_PLUGIN_PATH ) . '/languages/'
        );
        $this->options = MslsOptions::instance();
        $this->blogs   = MslsBlogCollection::instance();
    }

    /**
     * Get url
     * 
     * @param string $dir
     * @return string
     */
    public function get_url( $dir ) {
        $url = sprintf(
            '%s/%s/%s',
            WP_PLUGIN_URL, 
            dirname( MSLS_PLUGIN_PATH ),
            $dir
        );
        return esc_url( $url );
    }

    /**
     * Get flag url
     * 
     * @param string $language
     * @param bool $plugin
     * @return string
     */
    public function get_flag_url( $language, $plugin = false ) {
        if ( !$plugin && !empty( $this->options->image_url ) ) {
            $url = $this->options->image_url;
        }
        else {
            $url = $this->get_url( 'flags' );
        }
        if ( 5 == strlen( $language ) )
            $language = strtolower( substr( $language, -2 ) );
        return sprintf(
            '%s/%s.png',
            $url,
            $language
        );
    }

    /**
     * Save
     * 
     * @param integer $id
     * @param string $class
     */
    protected function save( $id, $class ) {
        if ( isset( $_POST['msls'] ) ) {
            $mydata  = $_POST['msls'];
            $options = new $class( $id );
            $options->save( $mydata );
            $language = $this->blogs->get_current_blog()->get_language();
            $mydata[$language] = $id;
            foreach ( $this->blogs->get() as $blog ) {
                $language = $blog->get_language();
                if ( !empty( $mydata[$language] ) ) {
                    switch_to_blog( $blog->userblog_id );
                    $temp    = $mydata;
                    $options = new $class( $temp[$language] );
                    unset( $temp[$language] );
                    $options->save( $temp );
                    restore_current_blog();
                }
            }
        }
    }

}

/**
 * Provides functionalities for activation an deactivation
 *
 * @package Msls
 */
class MslsPlugin {

    /**
     * Activate plugin
     */
    public static function activate() {
        if ( function_exists( 'is_multisite' ) && is_multisite() ) 
            return; 
        deactivate_plugins( __FILE__ );
        die(
            "This plugin needs the activation of the multisite-feature for working properly. Please read <a href='http://codex.wordpress.org/Create_A_Network'>this post</a> if you don't know the meaning.\n"
        );
    }

    /**
     * Deactivate plugin
     * 
     * @todo Write the deactivate-method
     */
    public static function deactivate() { }

}

/**
 * Generic class for overloading properties
 *
 * <code>
 * $obj = new MslsGetSet;
 * $obj->test = 'This is just a test';
 * echo $obj->test;
 * </code>
 * 
 * @package Msls
 */
class MslsGetSet {

    /**
     * @var array
     */
    protected $arr = array();

    /**
     * "Magic" set arg
     *
     * @param mixed $key
     * @param mixed $value
     */
    final public function __set( $key, $value ) {
        $this->arr[$key] = $value;
        if ( empty( $this->arr[$key] ) )
            unset( $this->arr[$key] );
    }

    /**
     * "Magic" get arg
     *
     * @param mixed $key
     * @return mixed
     */
    final public function __get( $key ) {
        return isset( $this->arr[$key] ) ? $this->arr[$key] : null;
    }

    /**
     * "Magic" isset
     *
     * @param mixed $key
     * @return bool
     */
    final public function __isset( $key ) {
        return isset( $this->arr[$key] );
    }

    /**
     * Check if the array has an non emty item
     * 
     * @param string $key
     * @return bool
     */ 
    final public function has_value( $key ) {
        return !empty( $this->arr[$key] ) ? true : false;
    }

    /**
     * Get args-array
     *
     * @return array
     */
    final protected function getArr() {
        return $this->arr;
    }

}

?>