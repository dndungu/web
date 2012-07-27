core.ajax = {
	sandbox: new core.sandbox(),
	maxPostSize: false,
	init: function(){
		if(core.ajax.maxPostSize) return;
		core.ajax.get('/upload', function(){
			if(arguments[0].readyState != 4 || arguments[0].status != 200) return;
			var response = core.ajax.parseJSON(arguments[0].responseText);
			core.ajax.maxPostSize = response.content.FileUploader[0];
		});
	},
	upload: function(){
		var defaults = {file: false, action: false, abort: false, error: false, load: false, loadend: false, loadstart: false,  progress: false};
		var options = arguments[0] ? arguments[0] : defaults;
		var xhr = core.ajax.getSender(options.callback);
		core.ajax.addFileEvents(xhr, options);
		xhr.open("POST",options.action);
		xhr.setRequestHeader("Cache-Control", "no-cache");
		xhr.setRequestHeader("X-Powered-By", 'Gereji');
		xhr.send(options.data);
	},
	addFileEvents: function(){
		var xhr = arguments[0];
		var options = arguments[1];
		if(options.abort) xhr.upload.addEventListener('abort', options.abort, true);
		if(options.error) xhr.upload.addEventListener('error', options.error, true);
		if(options.load) xhr.upload.addEventListener('load', options.load, true);
		if(options.loadend) xhr.upload.addEventListener('loadend', options.loadend, true);
		if(options.loadstart) xhr.upload.addEventListener('loadstart', options.loadstart, true);
		if(options.progress) xhr.upload.addEventListener('progress', options.progress, true);
	},
	get: function(){
		try{
			var defaults = {async: true};
			var options = arguments[2] ? arguments[2] : defaults;
			var ajax = core.ajax.getSender(arguments[1]);
			ajax.open('GET', arguments[0], options.async);
			ajax.send();
		}catch(e){
			core.ajax.sandbox.log(e,2);
		}
	},
	post: function(){
		try{
			var defaults = {async: true};
			var options = arguments[3] ? arguments[3] : defaults;
			var callback = (typeof arguments[2] == 'function') ? arguments[2] : function(){};
			var ajax = core.ajax.getSender(callback);
			var post = typeof arguments[1] == 'string' ? arguments[1] : jQuery.param(arguments[1]);
			ajax.open('POST', arguments[0], options.async);
			ajax.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			ajax.setRequestHeader("X-Powered-By", 'Gereji');
			ajax.send(post);
		}catch(e){
			core.ajax.sandbox.log(e,2);
		}
	},
	getSender: function(){
		try{
			var sender = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
			core.ajax.onAjaxReady(sender, arguments[0]);
			return sender;
		}catch(e){
			core.ajax.sandbox.log(e,2);
		}		
	},
	xget: function(){
		if(arguments.length != 2) return;
		try {
			var script = document.createElement("script");
			core.ajax.onScriptReady(script, arguments[1]);
			script.type = 'text/javascript';
			script.async = true;
			script.src = arguments[0];
			document.getElementsByTagName('head').appendChild(script);			
		}catch(e){
			core.ajax.sandbox.log(e,2);
		}
	},
	onScriptReady : function(script, callback) {
		if (script.readyState) {
		    script.onreadystatechange = function(){
		      if(script.readyState == "loaded" || script.readyState == "complete"){
		        script.onreadystatechange = null;
		        callback();
		      }
		    };        
		} else {
			script.onload = function(){
			    callback();
			};      
		}		
	},
	onAjaxReady: function(ajax, callback){
		ajax.onreadystatechange = function(){
			callback(ajax);
		};
	},
	
	parseJSON: jQuery.parseJSON,
	imageTypes: [
	             'image/jpg',
	             'image/jpeg',
	             'image/gif',
	             'image/png'
	            ],
	videoTypes: [
	             'application/annodex',
	             'application/mp4',
	             'application/ogg',
	             'application/vnd.rn-realmedia',
	             'application/x-matroska',
	             'video/3gpp',
	             'video/3gpp2',
	             'video/annodex',
	             'video/divx',
	             'video/flv',
	             'video/h264',
	             'video/mp4',
	             'video/mp4v-es',
	             'video/mpeg',
	             'video/mpeg-2',
	             'video/mpeg4',
	             'video/ogg',
	             'video/ogm',
	             'video/quicktime',
	             'video/ty',
	             'video/vdo',
	             'video/vivo',
	             'video/vnd.rn-realvideo',
	             'video/vnd.vivo',
	             'video/webm',
	             'video/x-bin',
	             'video/x-cdg',
	             'video/x-divx',
	             'video/x-dv',
	             'video/x-flv',
	             'video/x-la-asf',
	             'video/x-m4v',
	             'video/x-matroska',
	             'video/x-motion-jpeg',
	             'video/x-ms-asf',
	             'video/x-ms-dvr',
	             'video/x-ms-wm',
	             'video/x-ms-wmv',
	             'video/x-msvideo',
	             'video/x-sgi-movie',
	             'video/x-tivo',
	             'video/avi',
	             'video/x-ms-asx',
	             'video/x-ms-wvx',
	             'video/x-ms-wmx'
	             ]
};