"use strict"; 

window.onload = function() {
	/* Selects all elements with the class name 'show_password'
	and adds an event listener to them. It then checks if there are any elements found
	with that class using `if(showPasswordChars.length > 0)`. */
	let showPasswordChars = document.querySelectorAll('.show_password');

	if(showPasswordChars.length > 0) {
		showPasswordChars.forEach(showPasswordChar => {
			showPasswordChar.addEventListener('click', () => {				
				let input = showPasswordChar.parentNode.previousElementSibling.querySelector('input');
				if(input.type == 'password') {
					input.type = 'text';
					showPasswordChar.src = '/images/eye_closed.svg';
				} else {
					input.type = 'password';
					showPasswordChar.src = '/images/eye.svg';
				}
			});
		});
	}
	
	/** Show current date and time */
	let dateElement = document.querySelector('.date');

	function updateDateTime() {
		const date = new Date();
		const day = `0${date.getDate()}`.slice(-2);
		const month = `0${date.getMonth() + 1}`.slice(-2);
		const year = date.getFullYear();
		const hours = `0${date.getHours()}`.slice(-2);
		const minutes = `0${date.getMinutes()}`.slice(-2);
		const seconds = `0${date.getSeconds()}`.slice(-2);

		dateElement.innerText = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
	}
	  
	// Initial update
	updateDateTime();

	// Update every second
	setInterval(updateDateTime, 1000);
}
