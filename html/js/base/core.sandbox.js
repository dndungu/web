core.sandbox = function(){
	return {
		listen : function(types, listener) {
			types = typeof types == "string" ? [ types ] : types;
			for (i in types) {
				var type = types[i];
				core.events[type] = typeof core.events[type] == 'undefined' ? [] : core.events[type];
				core.events[type].push(listener);
			}
		},
		fire : function(event) {
			event = typeof event == "string" ? {type : event, data : new Object()} : event;
			event.data = typeof event.data == "undefined" ? new Object() : event.data;
			if (core.events[event.type] instanceof Array) {
				var listeners = core.events[event.type];
				var i = listeners.length - 1;
				do {
					if (typeof listeners[i] == 'function') {
						try {
							listeners[i](event);
						} catch (e) {
							this.log(e.message + ' : ' + event.type + ' : ' + event.data + ' : ' + listeners[i], 3);
						}
					}
				} while (i--);
			}
		},
		getService: function(name){
			return core[name];
		},
		getControl: function(){
			var name = arguments[0];
			var source = arguments[1] ? arguments[1] : false;
			var control = new core.control[name](source);
			return control;
		},
		log : function(message, level) {
			if (typeof console == 'undefined') return;
			severity = level ? level : 1;
			switch (severity) {
				case 1:
					console.info(message);
					break;
				case 2:
					console.warn(message);
					break;
				case 3:
					console.error(message);
					break;
			}
		}		
	};
};