pVids.onclick = (function(mBut) {

	return function() {

		function onSelectedVid(strV) {
			videNoeud('ficheVid');
			document.getElementsByTagName('SOURCE')[0].src = "../lookatthis/" + strV.fic;
			vidSVC = document.getElementById('vid1');
			vidSVC.style.display = "block";
			vidSVC.load();
			$('<p></p>').appendTo('#ficheVid').text("Ce vidéo a été vu " + strV.nbVues + " fois").css({
				'text-align' : 'center'
			});
			$('<p></p>').appendTo('#ficheVid').text("Comment était ce but?").css({
				'text-align' : 'center'
			});
			$('<div id="contEtoile"></div>').appendTo('#ficheVid');
			$("#contEtoile").css({
				'font-size' : 'large',
				'text-align' : 'justify'
			});
			$('<p class="etoile1"></p>').appendTo('#contEtoile');
			$('<p class="etoile2"></p>').appendTo('#contEtoile');
			$('<p class="etoile3"></p>').appendTo('#contEtoile');
			$('<p class="etoile4"></p>').appendTo('#contEtoile');
			$('<p class="etoile5"></p>').appendTo('#contEtoile');
			$("[class*='etoile']").css({
				'display' : 'inline',
				'cursor' : 'pointer',
				'font-size' : 'large'
			}).html("&#10029");

			var eval = getCookie('videoId_eval_' + strV.videoId);

			function colori(event) {
				for (var a = parseInt(event.data.classe); a > 0; a--) {
					$(".etoile" + a).css('color', '#0000FF')
				}
			};
			function decolori(event) {
				for (var a = parseInt(event.data.classe); a > 0; a--) {
					$(".etoile" + a).css('color', '#000000')
				}
			};

			if (eval == null) {
				for (var b = 1; b <= 5; b++) {
					$('.etoile' + b).on("mouseover", {
						classe : b
					}, colori).on("mouseout", {
						classe : b
					}, decolori);
					$('.etoile' + b).on("click", {
						classe : b
					}, function(event) {
						$.post('/scriptsphp/evalueVid.php', {
							videoId : strV.videoId,
							eval : event.data.classe
						});
						setCookie('videoId_eval_' + strV.videoId, event.data.classe, 1000);
						$("[class*='etoile']").off('mouseover');
						$("[class*='etoile']").off('mouseout');
						$("[class*='etoile']").off('click');
					});
				}

			} else {
				switch(parseInt(eval)) {
				case 1:
					$(".etoile1").css('color', '#0000FF');
					break;
				case 2:
					$('.etoile1,.etoile2').css('color', '#0000FF');
					break;
				case 3:
					$('.etoile1,.etoile2,.etoile3').css('color', '#0000FF');
					break;
				case 4:
					$(".etoile1,.etoile2,.etoile3,.etoile4").css('color', '#0000FF');
					break;
				case 5:
					$('[class*=etoile]').css('color', '#0000FF');
					break;

				}
			}//fin else

			$('<div id="divAngleOk"></div>').appendTo('#ficheVid').append($('<div></div>').attr("class", "selectAngleOk").append($('<img src="/images/icones/check.png" width="16" height="16"></img>'), $('<div></div>').text("That angle shows the goal")).click(function() {
				$.post('/scriptsphp/changeAngleOk.php', {
					changeAngle : 1,
					videoId : strV.videoId
				});
			}), $('<div></div>').attr("class", "selectAngleOk").append($('<img src="/images/icones/delete.png" width="16" height="16"></img>'), $('<div></div>').text("That angle doesn't show the goal")).click(function() {
				$.post('/scriptsphp/changeAngleOk.php', {
					changeAngle : -1,
					videoId : strV.videoId
				});
			}));

			$('<div id="scoreBut"></div>').appendTo('#ficheVid').text(parseInt(strV.eval).toFixed(1) + " / 5").css({
				"font-size" : 'larger',
				'text-align' : 'center'
			});
			$.post('/scriptsphp/incrementeVidNbVues.php', {
				videoId : strV.videoId
			});

		}

		// fin  onSelectedVid(strV)
		vidIndex = 0;
		strV = mBut.video[vidIndex];
		construitDialogue();

		dial = document.getElementById('divDialogue');
		div12 = document.createElement('DIV');
		$(div12).css('float', 'left').attr("id", "vidCont");

		dial.appendChild(div12);
		$('<div id="ficheVid"></div>').insertAfter(div12);

		$(dial).prepend('<div id="divEnteteVid"></div>');

		mVid = document.createElement('VIDEO');
		mVid.videoWidth = 320;
		mVid.videoHeight = 240;
		mVid.controls = "controls";
		mVid.load();
		mVid.id = "vid1";
		mSource = document.createElement('SOURCE');
		mSource.type = "video/mp4";
		mVid.appendChild(mSource);
		div12.appendChild(mVid);

		for (var a = 0; a < mBut.video.length; a++) {
			$('<div id=lienCam_' + mBut.video[a].cam + '<div>').appendTo('#divEnteteVid').text('Cam ' + mBut.video[a].cam).click({
				strV : mBut.video[a]
			}, function(event) {
				onSelectedVid(event.data.strV)
			}).css({
				'display' : 'inline',
				'padding-left' : '10px',
				'cursor' : 'pointer'
			});
		}

		onSelectedVid(strV);

		if (mVid.error) {
			switch (mVid.error.code) {
			case MEDIA_ERR_ABORTED:
				alert("You stopped the video.");
				break;
			case MEDIA_ERR_NETWORK:
				alert("Network error - please try again later.");
				break;
			case MEDIA_ERR_DECODE:
				alert("Video is broken..");
				break;
			case MEDIA_ERR_SRC_NOT_SUPPORTED:
				alert("Sorry, your browser can't play this video.");
				break;
			}
		}

	};
})(mBut); 

                            //fin pVids.onclick();
                            
