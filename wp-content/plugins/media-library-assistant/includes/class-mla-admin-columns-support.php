<?php
/**
 * Media Library Assistant Admin Columns (plugin) Support
 *
 * @package Media Library Assistant
 * @since 2.22
 */
defined( 'ABSPATH' ) or die();

/**
 * Class CPAC Storage Model MLA (Media Library Assistant) supports the Admin Columns plugin
 *
 * @package Media Library Assistant
 * @since 2.22
 */
class CPAC_Storage_Model_MLA extends AC_StorageModel_Media {

	/**
	 * Calls the parent function to set some default configs,
	 * then initializes some MLA-specific properties.
	 *
	 * @since 2.22
	 */
	public function init() {
		parent::init();

		$this->key = 'mla-media-assistant';
		$this->label = __( 'Media Library Assistant' );
		$this->singular_label = __( 'Assistant' );
		$this->screen = 'media_page_' . MLACore::ADMIN_PAGE_SLUG;
		$this->subpage = MLACore::ADMIN_PAGE_SLUG;

	}

	/**
	 * Sets an MLA filter to handle custom column display.
	 *
	 * @since 2.22
	 */
	public function init_manage_value() {
		add_filter( 'mla_list_table_column_default', array( $this, 'mla_manage_value' ), 100, 3 );
	}

	/**
	 * Returns the Media/Assistant sub menu table column slugs/keys
	 *
	 * @since 2.22
	 *
	 * @return    array    ( index => 'column_slug' )
	 */
	public function get_default_column_names() {
		if ( ! class_exists( 'MLAQuery' ) ) {
			require_once( MLA_PLUGIN_PATH . 'includes/class-mla-data-query.php' );
			MLAQuery::initialize();
		}

		return array_keys( apply_filters( 'mla_list_table_get_columns', MLAQuery::$default_columns ) );
	}

	/**
	 * Returns the custom fields assigned to Media Library items, removing those already present
	 * in the Media/Assistant submenu table
	 *
	 * @since 2.22
	 *
	 * @return    array    ( index => array( 0 => 'custom field name' ) )
	 */
	public function get_meta() {
		global $wpdb;

		/*
		 * Find all of the custom field names assigned to Media Library items
		 */
		$meta = $wpdb->get_results( "SELECT DISTINCT meta_key FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON pm.post_id = p.ID WHERE p.post_type = 'attachment' ORDER BY 1", ARRAY_N );

		/*
		 * Find the fields already present in the submenu table
		 */
		$mla_columns = apply_filters( 'mla_list_table_get_columns', MLAQuery::$default_columns );
		$mla_custom = array();
		foreach ( $mla_columns as $slug => $heading ) {
			if ( 'c_' === substr( $slug, 0, 2 ) ) {
				$mla_custom[] = $heading;
			}
		}

		/*
		 * Remove the fields already present in the submenu table
		 */
		foreach ( $meta as $index => $value ) {
			if ( in_array( esc_html( current( $value ) ), $mla_custom ) ) {
				unset( $meta[ $index ] );
			}
		}

		return $meta;
	}

	/**
	 * Return the content of an Admin Columns custom column
	 *
	 * @since 2.22
	 *
	 * @param    string $content Current column content (empty string)
	 * @param    object $item Current Media Library item
	 * @param    string $column_name Current column slug
	 *
	 * @return string Column value or NULL if not an Admin Columns custom column
	 */
	public function mla_manage_value( $content, $item, $column_name ) {
		$value = $this->get_display_value_by_column_name( $column_name, $item->ID );

		return $value ? $value : $content;
	}
} // class CPAC_Storage_Model_MLA
