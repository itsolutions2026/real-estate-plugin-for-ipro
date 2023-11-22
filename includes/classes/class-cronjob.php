<?php

/**
 * Class Es_Cron_Job.
 */
class Es_Cron_Job
{

    private $endpoint;
    private $clientId;
    private $clientSecret;
    private $accessToken;
    private $cronEnabled;

    private $endpointRouters = array(
        'lastUpdated' => array(
            'endpoint' => '/apis/properties/lastupdated?lastUpdated=time',
        ),
        'property' => array(
            'endpoint' => '/apis/property/propertyId',
        ),
        'properties' => array (
            'endpoint' => '/apis/properties'
        )
    );

    /**
     * Main constructor
     *
     * @since 1.0
     */
    public function __construct()
    {
        //Get the Settings


    }

    /**
     * Init divi functionality.
     *
     * @return void
     */
    public function init()
    {
        add_action('init', array($this, 'schedule_ipro'));
        add_filter('cron_schedules', array($this, 'add_wp_cron_schedule'));
        add_action('ipro_cron_scheduler', array($this, 'ipro_cron_scheduler'));
        add_action('ipro_cron_scheduler_for_images_queue', array($this, 'ipro_cron_scheduler_for_images_queue'));
        add_action('wp_ajax_es_ipro_load_properties', array( $this, 'ajax_load_properties' ) );
        add_action('es_settings_save', array( $this, 'validate_oauth_token' ), 10 ,5);
    }

    /**
     * Register Ipro WP cron Job
     */
    public function schedule_ipro()
    {
        if (!wp_next_scheduled('ipro_cron_scheduler')) {
            wp_schedule_event(time(), 'ipro_timeInterval', 'ipro_cron_scheduler');
        }
        if (!wp_next_scheduled('ipro_cron_scheduler_for_images_queue')) {
            wp_schedule_event(time(), 'ipro_timeInterval_for_images', 'ipro_cron_scheduler_for_images_queue');
        }
    }

    /**
     * Define Ipro Cron Job Time interval
     * @param $schedules
     * @return mixed
     */
    public function add_wp_cron_schedule($schedules)
    {

        $schedules['ipro_timeInterval'] = array(
            'interval' => 60, // in seconds
            'display' => __('Ipro Time Interval', 'ipro'),
        );
        $schedules['ipro_timeInterval_for_images'] = array(
            'interval' => 60, // in seconds
            'display' => __('Ipro Time Interval For Images', 'ipro'),
        );
        return $schedules;
    }

    public function initialCredential(){
        $settings = new Es_Settings_Container();
        $settings->load_options();
        $this->endpoint = $settings->__get('ipro_api_endpoint');
        $this->clientId = $settings->__get('ipro_client_id');
        $this->clientSecret = $settings->__get('ipro_client_secret');
        $this->cronEnabled = $settings->__get('is_rest_cron_enabled');
        $this->accessToken = get_transient('ipro_access_token');
        if (empty($this->accessToken)) $this->accessToken = $this->get_accessToken();
    }

    public function validate_oauth_token($name, $next, $prev, $data, $settingContainer){
        if($name === 'ipro_api_endpoint'){
            if(
                $settingContainer::$options['ipro_api_endpoint'] !== $data['ipro_api_endpoint'] ||
                $settingContainer::$options['ipro_client_id'] !== $data['ipro_client_id'] ||
                $settingContainer::$options['ipro_client_secret'] !== $data['ipro_client_secret']
            ){
                $this->endpoint = $data['ipro_api_endpoint'] ;
                $this->clientId = $data['ipro_client_id'] ;
                $this->clientSecret = $data['ipro_client_secret'];
                $this->get_accessToken();
            }
        }
    }

    /**
     *  WP-Cron Job Hook controller
     */

    public function ipro_cron_scheduler()
    {
        $this->initialCredential();
        //Get the Settings
        if(!$this->cronEnabled) return false;

        if (empty($this->accessToken)) return false;

        // Get the recent updated property list

        $recentUpdatedProperties = $this->fetchRecentUpdates();
        if(!empty($recentUpdatedProperties) && is_array($recentUpdatedProperties)){
            $propertyIDs= array_column($recentUpdatedProperties,'PropertyId');
            $this->setupPropertyDetail($propertyIDs);
        }
    }


    /**
     * WP -Cron JOb Hook for Images in queue
     */

