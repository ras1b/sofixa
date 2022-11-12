$('.superwheel').superWheel({
	slices: slices,
	text: {
		size: 20,
		color: '#ffffff',
		offset: 14,
		letterSpacing: 4,
		orientation: 'h',
		arc: true
	},
	line: {
		width: 5,
		color: "#ffca19"
	},
	outer: {
		width: 10,
		color: "#ffca19"
	},
	inner: {
		width: 10,
		color: "#ffca19"
	},
	marker: {
		background: "#ed2024",
		animate: 1
	},
	width: 400,
	selector: "id"
});

var tick_audio = new Audio('/apps/main/themes/sofixa/public/assets/css/plugins/superwheel/tick.mp3');
var win_audio = new Audio('/apps/main/themes/sofixa/public/assets/css/plugins/superwheel/win.mp3');
var lose_audio = new Audio('/apps/main/themes/sofixa/public/assets/css/plugins/superwheel/lose.mp3');

function getData(ajaxurl) {
	return $.ajax({
		url: ajaxurl,
		type: 'GET',
		error: function() {
			if (wheelSpining) {
				wheelSpining = false;
			}
			swal.fire({
				title: lang.alert_title_error,
				text: lang.alert_message_something_went_wrong,
				type: 'error',
				confirmButtonColor: '#02b875',
				confirmButtonText: lang.alert_btn_ok
			});
		}
	});
};

var wheelSpining = false;

$('#playGame').on('click', function() {
	if (wheelSpining == false) {
		wheelSpining = true;
		getData('/apps/main/public/ajax/lottery.php?action=play&id=' + lotteryID).then(function(ajaxResult) {
			if (ajaxResult) {
				ajaxResult = jQuery.parseJSON(ajaxResult);
				if (ajaxResult["data"] == 'error_login') {
					swal.fire({
						title: lang.alert_title_error,
						text: 'Please login to play the game',
						type: 'error',
						confirmButtonColor: '#02b875',
						confirmButtonText: 'Login'
					}).then(function() {
						window.location = '/login';
					});
				} else if (ajaxResult["data"] == 'error_credit') {
					swal.fire({
						title: lang.alert_title_error,
						text: lang.alert_message_lottery_credit_error,
						type: 'error',
						confirmButtonColor: '#02b875',
						confirmButtonText: lang.alert_btn_buy_credit
					}).then(function() {
						window.location = '/credit/buy';
					});
				} else if (ajaxResult["data"] == 'error_duration') {
					swal.fire({
						title: lang.alert_title_error,
						text: lang.alert_message_lottery_error_duration.replace('%date%', ajaxResult["variable"]),
						type: 'error',
						confirmButtonColor: '#02b875',
						confirmButtonText: lang.alert_btn_ok
					});
				} else {
					$('.superwheel').superWheel('start', 'id', parseInt(ajaxResult["data"]));
				}
			}
		});
	}
});

$('.superwheel').superWheel('onStart', function(results) {
	$('#playGame').text(lang.spinning).attr('disabled', 'disabled').addClass('disabled').css('cursor', 'no-drop');
});

$('.superwheel').superWheel('onStep', function(results) {
	tick_audio.pause();
	tick_audio.currentTime = 0;
	tick_audio.play();
});

$('.superwheel').superWheel('onComplete', function(results) {
	tick_audio.pause();
	tick_audio.currentTime = 0;
	if (results.type === 3) {
		lose_audio.pause();
		lose_audio.currentTime = 0;
		lose_audio.volume = 0.25;
		lose_audio.play();
		swal.fire({
			title: lang.alert_title_you_lost,
			text: lang.alert_message_lottery_you_lost,
			type: 'error',
			confirmButtonColor: '#02b875',
			confirmButtonText: lang.alert_btn_ok
		}).then(function() {
			lose_audio.pause();
			lose_audio.currentTime = 0;
		});
	} else {
		getData('/apps/main/public/ajax/lottery.php?action=credit').then(function(ajaxResult) {
			win_audio.pause();
			win_audio.currentTime = 0;
			win_audio.volume = 0.25;
			win_audio.play();
			swal.fire({
				title: lang.alert_title_you_win,
				html: lang.alert_message_lottery_you_win.replace('%prize%', results.text) + ' ' + (results.type == 2 ? lang.alert_message_lottery_you_win_chest + ' ' : '') + lang.alert_message_lottery_you_win_credit.replace('%credit%', ajaxResult),
				type: 'success',
				confirmButtonColor: '#02b875',
				confirmButtonText: lang.alert_btn_ok
			}).then(function() {
				win_audio.pause();
				win_audio.currentTime = 0;
			});
		});
	}
	$('#playGame').text(lang.play_again).removeAttr('disabled').removeClass('disabled').css('cursor', 'pointer');
	wheelSpining = false;
});
