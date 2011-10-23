M.qtype_regexp = M.qtype_regexp || {};

M.qtype_regexp.showhidealternate = function(Y, buttonel, showhideel) {
	Y.one(buttonel).on('click', function(e) {
		if (Y.one(showhideel).getStyle('display') == 'none') {	
			Y.one(showhideel).setStyle('display', 'block');
			Y.one(buttonel).set('value', M.util.get_string('hidealternate', 'qtype_regexp'));
		} else {
			Y.one(showhideel).setStyle('display', 'none');
			Y.one(buttonel).set('value', M.util.get_string('showalternate', 'qtype_regexp'));
		}
		e.halt();
	});
}

M.qtype_regexp.showhint = function(Y, buttonel, inputid, hintflag) {
	Y.one(buttonel).on('click', function(e) {
		inputfield = Y.one('#'+inputid);
		actualvalue = document.getElementById(inputid).value;
		document.getElementById(inputid).value = actualvalue + hintflag;
		Y.all('input[type=submit]').set('disabled', true);
		Y.all('input[type=button]').set('disabled', true);
		form = Y.one('#responseform');
	    form.submit();
	});
}

M.qtype_regexp.addnextletter = function(Y, buttonel, inputid, nextletter) {
	Y.one(buttonel).on('click', function(e) {
		inputfield = Y.one('#'+inputid);
		actualvalue = document.getElementById(inputid).value;
		document.getElementById(inputid).value = actualvalue + nextletter;
		Y.all('input[type=submit]').set('disabled', true);
		Y.all('input[type=button]').set('disabled', true);
		form = Y.one('#responseform');
	    form.submit();
	});
}