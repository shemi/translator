require('./bootstrap');


import HeaderComponent from "./components/Header.vue";
import SidebarComponent from "./components/Sidebar.vue";
import GroupTranslateComponent from "./components/GroupTranslate.vue";

Vue.component('translator-header', HeaderComponent);
Vue.component('translator-sidebar', SidebarComponent);
Vue.component('translator-group-translate', GroupTranslateComponent);

import alertComponent from 'vue-strap/src/Alert.vue';
import spinnerComponent from 'vue-strap/src/Spinner.vue';


var app = new Vue({
	el: 'body',
	
	components: {
		'vs-alert'  : alertComponent,
		'vs-spinner': spinnerComponent
	},
	
	data: {
		spinnerMsg   : "",
		showMainAlert: false,
		alertArgs    : {
			title      : "",
			description: "",
			type       : "success"
		}
	},
	
	methods: {
		onGroupsImported() {
			this.$broadcast('new-groups-imported');
		},
		
		updateActiveGroupChangedCount(to) {
			this.$broadcast('update-active-group-changed-count', to);
		}
		
	},
	
	events: {
		'main-loading-start': (msg = "") => {
			app.spinnerMsg = msg;
			app.$refs.spinner.show();
		},
		'main-loading-stop' : () => {
			app.$refs.spinner.hide();
		},
		'main-alert'        : (show = true, args = {}) => {
			if(!show) {
				app.showMainAlert = false;
				
				return;
			}
			
			app.alertArgs = _.defaults(args, app.alertArgs);
			app.showMainAlert = show;
		}
	},
	
	ready() {
		
	}
	
});
