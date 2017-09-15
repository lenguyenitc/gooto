<?php

/**
* 
*/
class Advanced_Search_Property_Shortcode {
	
    protected $gapi         = '';
    protected $timestart    = '';
    protected $timeend      = '';
    protected $timestep     = '';
    protected $timeformat   = '';
    protected $timespan     = '';

	public function __construct() {

        $this->init();

		if( shortcode_exists( 'noo_advanced_search_property' ) ) {
			remove_shortcode( 'noo_advanced_search_property' );
			add_shortcode( 'noo_advanced_search_property', array( &$this, 'noo_advanced_search_property_shortcode' ) );
		}

        add_action( 'wp_enqueue_scripts', array( &$this, 'advanced_search_scripts' ), 99 );
        add_filter( 'noo_property_search_meta_query', array( &$this, 'noo_property_search_meta_query_modify' ), 99, 2 );

        

        remove_action( 'wp_ajax_loadmore_property_request', 'noo_loadmore_property_request' );
        remove_action( 'wp_ajax_nopriv_loadmore_property_request', 'noo_loadmore_property_request' );

        add_action( 'wp_ajax_loadmore_property_request', array( &$this, 'noo_loadmore_property_request' ), 99 );
        add_action( 'wp_ajax_nopriv_loadmore_property_request', array( &$this, 'noo_loadmore_property_request' ), 99 );

        add_filter( 'noo_url_page_property_search', array( &$this, 'noo_url_page_property_search' ), 99, 1 );
	}


    protected function init() {
        $this->gapi     = Google_Calender_API_Connection::get_instance();

        $this->timestart    = 0;
        $this->timeend      = 24;
        $this->timestep     = 30;
        $this->timeformat   = 12;
        $this->timespan     = timespan_array( $this->timestart, $this->timeend, $this->timestep, $this->timeformat );
    }

    public function advanced_search_scripts() {

        if( !wp_script_is( 'jquery-datetimepicker', 'enqueued' ) ) {
            wp_register_script(
                'jquery-datetimepicker',
                child_theme_url( 'assets/js/jquery.datetimepicker.full.min.js' ),
                array( 'jquery' )
            );
        }

        wp_register_script(
            'advanced_search_js',
            child_theme_url( 'assets/js/advanced_search.js' ),
            array( 'jquery', 'jquery-datetimepicker' ),
            null,
            true
        );

        wp_enqueue_script( 'advanced_search_js' );
        wp_localize_script( 
            'advanced_search_js',
            'AdvancedSearch',
            array(
                'ajaxurl'           => admin_url( 'admin-ajax.php' ),
                'loader_image_html' => '<span class="resource_booking_form_loader"><img src="' . child_theme_url( 'assets/images/loader.gif' ) . '"></span>',
                'time'              => array(
                    'timespan'  => array_keys( $this->timespan ),
                    'step'      => $this->timestep,
                ),
            ) 
        );

        wp_register_script(
            'google-map-search-property-modification',
            child_theme_url( 'assets/js/search-property-modification.js' ),
            array( 'jquery', 'jquery-datetimepicker' ),
            null,
            true
        );
    }

