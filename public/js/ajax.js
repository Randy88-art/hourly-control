"use strict"; 

/** Función que crea objeto XMLHttpRequest para todos los navegadores */

function getXMLHTTPRequest() {
	var peticion = false;
	try {
		/* for Firefox */
		peticion = new XMLHttpRequest();
	} catch (err) {
		try {
			/* for some versions of IE */
			peticion = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (err) {
			try {
				/* for some other versions of IE */
				peticion = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (err) {
				peticion = false;
			}
		}
	}
	return peticion;
}

function example() {	
	let peticion = getXMLHTTPRequest();
	let params = new FormData();
	let url = "";	
	
	params.append('key', 'value');	
					
	peticion.onreadystatechange = consulta;
		
	peticion.open('POST', url, true);
	peticion.send(params);

	function consulta() {
		if(peticion.readyState == 1) {//función que se repite y se puede optimizar reduciendo código
			console.log("cargando...");
		}
		else if(peticion.readyState == 4 && peticion.status == 422) {			
			console.log("Error 422");								
		}
		else if(peticion.readyState == 4 && peticion.status == 200) {						
			const jsonResponse = JSON.parse(peticion.responseText);										
			document.getElementById("datos").innerHTML = jsonResponse.message;				
		} 
	}
}