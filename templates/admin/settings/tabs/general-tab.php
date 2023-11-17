<h2><?php echo _x( 'General', 'plugin settings', 'es' ); ?></h2>

<div class="es-settings-fields es-settings-fields--general">

    <div class="es-field-row">
        <?php es_settings_field_render( 'area_unit', array(
            'label' => __( 'Area unit', 'es' ),
            'type' => 'select',
        ) ); ?>

        <?php es_settings_field_render( 'lot_size_unit', array(
            'label' => __( 'Lot size unit', 'es' ),
            'type' => 'select',
        ) ); ?>
    </div>

    <?php es_settings_field_render( 'currency', array(
        'label' => __( 'Currency', 'es' ),
        'type' => 'select',
    ) );

    es_settings_field_render( 'date_format', array(
        'label' => __( 'Date format', 'es' ),
        'type' => 'select',
    ) );

    es_settings_field_render( 'time_format', array(
        'label' => __( 'Time format', 'es' ),
        'type' => 'radio-bordered',
    ) );


    es_settings_field_render( 'is_rest_support_enabled', array(
	    'label' => __( 'Enable REST Support', 'es' ),
	    'type' => 'switcher',
	    'description' => __( "Please enable REST support if you need to use Gutenberg or pull your listings via wp api.", 'es' ),
    ) );

    es_settings_field_render( 'logo_attachment_id', array(
        'label' => __( 'Logo image for admin login page', 'es' ),
        'type' => 'images',
        'description' => __( 'Maximum file size - 2MB.<br>Allowed file types: JPG, PNG, GIF.', 'es' ),
        'button_label' => __( 'Upload image', 'es' ),
    ) );

    es_settings_field_render( 'main_color', array(
        'label' => __( 'Main color', 'es' ),
        'type' => 'color',
        'description' => __( 'For large buttons like Search, Request info, etc.', 'es' ),
        'reset_button' => true,
        'reset_value' => ests_default( 'main_color' ),
    ) );

    es_settings_field_render( 'secondary_color', array(
        'label' => __( 'Secondary color', 'es' ),
        'type' => 'color',
        'description' => __( 'For smaller buttons like Search on results page, Contact, etc.', 'es' ),
        'reset_button' => true,
        'reset_value' => ests_default( 'secondary_color' ),
    ) );

//    es_settings_field_render( 'is_tel_code_disabled', array(
//        'label' => __( 'Disable tel country code', 'es' ),
//        'type' => 'switcher',
//    ) ); ?>
</div>
