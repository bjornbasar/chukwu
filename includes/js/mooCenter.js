function mooCenter(el)
{

	// get el size
	var elSize = el.getSize();

	// get window size
	var winSize = window.getSize();

	el.setStyle('position', 'fixed');
	el.setStyle('left', (winSize.x - elSize.x) < 1 ? 0
			: (winSize.x - elSize.x) / 2);
	el.setStyle('top', (winSize.y - elSize.y) < 1 ? 0
			: (winSize.y - elSize.y) / 2);

	window.addEvent('resize', function()
	{

		// get el size
		var elSize = el.getSize();

		// get window size
		var winSize = window.getSize();

		el.setStyle('position', 'fixed');
		el.setStyle('left', (winSize.x - elSize.x) < 1 ? 0
				: (winSize.x - elSize.x) / 2);
		el.setStyle('top', (winSize.y - elSize.y) < 1 ? 0
				: (winSize.y - elSize.y) / 2);
	});
}
