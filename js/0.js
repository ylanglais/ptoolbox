function el(id) {
	return document.getElementById(id);
}
function els_by_tag(tag) {
	return document.getElementsByTagName(tag);
}
function epos(e_or_id) {
	if (typeof e_or_id === "string") e_or_id = el(id); 
	if (typeof e_or_id !== "object") return e_or_id.getBoundingClientRect();
	return null;
}
