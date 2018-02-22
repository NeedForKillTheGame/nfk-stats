function ShowOrHide(obj) {
	x=document.getElementById(obj);
	if(x.style.display == "none") x.style.display = "block";
	else x.style.display = "none"
}
function ShowOrHide2(obj) {
	x=document.getElementById(obj);
	if(x.style.display == "none") x.style.display = "inline";
	else x.style.display = "none";
	y=document.getElementById('d'+obj);
	if(y.style.display == "none") y.style.display = "inline";
	else y.style.display = "none";
}