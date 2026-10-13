import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps({
		className: 'contact-form-editor-preview',
	});

	return (
		<div {...blockProps}>
			<p className="contact-form-editor-preview__label">Contact Form</p>

			<h2>
				Reach out using our contact form and we’ll respond{' '}
				<strong>typically within 48 hours.</strong>
			</h2>

			<p>
				The <strong>more detail you can provide</strong>, the better
				informed we’ll be when making an{' '}
				<strong>initial assessment</strong>.
			</p>

			<div className="contact-form-editor-preview__fields">
				<div>First name</div>
				<div>Last name</div>
				<div>Email address</div>
				<div>Website URL</div>
				<div>How can we help?</div>
			</div>

			<p>
				<em>The working form is generated on the front end.</em>
			</p>
		</div>
	);
}
