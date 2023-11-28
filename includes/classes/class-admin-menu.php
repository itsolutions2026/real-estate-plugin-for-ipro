<?php

/**
 * Class Es_Admin_Menu
 */
class Es_Admin_Menu {

	/**
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( 'Es_Admin_Menu', 'register_admin_pages' ) );
	}

	/**
	 * Register admin pages.
	 *
	 * @return void
	 */
	public static function register_admin_pages() {

		$parent = 'es_dashboard';

		$menu_pages = array(
			'ipro' => array(
				'args' => array(
					__( 'Ipro', 'es' ),
					__( 'Ipro', 'es' ),
					'manage_options',
					$parent,
                    array( 'Es_Settings_Page', 'render' ),
					ES_PLUGIN_URL . 'admin/images/slogo.png',
					'20.7'
				),
				'callback' => 'add_menu_page',
			),
			'my-listings' => array(
				'args' => array(
					$parent,
					__( 'My listings', 'es' ),
					__( 'My listings', 'es' ),
					'manage_options',
					'edit.php?post_type=properties',
				),
				'callback' => 'add_submenu_page',
			),
			'data-manager' => array(
				'args' => array(
					$parent,
					__( 'Data manager', 'es' ),
					__( 'Data manager', 'es' ),
					'manage_options',
					'es_data_manager',
					array( 'Es_Data_Manager_Page', 'render' )
				),
				'callback' => 'add_submenu_page',
			),
			'fields-builder' => array(
				'args' => array(
					$parent,
					__( 'Fields Builder', 'es' ),
					__( 'Fields Builder', 'es' ),
					'manage_options',
					'es_fields_builder',
					array( 'Es_Fields_Builder_Page', 'render' )
				),
				'callback' => 'add_submenu_page',
			)
		);

        $menu_pages['demo'] = array(
            'args' => array(
                $parent,
                __( 'Demo content', 'es' ),
                __( 'Demo content', 'es' ),
                'manage_options',
                'es_demo',
                array( 'Es_Demo_Page', 'render' )
            ),
            'callback' => 'add_submenu_page',
        );



        $menu_pages = apply_filters( 'es_register_admin_pages_args', $menu_pages );

		if ( ! empty( $menu_pages ) ) {
			foreach ( $menu_pages as $menu_page ) {
				call_user_func_array( $menu_page['callback'], $menu_page['args'] );
			}
		}

	}
}

Es_Admin_Menu::init();
