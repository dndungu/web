core.navigation = {
	sandbox: new core.sandbox(),
	init: function(){
		this.primary();
		this.sandbox.listen(['navigation.staging'], this.staging);
	},
	primary: function(){
		var extension = this;
		$('.panelNavigation>ul>li>ul>li>a').click(function(event){
			event.stopPropagation();
			var anchor = $(this);
			anchor.addClass('current');
			$('.panelNavigation a').not(anchor).removeClass('current');
			var href = $(this).attr('href');
			extension.sandbox.fire({type: 'navigation.primary', data: href});
			event.preventDefault();
		});
		$('.panelNavigation>ul>li>a').mousedown(function(event){
			var anchor = $(this);			
			anchor.siblings('ul').children('li:first-child').children('a').click();
			if(anchor.siblings('ul').children('li').length){
				anchor.parent('li').addClass('expanded');
				anchor.siblings('ul').slideDown();
			}
			var children = $('.panelNavigation > ul > li.expanded').children('a').not(anchor).siblings('ul');
			children.slideUp(function(){
				children.parent('li').removeClass('expanded');
			});
		});			
	},
	staging: function(event){
		var stage = event.data.stage;
		var control = event.data.control;
		switch(stage){
			case 'primary':
				$('.pageContentContent').html(control.getHTML());
			break;
		}
	}
};
$(document).ready(function(){
	if(!$('.panelNavigation').length) return;
	core.navigation.init();
	$('#main-nav>li:first-child>a').mousedown();
});