require('./bootstrap');


/// clock

function startTime() {
	var today = new Date();
	// today = 'Wed Aug 12 2020 13:44:58 GMT-0400 (Eastern Daylight Time)';
	var h = today.getHours();
	var m = today.getMinutes();
	var s = today.getSeconds();
	m = checkTime(m);
	s = checkTime(s);

	// console.log(h);

	document.getElementById('txt').innerHTML =
	h + ":" + m + ":" + s;
	var t = setTimeout(startTime, 500);
}
function checkTime(i) {
	if (i < 10) {i = "0" + i};
	return i;
}

startTime();