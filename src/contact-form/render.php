<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$form_id           = wp_unique_id( 'dbwp-contact-form-' );
$wrapper_attributes = get_block_wrapper_attributes(
	array( 'class' => 'contact-form-block' )
);
?>

<div <?php echo $wrapper_attributes; ?>>
	<h2 class="contact-form-heading">
		Reach out using our contact form and we’ll respond
		<strong>typically within 48 hours.</strong>
	</h2>

	<p class="contact-form-introduction">
		The <strong>more detail you can provide</strong>, the better informed we’ll be when making an
		<strong>initial assessment</strong>.
	</p>

	<form
		class="contact-form"
		data-contact-form
		data-endpoint="<?php echo esc_url( rest_url( 'dbwp/v1/contact-form' ) ); ?>"
		method="post"
	>
		<div class="contact-form-row">
			<div class="contact-form-field">
				<label for="<?php echo esc_attr( $form_id ); ?>-first-name">
					First name <span aria-hidden="true">*</span>
				</label>
				<input
					type="text"
					id="<?php echo esc_attr( $form_id ); ?>-first-name"
					name="firstName"
					autocomplete="given-name"
					required
				>
			</div>

			<div class="contact-form-field">
				<label for="<?php echo esc_attr( $form_id ); ?>-last-name">Last name</label>
				<input
					type="text"
					id="<?php echo esc_attr( $form_id ); ?>-last-name"
					name="lastName"
					autocomplete="family-name"
				>
			</div>
		</div>

		<div class="contact-form-row">
			<div class="contact-form-field">
				<label for="<?php echo esc_attr( $form_id ); ?>-email">
					Email address <span aria-hidden="true">*</span>
				</label>
				<input
					type="email"
					id="<?php echo esc_attr( $form_id ); ?>-email"
					name="email"
					autocomplete="email"
					required
				>
			</div>

			<div class="contact-form-field">
				<label for="<?php echo esc_attr( $form_id ); ?>-url">Website URL</label>
				<input
					type="url"
					id="<?php echo esc_attr( $form_id ); ?>-url"
					name="url"
					autocomplete="url"
					inputmode="url"
					placeholder="https://"
				>
			</div>
		</div>

		<div class="contact-form-field">
			<label for="<?php echo esc_attr( $form_id ); ?>-message">
				How can we help? <span aria-hidden="true">*</span>
			</label>
			<textarea
				id="<?php echo esc_attr( $form_id ); ?>-message"
				name="message"
				rows="7"
				required
			></textarea>
		</div>

		<div class="contact-form-honeypot" aria-hidden="true">
			<label for="<?php echo esc_attr( $form_id ); ?>-company">Company</label>
			<input
				type="text"
				id="<?php echo esc_attr( $form_id ); ?>-company"
				name="company"
				tabindex="-1"
				autocomplete="off"
			>
		</div>

		<div class="contact-form-actions">
			<button class="contact-form-submit" type="submit">Send message</button>

			<p
				class="contact-form-status"
				data-form-status
				role="status"
				aria-live="polite"
				aria-atomic="true"
			></p>
		</div>
	</form>
</div>
