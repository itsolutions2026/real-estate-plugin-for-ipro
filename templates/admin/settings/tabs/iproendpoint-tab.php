<h2><?php echo _x('Ipro Endpoint Setting', 'plugin settings', 'es'); ?></h2>
<style>
    .es-btn.js-es-ipro-import {
        margin-top: 20px;
    }
    .ipro-load-progress-bar {
        transition: all 0.5s;
        will-change: transform;
        box-shadow: 0 0 5px #3ba9f4;
        background-color: rgb(59 169 244 / 31%);
        border-radius: 2rem;
        overflow: hidden;
    }
    .ipro-load-progress-bar .notification-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
    }
    .es-progress-status{
        background-color: #3ba9f4;
        height: 24px;
        width: 0%
    }
</style>
<div class="es-settings-fields es-settings-fields--general">
    <?php es_settings_field_render('ipro_api_endpoint', array(
        'label' => __('Ipro API Endpoint', 'es'),
        'type' => 'text',));
    ?>

    <?php es_settings_field_render('ipro_client_id', array(
        'label' => __('Ipro Client Id', 'es'),
        'type' => 'text',));
    ?>
    <?php es_settings_field_render('ipro_client_secret', array(
        'label' => __('Ipro Client Secret', 'es'),
        'type' => 'password',));
    ?>

    <?php
    es_settings_field_render('is_rest_cron_enabled', array(
        'label' => __('Enable cron job', 'es'),
        'type' => 'switcher',
        'description' => __("Please enable Cron Job if you want to get updated properties from Ipro every minutes.", 'es'),
    ));
    ?>
    <div class="es-field es-field__ipro_client_secret es-field--password ">
        <label for="es-field-ipro_client_secret">
            <div class="es-field__label">Load Ipro Properties initially.</div>
            <p class="es-field__description">This is used to fetch all properties initially, you need to use this option before enable cron job. Don't refresh page or take any other actions while loading ipro properties initially.</p>
        </label>

        <button class="es-btn es-btn--secondary es-btn--middle js-es-ipro-import">Load Properties</button>

        <div class="es-field ipro-load-progress-bar">
            <div class="notification-text">
                <span class="currentStatus"></span> || <span class="total"></span>
            </div>
            <div class="es-progress-status"></div>
        </div>
    </div>

</div>


<script>
    ( function( $ ) {
        'use strict';

        $( function() {
            let loadTimeInterval, idList = [], $button, total, currentStatus = 0, bufferSize = 5;

            let clear = () => {
                $button.removeProp( 'disabled' ).removeAttr( 'disabled' );
                $button.removeClass('es-preload');
            }

            let updateProgressBar = () => {
                $('.notification-text .total').text(total);
                $('.notification-text .currentStatus').text(currentStatus);
                $('.es-progress-status').css('width', (currentStatus * 100 / total).toFixed(2) + '%')
            }

            let loadBuffer = () => {
                let idBufferArray = [];
                if(idList.length > bufferSize){
                    idBufferArray = idList.splice(0 , bufferSize);
                }
                else{
                    idBufferArray = idList.splice(0 , idList.length);
                }
                if(idBufferArray.length){
                    $.post( ajaxurl, {
                        _wpnonce: $("input[name='_wpnonce']").val(),
                        step: 'progress',
                        IDs: idBufferArray,
                        action: 'es_ipro_load_properties'
                    }, function (response) {
                        response = response  || {};

                        if(response.status === 'error'){
                            Estatik_Admin.renderNotification(response.message)
                            clear()
                        }
                        else if (response.status === 'success') {
                            currentStatus += idBufferArray.length;
                            updateProgressBar();
                            if(currentStatus >= total){
                                Estatik_Admin.renderNotification( "<div class='es-notification es-notification--success'>" + currentStatus + " properties are imported.</div>" );
                                clear()
                            }
                            else if (currentStatus < total){
                                loadBuffer();
                            }
                        }

                    }, 'json').fail( function(error) {
                        Estatik_Admin.renderNotification( "<div class='es-notification es-notification--error'>Saving error. Please, contact ipro support.</div>" );
                        clear()
                    })
                }
            }

            $( document ).on('click', '.es-btn.js-es-ipro-import', function() {
                $button = $( this );
                $button.prop( 'disabled', 'disabled' );
                $button.addClass('es-preload');

                $.post( ajaxurl, {
                    _wpnonce: $("input[name='_wpnonce']").val(),
                    step: 'init',
                    action: 'es_ipro_load_properties'
                }, function (response) {
                    response = response  || {};

                    if(response.status === 'error'){
                        Estatik_Admin.renderNotification(response.message)
                        clear()
                    }
                    else if (response.status === 'success') {
                        if(Array.isArray(response.message)){
                            idList = response.message
                            total = idList.length
                            currentStatus = 0
                            updateProgressBar()
                            loadBuffer()
                        }
                        else{
                            Estatik_Admin.renderNotification( "<div class='es-notification es-notification--error'>No Property error. Please contact IPRO support.</div>" );
                            clear()
                        }
                    }

                }, 'json').fail( function(error) {
                    Estatik_Admin.renderNotification( "<div class='es-notification es-notification--error'>Unknown Server Error. Please, contact ipro support.</div>" );
                    clear()
                })

                return false;
            } );
        } );
    } )( jQuery );
</script>