
function enterLink() {
    let form = document.getElementById('hours_control_form');
    if(form) {
        form.setAttribute('action', '/hourlycontrol/hourly/setInput');        
    }   
}

function exitLink() {
    let form = document.getElementById('hours_control_form');
    if(form) {
        form.setAttribute('action', '/hourlycontrol/hourly/setOutput'); 
    }           
}

export { enterLink, exitLink };