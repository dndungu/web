core.register('studio', function(sandbox){
	return {
		init: function(){
			sandbox.module = this;
			sandbox.listen('navigation.primary', this.route);
		},
		kill: function(){
			
		},
		route: function(event){
			var href = event.data;
			var control = sandbox.module.initControl(href);
			if(!control) return;
			control.onReady(function(){
				sandbox.fire({type: 'navigation.staging', data: {"stage": "primary", "control": control}});
			});
		},
		initControl: function(href){
			var control = false;
			if(sandbox.module.isGrid(href)){
				control = sandbox.getControl('grid', href);
			}
			if(sandbox.module.isForm(href)){
				control = sandbox.getControl('form', href);
			}
			return control;					
		},
		isGrid: function(href){
			return href.indexOf('/grid/') == -1 ? false : true;
		},
		isForm: function(href){
			return href.indexOf('/form/') == -1 ? false : true;
		}
	};
});