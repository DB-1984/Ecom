function initialiseContactForms() {
	const forms = document.querySelectorAll('[data-contact-form]');

	forms.forEach((form) => {
		const status = form.querySelector('[data-form-status]');
		const submitButton = form.querySelector('[type="submit"]');
		const endpoint = form.dataset.endpoint;

		if (!endpoint || !submitButton) {
			return;
		}

		form.addEventListener('submit', async (event) => {
			event.preventDefault();

			if (!form.reportValidity()) {
				return;
			}

			submitButton.disabled = true;
			setStatus(status, 'Sending your message…');

			try {
				const response = await fetch(endpoint, {
					method: 'POST',
					body: new FormData(form),
					headers: {
						Accept: 'application/json',
					},
				});

				const data = await response.json().catch(() => null);

				if (!response.ok || data?.success === false) {
					throw new Error(
						data?.message ||
							'Your message could not be sent. Please try again.'
					);
				}

				form.reset();
				setStatus(
					status,
					data?.message ||
						'Thank you. We’ll get back to you as soon as possible.',
					'success'
				);
			} catch (error) {
				setStatus(
					status,
					error.message ||
						'Your message could not be sent. Please try again.',
					'error'
				);
			} finally {
				submitButton.disabled = false;
			}
		});
	});
}

function setStatus(element, message, state = '') {
	if (!element) {
		return;
	}

	element.textContent = message;

	if (state) {
		element.dataset.state = state;
	} else {
		delete element.dataset.state;
	}
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initialiseContactForms, {
		once: true,
	});
} else {
	initialiseContactForms();
}
