<?php
/**
 * Plugin Name: Scheduler for Elementor
 * Description: Adds a scheduling control to elementor blocks to time its visibility
 * Plugin URI: https://www.wpcraft.de/scheduler-for-elementor
 * Author: WPCraft
 * Version: 1.0.0
 * Author URI: https://www.wpcraft.de
 * Text Domain: scheduler-elementor
 * Domain Path: /languages
 *
 * Scheduler for Elementor is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Scheduler for Elementor is distributed WITHOUT ANY WARRANTY;
 * See GNU General Public License for more details <http://www.gnu.org/licenses/>.
 */

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Element_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

class Scheduler_For_Elementor {
	/**
	 * Scheduler_For_Elementor constructor.
	 */
	public function __construct() {
		add_action( 'elementor/element/before_section_start', [ $this, 'scheduler_register_controls' ], 10, 3 );

		add_action( 'elementor/frontend/widget/before_render', [ $this, 'scheduler_before_render' ] );
		add_action( 'elementor/frontend/section/before_render', [ $this, 'scheduler_before_render' ] );
		add_action( 'elementor/frontend/column/before_render', [ $this, 'scheduler_before_render' ] );

		add_action( 'elementor/frontend/widget/after_render', [ $this, 'scheduler_after_render' ] );
		add_action( 'elementor/frontend/section/after_render', [ $this, 'scheduler_after_render' ] );
		add_action( 'elementor/frontend/column/after_render', [ $this, 'scheduler_after_render' ] );
	}

	/**
	 * Register Scheduler Control Section on Advanced Tab
	 *
	 * @param Controls_Stack $element
	 * @param string $section_id id of the current section
	 * @param array $args section arguments
	 */
	function scheduler_register_controls( Controls_Stack $element, string $section_id, array $args ) {
		if ( '_section_style' === $section_id || 'section_advanced' === $section_id ) {
			$element->start_controls_section(
				'scheduler-elementor',
				[
					'tab'   => Controls_Manager::TAB_ADVANCED,
					'label' => __( 'Scheduler', 'scheduler-elementor' )
				]
			);

			$element->add_control(
				'schedule_visibility_start',
				[
					'label'        => __( 'Schedule Start', 'scheduler-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => 'On',
					'label_off'    => 'Off',
					'return_value' => 'yes',
					'separator'    => 'none',
				]
			);

			$element->add_control(
				'schedule_visibility_start_date',
				[
					'type'       => Controls_Manager::DATE_TIME,
					'label'      => __( 'Date and Time', 'scheduler-elementor' ),
					'show_label' => false,
					'default'    => current_time( 'mysql' ),
					'condition'  => [
						'schedule_visibility_start!' => '',
					],
					'separator'  => 'none',
				]
			);

			$element->add_control(
				'schedule_visibility_end',
				[
					'label'        => __( 'Schedule End', 'scheduler-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'default'      => '',
					'label_on'     => 'On',
					'label_off'    => 'Off',
					'return_value' => 'yes',
					'separator'    => 'none',
				]
			);

			$element->add_control(
				'schedule_visibility_end_date',
				[
					'type'       => Controls_Manager::DATE_TIME,
					'label'      => __( 'Date and Time', 'scheduler-elementor' ),
					'show_label' => false,
					'default'    => current_time( 'mysql' ),
					'condition'  => [
						'schedule_visibility_end!' => '',
					],
					'separator'  => 'none',
				]
			);
			$element->end_controls_section();
		}
	}

	/**
	 * Before rendering the element, check if it is visible now
	 *
	 * @param Element_Base $element
	 */
	function scheduler_before_render( Element_Base $element ) {
		if ( ! $this->scheduler_is_element_visible( $element ) ) {
			ob_start();
		}
	}

	/**
	 * After rendering the element, check if it is visible now
	 *
	 * @param Element_Base $element
	 */
	function scheduler_after_render( Element_Base $element ) {
		if ( ! $this->scheduler_is_element_visible( $element ) ) {
			ob_end_clean();
		}
	}

	/**
	 * Check if element is visible now
	 *
	 * @param Element_Base $element the current element
	 *
	 * @return bool visibility of the current element
	 */
	function scheduler_is_element_visible( Element_Base $element ): bool {
		if ( 'yes' !== $element->get_settings( 'schedule_visibility_start' ) && 'yes' !== $element->get_settings( 'schedule_visibility_end' ) ) {
			return true;
		}

		$start = 'yes' === $element->get_settings( 'schedule_visibility_start' ) ? $element->get_settings( 'schedule_visibility_start_date' ) : '';
		$end   = 'yes' === $element->get_settings( 'schedule_visibility_end' ) ? $element->get_settings( 'schedule_visibility_end_date' ) : '';

		$now = current_time( 'timestamp' );

		$start_date = false;
		$end_date   = false;

		if ( $start ) {
			$start_date = strtotime( $start );
		}
		if ( $end ) {
			$end_date = strtotime( $end );
		}


		if ( $start_date && $end_date ) {
			return ( $now >= $start_date && $now <= $end_date );
		}

		if ( $start_date && ! $end_date ) {
			return $now >= $start_date;
		}

		if ( ! $start_date && $end_date ) {
			return $now <= $end_date;
		}

		return false;
	}
}

add_action( 'elementor/init', function () {
	new Scheduler_For_Elementor();
} );

