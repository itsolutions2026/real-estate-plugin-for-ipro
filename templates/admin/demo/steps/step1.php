<div class="es-step" id="step1">

    <div class="es-demo__field-container es-demo__field-container--country">
        <h2><?php _e( 'What is your country?', 'es' ); ?></h2>
        <p><?php _e( 'The plugin will pick up the language, currency, area dimensions<br> and other settings of the country you select.', 'es' ); ?></p>
        <?php $description = __( '{0} language, {1} currency, {2} and {3} for area and lot sizes.<br>Go to Settings to change it.', 'es' );
        $countries = ests_values( 'country' );
        es_framework_field_render( 'country', array(
            'type' => 'radio-bordered',
            'options' => $countries,
            'value' => ests( 'country' ),
            'attributes' => array(
                'data-description' => $description,
                'class' => 'js-es-country-field'
            ),
            'description' => '-'
        ) ); ?>
    </div>

    <a href="#step2" class="es-btn es-btn--step es-btn--large es-btn--primary es-btn--icon-right js-es-next-step"><?php _e( 'Continue', 'es' ); ?><span class="es-icon es-icon_arrow-right"></a>
</div>
