"use strict";
window.core = window.core || {
	events: [],
	modules: {},
	register : function(moduleId, creator) {
		this.modules[moduleId] = {
			creator : creator,
			instance : null
		};
	},
	start : function(moduleId) {
		var module = this.modules[moduleId];
		module.instance = module.creator(new core.sandbox());
		try {
			module.instance.init();
		} catch (e) {
			if (typeof console === 'object') {
				console.error(e.message);
			}
		}
	},
	stop : function(moduleId) {
		var data = this.modules[moduleId];
		if (data.instance) {
			data.instance.kill();
			data.instance = null;
		}
	},
	boot : function() {
		for (var moduleId in this.modules) {
			if (this.modules.hasOwnProperty(moduleId)) {
				this.start(moduleId);
			}
		}
	},
	halt : function() {
		for ( var moduleId in this.modules) {
			if (this.modules.hasOwnProperty(moduleId)) {
				this.stop(moduleId);
			}
		}
	}
};