/**
*
*  Base64 encode / decode
*  http://www.webtoolkit.info/
*
**/
var b64array = "ABCDEFGHIJKLMNOP" +
           "QRSTUVWXYZabcdef" +
           "ghijklmnopqrstuv" +
           "wxyz0123456789+/" +
           "=";

function encode64(input) {
    var base64 = "";
    var hex = "";
    var chr1, chr2, chr3 = "";
    var enc1, enc2, enc3, enc4 = "";
    var i = 0;

    do {
        chr1 = input.charCodeAt(i++);
        chr2 = input.charCodeAt(i++);
        chr3 = input.charCodeAt(i++);
    
        enc1 = chr1 >> 2;
        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
        enc4 = chr3 & 63;
    
        if (isNaN(chr2)) {
            enc3 = enc4 = 64;
        } else if (isNaN(chr3)) {
            enc4 = 64;
        }

        base64  = base64  +
            b64array.charAt(enc1) +
            b64array.charAt(enc2) +
            b64array.charAt(enc3) +
            b64array.charAt(enc4);
        chr1 = chr2 = chr3 = "";
        enc1 = enc2 = enc3 = enc4 = "";
    } while (i < input.length);

   return base64;
}


var Base64 = {
 
	// private property
	_keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
 
	// public method for encoding
	encode : function (input) {
		var output = "";
		var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = Base64._utf8_encode(input);
 
		while (i < input.length) {
 
			chr1 = input.charCodeAt(i++);
			chr2 = input.charCodeAt(i++);
			chr3 = input.charCodeAt(i++);
 
			enc1 = chr1 >> 2;
			enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
			enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
			enc4 = chr3 & 63;
 
			if (isNaN(chr2)) {
				enc3 = enc4 = 64;
			} else if (isNaN(chr3)) {
				enc4 = 64;
			}
 
			output = output +
			this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
			this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);
 
		}
 
		return output;
	},
 
	// public method for decoding
	decode : function (input) {
		var output = "";
		var chr1, chr2, chr3;
		var enc1, enc2, enc3, enc4;
		var i = 0;
 
		input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
 
		while (i < input.length) {
 
			enc1 = this._keyStr.indexOf(input.charAt(i++));
			enc2 = this._keyStr.indexOf(input.charAt(i++));
			enc3 = this._keyStr.indexOf(input.charAt(i++));
			enc4 = this._keyStr.indexOf(input.charAt(i++));
 
			chr1 = (enc1 << 2) | (enc2 >> 4);
			chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
			chr3 = ((enc3 & 3) << 6) | enc4;
 
			output = output + String.fromCharCode(chr1);
 
			if (enc3 != 64) {
				output = output + String.fromCharCode(chr2);
			}
			if (enc4 != 64) {
				output = output + String.fromCharCode(chr3);
			}
 
		}
 
		output = Base64._utf8_decode(output);
 
		return output;
 
	},
 
	// private method for UTF-8 encoding
	_utf8_encode : function (string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";
 
		for (var n = 0; n < string.length; n++) {
 
			var c = string.charCodeAt(n);
 
			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}
 
		}
 
		return utftext;
	},
 
	// private method for UTF-8 decoding
	_utf8_decode : function (utftext) {
		var string = "";
		var i = 0;
		var c = c1 = c2 = 0;
 
		while ( i < utftext.length ) {
 
			c = utftext.charCodeAt(i);
 
			if (c < 128) {
				string += String.fromCharCode(c);
				i++;
			}
			else if((c > 191) && (c < 224)) {
				c2 = utftext.charCodeAt(i+1);
				string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
				i += 2;
			}
			else {
				c2 = utftext.charCodeAt(i+1);
				c3 = utftext.charCodeAt(i+2);
				string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
				i += 3;
			}
 
		}
 
		return string;
	}
 
};