    public function ipro_cron_scheduler_for_images_queue(){
        $this->initialCredential();
        $queue_process_limit = 25;
        //Get the Settings


        if(!$this->cronEnabled) return false;
        if (empty($this->accessToken)) return false;
        $properties = es_search_properties_by_meta('1', 'queued_image_urls_status');

        $image_Urls = array();
        foreach ($properties as $property){
            $propertyId = $property->ID;
            if($queue_process_limit < 1) break;
            if(!empty($propertyId)){
                $property_images = get_post_meta($propertyId, 'queued_image_urls', true);
                if(!empty($property_images) && is_array($property_images)){
                    $length = count($property_images);

                    if($length > $queue_process_limit) {
                        $new_property_images = array_slice($property_images, $queue_process_limit);
                        update_post_meta($propertyId, 'queued_image_urls', $new_property_images);
                        $image_Urls[$propertyId] = array_slice($property_images, 0, $queue_process_limit);
                        $queue_process_limit = 0;
                    } else{
                        update_post_meta($propertyId, 'queued_image_urls_status', false);
                        update_post_meta($propertyId, 'queued_image_urls', array());
                        $image_Urls[$propertyId] = $property_images;
                        $queue_process_limit = $queue_process_limit - $length;
                    }

                }
            }
        }

        foreach($image_Urls as $propertyId => $images){
            $property = es_get_property( $propertyId );
            $property->save_field_value( 'gallery',  $images, true);
        }

    }

