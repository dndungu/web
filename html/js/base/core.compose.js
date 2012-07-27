core.compose = core.compose || function(template, records){
	var html = new String(template);
	var methods =  {
		compile: function(record){
		},
		compose: function(template, records){
		}
	};
	return methods.compose(template, records);
};