	public function noo_advanced_search_property_shortcode( $atts ) { 
		$list_option = array(
            'source'                => 'property',
            'show_map'              => 'yes',
            'show_controls'         => 'yes',
            'style'                 => 'style-1',
            'latitude'              => Noo_Property::get_setting( 'google_map', 'latitude', '40.714398' ),
            'longitude'             => Noo_Property::get_setting( 'google_map', 'longitude', '-74.005279' ),
            'zoom'                  => Noo_Property::get_setting( 'google_map', 'zoom', '17' ),
            'height'                => Noo_Property::get_setting( 'google_map', 'map_height', '800' ),
            'drag_map'              => 'true',
            'fitbounds'             => 'true',
            'disable_auto_complete' => Noo_Property::get_setting( 'google_map', 'disable_auto_complete', false ),
            'country_restriction'   => Noo_Property::get_setting( 'google_map', 'country_restriction', 'all' ),
            'location_type'         => Noo_Property::get_setting( 'google_map', 'location_type', 'geocode' ),
            'title'                 => esc_html__( 'Find Property', 'noo-landmark-core' ),
            'sub_title'             => '',
            'option_1'              => Noo_Property::get_setting( 'advanced_search', 'option_1', 'keyword' ),
            'option_2'              => Noo_Property::get_setting( 'advanced_search', 'option_2', 'property_status' ),
            'option_3'              => Noo_Property::get_setting( 'advanced_search', 'option_3', 'property_types' ),
            'option_4'              => Noo_Property::get_setting( 'advanced_search', 'option_4', 'city' ),
            'option_5'              => Noo_Property::get_setting( 'advanced_search', 'option_5', '_bedrooms' ),
            'option_6'              => Noo_Property::get_setting( 'advanced_search', 'option_6', '_bathrooms' ),
            'option_7'              => Noo_Property::get_setting( 'advanced_search', 'option_7', '_garages' ),
            'option_8'              => Noo_Property::get_setting( 'advanced_search', 'option_8', 'price' ),
            'show_features'         => Noo_Property::get_setting( 'advanced_search', 'show_features', 'true' ),
            'text_show_features'    => esc_html__( 'More Filters', 'noo-landmark-core' ),
            'text_button_search'    => esc_html__( 'Search Property', 'noo-landmark-core' ),
        );
        extract( shortcode_atts( $list_option, $atts ) );

        /**
         * VAR
         */
            $url_page_property_search = noo_get_url_page_search_template();
            $url_page_property_search = home_url() . '/search-results/';
			
            $class_form = '';
            if ( $show_map === 'yes' ) {
                wp_enqueue_script( 'google-map-search-property' );
                wp_enqueue_script( 'google-map-search-property-modification' );
                $class_form = 'noo-box-map ' . $style;
            }

        ob_start(); ?>
        <div class="noo-advanced-search-property">
            <?php
                /**
                 * Call title first word
                 */
                if ( $show_map === 'no' ) {
                    noo_title_first_word( $title, $sub_title );
                }
            ?>
            <?php
            /**
             * Check if source is property
             */
            if ( $source === 'property' ) : ?>
                <form class="noo-advanced-search-property-form <?php echo esc_attr( $class_form ) ?>" action="<?php echo esc_url( $url_page_property_search ) ?>" method="get" accept-charset="utf-8">
                    <?php
                    /**
                     * Show boxx find address if choose style 3
                     */
                    if ( $style === 'style-3' ) : ?>
                    <div class="noo-advanced-search-property-top-wrap">
                        <div class="noo-advanced-search-property-top">
                            <?php
                            /**
                             * Create box address
                             */
                            $args_address = array(
                                'name'        => 'address_map',
                                'title'       => '',
                                'type'        => 'text',
                                'placeholder' => esc_html__( 'Enter an address, town, street, or zip ...', 'noo-landmark-core' ),
                                'class'       => 'noo-address-map',
                            );
                            noo_create_element( $args_address, '' );
                            ?>
                            <button type="submit">
                                <span class="ion-search"></span>
                                <?php echo esc_html__( 'Search', 'noo-landmark-core' ); ?>
                            </button>
        
                        </div><!-- /.noo-advanced-search-property-top -->
                    </div><!-- /.noo-advanced-search-property-top-wrap -->
                    <?php endif; ?>

                    <?php
                        /**
                         * Show google map
                         */
                        if ( $show_map === 'yes' ) {
                            $id_map           = uniqid( 'id-map' );
                            $background_map   = Noo_Property::get_setting( 'google_map', 'background_map', '' );
                            $background_map   = ( !empty( $background_map ) ? noo_thumb_src_id( $background_map, 'full' ) : '' );
                            $background_style = '';
                            if( !empty( $background_map ) ) {
                                $background_style = ' background: url(' . esc_url_raw( $background_map ) . ') repeat-x scroll 0 center transparent;';
                            }
                            echo '<div
                                    class="noo-search-map"
                                    style="height: ' . esc_attr( $height ) . 'px; ' . $background_style . '"
                                    id="' . esc_attr( $id_map ) . '"
                                    data-source="property"
                                    data-id="' . esc_attr( $id_map ) . '"
                                    data-latitude="' . esc_attr( $latitude ) . '"
                                    data-longitude="' . esc_attr( $longitude ) . '"
                                    data-zoom="' . esc_attr( $zoom ) . '"
                                    data-drag-map="' . esc_attr( $drag_map ) . '"
                                    data-disable_auto_complete="' . esc_attr( $disable_auto_complete ) . '"
                                    data-country_restriction="' . esc_attr( $country_restriction ) . '"
                                    data-location_type="' . esc_attr( $location_type ) . '"
                                    data-fitbounds="' . esc_attr( $fitbounds ) . '">';

                            ?>
                                    <div class="gmap-loading"><?php echo esc_html__( 'Loading Maps', 'noo-landmark-core' ); ?>
                                        <div class="gmap-loader">
                                            <div class="rect1"></div>
                                            <div class="rect2"></div>
                                            <div class="rect3"></div>
                                            <div class="rect4"></div>
                                            <div class="rect5"></div>
                                        </div>
                                   </div>

                                </div>

                                <div class="gmap-controls-wrap <?php echo ( $show_controls === 'true' ? ' hidden' : '' ) ?>">
                                    <div class="gmap-controls">
                                        <div class="map-view">
                                            <i class="fa fa-picture-o"></i>
                                            <?php echo esc_html__( 'View', 'noo-landmark-core' ); ?>
                                            <span class="map-view-type">
                                                <span data-type="roadmap">
                                                    <?php echo esc_html__( 'Roadmap', 'noo-landmark-core' ); ?>
                                                </span>
                                                <span data-type="satellite">
                                                    <?php echo esc_html__( 'Satellite', 'noo-landmark-core' ); ?>
                                                </span>
                                                <span data-type="hybrid">
                                                    <?php echo esc_html__( 'Hybrid', 'noo-landmark-core' ); ?>
                                                </span>
                                                <span data-type="terrain">
                                                    <?php echo esc_html__( 'Terrain', 'noo-landmark-core' ); ?>
                                                </span>
                                            </span>
                                        </div>
                                        <div class="my-location" id="<?php echo esc_attr( uniqid( 'my-location-' ) ) ?>">
                                            <i class="fa fa-map-marker"></i>
                                            <?php echo esc_html__( 'My Location', 'noo-landmark-core' ); ?>
                                        </div>
                                        <div class="gmap-full">
                                            <i class="fa fa-expand"></i> 
                                            <?php echo esc_html__( 'Fullscreen', 'noo-landmark-core' ); ?>
                                        </div>
                                        <div class="gmap-prev">
                                            <i class="fa fa-chevron-left"></i> 
                                            <?php echo esc_html__( 'Prev', 'noo-landmark-core' ); ?>
                                        </div>
                                        <div class="gmap-next">
                                            <?php echo esc_html__( 'Next', 'noo-landmark-core' ); ?> 
                                            <i class="fa fa-chevron-right"></i>
                                        </div>
                                    </div>
                                    <div class="gmap-zoom">
                                        <span class="zoom-in" id="<?php echo esc_attr( uniqid( 'zoom-in-' ) ) ?>">
                                            <i class="fa fa-plus"></i>
                                        </span>
                                        <span class="miniature" id="<?php echo esc_attr( uniqid( 'miniature-' ) ) ?>">
                                            <i class="fa fa-minus"></i>
                                        </span>
                                    </div>
                                    <div class="box-search-map">
                                        <input type="text" id="gmap_search_input" name="find-address-map" placeholder="<?php echo esc_html__( 'Google Maps Search', 'noo-landmark-core' ); ?>"  autocomplete="off" />
                                    </div>
                                </div>
                                <?php
                        }
                    ?>

                    <?php
                    /**
                     * If show style 3 then hide form
                     */
                    if ( $style !== 'style-3' ) : ?>
                    <div class="noo-advanced-search-property-wrap">
                        <div class="noo-action-search-top">
                            <?php
                                if ( $show_map === 'yes' ) {
                                    echo '<div class="noo-label-form">' . esc_html( $title ) . '</div>';
                                }
                            ?>
                            <button type="submit" class="show-filter-property">
                                <?php echo wp_kses( __( 'We found <b>0</b> results. Do you want to load the results now?', 'noo-landmark-core' ), noo_allowed_html() ); ?>
                            </button>
                        </div>
                        <div class="noo-row noo-box-field">
                        	<?php
                        	$date = array(
							    'name' => 'google_calender_start_date',
							    'type' => 'text',
							    'required' => 1,
							    // 'hide' => ,
							    // 'title' => '',
							    'class' => 'noo-md-3 datepicker',
							    'placeholder' => 'Date',
							);

							noo_create_element( $date );

							/*$start = array(
							    'name' => 'google_calender_start_time',
							    'type' => 'text',
							    'required' => 1,
							    // 'hide' => ,
							    // 'title' => ,
							    'class' => 'noo-md-3 timepicker',
							    'placeholder' => 'Start time'
							);

							noo_create_element( $start );

							$end = array(
							    'name' => 'google_calender_end_time',
							    'type' => 'text',
							    'required' => 1,
							    // 'hide' => ,
							    // 'title' => ,
							    'class' => 'noo-md-3 timepicker',
							    'placeholder' => 'End time',
							);

							noo_create_element( $end );*/

							/*$date = array(
							    'name' => 'google_calender_end_date',
							    'type' => 'text',
							    'required' => 1,
							    // 'hide' => ,
							    // 'title' => '',
							    'class' => 'noo-md-2 datepicker',
							    'placeholder' => 'End Date',
							);

							noo_create_element( $date );*/

                            $start = array(
                                'name' => 'google_calender_start_time',
                                'type' => 'select',
                                'required' => 1,
                                // 'hide' => ,
                                // 'title' => ,
                                'std' => '',
                                'class' => 'noo-md-3 timepicker',
                                'placeholder' => __( 'Start time', 'noo-landmark' ),
                                'options'          => $this->timespan,
								'default' => '09:00',
                                'show_none_option' => true
                            );

                            noo_create_element( $start );

                            $end = array(
                                'name' => 'google_calender_end_time',
                                'type' => 'select',
                                'required' => 1,
                                // 'hide' => ,
                                // 'title' => ,
                                'std' => '',
                                'class' => 'noo-md-3 timepicker',
                                'placeholder' => __( 'End time', 'noo-landmark' ),
                                'options'          => $this->timespan,
								'default' => '10:00',
                                'show_none_option' => true
                            );

                            noo_create_element( $end );

							$timezones = array();
							foreach( DateTimeZone::listIdentifiers() as $value ) {
								$timezones[$value] = $value;
							}

                            $bookings_setting   = get_option( 'bookings_setting' );
                            $google_calender    = isset( $bookings_setting['google_calender'] ) ? $bookings_setting['google_calender'] : '';
                            $default_timezone   = isset( $google_calender['default_timezone'] ) ? $google_calender['default_timezone'] : '';

							$timezone = array(
							    'name' => 'google_calender_timezone',
							    'type' => 'select',
							    'required' => 1,
							    // 'hide' => ,
							    // 'title' => ,
                                'std' => $default_timezone,
							    'class' => 'noo-md-3',
							    'placeholder' => __( 'Timezone', 'noo-landmark' ),
							    'options'          => $timezones,
								'show_none_option' => true
							);

							noo_create_element( $timezone );
                        	?>
                        </div>
                        <div class="noo-row noo-box-field">
                            <?php
                                /**
                                 * Process option
                                 */
                                noo_advanced_search_fields( $option_1 );
                                noo_advanced_search_fields( $option_2 );
                                noo_advanced_search_fields( $option_3 );
                                noo_advanced_search_fields( $option_4 );
                                noo_advanced_search_fields( $option_5 );
                                noo_advanced_search_fields( $option_6 );
                                noo_advanced_search_fields( $option_7 );
                                noo_advanced_search_fields( $option_8 );
                            ?>
                            <?php
                            /**
                             * Check if show feature
                             */
                            if ( empty( $show_features ) || $show_features !== 'true' ) :
                            ?>
                            <div class="noo-md-3 noo-box-button">
                                <button type="submit" class="noo-button">
                                    <span class="ion-search"></span>
                                    <?php echo esc_html( $text_button_search ) ?>
                                </button>
                            </div>
                            <?php endif; ?>
                        </div><!-- /.noo-box-field -->
                        
                        <?php
                        /**
                         * Check if show feature
                         */
                        if ( !empty( $show_features ) && $show_features === 'true' ) :
                        ?>
                        <div class="noo-row noo-box-action">
                            
                            <div class="noo-md-9 box-show-features">
                                <?php
                                    $id_features = uniqid( 'box-features' );
                                    echo '<span class="show-features">' . esc_html( $text_show_features ) . '</span>';
                                ?>
                            </div>
                            <div class="noo-md-3 noo-box-button">
                                <button type="submit" class="noo-button">
                                    <span class="ion-search"></span>
                                    <?php echo esc_html( $text_button_search ) ?>
                                </button>
                            </div>

                        </div>
                        <?php endif; ?>
                        <?php
                            /**
                             * Check if show feature
                             */
                            if ( !empty( $show_features ) && $show_features === 'true' ) {
                                echo '<div class="noo-row noo-box-features">';
                                    echo '<div id="' . esc_attr( $id_features ) . '" class="noo-md-12 noo-box-features-content">';
                                        $args_property_featured = array(
                                            'name'        => 'property_featured',
                                            'title'       => '',
                                            'type'        => 'property_featured',
                                            'class'       => '',
                                            'class_child' => 'noo-md-3',
                                        );
                                        noo_create_element( $args_property_featured, '' );
                                    echo '</div>';

                                echo '</div>';
                            }
                        ?>

                    </div><!-- /.noo-advanced-search-property-wrap -->
                    <?php endif; ?>
                </form>
            <?php
            /**
             * Check if source is idx
             */
            else : ?>
                <?php
                /**
                 * noo_advanced_search_idx_form hooked
                 */
                do_action( 'noo_advanced_search_idx_form', array_merge( $list_option, array( 'class_form' => $class_form ) ), $atts );
                ?>
            <?php
            /**
             * Check if source is property
             */
            endif; ?>
            
            <?php
            /**
             * Show results
             */
            if ( $show_map === 'yes' ) {
                echo '<div class="noo-results-property"></div>';
            }
            ?>
        </div><!-- /.noo-advanced-search-property -->
        <?php $html = ob_get_contents();
        ob_end_clean();
        return $html;
	}

