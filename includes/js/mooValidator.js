var mooValidator = new Class(
{

	Implements : [ Options, Events ],

	// default options
	// don't change these here but on the instance (unless you want to)
	options :
	{
		closeDelay : 3000,
		position : 'right',
		edge : 'left',
		validate : null,
	},

	form : null,

	validator : null,

	initialize : function(form, options)
	{
		this.form = document.id(form);

		// start everything.
		this.setOptions(options);

		var self = this;

		this.validator = new Form.Validator(this.form,
		{
			stopOnFailure : true,
			evaluateOnSubmit : true,
			evaluateFieldsOnBlur : true,
			evaluateFieldsOnChange : true,
			serial : false,
			onElementFail : function(el, errors)
			{

				var fv = this;
				var errorMsg = '';
				errors.each(function(error)
				{

					errorMsg += fv.validators[error].getError(el, fv.validators[error].getProps(el)) + "<br/>\n";
				});

				self.addError(el, errorMsg);

			},
			onFormValidate : self.options.validate ? self.options.validate : function(passed, form, evt)
			{
				if (passed)
				{
					form.submit();
					return true;
				}
				else
				{
					return false;
				}
			}
		});
		this.form.store('validator', this.validator);

	},

	addError : function(el, message)
	{

		var self = this;

		var container = new Element('div',
		{
			'class' : 'alert-container'
		}).injectInside(self.form);

		var alert = new Element('div',
		{
			'class' : 'alert alert-error'
		}).injectInside(container);

		var closer = new Element('a',
		{
			'class' : 'close',
			'data-dismiss' : 'alert'
		}).injectInside(alert);
		closer.set('text', '×');

		var text = new Element('div').injectAfter(closer);
		text.innerHTML = message;

		closer.addEvent('click', function()
		{
			this.getParent().getParent().destroy();
		});

		container.position(
		{
			relativeTo : el,
			position : self.options.position,
			edge : self.options.edge,
		});

		if (this.options.closeDelay)
		{
			setTimeout(function()
			{
				closer.fireEvent('click');
			}, this.options.closeDelay);
		}

	}
});
