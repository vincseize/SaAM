var chart;

// DOCUMENT READY
$(function () {
	// Changement de type de données (menu header)
	$('.headerBtn').click(function(){
		var chartType = $(this).attr('chartType');
		var dataType  = $(this).attr('dataType');
		$('.headerBtn').removeClass('ui-state-activeFake');
		$(this).addClass('ui-state-activeFake');
		$('.titleOverviewType').html(dataType.toUpperCase());
		getChartdata(chartType, dataType);
	});

});
// FIN DU DOCUMENT READY


// Récupération de données
//
function getChartdata(chartType, dataType) {
	$.post("actions/get_chart_data.php", {'action':'getChartData_'+chartType+'Chart', 'dataType':dataType, 'filterProj':limitToProj}, function(retour){
		console.log(retour);
		if (retour.error != 'OK') {
			$('#chartContainer').html('<div class="red big pad20">'+retour.message+'</div>');
			return;
		}
		if (chartType == 'line')
			display_LineChartData(retour.data);
		if (chartType == 'pie')
			display_PieChartData(retour.data);
	}, 'json');
}


// AFFICHAGE DES DONNÉES EN LIGNES
//
function display_LineChartData(data) {
	$('#chartContainer').highcharts({
		title: {
			text: data.chartTitle
		},
		subtitle: {
			text: data.chartSubTitle
		},
		series: data.allSeries,
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'top',
			x: -10,
			y: 100,
			borderWidth: 0
		},
		credits: {
			enabled: false
		},
		/////////// SPÉCIFIQUE AU TYPE DE GRAPHIQUE ///////////
		chart: {
			type: 'line'
		},
		tooltip: {
			formatter: function() {
				return '<b>'+ this.series.name +'</b><br/>'+
				this.x +': '+ this.y;
			}
		},
		xAxis: {
			categories: data.axe_X
		},
		yAxis: {
			title: { text: data.dataDescr }
		}
	});
};

// AFFICHAGE DES DONNÉES EN LIGNES
//
function display_PieChartData(data) {
	$('#chartContainer').highcharts({
		title: {
			text: data.chartTitle
		},
		subtitle: {
			text: data.chartSubTitle
		},
		series: data.allSeries,
		legend: {
			layout: 'vertical',
			align: 'right',
			verticalAlign: 'top',
			x: -10,
			y: 100,
			borderWidth: 0,
			useHTML: true,
			labelFormatter: function() {
				return '<div style="width:200px"><span style="float:left">' + this.name + '</span><span style="float:right">' + this.detail + '</span></div>';
			}
		},
		credits: {
			enabled: false
		},
		/////////// SPÉCIFIQUE AU TYPE DE GRAPHIQUE ///////////
		chart: {
			type: 'pie'
		},
		tooltip: {
			pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b><br /><i>{point.detail}</i>'
		},
		plotOptions: {
			pie: {
				allowPointSelect: true,
				cursor: 'pointer',
				showInLegend: true,
				ignoreHiddenPoint: true,
				dataLabels: {
					enabled: true,
					formatter: function() {
						if (this.percentage < 0.01) return null;
						return '<b>'+this.point.name+'</b>: '+ Math.round(this.percentage) +' %';
					},
					style: {
						color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
					}
				}
			}
		}
	});
}