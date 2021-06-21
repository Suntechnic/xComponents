var APP = APP || {};


APP.config = Object.assign({
        mobileMaxWidth: 760,
        lang: 'ru',
        env: 'production'
    }, APP.config || {});


APP.env = {
    is_desktopView: function () {
        return $(window).width() > APP.config.mobileMaxWidth;
    },
    
    is_mobileView: function () {
        return !APP.env.is_desktopView();
    },
    
    currentView: function () {
        if (APP.env.is_desktopView()) return 'desktop';
        return 'mobile';
    }
}

APP.Util = {
    
    setCooke: function (name, value, options = {}) {
        //setCookie('user', 'John', {secure: true, 'max-age': 3600});
        if ('undefined' != typeof APP.config.cookie_name && APP.config.cookie_name) name =  + '_' + name;
        
        options = Object.assign({
                path: '/'
            }, options || {});
        
        if (options.expires instanceof Date) {
            options.expires = options.expires.toUTCString();
        }
        
        let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
        
        for (let optionKey in options) {
            updatedCookie += "; " + optionKey;
            let optionValue = options[optionKey];
            if (optionValue !== true) {
              updatedCookie += "=" + optionValue;
            }
        }
        
        document.cookie = updatedCookie;
    },
    
    submitForm: function (form, callback) {
        let url = form.getAttribute('action');
		let formData = new FormData(form);
		let sendData = {};

		
		var oReq = new XMLHttpRequest();
		oReq.onload = function () {
			callback(this.response);
		};
		
		oReq.open('post', url);
		oReq.send(formData);
		
		return false;
    },
    
    maskingInput: function (input, pattern, mask) {

        var placeholder = input.getAttribute('placeholder');
        mask = mask || '';
        
        pattern = new RegExp(pattern);
        
        if (pattern.test(placeholder)) {
            
            var proc = function (value) {
                var newValue = value;
                var l = newValue.length;
                
                for ( i = l; i >= 0; i--) {
                    testValue = newValue + placeholder.substr(newValue.length);
                    if (pattern.test(testValue)) {
                        break;
                    } else {
                        newValue = newValue.substr(0, newValue.length-1);
                    }
                }
                
                
                while (newValue.length < mask.length && mask.substr(newValue.length,1) != '_') {
                    newValue = newValue+mask.substr(newValue.length,1);
                }
                
                return newValue;
            }
            
            if (mask.length) input.value = proc('');
            
            input.addEventListener('keyup', function(e) {
                
                switch (e.keyCode) { // allows navigating thru input
                    case 8: //backspace
                    case 20: // caplocks
                    case 17: // control
                    case 18: // option
                    case 16: // shift
                    case 37: // arrow keys
                    case 38:
                    case 39:
                    case 40:
                    case  9: // tab (let blur handle tab)
                        return;
                }
                
                input.value = proc(input.value);
                
            }, false);
        }
    }
}