    /**
     * API Wrapper
     */
    private function apiWrapper( $route, $params, $method = 'GET'){

        if(empty($route)) return false;

        $full_endpoint = $this->endpoint . $this->endpointRouters[$route]['endpoint'];

        if(is_array($params)){
            foreach($params as $key => $param){
                $full_endpoint = str_replace($key, $param, $full_endpoint);
            }
        }

        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->accessToken,
            ),
            'timeout' => 25
        );

        if($method === 'GET'){
            $response = wp_remote_get(
                $full_endpoint,
                $args
            );
        }
        else{
            $response = wp_remote_post(
                $full_endpoint,
                $args
            );
        }

        // Response body.
        $body = wp_remote_retrieve_body($response);
        return  json_decode($body, true);
    }

    private function get_accessToken()
    {

        $args = array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode($this->clientId . ':' . $this->clientSecret),
                'Pragma' => ' no-cache',
                'Cache-Control' => ' no-store,no-cache',
                'Content-Type' => ' application/x-www-form-urlencoded; charset=utf-8',
            ),
            'body' => array(
                'grant_type' => 'client_credentials'
            )
        );

        $response = wp_remote_post(
            $this->endpoint . '/oauth/2.0/token',
            $args
        );

        // Response body.
        $body = wp_remote_retrieve_body($response);

        $parseData = (!is_wp_error($response)) ? json_decode($body, true) : null;

        // Set the access token
        if (!empty($parseData['access_token'])) {
            set_transient('ipro_access_token', $parseData['access_token'], $parseData['expires_in']);
            return $parseData['access_token'];
        }
        else {
            set_transient('ipro_access_token', null, 1000);
            return false;
        }
    }

    private function fetchRecentUpdates()
    {
        $currentTime = time();
        $lastUpdatedTime = get_option('ipro_last_updated_time');

        if(!empty($lastUpdatedTime)){
            return $this->apiWrapper('lastUpdated', array(
                'time' => round(($currentTime - $lastUpdatedTime) / 60) + 1
            ));
        }
        update_option('ipro_last_updated_time', $currentTime);
        return null;
    }

    private function fetchAllProperties(){

        $properties = $this->apiWrapper('properties', array());

        if(isset($properties['Success']) && $properties['Success'] === false && isset($properties['Message'])) {
            $response = array(
                'status' => 'error',
                'message' => es_get_notification_markup(
                    __( $properties['Message'], 'es' ),
                    'error'
                ),
            );
        }
        else if (is_array($properties) && count($properties) > 0) {
            $propertyIDs = array_column($properties, 'Id');

            if(count($propertyIDs) > 0) {
                $response = array(
                    'status' => 'success',
                    'message' => $propertyIDs,
                );
            }
            else{
                $response = array(
                    'status' => 'error',
                    'message' => es_get_notification_markup(
                        __( 'No Property error. Please contact IPRO support.', 'es' ),
                        'error'
                    ),
                );
            }
        }
        else{
            $response = array(
                'status' => 'error',
                'message' => es_get_notification_markup(
                    __( "Did you configure Ipro API Endpoint exactly? Please contact support IPRO team.", 'es' ),
                    'error'
                ),
            );
        }

        return $response;
    }

    public function setupPropertyDetail($propertyIDs = array ()){
        try{
            if(!is_array($propertyIDs)) throw new Exception('Invalid IDs!');


            foreach ($propertyIDs as $ID){
                $propertyDetail = $this->apiWrapper('property', array(
                    'propertyId' => $ID
                ));

                if(is_array($propertyDetail) && !empty($propertyDetail['Id'])){

                    $fields = array();

                    $fields['ipro_Id'] = $propertyDetail['Id'];

                    if(!empty($propertyDetail['GeoLocation'])){
                        $geolocation = explode(",",$propertyDetail['GeoLocation'], 2);
                        if(isset($geolocation[0]) && isset($geolocation[1])){
                            $fields['latitude'] = $geolocation[0];
                            $fields['longitude'] = $geolocation[1];
                            $fields['longitude'] = str_ireplace( array(','), '',  $geolocation[1]);
                        }
                    }
                    //Location
                    if(!empty($propertyDetail['Location']) && is_array($propertyDetail['Location']) && count($propertyDetail['Location']) > 0){
                        $fields['es_type'] = array ($propertyDetail['Location'][0] );
                        if(!empty($propertyDetail['Location'][1])) $fields['country'] = $propertyDetail['Location'][1];
                        if(!empty($propertyDetail['Location'][2])) $fields['state'] = $propertyDetail['Location'][2];
                        if(!empty($propertyDetail['Location'][3])) $fields['province'] = $propertyDetail['Location'][3];
                        if(!empty($propertyDetail['Location'][4])) $fields['city'] = $propertyDetail['Location'][4];
                    }

                    if(!empty($propertyDetail['Attributes']['Bathrooms'][0])) $fields['bathrooms'] = $propertyDetail['Attributes']['Bathrooms'][0];
                    if(!empty($propertyDetail['Attributes']['Bedrooms'][0])) $fields['bedrooms'] = $propertyDetail['Attributes']['Bedrooms'][0];
                    if(!empty($propertyDetail['Attributes']['Dogs'][0])) $fields['es_dog'] = $propertyDetail['Attributes']['Dogs'][0];
                    if(!empty($propertyDetail['Attributes']['Sleeps'][0])) $fields['sleeps'] = $propertyDetail['Attributes']['Sleeps'][0];
                    if(!empty($propertyDetail['Address'])) $fields['address'] = $propertyDetail['Address'];
                    if(!empty($propertyDetail['Postcode'])) $fields['postal_code'] = $propertyDetail['Postcode'];

                    if(!empty($propertyDetail['Attributes']['Holiday Ideas']) && is_array($propertyDetail['Attributes']['Holiday Ideas']) && count($propertyDetail['Attributes']['Holiday Ideas']))
                        $fields['es_holiday_ideas'] = $propertyDetail['Attributes']['Holiday Ideas'];
                    if(!empty($propertyDetail['Attributes']['Bedrooms Style']) && is_array($propertyDetail['Attributes']['Bedrooms Style']) && count($propertyDetail['Attributes']['Bedrooms Style']))
                        $fields['es_bedroom_style'] = $propertyDetail['Attributes']['Bedrooms Style'];

                    if(!empty($propertyDetail['Attributes']['Amenities']) && is_array($propertyDetail['Attributes']['Amenities']) && count($propertyDetail['Attributes']['Amenities']))
                        $fields['es_amenity'] = $propertyDetail['Attributes']['Amenities'];
                    if(!empty($propertyDetail['Attributes']['Theme']) && is_array($propertyDetail['Attributes']['Theme']) && count($propertyDetail['Attributes']['Theme']))
                        $fields['es_theme'] = $propertyDetail['Attributes']['Theme'];
                    if(!empty($propertyDetail['Attributes']['Entertainment']) && is_array($propertyDetail['Attributes']['Entertainment']) && count($propertyDetail['Attributes']['Entertainment']))
                        $fields['es_entertainment'] = $propertyDetail['Attributes']['Entertainment'];
                    if(!empty($propertyDetail['Attributes']['Distances']) && is_array($propertyDetail['Attributes']['Distances']) && count($propertyDetail['Attributes']['Distances']))
                        $fields['es_distance'] = $propertyDetail['Attributes']['Distances'];
                    if(!empty($propertyDetail['Attributes']['Internet']) && is_array($propertyDetail['Attributes']['Internet']) && count($propertyDetail['Attributes']['Internet']))
                        $fields['es_internet'] = $propertyDetail['Attributes']['Internet'];
                    if(!empty($propertyDetail['Attributes']['Parking']) && is_array($propertyDetail['Attributes']['Parking']) && count($propertyDetail['Attributes']['Parking']))
                        $fields['es_parking'] = $propertyDetail['Attributes']['Parking'];
                    if(!empty($propertyDetail['Attributes']['Property Type']) && is_array($propertyDetail['Attributes']['Property Type']) && count($propertyDetail['Attributes']['Property Type']))
                        $fields['es_property_type'] = $propertyDetail['Attributes']['Property Type'];
                    if(!empty($propertyDetail['Attributes']['Family']) && is_array($propertyDetail['Attributes']['Family']) && count($propertyDetail['Attributes']['Family']))
                        $fields['es_family'] = $propertyDetail['Attributes']['Family'];
                    if(!empty($propertyDetail['SEOTitle']) && is_array($propertyDetail['SEOTitle']) && count($propertyDetail['SEOTitle']) > 0){
                        $fields['keywords'] = array($propertyDetail['SEOTitle']);
                    }

                    if(!empty($propertyDetail['VirtualTour'])){
                        $fields['video']['video_iframe'] = $propertyDetail['VirtualTour'];
                    }

                    if (!empty($propertyDetail['FloorPlan'])) {
                        preg_match('@src="([^"]+)"@', $propertyDetail['FloorPlan'], $matches);

                        if (is_array($matches) && count($matches) > 1) {
                            $match = array_pop($matches);
                            $fields['floor_plans'] = array();
                            if (!preg_match("/^([a-z0-9-.]*)\.([a-z]{2,3})$/i", $match)) {
                                array_push($fields['floor_plans'], $this->endpoint . $match);
                            } else {
                                array_push($fields['floor_plans'], $match);
                            }
                        }
                    }

                    if(!empty($propertyDetail['PlotSize'])){
                        $fields['lot_size'] = $propertyDetail['PlotSize'];
                    }

                    if(!empty($propertyDetail['BuildSize'])){
                        $fields['area'] = $propertyDetail['BuildSize'];
                    }

                    $data = array(
                        'system' => array(
                            'post_title' => $propertyDetail['Title'],
                            'post_content' => $propertyDetail['Intro'] . $propertyDetail['MainDescription'],
                            'post_name' => sanitize_title( $propertyDetail['PropertyName'] ),
                        ),
                        'fields' => $fields
                    );

                    $post_id = es_search_property_by_meta($propertyDetail['Id'], 'es_property_ipro_Id');

                    if(empty($post_id)){

                        $post_data = wp_parse_args( $data['system'], array(
                            'post_type' => 'properties',
                            'post_status' => 'publish',
                        ) );

                        $post_id = wp_insert_post( $post_data, true );
                    } else{
                        wp_update_post(wp_parse_args( $data['system'], array(
                            'ID' => $post_id,
                            'post_status' => 'publish',
                        )));
                    }

                    if ( ! is_wp_error( $post_id ) ) {
                        $property = es_get_property( $post_id );
                        $property->save_fields( $data['fields'] );

                        if(is_array($propertyDetail['Images']) && count($propertyDetail['Images'])) {
                            $gallery = array_column($propertyDetail['Images'], 'Source');
                            update_post_meta($post_id, 'queued_image_urls', $gallery);
                            update_post_meta($post_id, 'queued_image_urls_status', true);
                        }
                    }
                }
            }

            return array(
                'status' => 'success',
            );
        }
        catch (Exception $exception){
            return array(
                'status' => 'error',
                'message' => es_get_notification_markup(
                    __( $exception->getMessage(), 'es' ),
                    'error'
                ),
            );
        }
    }


    public function ajax_load_properties(){
        if ( check_ajax_referer( 'es_save_settings', '_wpnonce', false ) ) {

            if ( current_user_can( 'manage_options' ) ) {
                $this->initialCredential();

                switch ($_POST['step']) {
                    case 'progress':
                        $response = $this->setupPropertyDetail($_POST['IDs']);
                        break;
                    case 'init':
                        $currentTime = time();
                        update_option('ipro_last_updated_time', $currentTime);
                        $response  = $this->fetchAllProperties();
                        break;
                    default:
                        $response = array(
                            'status' => 'error',
                            'message' => es_get_notification_markup(
                                __( "Action is not defined!", 'es' ),
                                'error'
                            ),
                        );
                }

            } else {
                $response = array(
                    'status' => 'error',
                    'message' => es_get_notification_markup(
                        __( "You don't have permissions for this action.", 'es' ),
                        'error'
                    ),
                );
            }
        } else {
            $response = array(
                'status' => 'error',
                'message' => es_get_notification_markup(
                    __( "Authentication failed! Invalid request.", 'es' ),
                    'error'
                ),
            );
        }

        wp_die( json_encode( $response ) );
    }



}

$cronJob = new Es_Cron_Job();
$cronJob->init();
