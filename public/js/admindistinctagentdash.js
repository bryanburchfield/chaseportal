Chart.pluginService.register({
    beforeDraw: function (chart) {
        if (chart.config.options.elements.center) {
            //Get ctx from string
            var ctx = chart.chart.ctx;

            //Get options from the center object in options
            var centerConfig = chart.config.options.elements.center;
            var fontStyle = centerConfig.fontStyle || 'Arial';
            var txt = centerConfig.text;
            var color = Master.tick_color;
            var sidePadding = centerConfig.sidePadding || 20;
            var sidePaddingCalculated = (sidePadding / 100) * (chart.innerRadius * 2)
            //Start with a base font of 30px
            ctx.font = "40px " + fontStyle;

            //Get the width of the string and also the width of the element minus 10 to give it 5px side padding
            var stringWidth = ctx.measureText(txt).width;
            var elementWidth = (chart.innerRadius * 2) - sidePaddingCalculated;

            // Find out how much the font can grow in width.
            var widthRatio = elementWidth / stringWidth;
            var newFontSize = Math.floor(20 * widthRatio);
            var elementHeight = (chart.innerRadius * 2);

            // Pick a new font size so it will not be larger than the height of label.
            var fontSizeToUse = Math.min(newFontSize, elementHeight);

            //Set font settings to draw it correctly.
            ctx.textAlign = 'center';
            ctx.textBaseline = 'top';
            var centerX = ((chart.chartArea.left + chart.chartArea.right) / 2);
            var centerY = ((chart.chartArea.top + chart.chartArea.bottom) / 1.7);
            ctx.font = fontSizeToUse + "px " + fontStyle;
            ctx.fillStyle = color;

            //Draw text in center
            ctx.fillText(txt, centerX, centerY);
        }
    }
});

var Dashboard = {

	chartColors: {
	    red: 'rgb(255,67,77)',
	    blue: 'rgb(1,1,87)',
	    orange: 'rgb(228,154,49)',
	    green: 'rgb(51,160,155)',
	    grey: 'rgb(98,98,98)',
	    yellow: 'rgb(255, 205, 86)',
	    lightblue: 'rgb(66, 134, 244)'
	},

	datefilter: document.getElementById("datefilter").value,
	databases: '',
	time: new Date().getTime(),

	init:function(){

		/// dashboard widgets
		// $.when(this.call_volume(this.datefilter, this.chartColors)).done(function () {
		//     $('.preloader').fadeOut('slow');
		//     Master.check_reload();
		// });
	},
}
$(document).ready(function(){
	Dashboard.init();
});