(function($){
 	var jlib = {
		floatElement: function(sourceElement, displayElement) {
			displayElement.show();
			displayElement.css({
				"position": "absolute",
				"z-index": 100000
			});
			var fieldPos = sourceElement.offset();
			displayElement.css('left',fieldPos.left);
			displayElement.css('top',fieldPos.top+sourceElement.outerHeight());
			//displayElement.css('width',sourceElement.outerWidth());
		},
		create: function(nodeType, appendTo, raw) {
			var element = document.createElement(nodeType);
			if (appendTo != undefined) {
				$(appendTo).append($(element));
			}
			return (raw === true)?element:$(element);
		},
		serializeObject: function(object) {
			return JSON.stringify(object);
		},
		flatString: function(str) {
			return str.replace(/[\.\/]+/g, "_");
		},
		jsonHashEncode: function(object) {
			var jsonString = JSON.stringify(object);
			jsonString = $.strtr(jsonString, {
				"\\\\\"":"\""
			});
			//var b64 = this.b64(jsonString, 'encode');
			var b64 = encode64(jsonString);
			return $.strtr(b64, {
				"=":"_",
				"\\/":"-",
				"\\+":"|"
			});
		},
		jsonHashDecode: function(str) {
			str = $.strtr(str, {
				"_":"=",
				"-":"/",
				"\\|":"+"
			});
			var b64_decoded = this.b64(str, 'decode');
			return JSON.parse(b64_decoded);
		},
		rand: function() {
			return (Math.random()*Math.random()*Math.random())*1000000;
		},
		path_info: function(filename) {
			var info = filename.split('/');
			var infofilename = filename.split('.');
			var ext = infofilename[infofilename.length-1];
			var pathArray = info.slice(0,info.length-1);
			return {
				filename:  info[info.length-1],
				parent: info[info.length-2],
				fullpath: info,
				path: pathArray,
				ext: ext
			};
		},
		number_format: function (number, decimals, dec_point, thousands_sep) {
			number 		= (number + '').replace(/[^0-9+\-Ee.]/g, '');
			var n 		= !isFinite(+number) ? 0 : +number;
			var prec 	= !isFinite(+decimals) ? 0 : Math.abs(decimals);
			var sep 	= (typeof thousands_sep === 'undefined') ? ' ' : thousands_sep;
			var dec 	= (typeof dec_point === 'undefined') ? '.' : dec_point;
			var s 		= '';
			var toFixedFix = function (n, prec) {
				var k = Math.pow(10, prec);
				return '' + Math.round(n * k) / k;
			};
			// Fix for IE parseFloat(0.55).toFixed(0) = 0;
			s 			= (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
			if (s[0].length > 3) {
				s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
			}
			if ((s[1] || '').length < prec) {
				s[1] = s[1] || '';
				s[1] += new Array(prec - s[1].length + 1).join('0');
			}
			return s.join(dec);
		},
		flatString: function(str) {
			return str.replace(/[\.\/]+/g, "_");
		},
		path2Object: function(path, object) {
			var array = path.split('.');
			for (var i=0;i<array.length;i++) {
				object = object[array[i]];
			}
			return object;
		},
		proxycall: function(context, fn, arguments) {
			return function(e) {
				fn.apply(context, [e, arguments]);
			}
		},
		b64: function (str, op) {
			switch (op) {
				case "encode":
					return Base64.encode(str);
				break;
				case "decode":
					return Base64.decode(str);
				break;
				case "urldecode":
					return Base64.decode(this.strtr(str,'-_','+/'));
				break;
				case "urlencode":
					return this.strtr(Base64.encode(str),'+/','-_');
				break;
			}
		},
		strtr: function (str, assoc_array) {
			for (var i in assoc_array) {
				str = str.replace(new RegExp(i, 'g'), assoc_array[i]);
			}
			return str;
		},
		replace: function(_search, _replace, _subject) {
  			return _subject.replace(new RegExp(_search, 'g'),_replace);
		}
	}
 	$.extend(jlib);
})(jQuery);