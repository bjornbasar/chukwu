var mooLB = new Class({

	Implements : [ Options, Events ],

	// default options
	// don't change these here but on the instance (unless you want to)
	options : {
		url : '',
		width : 400,
		height : 300,
		padding : 10,
		draggable : false,
		closeOnOverlay : true,
		lbClass : 'mooLB',
		lbContentClass : 'mooLBContent',
		overlayColor : 'transparent',
		overlayOpacity : 1
	},

	initialize : function(options)
	{

		// start everything.
		this.setOptions(options);
		this.createOverlay();
		this.createLB();
	},

	createOverlay : function()
	{

		this.options.overlay = new Element('div', {
			id : 'mooLB_overlay',
			styles : {
				position : 'fixed',
				left : '0px',
				top : '0px',
				height : '100%',
				width : '100%',
				'background-color' : this.options.overlayColor,
				'z-index' : 10000
			}
		});

		this.options.overlay.setStyle('opacity', this.options.overlayOpacity);

		var lb = this;

		if (this.options.closeOnOverlay)
		{
			this.options.overlay.addEvent('click', function()
			{

				lb.close();
			});
		}

		this.options.overlay.injectInside(document.body);
	},

	createLB : function()
	{

		this.options.lb = new Element('div', {
			id : 'mooLB_lb',
			'class' : this.options.lbClass,
			styles : {
				position : 'fixed',
				width : this.options.width,
				height : this.options.height,
				padding : this.options.padding,
				'z-index' : 10001,
				/*
				 * left: '50%', top: '50%', 'margin-left': ((this.options.width /
				 * 2) + this.options.padding) * (-1), 'margin-top':
				 * ((this.options.height / 2) + this.options.padding) * (-1),
				 */
				'background-color' : '#cccccc'
			}
		});

		this.options.lb.injectInside(document.body);

		if (this.options.draggable)
		{
			this.options.lb.makeDraggable();
		}
		else
		{
			var lb = this.options.lb;
			window.addEvent('resize', function()
			{

				mooCenter(lb);
			});
		}

		this.options.lbContent = new Element('div', {
			id : 'mooLB_content',
			'class' : this.options.lbContentClass,
			styles : {
				width : (this.options.width - 10),
				height : (this.options.height - 10),
				overflow : 'hidden',
				'background-color' : '#fff',
				padding : '5px'
			}
		});

		this.options.lbContent.injectInside(this.options.lb);

		mooCenter(this.options.lb);

		if (this.options.url != '')
		{
			this.options.lbContent.load(this.options.url);
		}
	},

	close : function()
	{

		this.options.overlay.destroy();
		this.options.lb.destroy();
	}
});