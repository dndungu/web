core.control.extend('form', function(){
	var control = this;
	var _private = {
		html: new Object(),
		source: new String(),
		template: new String(),
		record: new Object(),
		command: new String(),
		grid: new Object(),
		sandbox: new core.sandbox(),
		primarykey: false,
		readyCallback: false,
		activity: {},
		validation: false,
		getTemplate: function(){
			var that = this;
			core.ajax.get(that.source, function(){
				that.activity.getTemplate = arguments[0].readyState;
				if(arguments[0].readyState != 4 || arguments[0].status != 200) return;
				that.template = arguments[0].responseText;
				that.html = $(that.template);
			});
		},
		getRecord: function(){
			var that = this;
			var data = {command: "select", primarykey: arguments[0]};
			core.ajax.post(that.source, data, function(){
				that.activity.getRecord = arguments[0].readyState;
				if(arguments[0].readyState != 4 || arguments[0].status != 200) return;
				that.record = jQuery.parseJSON(arguments[0].responseText);
				that.renderForm();
			});
		},
		postParameters: function(){
			return this.html.serialize()+'&command='+this.command+'&primarykey='+String(this.primarykey);
		},
		initPoster: function(){
			var command = this.command;
			var that = this;
			that.html.unbind('mousedown').mousedown(function(event){
				event.stopPropagation();
			});
			that.html.unbind('submit').submit(function(event){
				if(!core.validator.checkForm($(this))) return;
				event.stopPropagation();
				var data = that.postParameters();
				var url = that.source;
				core.ajax.post(url, data, function(){
					if(arguments[0].readyState != 4 || arguments[0].status != 200) return;
					that.grid.refresh();
					that.html.slideUp();
				});
				event.preventDefault();
			});
			that.html.find('input[name="cancel"]').unbind('mousedown').mousedown(function(event){
				event.stopPropagation();
				if(command == "update"){
					that.html.slideUp(function(){
						that.html;
					});
				}
				if(command == "insert"){
					that.grid.restore();
				}
			});
		},
		renderForm: function(){
			if(this.activity.getRecord != 4 || this.activity.getTemplate != 4) return;
			var template = new String(this.template);
			var that = this;
			this.html = $(control.render(template, this.record));
			this.html.find('select').each(function(){
				if(!that.record[0]) return;
				var subject = $(this);
				var name = subject.attr('name');
				var record = that.record[0][name] ? that.record[0][name] : false;
				if(record){
					subject.val(record);
				}
			});
			this.html.find('input.joinoptions').each(function(){
				if(!that.record[0]) return;
				var subject = $(this);
				var name = subject.attr('name').replace('[]', '');
				var value = subject.attr('value');
				var record = that.record[0][name] ? that.record[0][name] : false;
				if(record){
					var records = record.split(', ');
					for(i in records){
						if(records[i] == value){
							subject.prop('checked', 'checked');
						}
					}
				}
			});
			this.readyCallback();
		}
	};
	var _public = {
			init: function(){
				if(!arguments.length) return;
				_private.source = arguments[0];
				_private.getTemplate();
			},
			getHTML: function(){
				_private.html.css({display: 'block'});
				_private.initPoster();
				_private.validation = setTimeout(function(){
					core.validator.setChangeCheck(_private.html);
				}, 1000);
				return _private.html;
			},
			setGrid: function(){
				_private.grid = arguments[0];
			},
			setCommand: function(){
				_private.command = arguments[0];
			},
			setPrimaryKey: function(ID){
				_private.primarykey = ID;
				_private.getRecord(ID);
			},
			getContent: function(){
				var content = {};
				_private.html.find('textarea, input').each(function(){
					var element = $(this);
					var name = element.attr('name');
					content[name] = element.val();
				});
				_private.html.find('select').each(function(){
					var element = $(this);
					var name = element.attr('name');
					content[name] = element.find('option:selected').text();
				});
				return content;
			},
			updateGrid: function(){
				var data = this.getContent();
				_private.html.siblings('div').each(function(event){
					var column = $(this);
					var name = column.attr('name');
					column.html(data[name]);
				});				
			},
			clearForm: function(){
				_private.html = $(_private.template);
				_private.html.find('input[type="text"], input[type="password"], textarea').each(function(){
					var pattern = /{{([^}]*)}}/g;
					var element = $(this);
					element.val(element.val().replace(pattern, ''));
				});
			},
			setHTML: function(){
				_private.html = arguments[0];
				_private.initPoster();
			},
			onReady: function(){
				_private.readyCallback = arguments[0];
			}		
	};
	for(i in _public){
		this[i] = _public[i];
	}
	this.init(arguments[0]);	
});