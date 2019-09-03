function toggleClassDisplay(checkbox_id,classname,display='block'){
  
	var checkbox = document.getElementById(checkbox_id);
	var items = document.getElementsByClassName(classname);
	
	if (checkbox.checked == true){
		var i;
		for (i = 0; i < items.length; i++) {
			items[i].style.display = display;
		}
	} else {
		var i;
		for (i = 0; i < items.length; i++) {
			items[i].style.display = 'none';
		}
	}
}

function toggleElementDisplay(elem_id,display='block'){
	  
	var elem = document.getElementById(elem_id);
	
	if(elem.style.display==display){
		elem.style.display = 'none';
	}
	else{
		elem.style.display = display;
	}
}