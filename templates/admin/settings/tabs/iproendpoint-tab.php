<h2><?php echo _x( 'Ipro Endpoint Setting', 'plugin settings', 'es' ); ?></h2>

<div class="es-settings-fields es-settings-fields--general">
    <?php es_settings_field_render( 'ipro_api_endpoint', array(
        'label' => __( 'Ipro API Endpoint', 'es' ),
        'type' => 'text', ) );
    ?>

    <?php es_settings_field_render( 'ipro_client_id', array(
        'label' => __( 'Ipro Client Id', 'es' ),
        'type' => 'text', ) );
    ?>
    <?php es_settings_field_render( 'ipro_client_secret', array(
        'label' => __( 'Ipro Client Secret', 'es' ),
        'type' => 'password', ) );
    ?>
</div>