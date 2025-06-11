import { exitLink, enterLink } from "./set_hourly_times.js";

"use strict";

window.addEventListener('DOMContentLoaded', () => {
	const showPasswordChars = document.querySelectorAll('.show_password');

	/* If there are any elements with the class name 'show_password' in the
	document, it adds a click event listener. */
	if (showPasswordChars.length > 0) {
		showPasswordChars.forEach(showPasswordChar => {
			showPasswordChar.addEventListener('click', () => {
				const input = showPasswordChar.parentNode.previousElementSibling.querySelector('input');
				input.type = input.type === 'password' ? 'text' : 'password';
				showPasswordChar.src = input.type === 'password' ? '/images/eye.svg' : '/images/eye_closed.svg';
			});
		});
	}

	const dateElement = document.querySelector('.date');

	/* Updates the text content of the `dateElement` every second with the 
	current date and time in a localized format. */
	if (dateElement) {
		setInterval(() => dateElement.innerText = new Date().toLocaleString(), 1000);
	}

	const datePicker = document.querySelector('input[type="date"]');

	/* Checks if there is an input element of type "date" in the document. If such an element exists, it
	sets the value of that input element to the current date in the format "YYYY-MM-DD". */
	if (datePicker) {
		datePicker.value = new Date().toISOString().split('T')[0];
	}

	/** Add events listeners to "Enter" and "Exit" buttons */
	let enter = document.querySelector('#enter');
    let exit = document.querySelector('#exit');	

    if(enter && exit) {
        enter.addEventListener('click', enterLink);
        exit.addEventListener('click', exitLink);
    }
});
