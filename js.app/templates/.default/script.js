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
    
    setCookie: function (name, value, options = {}) {
        //setCookie('user', 'John', {secure: true, 'max-age': 3600});
        if ('undefined' != typeof APP.config.cookie_name
                && APP.config.cookie_name
            ) name = APP.config.cookie_name + '_' + name;
        
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
        
		var oReq = new XMLHttpRequest();
		oReq.onload = function () {
			callback(this.response);
		};
		
		oReq.open('post', url);
		oReq.send(formData);
		
		return false;
    },
    
    sendObjectAsJSON: function (url, obj, callback) {
		//let formData = new FormData();
		let sendData =JSON.stringify(obj);
		
		var oReq = new XMLHttpRequest();
		oReq.onload = function () {
			callback(this.response);
		};
		
		oReq.open('post', url);
        oReq.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
		oReq.send(sendData);
		
		return false;
    },
    
}