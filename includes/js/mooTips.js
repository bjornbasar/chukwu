var mooTips = new Class({

	Implements : [ Options, Events ],

	// default options
	// don't change these here but on the instance (unless you want to)
	options : {
		element : null,
		url : '',
		width : 220,
		height : 300,
		padding : 2,
		left : false,
		top : false,
		tipClass : 'mooLB',
		tipContentClass : 'mooLBContent'
	},

	initialize : function(options)
	{

		// start everything.
		this.setOptions(options);

		var options = this.options;
		var tip = this;

		this.options.element.addEvent('mouseover', function(e)
		{

			tip.showTip(options, e);

		});
		this.options.element.addEvent('mouseout', function(e)
		{

			tip.hideTip(options);
		});
	},

	showTip : function(opt, e)
	{

		if (!this.options.tip)
		{
			this.options.tip = new Element('div', {
				id : 'mooTips_tip',
				'class' : this.options.tipClass,
				styles : {
					position : 'fixed',
					width : this.options.width,
					height : this.options.height,
					padding : this.options.padding,
					'z-index' : 9001,
					/*
					 * left: '50%', top: '50%', 'margin-left':
					 * ((this.options.width / 2) + this.options.padding) * (-1),
					 * 'margin-top': ((this.options.height / 2) +
					 * this.options.padding) * (-1),
					 */
					'background-color' : '#cccccc'
				}
			});

			this.options.tip.injectInside(document.body);

			this.options.tipContent = new Element('div', {
				id : 'mooTips_content',
				'class' : this.options.tipContentClass,
				styles : {
					width : (this.options.width - 10),
					height : (this.options.height - 10),
					overflow : 'hidden',
					'background-color' : '#fff',
					padding : '5px'
				}
			});

			this.options.tipContent.injectInside(this.options.tip);

			// position tooltip here
			this.reposition(e);

			if (this.options.url != '')
			{
				this.options.tipContent.load(this.options.url);
			}
		}
	},

	reposition : function(e)
	{

		if (this.options.left || this.options.top)
		{
			this.options.tip.setStyle('position', 'absolute');

			if (this.options.left)
			{
				this.options.tip.setStyle('left', this.options.left);
			}
			else
			{
				this.options.tip.setStyle('left', e.page.x + 10);
			}

			if (this.options.top)
			{
				this.options.tip.setStyle('top', this.options.top);
			}
			else
			{
				this.options.tip.setStyle('top', e.page.y + 10);
			}
		}
		else
		{
			if (document.width - (e.page.x + this.options.width) < 20)
			{
				this.options.tip.setStyle('left', e.page.x - this.options.width
						- 10 - (this.options.padding * 2));
			}
			else
			{
				this.options.tip.setStyle('left', e.page.x + 10);
			}
			this.options.tip.setStyle('top', e.page.y + 20);
		}
	},

	hideTip : function(opt)
	{

		this.options.tip.destroy();
		this.options.tip = null;
	}

});