    public function noo_property_search_meta_query_modify( $meta_query, $REQUEST ) {

        //seating
        foreach( $meta_query as $index => $meta_field ) {
            if( $meta_field["key"] == "noo_property_seating" ) {
                $meta_query[$index]['value']    = absint( $meta_query[$index]['value'] );
                $meta_query[$index]['compare']  = ">=";
                $meta_query[$index]['type']     = "numeric";
            }
        }

        //google resouces
        $start_date     = isset( $REQUEST['google_calender_start_date'] ) ? sanitize_text_field( $REQUEST['google_calender_start_date'] ) : '';
        $end_date       = isset( $REQUEST['google_calender_start_date'] ) ? sanitize_text_field( $REQUEST['google_calender_start_date'] ) : '';
        // $end_date        = isset( $REQUEST['google_calender_end_date'] ) ? sanitize_text_field( $REQUEST['google_calender_end_date'] ) : '';
        $start_time     = isset( $REQUEST['google_calender_start_time'] ) ? sanitize_text_field( $REQUEST['google_calender_start_time'] ) : '';
        $end_time       = isset( $REQUEST['google_calender_end_time'] ) ? sanitize_text_field( $REQUEST['google_calender_end_time'] ) : '';
        $timezone       = isset( $REQUEST['google_calender_timezone'] ) ? sanitize_text_field( $REQUEST['google_calender_timezone'] ) : '';

        if( $timezone ) {
            $timezone = new DateTimeZone( $timezone );
        } else {
            $timezone = new DateTimeZone( 'UTC' );
        }

        if( $start_date ) {
            $start = new DateTime( $start_date, $timezone );
        } else {
            $start = new DateTime( 'now', $timezone );
        }

        if( $end_date ) {
            $end = new DateTime( $end_date, $timezone );
        } else {
            $end = new DateTime( 'now', $timezone );
        }

        if( $start_time ) {
            $hour   = date( 'H', strtotime( $start_time ) );
            $minute = date( 'i', strtotime( $start_time ) );
            $second = date( 's', strtotime( $start_time ) );
            $start->setTime( $hour, $minute, $second );
        }

        if( $end_time ) {
            $hour   = date( 'H', strtotime( $end_time ) );
            $minute = date( 'i', strtotime( $end_time ) );
            $second = date( 's', strtotime( $end_time ) );
            $end->setTime( $hour, $minute, $second );
        }

        $booked_resources = $this->get_booked_resources( $start->format( 'c' ), $end->format( 'c' ), $timezone );

        $meta_query[] = array(
            'key'       => '_google_calender_resource_id',
            'value'     => $booked_resources,
            'compare'   => 'NOT IN'
        );

        return $meta_query;
    }

