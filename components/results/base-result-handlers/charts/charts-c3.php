<?php
/**
 * Showing Charts with C3
 *
 * This class shows charts by C3 which is based on D3
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Results
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_Chart_Creator_C3 extends AF_Chart_Creator
{
	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function init()
	{
		$this->name = 'c3';
		$this->title = __( 'C3', 'af-locale' );
		$this->description = __( 'Chart creating with C3.', 'af-locale' );
	}

	/**
	 * Showing bars
	 *
	 * @param string $title Title
	 * @param array  $answers
	 * @param array  $attr
	 *
	 * @return mixed
	 */
	public function bars( $title, $results, $params = array() )
	{
		$defaults = array(
			'id'        => 'dimple' . md5( rand() ),
			'title_tag' => 'h3',
		);

		$params = wp_parse_args( $defaults, $params );

		$id = $params[ 'id' ];

		$value_text = __( 'Count', 'af-locale' );

		/**
		 * Preparing Data for JS
		 */
		$data = array( '\'values\'' );
		foreach( $results AS $value )
		{
			$data[] = $value;
		}
		$column_data = '[ [' . implode( ',', $data ) . ' ] ]';

		$categories = array_keys( $results );
		foreach( $categories AS $key => $category )
		{
			$categories[ $key ] = '\'' . $category . '\'';
		}
		$categories = implode( ',', $categories );

		/**
		 * C3 Chart Script
		 */
		$html  = '<div id="' . $id . '" class="c3-chart"></div>';
		$html .= "<script>
			        jQuery(document).ready( function($){
						var chart_{$id} = c3.generate({
						    bindto: '#{$id}',
						    data: {
						      columns: {$column_data},
						      type: 'bar',
						      keys: {
					          	value: [ 'value' ],
					          },
					          colors: {
					          	values: '#0073aa',
					          }
						    },
						    axis: {
								x: {
							    	type: 'category',
							    	categories: [{$categories}]
							    },
							    y: {
							    	tick: {
									    format: function(x) {
									        return ( x == Math.floor(x)) ? x : '';
									    }
								    }
								}
							},
							legend: {
						        show: false
						    },
						    tooltip: {
							  format: {
							    name: function (name, ratio, id, index) { return '{$value_text}'; }
							  }
							}

						});
					});
			    </script>";

		return $html;
	}

	public function pies( $title, $results, $params = array() )
	{
	}

	/**
	 * Loading Admin Styles
	 */
	public function admin_styles()
	{
		$morris_css = AF_URLPATH . 'components/results/base-result-handlers/charts/includes/css/c3.css';
		wp_enqueue_style( 'af-c3-css', $morris_css );
	}

	/**
	 * Loading Admin Scripts
	 */
	public function admin_scripts()
	{
		$d3_script_url = AF_URLPATH . 'components/results/base-result-handlers/charts/includes/js/d3.min.js';
		wp_enqueue_script( 'af-d3', $d3_script_url );

		$c3_script_url = AF_URLPATH . 'components/results/base-result-handlers/charts/includes/js/c3.min.js';
		wp_enqueue_script( 'af-c3-js', $c3_script_url );

		$c3_helper_script_url = AF_URLPATH . 'components/results/base-result-handlers/charts/includes/js/c3-helpers.js';
		wp_enqueue_script( 'af-c3-helper-js', $c3_helper_script_url );
	}

	/**
	 * Loading Frontend Scripts
	 */
	public function frontend_scripts()
	{
		$this->admin_scripts();
	}
}

af_register_chartcreator( 'AF_Chart_Creator_C3' );