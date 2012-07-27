core.validator = {
	sandbox: new core.sandbox(),
	elements: {},
	setChangeCheck: function(){
		var extension = core.validator;
		var elements = extension.findElements(arguments[0]);
		for(i in elements){
			elements[i].unbind('change').change(function(){
				extension.checkRule($(this));
			})
		}
	},
	setSubmitCheck: function(){
		var extension = core.validator;
		var form = $(arguments[0]);
		form.submit(function(event){
			extension.checkForm(form);
		});		
	},
	checkForm: function(){
		var form = arguments[0];
		var subject = $(this);
		var extension = core.validator;
		var name = String(subject.attr('name'));
		var elements = extension.findElements(subject);
		var pass = true;
		for(i in elements){
			if(!extension.checkRule(elements[i])) {
				pass = false;
			}
		}
		if(pass){
			var dateField = subject.find('input.date');
			var dateFormat = dateField.val();
			dateField.val(Date.parse(dateFormat)/1000);
		}
		return pass;		
	},
	setStepCheck: function(){
		var steps = $('.step', arguments[0]);
		if(!steps.length) return;
		var extension = core.validator;
		steps.find('input.stepNext').click(function(){
			var step = $(this).parents('form>.step');
			var elements = extension.findElements(step);
			var pass = true;
			for(i in elements){
				if(!extension.checkRule(elements[i])) {
					pass = false;
				}
			}
			if(pass) {
				step.slideUp();
			}
		});
	},
	findElements: function(){
		var extension = core.validator;
		var container = $(arguments[0]);
		var elements = [];
		$('input, textarea, select', container).each(function(){
			var index = $(arguments[0]);
			var element = $(arguments[1]);
			if(element.is(':hidden')) return;
			var attr = element.attr('class');
			if(typeof attr != 'string' || attr.length == 0) return;
			var tests = attr.split(' ');
			var testable = false;
			for(i in tests){
				if(extension.getRules().indexOf(tests[i]) != -1) {
					elements.push(element);
					break;
				}
			}			
		});
		return elements;
	},
	checkRule: function(){
		var subject = $(arguments[0]);
		var extension = core.validator;
		var rules = (String(subject.attr('class'))).split(' ');
		var value = subject.val();
		var pass = false;
		for(i in rules){
			var rule = rules[i];
			if(extension.getRules().indexOf(rule) == -1) continue;
			if(!extension.testRule(rule, value)) {
				pass = false;
				break;
			}
			pass = true;
		}
		if(pass) {
			extension.greenStatus(subject);
		} else {
			extension.redStatus(subject);
			subject.attr('placeholder', core.l18n['validator_'+rule]);
			subject.val('');
		}
		return pass;
	},
	getRules: function(){
		var rules = [];
		rules.push('required');
		rules.push('string');
		rules.push('integer');
		rules.push('positiveInteger');
		rules.push('negativeInteger');
		rules.push('positiveDecimal');
		rules.push('negativeDecimal');
		rules.push('currency');
		rules.push('decimal');
		rules.push('phone');
		rules.push('year');
		rules.push('date');
		rules.push('ip');
		rules.push('password');
		rules.push('passwordstrong');
		rules.push('email');
		rules.push('domain');
		rules.push('subdomain');
		rules.push('loginexists');
		rules.push('html');
		rules.push('url');
		return rules;
	},
	testRule: function(){
		var rule = arguments[0];
		var value = arguments[1];
		var extension = core.validator;
		switch(rule){
			case 'required':
				return extension.testRequired(value);
				break;		
			case 'string':
				return extension.testString(value);
				break;
			case 'integer':
				return extension.testInteger(value);
				break;
			case 'positiveinteger':
				return extension.testPositiveInteger(value);
				break;
			case 'negativeinteger':
				return extension.testNegativeInteger(value);
				break;
			case 'currency':
				return extension.testCurrency(value);
				break;
			case 'decimal':
				return extension.testDecimal(value);
				break;
			case 'positivedecimal':
				return extension.testPositiveDecimal(value);
				break;
			case 'negativedecimal':
				return extension.testNegativeDecimal(value);
				break;
			case 'phone':
				return extension.testPhone(value);
				break;
			case 'year':
				return extension.testYear(value);
				break;
			case 'date':
				return extension.testDate(value);
				break;
			case 'ip':
				return extension.testIP(value);
				break;
			case 'password':
				return extension.testPassword(value);
				break;
			case 'passwordstrong':
				return extension.testPasswordStrong(value);
				break;
			case 'email':
				return extension.testEmail(value);
				break;
			case 'domain':
				return extension.testDomain(value);
				break;
			case 'subdomain':
				return extension.testSubDomain(value);
				break;
			case 'loginexists':
				return extension.testLoginExists(value);
				break;
			case 'html':
				return extension.testHTML(value);
				break;
			case 'url':
				return extension.testURL(value);
				break;
		}		
	},
	testRequired: function(){
		return String(arguments[0]).length ? true : false;
	},
	testString: function(){
		var pattern = /^[a-z0-9]{1,255}$/i;
		return pattern.test(arguments[0]);
	},
	testInteger: function(){
		var pattern = /^-{0,1}\d+$/;
		return pattern.test(arguments[0]);
	},
	testPositiveInteger: function(){
		var pattern = /^\d+$/;
		return pattern.test(arguments[0]);
	},
	testNegativeInteger: function(){
		var pattern = /^-\d+$/;
		return pattern.test(arguments[0]);
	},
	testPositiveDecimal: function(){
		var pattern = /^\d*\.{0,1}\d+$/;
		return pattern.test(arguments[0]);
	},
	testNegativeDecimal: function(){
		var pattern = /^-\d*\.{0,1}\d+$/;
		return pattern.test(arguments[0]);
	},
	testCurrency: function(){
		var pattern = /^-{0,1}\d*\.{0,2}\d+$/;
		return pattern.test(arguments[0]);
	},
	testDecimal: function(){
		var pattern = /^-{0,1}\d*\.{0,1}\d+$/;
		return pattern.test(arguments[0]);
	},
	testPhone: function(){
		var pattern = /^\+?[0-9\s]{8,16}/;
		return pattern.test(arguments[0]);
	},
	testYear: function(){
		var pattern = /^(19|20)[\d]{2,2}$/;
		return pattern.test(arguments[0]);
	},
	testDate: function(){
		return !isNaN(Date.parse(arguments[0]));
	},
	testIP: function(){
		var pattern = /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/;
		return pattern.test(arguments[0]);
	},
	testPassword: function(){
		var pattern = /^[a-z0-9_-]{6,32}$/i;
		return pattern.test(arguments[0]);
	},
	testPasswordStrong: function(){
		var pattern = /^[a-z0-9_-]{8,32}$/i;
		return pattern.test(arguments[0]);
	},
	testEmail: function(){
		var pattern = /^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/i;
		return pattern.test(arguments[0]);
	},
	testDomain: function(){
		var pattern = /([\da-z\.-]+)\.([a-z\.]{2,6})$/i;
		return pattern.test(arguments[0]);
	},
	testSubDomain: function(){
		var pattern = /^[a-z\d]+([-_][a-z\d]+)*$/i;
		return pattern.test(arguments[0]);
	},
	testLoginExists: function(){
		var test = arguments[0];
		$.post('/loginexists', {alias: test}, function(){
			var extension = core.validator;
			var response = jQuery.parseJSON(arguments[0]);
			if(response.launcher.LoginExists[0] == "Yes"){
				extension.redStatus($('.loginexists'));
			}
		});
		return true;
	},
	testHTML: function(){
		var pattern = /^<([a-z]+)([^<]+)*(?:>(.*)<\/\1>|\s+\/>)$/i;
		return pattern.test(arguments[0]);
	},
	testURL: function(){
		var pattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i;
		return pattern.test(arguments[0]);
	},
	redStatus: function(subject){
		subject.css({border:"1px inset #fb3a3a"});
	},
	greenStatus: function(subject){
		subject.css({border:"1px inset #59bd45"});
	}
};