    public function get_booked_resources( $timeMin, $timeMax, $timeZone ) {
        $service_directory  = $this->gapi->Google_Service_Directory();
        $results            = $service_directory->resources_calendars->listResourcesCalendars( 'my_customer' );
        $resources  = $results->getItems();

        $booked_resources   = array();
        try {
            foreach( $resources as $resource ) {
                $calendarId = $resource->resourceEmail;
                
                $items       = new Google_Service_Calendar_FreeBusyRequestItem();
                $items->setId( $calendarId );

                $postBody   = new Google_Service_Calendar_FreeBusyRequest();
                $postBody->setTimeMin( $timeMin );
                $postBody->setTimeMax( $timeMax );
                $postBody->setTimeZone( $timeZone );
                $postBody->setItems( array( $items ) );

                $optParams          = array();

                $service_calendar   = $this->gapi->Google_Service_Calendar();
                $freebusy           = $service_calendar->freebusy->query( $postBody, $optParams );

                if( !empty( $freebusy->getCalendars()[$calendarId]->getBusy() ) ) {
                    $booked_resources[] = $resource->resourceId;
                }
            }
        } catch( Exception $e ) {
            // print_r($e->getMessage());
        }

        return $booked_resources;
    }


    public function noo_loadmore_property_request() {

        /**
         * Check security
         */
            check_ajax_referer( 'google-map-search-property', 'security', esc_html__( 'Security Breach! Please contact admin!', 'noo-landmark-core' ) );

        /**
         * Process
         */
            unset( $_POST['security'] );
            unset( $_POST['action'] );

            $current_page = ( !empty( $_POST['current_page'] ) && $_POST['current_page'] !== 'NaN' ) ? absint( $_POST['current_page'] ) : 1;

            $args = array(
                'post_status'    => 'publish',
                'post_type'      => 'noo_property',
                'paged'          => $current_page,
				'orderby' => 'meta_value',
				'order' => 'ASC',
            );

            if( function_exists( 'pll_current_language' ) ) {
                $args['lang'] = pll_current_language();
            }

            $meta_query = array( 'relation' => 'AND' );
			if(empty($value)){
				$meta_query[] = array(
					'key'       => 'noo_property_seating',
					'value'     => 0,
					'compare'   => ">=",
					'type'      => "numeric",
				);
			}
            foreach( $_POST as $key => $value ) {
                if( !empty( $value ) && strpos( $key, "noo_property" ) !== false ) {
                    if( $key == "noo_property_seating" ) {
                        $meta_query[] = array(
                            'key'       => $key,
                            'value'     => absint( $value ),
                            'compare'   => ">=",
                            'type'      => "numeric",
                        );
                    } else {
                        $meta_query[] = array(
                            'key'   => $key,
                            'value' => $value,
                        );
                    }
                }
            }

            //google resouces
            $start_date     = isset( $_POST['google_calender_start_date'] ) ? sanitize_text_field( $_POST['google_calender_start_date'] ) : '';
            $end_date       = isset( $_POST['google_calender_start_date'] ) ? sanitize_text_field( $_POST['google_calender_start_date'] ) : '';
            // $end_date        = isset( $_POST['google_calender_end_date'] ) ? sanitize_text_field( $_POST['google_calender_end_date'] ) : '';
            $start_time     = isset( $_POST['google_calender_start_time'] ) ? sanitize_text_field( $_POST['google_calender_start_time'] ) : '';
            $end_time       = isset( $_POST['google_calender_end_time'] ) ? sanitize_text_field( $_POST['google_calender_end_time'] ) : '';
            $timezone       = isset( $_POST['google_calender_timezone'] ) ? sanitize_text_field( $_POST['google_calender_timezone'] ) : '';

            if( $timezone ) {
                $timezone = new DateTimeZone( $timezone );
            } else {
                $timezone = new DateTimeZone( 'UTC' );
            }

            if( $start_date ) {
                $start = new DateTime( $start_date, $timezone );
            } else {
                $start = new DateTime( 'now', $timezone );
            }

            if( $end_date ) {
                $end = new DateTime( $end_date, $timezone );
            } else {
                $end = new DateTime( 'now', $timezone );
            }

            if( $start_time ) {
                $hour   = date( 'H', strtotime( $start_time ) );
                $minute = date( 'i', strtotime( $start_time ) );
                $second = date( 's', strtotime( $start_time ) );
                $start->setTime( $hour, $minute, $second );
            }

            if( $end_time ) {
                $hour   = date( 'H', strtotime( $end_time ) );
                $minute = date( 'i', strtotime( $end_time ) );
                $second = date( 's', strtotime( $end_time ) );
                $end->setTime( $hour, $minute, $second );
            }

            $booked_resources = $this->get_booked_resources( $start->format( 'c' ), $end->format( 'c' ), $timezone );

            $meta_query[] = array(
                'key'       => '_google_calender_resource_id',
                'value'     => $booked_resources,
                'compare'   => 'NOT IN'
            );

            $args['meta_query'] = $meta_query;

            $r = new WP_Query( apply_filters( 'noo_loadmore_property_request', $args ) );

            if ( isset($_POST['results']) && $_POST['results'] === 'load_more' ) :

                $ajax_only_item = true;

            else :

                if ( $r->found_posts <= 0 ) :

                    wp_die( '<div class="no_results">' . esc_html__( 'We found no results', 'noo-landmark-core' ) . '</div>' );

                endif;

            endif;

            $display_style      = get_theme_mod( 'noo_property_listing_style', 'style-list' );
            $class_grid         = '';
            if ( $display_style === 'style-grid' ) {
                $class_grid    = 'style-grid column';
                $class_column = 'noo-md-6';
            }

            $style_show_ajax    = isset( $_POST['style'] ) ? noo_validate_data( $_POST['style'] ) : '';
            $show_loadmore_ajax = isset( $_POST['loadmore'] ) ? noo_validate_data( $_POST['loadmore'] ) : true;

            $user_id            = noo_get_current_user(true);

            $show_social        = get_theme_mod( 'noo_property_social', true);
            $show_favories      = get_theme_mod( 'noo_property_favories', true);
            $show_compare       = get_theme_mod( 'noo_property_compare', true);

            $is_favorites       = get_user_meta( $user_id, 'is_favorites', true );

            if ( empty( $style_show_ajax  ) )  echo '<div class="noo-list-property ' . esc_attr( $class_grid ) . '">';
			 $display_style = 'style-half-map';
			 $class_column  = 'noo-md-6';
            if ( $r->have_posts() ) {
                if ( !empty( $style_show_ajax ) && $style_show_ajax === 'style-2' ) {
                    $display_style = 'style-half-map';
                    $class_column  = 'noo-md-6';
                    $keyword       = isset( $_POST['keyword'] ) ? noo_validate_data( $_POST['keyword'] ) : '';
                    $types         = isset( $_POST['types'] ) ? noo_validate_data( $_POST['types'] ) : '';
                    $status        = isset( $_POST['status'] ) ? noo_validate_data( $_POST['status'] ) : '';
                    $location      = isset( $_POST['location'] ) ? noo_validate_data( $_POST['location'] ) : '';
                    $orderby       = isset( $_POST['orderby'] ) ? noo_validate_data( $_POST['orderby'] ) : '';
                    ?><div class="noo-list-property-action">
                        <div class="noo-count">
                            <?php echo sprintf( esc_html__( '%s listings found', 'noo-landmark-core' ), $r->found_posts ) ?>
                        </div>
                        <form class="sort-property">
                            <?php echo esc_html__( 'Sort By:', 'noo-landmark-core' ); ?>
                            <!--<select name="orderby">
                                <option value="date"<?php selected( $orderby, 'date', true ); ?>>
                                    <?php echo esc_html__( 'Date', 'noo-landmark-core' ); ?>
                                </option>
                                <option value="price"<?php selected( $orderby, 'price', true ); ?>>
                                    <?php echo esc_html__( 'Price', 'noo-landmark-core' ); ?>
                                </option>
                                <option value="bath"<?php selected( $orderby, 'bath', true ); ?>>
                                    <?php echo esc_html__( 'Bath', 'noo-landmark-core' ); ?>
                                </option>
                                <option value="bed"<?php selected( $orderby, 'bed', true ); ?>>
                                    <?php echo esc_html__( 'Bed', 'noo-landmark-core' ); ?>
                                </option>
                                <option value="area"<?php selected( $orderby, 'area', true ); ?>>
                                    <?php echo esc_html__( 'Area', 'noo-landmark-core' ); ?>
                                </option>
                                <option value="featured"<?php selected( $orderby, 'featured', true ); ?>>
                                    <?php echo esc_html__( 'Featured', 'noo-landmark-core' ); ?>
                                </option>
                                <option value="name"<?php selected( $orderby, 'name', true ); ?>>
                                    <?php echo esc_html__( 'Name', 'noo-landmark-core' ); ?>
                                </option>
                            </select>-->
							<select name="orderby">
                                <option value="capacity"<?php selected( $orderby, 'capacity', true ); ?>>
                                    <?php echo esc_html__( 'Capacity', 'noo-landmark-core' ); ?>
                                </option>
                                <option value="privacy"<?php selected( $orderby, 'privacy', true ); ?>>
                                    <?php echo esc_html__( 'Privacy', 'noo-landmark-core' ); ?>
                                </option>
                                <option value="noise"<?php selected( $orderby, 'noise', true ); ?>>
                                    <?php echo esc_html__( 'Noise', 'noo-landmark-core' ); ?>
                                </option>
                            </select>
                            <input type="hidden" name="keyword" value="<?php echo esc_attr( $keyword ) ?>" />
                            <input type="hidden" name="types" value="<?php echo esc_attr( $types ) ?>" />
                            <input type="hidden" name="status" value="<?php echo esc_attr( $status ) ?>" />
                            <input type="hidden" name="location" value="<?php echo esc_attr( $location ) ?>" />
                            <input type="hidden" name="style" value="<?php echo esc_attr( $style ) ?>" />
                        </form>
                    </div><?php
                } elseif ( $current_page === 1 ) {
                    ?>
                    <div class="noo-title-header noo-md-12">
                        <?php
                        /**
                         * Render title
                         */
                        noo_title_first_word( 'Your search results', sprintf( esc_html__( '%s listings found', 'noo-landmark-core' ), $r->found_posts ) );
                        ?>
                    </div>
                    <?php
                }
                while ( $r->have_posts() ) : $r->the_post();
                    $property_id        = get_the_ID();
                    $check_is_favorites = ( !empty( $is_favorites ) && in_array( $property_id, $is_favorites ) ) ? true : false;
                    $class_favorites    = $check_is_favorites ? 'is_favorites' : 'add_favorites';
                    $text_favorites     = $check_is_favorites ? esc_html__( 'View favorites', 'noo-landmark-core' ) : esc_html__( 'Add to favorites', 'noo-landmark-core' );
                    $icon_favorites     = $check_is_favorites ? 'fa-heart' : 'fa-heart-o';
                    require noo_get_template( 'property/item-property' );

                endwhile;

                if ( !empty( $show_loadmore_ajax ) ) {
                    $max_num_pages = intval( $r->max_num_pages );
                    if ( $max_num_pages > 1 && $current_page < $max_num_pages ) {
                        echo '<div class="loadmore-results-wrap">';
                            echo '<span class="loadmore-results noo-button" data-current-page="' . $current_page . '" data-max-page="' . $max_num_pages . '">' . esc_html__( 'Load more', 'noo-landmark-core' ) . '</span>';
                        echo '</div><!-- /.loadmore-results-wrap -->';
                    }
                } else {
                    noo_pagination_loop( array(), $r );
                }
                wp_reset_postdata();
            } else {
                echo '<div class="noo-found">' . esc_html__( 'Nothing Found!', 'noo-landmark-core' ) . '</div>';
            }
            if ( empty( $style_show_ajax  ) ) echo '</div><!-- /.noo-list-property -->';

            wp_die();

    }

    public function noo_url_page_property_search( $url_page_property_search ) {

        $id_page_search_template    = noo_get_page_by_template( 'property-search-modified.php' );
        $url_page_property_search   = !empty( $id_page_search_template ) ? get_permalink( absint( $id_page_search_template ) ) : get_post_type_archive_link( 'noo_property' );

        return $url_page_property_search;
    }
}

new Advanced_Search_Property_Shortcode();

?>