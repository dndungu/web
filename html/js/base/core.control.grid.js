core.control.extend('grid', function(){
	var control = this;
	var _private = {
			form: new String(),
			sandbox: new core.sandbox(),
			source: new String(),
			template: new String(),
			html: new Object(),
			records: new Object(),
			offset: 0,
			limit: false,
			search: new String(),
			page: 1,
			sortdirection: new String(),
			sortcolumn: new String(),
			insertable:false,
			sortable: false,
			searchable: false,
			paginatable: false,
			activity: new Object(),
			readyCallback: false,
			renderGrid: function(){
				if(this.activity.getRecords != 4 || this.activity.getTemplate != 4) return;
				this.renderContent();
				this.initGridCell();
				this.renderLegend();
				this.renderPaginator();
				this.setUp();
				if(this.readyCallback){
					this.readyCallback();
				}
			},	
			reset: function(){
				this.offset = 0;
				this.page = 1;
			},
			renderContent: function(){
				var template = new String($('.gridContent' ,$(this.template)).html());
				var rows = "";
				if(this.records.body) {
					rows = control.render(template, this.records.body);
				}
				$('.gridContent', this.html).html(rows);
			},
			renderLegend: function(){
				var template = new String($('.gridFooter>span' ,$(this.template)).html());
				var records = this.records.footer;
				var rowCount = this.records.footer['rowCount'];
				var rowLimit = (parseInt(records['rowOffset'])+parseInt(records['rowLimit']));
				records['rowLimit'] = rowLimit > rowCount ? rowCount : rowLimit; 
				var legend = control.render(template, [records]);
				$('.gridFooter>span', this.html).html(legend);
			},
			renderPaginator: function(){
				var template = $('.gridFooter a.previous', $(this.template));
				var pagination = $('.gridFooter a.previous', this.html);
				var rowCount = this.records.footer['rowCount'] ? this.records.footer['rowCount'] : false;
				var pageCount = rowCount ? Math.ceil(rowCount/this.limit) : 0;
				if(pageCount){
					var i = this.page - 2;
					i = i > 0 ? i : 1;
					var j = i + 4;
					j = j > pageCount ? pageCount : j;
					i = ((j-i) < 4) ? (((j-4) > 0) ? (j-4) : i) : i;
					var buttons = new Array();
					while(i <= j){
						var liClass = i === this.page ? ' class="pagecurrent"' : '';
						buttons.push(' <li'+liClass+'><a class="pagenavigator" name="'+i+'">' + i++ +'</a></li> ');
					}
					template.parent('li').after(buttons.join(''));
					pagination.parent('li').parent('ul').html(template.parent('li').parent('ul').html());
				}else{
					pagination.parent('li').parent('ul').css({display: 'none'});
				}
			},
			initPaginator: function(){
				var that = this;
				$('.gridFooter a.pagenavigator', this.html).unbind('mousedown').mousedown(function(event){
					var name = $(this).attr('name');
					var rowCount = that.records.footer['rowCount'];
					var pageCount = Math.ceil(rowCount/that.limit);
					if(isNaN(name)){
						switch(name){
							case "first":
								that.page = 1;
								break;
							case "previous":
								that.page--;
								break;
							case "next":
								that.page++;
								break;
							case "last":
								that.page = pageCount;
								break;
						}
						that.page = that.page > pageCount ? pageCount : that.page;
						that.page = that.page < 1 ? 1 : that.page;
					}else{
						that.page = parseInt(name);
					}
					that.offset = (that.limit*(that.page-1));
					control.refresh();
				});
			},
			initSorter: function(){
				var orderclass = this.orderdirection.toLowerCase();
				$('.gridColumns>div>span[name="'+this.ordercolumn+'"]', this.html).addClass(orderclass);
				$('.gridColumns>div', this.html).unbind('mousedown').mousedown(function(){
					var icon = $(this).children('span');
					_private.orderdirection = icon.hasClass('asc') ? 'desc' : 'asc';
					_private.ordercolumn = icon.attr('name');
					$('.gridColumns>div>span.sort-icon', this.html).not(this).removeClass('asc').removeClass('desc');
					icon.removeClass('asc').removeClass('desc').addClass(_private.orderdirection);
					_private.reset();
					control.refresh();
				});
			},
			initSearcher: function(){
				var form = $('.gridSearch', this.html);
				form.unbind('submit').submit(function(event){
					event.preventDefault();
					_private.search = $('input[name="keywords"]', form).val();
					_private.html = $(_private.template);
					_private.getRecords('search');
				});
			},
			initInserter: function(){
				var that = this;
				var button = $('.gridActionsBar input[name="addButton"]', this.html);
				button.unbind('mousedown').mousedown(function(event){
					that.renderInserter();
				});
			},
			renderInserter: function(){
				this.form.setCommand('insert');
				this.form.clearForm();
				this.html.find('.gridColumns').slideUp();
				this.html.find('.gridContent').html(this.form.getHTML());
				this.html.find('.gridFooter').slideUp();
				this.html.find('.addButton').fadeOut();
			},
			updateSelectors: function(){
				var that = this;
				setTimeout(function(){
					if($('.gridContent .gridContentRecord .gridMultipleSelect input[type="checkbox"]:checked', that.html).length){
						$('.gridActionButtons').fadeIn();
					}else{
						$('.gridActionButtons').fadeOut();
					}						
				}, 500);
			},
			initGridCell: function(){
				var that = this;
				var rows = $('.gridContent .gridContentRecord', that.html);
				$('.gridColumns .gridSelectAll' ,that.html).unbind('click').click(function(){
					$('.gridContent .gridContentRecord .gridMultipleSelect input[type="checkbox"]', that.html).prop('checked', $(this).find('input[type="checkbox"]').is(':checked'));
					that.updateSelectors();
				});
				$('.gridContent .gridContentRecord .gridMultipleSelect', that.html).unbind('mousedown').mousedown(function(){
					event.stopPropagation();
					that.updateSelectors();
				});				
				rows.unbind('mousedown').mousedown(function(event){
					event.stopPropagation();
					var subject = $(this);
					that.renderGridCell(subject);
					$('>.gridCell,>form', rows.not(subject)).slideUp();						
				});
			},
			renderGridCell: function(){
				var that = this;
				var subject = arguments[0];
				var openCell = subject.find('.gridCell');
				var source = this.source.replace('grid', 'cell');
				if(openCell.siblings('form:visible').length) return;
				if(openCell.length){
					openCell.slideDown();
				}else{
					var primarykey = parseInt(subject.attr('title'));
					var lastColumn = $('.column:last-child', subject);
					core.ajax.post(source, {primarykey: primarykey}, function(){
						if(arguments[0].readyState != 4 || arguments[0].status != 200) return;
						var gridCell = $(arguments[0].responseText);
						gridCell.css({display: 'none'});
						lastColumn.after(gridCell);
						gridCell.slideDown();
						that.initCellActions(gridCell);
					});
				}				
			},
			initCellActions: function(){
				var gridCell = arguments[0];
				var that = this;
				$('.actionsCell input[type="button"]', gridCell).each(function(){
					var button = $(this);
					switch(button.attr('name')){
						case "updater":
							button.unbind('mousedown').mousedown(function(event){
								event.stopPropagation();
								that.renderUpdater(gridCell);
							});
							break;
						case "deleter":
							button.unbind('mousedown').mousedown(function(event){
								event.stopPropagation();
								if(confirm(core.l18n['delete.confirm.label'])){
									var source = that.source.replace('grid', 'form');
									var primarykey = gridCell.attr('title');
									core.ajax.post(source, {command: 'delete', primarykey: primarykey}, function(){
										if(arguments[0].readyState != 4 || arguments[0].status != 200) return;
										control.refresh();
									});
								}
							});
							break;
					}
				});
			},
			initGridActions: function(){
				var that = this;
				$('.gridActionsBar input[type="button"]', this.html).not('.addButton').unbind('mousedown').mousedown(function(){
					var command = $(this).attr('name');
					if(!confirm(core.l18n[command + '.confirm.label'])) return;
					var keys = $('.gridContent .gridContentRecord .gridMultipleSelect input[type="checkbox"]:checked', that.html);
					var items = [];
					keys.each(function(){
						items.push($(this).attr('value'));
					});
					var source = that.source.replace('grid', 'form');
					core.ajax.post(source, {"command": command, "ids": items.join(', ')}, function(){
						if(arguments[0].readyState != 4 || arguments[0].status != 200) return;
						control.refresh();
					});
				});
			},
			renderUpdater: function(){
				var gridCell = arguments[0];
				var openForm = gridCell.siblings('form');
				if(openForm.length){
					this.reOpenForm(openForm, gridCell);
				}else{
					this.createUpdator(gridCell);
				}
			},
			reOpenForm: function(){
				var openForm = arguments[0];
				var gridCell = arguments[1];
				this.form.setHTML(openForm);
				openForm.slideDown();
				gridCell.slideUp();				
			},
			createUpdator: function(){
				var gridCell = arguments[0];
				var updator = this.form;
				updator.setCommand("update");
				var primarykey = parseInt($(gridCell).attr('title'));
				this.form.setPrimaryKey(primarykey);
				var that = this;
				this.form.onReady(function(){
					var form = $(that.form.getHTML());
					form.css({display: 'none'});
					gridCell.after(form);
					form.slideDown();
					gridCell.slideUp();
				});				
			},
			initUpdater: function(){
				var that = this;
				var rows = $('.gridContent .gridContentRecord', this.html);
				rows.unbind('mousedown').mousedown(function(event){
					$('.gridContent .gridContentRecord', that.html).not(this).find('form').slideUp();
					that.renderUpdater(this);
				});
			},
			postParameters: function(){
				var parameters = {};
				if(this.ordercolumn){
					parameters.ordercolumn = this.ordercolumn;
				}
				if(this.orderdirection){
					parameters.orderdirection = this.orderdirection;
				}
				parameters.command = arguments[0];
				parameters.keywords = this.search;
				parameters.offset = (this.limit*(this.page-1));
				if(this.limit){
					parameters.limit = this.limit;
				}
				return parameters;
			},
			getRecords: function(){
				var that = this;				
				var data = that.postParameters(arguments[0]);
				core.ajax.post(that.source, data, function(){
					that.activity.getRecords = arguments[0].readyState;
					if(arguments[0].readyState != 4 || arguments[0].status != 200) return;
					that.records = jQuery.parseJSON(arguments[0].responseText);
					that.limit = that.records.footer.rowLimit;
					that.offset = that.records.footer.rowOffset;
					that.ordercolumn = that.records.ordercolumn;
					that.orderdirection = that.records.orderdirection;
					that.primarykey = that.records.primarykey;
					that.renderGrid();
				});
			},
			getTemplate: function(){
				var that = this;
				core.ajax.get(that.source, function(){
					that.activity.getTemplate = arguments[0].readyState;
					if(arguments[0].readyState != 4 || arguments[0].status != 200) return;
					that.template = arguments[0].responseText;
					that.html = $(that.template);
					that.updateable = that.html.hasClass('updateable');
					that.insertable = that.html.hasClass('insertable');
					that.searchable = that.html.hasClass('searchable');
					that.sortable = that.html.hasClass('sortable');
					that.paginatable = that.html.hasClass('paginatable');
					if(that.insertable || that.updateable){
						control.setForm(that.source.replace('/grid/', '/form/'));
						that.form.setGrid(control);
					}					
					that.renderGrid();
				});
			},
			setUp: function(){
				if(this.searchable){
					this.initSearcher();
				}
				if(this.paginatable){
					this.initPaginator();
				}
				if(this.sortable){
					this.initSorter();
				}
				if(this.insertable || this.updateable){
					this.initInserter();
				}
				if(this.updateable){
					this.initGridActions();
				}
			}			
	};
	var _public = {	
			init: function(source){
				if(!source) return;
				_private.source = source;
				_private.getTemplate();
				_private.getRecords('browse');
			},
			setOffset: function(offset){
				_private.offset = offset;
			},
			setLimit: function(limit){
				_private.limit = limit;
			},
			setForm: function(){
				_private.form =  _private.sandbox.getControl('form', arguments[0]);
			},
			onReady: function(){
				_private.readyCallback = arguments[0];
			},
			getHTML: function(){
				return _private.html;
			},
			restore: function(){
				_private.form.clearForm();
				_private.html.find('.gridColumns').slideDown();
				_private.renderContent();
				_private.initGridCell();
				_private.html.find('.gridFooter').slideDown();
				_private.html.find('.addButton').fadeIn();				
			},
			refresh: function(){
				_private.html = $(_private.template);
				_private.getRecords('browse');
			}
		};
	for(i in _public){
		this[i] = _public[i];
	}
	this.init(arguments[0]);
});