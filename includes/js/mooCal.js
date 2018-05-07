var mooCal = new Class({

	Implements : [ Options, Events ],

	// default options
	// don't change these here but on the instance (unless you want to)
	options : {
		onMouseover : '',
		onMouseout : '',
		onClick : '',
		container : '',
		tableClass : 'mooCalTable',
		theadClass : 'mooCalThead',
		tbodyClass : 'mooCalTbody',
		dayClass : 'mooCalDay',
		monthClass : 'mooCalMonth',
		dayofweekClass : 'mooCalDayofweek',
		highlightClass : 'mooCalHighlight',
		monthHeaderClass : 'mooCalMonthheader',
		selectClass : 'mooCalSelect',
		prevClass : 'mooCalPrev',
		nextClass : 'mooCalNext',
		data : []
	},

	months : {
		0 : 'January',
		1 : 'February',
		2 : 'March',
		3 : 'April',
		4 : 'May',
		5 : 'June',
		6 : 'July',
		7 : 'August',
		8 : 'September',
		9 : 'October',
		10 : 'November',
		11 : 'December'
	},

	dayofweek : {
		0 : 'Sun',
		1 : 'Mon',
		2 : 'Tue',
		3 : 'Wed',
		4 : 'Thu',
		5 : 'Fri',
		6 : 'Sat'
	},

	calData : {

	},

	calendarObject : {

	},

	parsedData : [],

	initialize : function(options)
	{

		// start everything.
		this.setOptions(options);
		$(this.options.container).innerHTML = '';

		var d = new Date();

		d.parse(this.months[d.get('mo')] + ' 1, ' + d.get('Fullyear'));

		this.calData.day = d;
		this.calData.startofmonth = d.getDay();
		this.calData.startofmonthstr = this.dayofweek[d.getDay()];
		this.calData.daysofmonth = d.get('lastdayofmonth');
		this.calData.month = this.months[d.get('mo')];
		this.calData.year = d.get('Fullyear');
		this.calData.prevmonth = d.get('mo') == 0 ? 11 : d.get('mo') - 1;
		this.calData.nextmonth = d.get('mo') == 11 ? 0 : d.get('mo') + 1;
		this.calData.prevyear = d.get('mo') == 0 ? d.get('Fullyear') - 1 : d
				.get('Fullyear');
		this.calData.nextyear = d.get('mo') == 11 ? d.get('Fullyear') + 1 : d
				.get('Fullyear');

		this.createCalendar();
		this.setData(this.options.data);

	},

	setData : function(data)
	{

		this.options.data = data;
		this.parseData();
		var calObj = this;

		$$('.' + this.options.highlightClass).each(function(el)
		{

			el.removeClass(calObj.options.highlightClass);
		});

		$$('.' + this.options.dayClass).each(function(el)
		{

			var t = new Date().parse(el.get('title'));
			if (calObj.inData(t))
			{
				el.addClass(calObj.options.highlightClass);
			}
		});
	},

	parseData : function()
	{

		for ( var i in this.options.data)
		{
			this.parsedData[i] = new Date.parse(this.options.data[i]);
		}
	},

	inData : function(date)
	{

		var indata = false;
		this.parsedData.each(function(pd)
		{

			if (pd + '' == date + '')
			{
				indata = true;
			}
		});

		return indata;
	},

	createCalendar : function()
	{

		// create header
		this.createHeader();

		this.calendarObject.tbody = new Element('tbody', {
			'class' : this.options.tbodyClass
		}).injectInside(this.calendarObject.table);

		var tr = new Element('tr').injectInside(this.calendarObject.tbody);
		for ( var i = 0; i < this.calData.startofmonth; i++)
		{
			new Element('td').set('text', '').injectInside(tr);
		}

		var d = this.calData.day;

		var calObj = this;

		for ( var i = 1; i <= this.calData.daysofmonth; i++)
		{
			if (d.getDay() == 0)
			{
				var tr = new Element('tr')
						.injectInside(this.calendarObject.tbody);
			}

			var day = new Element('td', {
				'class' : this.options.dayClass,
				'title' : this.months[d.get('mo')] + ' ' + i + ', '
						+ d.get('Fullyear')
			}).set('text', i).injectInside(tr);

			day.addEvent('click', function(e)
			{

				$$('.' + calObj.options.selectClass).each(function(el)
				{

					el.removeClass(calObj.options.selectClass);
				});
				this.addClass(calObj.options.selectClass);
			});

			if (this.$events.click)
			{
				day.addEvent('click', function()
				{

					calObj.$events.click[0](this.get('title'), this);
				});
			}

			if (this.$events.mouseover)
			{
				day.addEvent('mouseover', function()
				{

					calObj.$events.mouseover[0](this.get('title'), this);
				});
			}

			if (this.$events.mouseout)
			{
				day.addEvent('mouseout', function()
				{

					calObj.$events.mouseout[0](this.get('title'), this);
				});
			}

			d.increment('day', 1);
		}

		if (d.getDay() != 0)
		{
			for ( var i = d.getDay(); i <= 6; i++)
			{
				new Element('td').set('text', '').injectInside(tr);
			}
		}
	},

	createHeader : function()
	{

		this.calendarObject.table = new Element('table', {
			'width' : '100%',
			'class' : this.options.tableClass
		}).injectInside($(this.options.container));

		this.calendarObject.thead = new Element('thead', {
			'class' : this.options.theadClass
		}).injectInside(this.calendarObject.table);

		this.calendarObject.monthRow = new Element('tr', {
			'class' : this.options.monthClass
		}).injectInside(this.calendarObject.thead);

		var prev = new Element('td', {
			'align' : 'center',
			'class' : this.options.prevClass
		}).set('text', '<<').injectInside(this.calendarObject.monthRow);
		new Element('td', {
			'colspan' : 5,
			'align' : 'center',
			'class' : this.options.monthHeaderClass
		}).set('text', this.calData.month + ' ' + this.calData.year)
				.injectInside(this.calendarObject.monthRow);
		var next = new Element('td', {
			'align' : 'center',
			'class' : this.options.nextClass
		}).set('text', '>>').injectInside(this.calendarObject.monthRow);

		var calObj = this;

		prev.addEvent('click', function()
		{

			// go to previous month
			var p = calObj.calData.day;

			p.parse(calObj.months[calObj.calData.prevmonth] + ' 1, '
					+ calObj.calData.prevyear);

			calObj.updateCal(p);
		});

		next.addEvent('click', function()
		{

			// go to previous month
			var p = calObj.calData.day;

			p.parse(calObj.months[calObj.calData.nextmonth] + ' 1, '
					+ calObj.calData.nextyear);

			calObj.updateCal(p);
		});

		this.calendarObject.headerRow = new Element('tr', {
			'class' : this.options.dayofweekClass
		}).injectInside(this.calendarObject.thead);

		for ( var i = 0; i < 7; i++)
		{
			new Element('td', {
				'align' : 'center'
			}).set('text', this.dayofweek[i]).injectInside(
					this.calendarObject.headerRow);
		}
	},

	updateCal : function(p)
	{

		this.calData.day = p;
		this.calData.startofmonth = p.getDay();
		this.calData.startofmonthstr = this.dayofweek[p.getDay()];
		this.calData.daysofmonth = p.get('lastdayofmonth');
		this.calData.month = this.months[p.get('mo')];
		this.calData.year = p.get('Fullyear');
		this.calData.prevmonth = p.get('mo') == 0 ? 11 : p.get('mo') - 1;
		this.calData.nextmonth = p.get('mo') == 11 ? 0 : p.get('mo') + 1;
		this.calData.prevyear = p.get('mo') == 0 ? p.get('Fullyear') - 1 : p
				.get('Fullyear');
		this.calData.nextyear = p.get('mo') == 11 ? p.get('Fullyear') + 1 : p
				.get('Fullyear');

		$(this.options.container).set('text', '');

		this.createCalendar();

		this.setData(this.options.data);
	}
});

