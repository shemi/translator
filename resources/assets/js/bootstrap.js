window._ = require('lodash');
window.Cookies = require('js-cookie');

window.Vue = require('vue');
require('vue-resource');

Vue.http.interceptors.push(function (request, next) {
    request.root = window.translator.baseUrl;
    request.headers['X-XSRF-TOKEN'] = Cookies.get('XSRF-TOKEN');

    next();
});