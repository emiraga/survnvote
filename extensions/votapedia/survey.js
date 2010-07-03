/* copied from prefs.js in MediaWiki */
function tabbedprefs() {
	var prefform = document.getElementById('preferences');
	if (!prefform || !document.createElement) {
		return;
	}
	if (prefform.nodeName.toLowerCase() == 'a') {
		return; // Occasional IE problem
	}
	prefform.className = prefform.className + 'jsprefs';
	var sections = [];
	var children = prefform.childNodes;
	var seci = 0;
	for (var i = 0; i < children.length; i++) {
		if (children[i].nodeName.toLowerCase() == 'fieldset') {
			children[i].id = 'surveysection-' + seci;
			children[i].className = 'prefsection';
			if (is_opera || is_khtml) {
				children[i].className = 'prefsection operaprefsection';
			}
			var legends = children[i].getElementsByTagName('legend');
			sections[seci] = {};
			if (legends[0]) legends[0].className = 'mainLegend';
			if (legends[0] && legends[0].firstChild.nodeValue) {
				sections[seci].text = legends[0].firstChild.nodeValue;
			} else {
				sections[seci].text = '# ' + seci;
			}
			sections[seci].secid = children[i].id;
			seci++;
			if (sections.length != 1) {
				children[i].style.display = 'none';
			} else {
				var selectedid = children[i].id;
			}
		}
	}
	var toc = document.createElement('ul');
	toc.id = 'preftoc';
	toc.selectedid = selectedid;
	for (i = 0; i < sections.length; i++) {
		var li = document.createElement('li');
		if (i === 0) {
			li.className = 'selected';
		}
		var a = document.createElement('a');
		a.href = '#' + sections[i].secid;
		a.onmousedown = a.onclick = uncoversection;
		a.appendChild(document.createTextNode(sections[i].text));
		a.secid = sections[i].secid;
		li.appendChild(a);
		toc.appendChild(li);
	}
	prefform.parentNode.insertBefore(toc, prefform.parentNode.childNodes[0]);
        prefsubmit = document.getElementById('prefsubmit');
        if(prefsubmit)
            prefsubmit.id = 'prefcontrol';
}

function uncoversection() {
	var oldsecid = this.parentNode.parentNode.selectedid;
	var newsec = document.getElementById(this.secid);
	if (oldsecid != this.secid) {
		var ul = document.getElementById('preftoc');
		document.getElementById(oldsecid).style.display = 'none';
		newsec.style.display = 'block';
		ul.selectedid = this.secid;
		var lis = ul.getElementsByTagName('li');
		for (var i = 0; i< lis.length; i++) {
			lis[i].className = '';
		}
		this.parentNode.className = 'selected';
	}
	return false;
}

hookEvent("load", tabbedprefs);

function htmlspecialchars(str) {
	if (typeof(str) == "string") {
		str = str.replace(/&/g, "&amp;"); /* must be first */
		str = str.replace(/"/g, "&quot;");
		str = str.replace(/'/g, "&#039;");
		str = str.replace(/</g, "&lt;");
		str = str.replace(/>/g, "&gt;");
	}
	return str;
}

function rhtmlspecialchars(str) {
	if (typeof(str) == "string") {
		str = str.replace(/&gt;/ig, ">");
		str = str.replace(/&lt;/ig, "<");
		str = str.replace(/&#039;/g, "'");
		str = str.replace(/&quot;/ig, '"');
		str = str.replace(/&amp;/ig, '&'); /* must be last */
	}
	return str;
}

/**
sprintf() for JavaScript 0.5

Copyright (c) Alexandru Marasteanu <alexaholic [at) gmail (dot] com>
All rights reserved.
**/

function str_repeat(i, m) {
	for (var o = []; m > 0; o[--m] = i);
	return(o.join(""));
}

function sprintf() {
	var i = 0, a, f = arguments[i++], o = [], m, p, c, x, s = '';
	while (f) {
		if (m = /^[^\x25]+/.exec(f)) {
			o.push(m[0]);
		}
		else if (m = /^\x25{2}/.exec(f)) {
			o.push("%");
		}
		else if (m = /^\x25(?:(\d+)\$)?(\+)?(0|'[^$])?(-)?(\d+)?(?:\.(\d+))?([b-fosuxX])/.exec(f)) {
			if (((a = arguments[m[1] || i++]) == null) || (a == undefined)) {
				throw("Too few arguments.");
			}
			if (/[^s]/.test(m[7]) && (typeof(a) != "number")) {
				throw("Expecting number but found " + typeof(a));
			}
			switch (m[7]) {
				case 'b':a = a.toString(2);break;
				case 'c':a = String.fromCharCode(a);break;
				case 'd':a = parseInt(a);break;
				case 'e':a = m[6] ? a.toExponential(m[6]) : a.toExponential();break;
				case 'f':a = m[6] ? parseFloat(a).toFixed(m[6]) : parseFloat(a);break;
				case 'o':a = a.toString(8);break;
				case 's':a = ((a = String(a)) && m[6] ? a.substring(0, m[6]) : a);break;
				case 'u':a = Math.abs(a);break;
				case 'x':a = a.toString(16);break;
				case 'X':a = a.toString(16).toUpperCase();break;
			}
			if (/[def]/.test(m[7])) {
				s = (a >= 0 ? (m[2] ? '+' : '') : '-');
				a = Math.abs(a);
			}
			c = m[3] ? m[3] == '0' ? '0' : m[3].charAt(1) : ' ';
			x = m[5] - String(a).length - s.length;
			p = m[5] ? str_repeat(c, x) : '';
			o.push(s + (m[4] ? a + p : p + a));
		}
		else {
			throw("Huh ?!");
		}
		f = f.substring(m[0].length);
	}
	return o.join("");
}

function sur_showhide(id,n,c,exp)
{
    for(i=1;i<=n;i++)
    {
        document.getElementById(id+''+i).style.display = (c == 0 || c == i)?'block':'none';
    }
    document.getElementById('btn_next').style.display = (c && c < n)?'inline':'none';
    document.getElementById('btn_prev').style.display = (c && c > 1)?'inline':'none';
    document.getElementById('btn_collapse').style.display = (exp)?'none':'inline';
    document.getElementById('btn_expand').style.display = (exp)?'inline':'none';
}

function sur_collapse(id, n)
{
    sur_current = 1;
    sur_showhide(id, n, 1, true);
}

function sur_expand(id,n)
{
    sur_showhide(id, n, 0, false);
}

function sur_next(id,n)
{
    sur_current++;
    sur_showhide(id, n, sur_current, true);
}

function sur_prev(id,n)
{
    sur_current--;
    sur_showhide(id, n, sur_current, true);
}
