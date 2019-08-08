<?php
/**
 * Form frontend standard output handler class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Frontends;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Forms\Form_Frontend_Output_Handler;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;

/**
 * Class for handling the form frontend output in the old fashioned way.
 *
 * @since 1.1.0
 */
class Frontend_Standard extends Frontend {
	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.1.0
	 */
	protected function bootstrap() {
		$this->slug  = 'standard';
		$this->title = __( 'Standard', 'torro-forms' );
	}

	/**
	 * Renders the content for a given form.
	 *
	 * @since 1.1.0
	 *
	 * @param Form_Frontend_Output_Handler $frontend   Form frontend output handler.
	 * @param Form                         $form       Form object.
	 * @param Submission|null              $submission Optional. Submission object, or null if none available. Default null.
	 */
	public function render_output( $frontend, $form, $submission = null ) {
		$prefix = $frontend->form_manager->get_prefix();

		if ( ! $frontend->can_access_form_check( $form, $submission ) ) {
			return;
		}

		if ( $frontend->is_completed_check( $submission ) ) {
			return;
		}

		$container = $frontend->get_current_container( $form, $submission );
		if ( ! $container ) {
			$frontend->print_notice( __( 'No container exists for this form.', 'torro-forms' ), 'error' );
			return;
		}

		$frontend->maybe_print_form_error( $form );

		if ( $submission ) {
			$frontend->maybe_print_submission_errors( $submission );
		}

		$template_data = $form->to_json( false );

		$template_data['hidden_fields']  = '<input type="hidden" name="torro_submission[nonce]" value="' . wp_create_nonce( $frontend->get_nonce_action( $form, $submission ) ) . '">';
		$template_data['hidden_fields'] .= '<input type="hidden" name="torro_submission[form_id]" value="' . esc_attr( $form->id ) . '">';
		if ( $submission ) {
			$template_data['hidden_fields'] .= '<input type="hidden" name="torro_submission[id]" value="' . esc_attr( $submission->id ) . '">';
		}
		if ( ! is_archive() && in_the_loop() && (int) get_the_ID() !== $form->id ) {
			$template_data['hidden_fields'] .= '<input type="hidden" name="torro_submission[original_form_id]" value="' . esc_attr( get_the_ID() ) . '">';
		}

		/**
		 * Filters the CSS class to use for every button for a form in the frontend.
		 *
		 * @since 1.0.0
		 *
		 * @param string $button_class Button CSS class. Default 'torro-button'.
		 */
		$button_class = apply_filters( "{$prefix}form_button_class", 'torro-button' );

		$template_data['navigation'] = array();
		if ( $frontend->has_next_container( $form, $submission ) ) {
			/**
			 * Filters the label for the Next button for a form in the frontend.
			 *
			 * @since 1.0.0
			 *
			 * @param string $next_button_label Next button label. Default 'Next Step'.
			 * @param int    $form_id           Form ID.
			 */
			$next_button_label = apply_filters( "{$prefix}form_button_next_step_label", _x( 'Next Step', 'button label', 'torro-forms' ), $form->id );

			$template_data['navigation']['next_button'] = array(
				'label' => $next_button_label,
				'attrs' => array(
					'type'  => 'submit',
					'name'  => 'torro_submission[action]',
					'value' => 'next',
					'class' => $button_class,
				),
			);
		} else {
			/**
			 * Filters the label for the Submit button for a form in the frontend.
			 *
			 * @since 1.0.0
			 *
			 * @param string $submit_button_label Submit button label. Default 'Submit'.
			 * @param int    $form_id             Form ID.
			 */
			$submit_button_label = apply_filters( "{$prefix}form_button_submit_label", _x( 'Submit', 'button label', 'torro-forms' ), $form->id );

			/**
			 * Filters the CSS class to use for a primary button for a form in the frontend.
			 *
			 * @since 1.0.0
			 *
			 * @param string $button_primary_class Primary button CSS class. Default 'torro-button-primary'.
			 */
			$button_primary_class = apply_filters( "{$prefix}form_button_primary_class", 'torro-button-primary' );

			$submit_button_before = '';
			if ( has_action( "{$prefix}form_submit_button_before" ) ) {
				ob_start();

				/**
				 * Allows to print additional content before the Submit button for a form in the frontend.
				 *
				 * @since 1.0.0
				 *
				 * @param int $form_id Form ID.
				 */
				do_action( "{$prefix}form_submit_button_before", $form->id );

				$submit_button_before = ob_get_clean();
			}

			$submit_button_after = '';
			if ( has_action( "{$prefix}form_submit_button_after" ) ) {
				ob_start();

				/**
				 * Allows to print additional content after the Submit button for a form in the frontend.
				 *
				 * @since 1.0.0
				 *
				 * @param int $form_id Form ID.
				 */
				do_action( "{$prefix}form_submit_button_after", $form->id );

				$submit_button_after = ob_get_clean();
			}

			$template_data['navigation']['submit_button'] = array(
				'label'  => $submit_button_label,
				'attrs'  => array(
					'type'  => 'submit',
					'name'  => 'torro_submission[action]',
					'value' => 'submit',
					'class' => $button_class . ' ' . $button_primary_class,
				),
				'before' => $submit_button_before,
				'after'  => $submit_button_after,
			);
		}
		if ( $frontend->has_previous_container( $form, $submission ) ) {
			/**
			 * Filters the label for the Previous button for a form in the frontend.
			 *
			 * @since 1.0.0
			 *
			 * @param string $prev_button_label Previous button label. Default 'Previous Step'.
			 * @param int    $form_id           Form ID.
			 */
			$prev_button_label = apply_filters( "{$prefix}form_button_prev_step_label", _x( 'Previous Step', 'button label', 'torro-forms' ), $form->id );

			$template_data['navigation']['prev_button'] = array(
				'label' => $prev_button_label,
				'attrs' => array(
					'type'  => 'submit',
					'name'  => 'torro_submission[action]',
					'value' => 'prev',
					'class' => $button_class,
				),
			);
		}

		$template_data['current_container'] = $container->to_json( false );

		$container_collection = $form->get_containers(
			array(
				'number'        => 2,
				'no_found_rows' => true,
			)
		);

		$show_container_title = $container_collection->get_total() > 1;

		/**
		 * Filters whether the container title should be displayed on the frontend.
		 *
		 * @since 1.0.0
		 *
		 * @param bool $show_container_title Whether to show the title. Default is true if the current form has multiple containers,
		 *                                   or false otherwise.
		 * @param int  $form_id              Form ID.
		 * @param int  $container_id         Container ID.
		 */
		if ( ! apply_filters( "{$prefix}form_container_show_title", $show_container_title, $form->id, $container->id ) ) {
			$template_data['current_container']['label'] = '';
		}

		$template_data['current_container']['elements'] = array();
		foreach ( $container->get_elements() as $element ) {
			$template_data['current_container']['elements'][] = $element->to_json( false, $submission );
		}

		$frontend->form_manager->template()->get_partial( 'form', $template_data );
	}
}