var mooDay = new Class({

	Implements : [ Options, Events ],

	// default options
	// don't change these here but on the instance (unless you want to)
	options : {
		onMouseover : '',
		onMouseout : '',
		onClick : '',
		container : '',
		timeClass : 'mooDayTime',
		textClass : 'mooDayText',
		rowClass : 'mooDayRow',
		date : '',
		data : []
	},

	dayData : {

	},

	dayObject : {

	},

	dayofweek : {
		0 : 'Sunday',
		1 : 'Monday',
		2 : 'Tuesday',
		3 : 'Wednesday',
		4 : 'Thursday',
		5 : 'Friday',
		6 : 'Saturday'
	},

	initialize : function(options)
	{

		// start everything.
		this.setOptions(options);
		$(this.options.container).innerHTML = '';

		this.dayData.date = new Date().parse(this.options.date);
		this.dayData.dayofweek = this.dayofweek[this.dayData.date.getDay()];

		this.createDay();
		this.setData(this.options.data);
	},

	createDay : function()
	{

		this.createHeader();
		var dayObj = this;

		this.dayObject.tbody = new Element('tbody')
				.injectInside(this.dayObject.table);

		for ( var i = 0; i < 24; i++)
		{
			if (i < 10)
			{
				var time = '0' + i + ':00';
			}
			else
			{
				var time = i + ':00';
			}

			var tr = new Element('tr', {
				'class' : this.options.rowClass
			}).injectInside(this.dayObject.tbody);

			var tdtime = new Element('td', {
				'align' : 'center',
				'width' : '100px',
				'class' : this.options.timeClass
			}).set('text', time).injectInside(tr);
			var tdcontent = new Element('td', {
				'align' : 'center',
				'class' : this.options.textClass
			}).set('text', '').injectInside(tr);

			tr.set('title', time);

			if (this.$events.click)
			{
				tr.addEvent('click', function()
				{

					dayObj.$events.click[0](dayObj.options.date, this
							.get('title'), this.getChildren()[1].get('text'));
				});
			}

		}
	},

	createHeader : function()
	{

		this.dayObject.table = new Element('table', {
			'width' : '100%'
		}).injectInside($(this.options.container));

		this.dayObject.thead = new Element('thead')
				.injectInside(this.dayObject.table);

		var tr = new Element('tr').injectInside(this.dayObject.thead);

		new Element('td', {
			'align' : 'center',
			'colspan' : 2
		}).set('text', this.dayData.dayofweek + ' ' + this.options.date)
				.injectInside(tr);
	},

	setData : function(data)
	{

		this.options.data = data;
		var dayObj = this;

		$$('.' + this.options.rowClass).each(
				function(el)
				{

					el.getChildren()[1].set('text', dayObj.getText(el
							.getChildren()[0].get('text')));
				});
	},

	getText : function(time)
	{

		var text = '';
		this.options.data.each(function(data)
		{

			if (data.time == time)
			{
				text = data.text;
			}
		});
		return text;
	}
});
