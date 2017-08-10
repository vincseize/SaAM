
<style type="text/css"> @import "css/jquery.countdown.css"; </style>
<script type="text/javascript" src="js/jquery.countdown.js"></script>

<script>
	$(function () {
		var nowDay = new Date();
		var austDay = new Date(<?php echo SAAM_NEXT_VERSION_DATE; ?> - 1, <?php echo SAAM_NEXT_VERSION_DAY; ?>, 14, 0, 0); // Y, Mois - 1, Day, H, M, S

		if (austDay <= nowDay)
			countExpired();
		else {
			$('#versionCountdown').countdown({
				until: austDay,
				compact: false,
				layout: '{dn} {dl} {hnn}{sep}{mnn}{sep}{snn} {desc}',
				description: 'until release <?php echo SAAM_NEXT_VERSION;?>',
				onExpiry: countExpired
			});
		}
	});

	function countExpired() {
		$('#versionCountdown').html('Release <?php echo SAAM_NEXT_VERSION;?> will be out soon!');
	}
</script>

<?php
$drinfos = explode(', ', SAAM_NEXT_VERSION_DATE);
$nextVersionDateStr = SAAM_NEXT_VERSION_DAY .'/'. $drinfos[1] .'/'. $drinfos[0];
?>
<div class="colorDiscret" id="versionCountdown" title="Date for SaAM v<?php echo SAAM_NEXT_VERSION;?>: <?php echo $nextVersionDateStr; ?>"></div>
