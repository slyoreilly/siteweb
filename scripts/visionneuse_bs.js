document.addEventListener("DOMContentLoaded", function () {
	mediaPlayer = document.getElementById('media-video');
	$( ".play-btn" ).show();
	$( "#media-play-list" ).show();
}, false);



function loadVideo() {
	
	$( ".play-btn" ).show();
	$( "#media-play-list" ).show();
		mediaPlayer.pause();
    for (var i = 0; i < arguments.length; i++) {
        var file = arguments[i].split('.');
        var ext = file[file.length - 1];
        // Check if this media can be played
        if (canPlayVideo(videofile)) {
            // Reset the player, change the source file and load it
			$('.player').remove();
		
            mediaPlayer.src = videofile;
            mediaPlayer.load();
            break;
        }
    }
	$('.media-box').find('.fa-pause').addClass('fa-play').removeClass('fa-pause');


}

function canPlayVideo(ext) {
    var ableToPlay = mediaPlayer.canPlayType('/lookatthis/' + ext);
    if (ableToPlay == '')
        return false;
    else
        return true;
}

var flag=0;

(function($) {

	$.fn.videoPlayer = function(options) {
		
				
		var settings = {  
			playerWidth : '1', // Default is 95%
			videoClass : 'video'  // Video Class
		};
		
		// Extend the options so they work with the plugin
		if(options) {
			$.extend(settings, options);
		}
		
		//closing entire video
				    mediaPlayer = document.getElementById('media-video');
				$('.close-btn').remove();
		
		
		
		// For each so that we keep chainability.
		return this.each(function() {	
			
			$(this)[0].addEventListener('loadedmetadata', function() {
				// Basic Variables 
				var $this = $(this);
				var $settings = settings;
				$( "#btn-play-centre" ).hide();
				$(".fa-play").addClass('fa-pause').removeClass('fa-play');
			var $that = $this.parent('.'+$settings.videoClass);
			if($('.video').length<1){
				// Wrap the video in a div with the class of your choosing
				$this.wrap('<div class="'+$settings.videoClass+'"></div>');
				$('.'+$settings.videoClass).prepend('<h2 id="titreCam"></h2>');
			}
				// Select the div we just wrapped our video in for easy selection.
				
				// The Structure of our video player
				if($('.player').length<1){
				
				$( '<div class="player container">'
				+'<div class="row h-50">'
					 + '<div class="time col-3">'
				         + '<span class="ctime">00:00</span>' 
				         + '<span class="stime"> / </span>'
				         + '<span class="ttime">00:00</span>'
				     + '</div>'		
					 			
				     + '<div class="progress col-7">'
						+ '<div class="progress-bar">'
				         + '<div class="button-holder">'
				           + '<div class="progress-button">'
						   		+ '<div class="timer-wrap">'
									+ '<span class="ctime progressbar-timer">00:00</span>'
									+ '<span class="timer-triangle"></span>' 
						   		+ ' </div>'
						   + ' </div>'
				         + '</div>'
				       	+ '</div>'				       
				     + '</div>'
					 
					 + '<div class="site-logo col-2"></div><br><br>'
					  + '</div>'
					   +'<div class="d-flex  h-50 justify-content-around flex-row w-100">'
					 + '<div class="flex-fill flex-grow-1">'
					 +'<i class="fas fa-play"></i>'
				     + '</div>'
				     + '<div class="flex-fill flex-grow-1">|</div>'
					 
					+ '<div class="flag-div flex-fill flex-grow-1">'
                     + '<i id="flag-btn" class="fas fa-flag" title="flag"></i>'
                     + '<div class="flag-modal">'
					 		+ '<span class="top-triangle"></span>'
							+ '<span class="modal-bg">'
								+ '<img id="drapeauVert" src="/images/green-flag.png" style="margin: 0 10px 0 0;">'
								+ '<img id="drapeauRouge" src="/images/red-flag.png">'
							+ '</span>'
					 + '</div>'		
                     + '</div>'
					 			 
					 		+ '<div id="rate-div" class=" flex-fill flex-grow-1">'		 
                     + '<i id="rate-btn" class="fas fa-star" title="rate"></i>'
                      + '<div class="rate-modal2">'
					 		
							+ '<div id="rating-star" style="cursor: pointer;"></div>'
					 + '</div>'		
                     + '</div>'
					
					 			+ '<div id="share-div" class=" flex-fill flex-grow-1">'		 
                      + '<i  id="share-btn"  class="fas fa-share-square"  title="sharing"></i>	 '
                       + '<div class="share-modal2">'
					 		+ '<span class="top-triangle" style="margin: -5px 0 0 59px;"></span>'
							+ '<span class="modal-bg">'
								+ '<img id="fbLogo" src="/images/fb-icon.png" style="margin: 0 20px 5px 0;">'
								+ '<img id="twitterLogo" src="/images/twitter-icon.png" style="margin: 0 0 5px 0;"> <br>'
								+ '<img id="googleLogo" src="/images/gmail-icon.png">'
								+ '<img src="/images/youtube-icon.png" style="margin: 0 10px;">'
								+ '<a id="downloadLink" href='+'/lookatthis/'+getValue('videofile')+'.mp4'+' download><img id="downloadLogo" src="/images/download-icon.png"></a>'
							+ '</span>'
					 + '</div>'	
                     + '</div>'	
					 			
                
					 					 
                     + '<div class="flex-fill flex-grow-1">|</div>'
					 + '<div class="flex-fill flex-grow-1 volume">'
				         + '<i class="fas fa-volume-up"></i>'
				          + '<div class="volume-holder">'
				         + '<div class="volume-bar-holder">'
				           + '<div class="volume-bar">'
				             + '<div class="volume-button-holder">'	
				             + '</div>'
				           + '</div>'
				         + '</div>'
				       + '</div>'
				       + '</div>'
				     
				       
				    
				     + '<div class="flex-fill fullscreen2 flex-grow-1"> '
				       + '<a href="#"> <i class="fas fa-expand"></i></a>'
				     + '</div>'
								 
					 		+ '<div class=" flex-fill flex-grow-1">'		 
                    
					 + '<i id="setting" title="setting" class="fas fa-cog"></i>'
					 + '</div>'					 
					
					  + '</div>'
					 +'<!--div id="progress"></div-->'
				   + '</div>'
				  ).appendTo($('.video'));
				$('i').addClass("text-info");
				}
				$('#rate-btn').hover(function(){
					$('.rate-modal2').show();
				},function(){
				});
				$('.rate-modal2').hover(function(){
				},function(){
					$('.rate-modal2').hide();
				});
				$('#flag-div').hover(function(){
					$('.flag-modal').show();
				},function(){
					$('.flag-modal').hide();
                    });
                $('#share-btn').hover(function () {
                    $('.share-modal2').show();
                }, function () {
                   
				});
				$('.share-modal2').hover(function () {
                    
                }, function () {
                    $('.share-modal2').hide();
                });
				//$('#rating-star').raty();
				// Width of the video
				$videoWidth = $this.width();
				$that.width($videoWidth+'px');
				// Set width of the player based on previously noted settings
				//$that.find('.player').css({'width' : ($settings.playerWidth*100)+'%', 'left' : ((100-$settings.playerWidth*100)/2)+'%'}).show();

				// Video information
				var $spc = $(this)[0], // Specific video
					$duration =$spc.duration, // Video Duration
					$volume = $spc.volume, // Video volume
					currentTime;
					console.log($duration+" durée");
				
				// Some other misc variables to check when things are happening
				var $mclicking = false, 
				    $vclicking = false, 
				    $vidhover = false,
				    $volhover = false, 
				    $playing = false, 
				    $drop = false,
				    $begin = false,
				    $draggingProgess = false,
				    $storevol,	
				    x = 0, 
				    y = 0, 
				    vtime = 0, 
				    updProgWidth = 0, 
				    volume = 0;
				    
				// Setting the width, etc of the player
				var $volume = $spc.volume;
				
				// So the user cant select text in the player
				$that.bind('selectstart', function() { return false; });
						
				// Set some widths
				var progWidth = $that.find('.progress').width();
				
				//alert(progWidth);
				

				var bufferLength = function() {
				
					// The buffered regions of the video
					var buffered = $spc.buffered;
					
					// Rest all buffered regions everytime this function is run
					$that.find('[class^=buffered]').remove();
					
					// If buffered regions exist
					if(buffered.length > 0) {
							
						// The length of the buffered regions is i
						var i = buffered.length;
							
						while(i--) {
							// Max and min buffers
							$maxBuffer = buffered.end(i);
							$minBuffer = buffered.start(i);
									
							// The offset and width of buffered area				
							var bufferOffset = ($minBuffer / $duration) * 100;			
							var bufferWidth = (($maxBuffer - $minBuffer) / $duration) * 100;
											
							// Append the buffered regions to the video
							$('<div class="buffered"></div>').css({"left" : bufferOffset+'%', 'width' : '100%'}).appendTo($that.find('.progress'));
							
						}
					}
				} ;
				
				// Run the buffer function
				bufferLength();
				$ignore=false;
				// The timing function, updates the time.
				var timeUpdate = function($ignore) {
					
				if(!progWidth>0){
					progWidth=340;
				}
					//console.log("Courant le temps haut-date");
					
					// The current time of the video based on progress bar position
					var time = Math.round(($('.progress-bar').width() / progWidth) * $duration);
					
					//console.log(time+"---"+$('.progress-bar').width()+"---"+progWidth+"---"+$duration+"---");
					// The 'real' time of the video
					var curTime = $spc.currentTime;
					
					// Seconds are set to 0 by default, minutes are the time divided by 60
					// tminutes and tseconds are the total mins and seconds.
					var seconds = 0,
						minutes = Math.floor(time / 60),
						tminutes = Math.floor(($duration) / 60),
						tseconds = Math.floor(($duration) - (tminutes*60));
					
					// If time exists (well, video time)
					if(time) {
						// seconds are equal to the time minus the minutes
						seconds = Math.round(time) - (60*minutes);
						
						// So if seconds go above 59
						if(seconds > 59) {
							// Increase minutes, reset seconds
							seconds = Math.round(time) - (60*minutes);
							if(seconds == 60) {
								minutes = Math.round(time / 60); 
								seconds = 0;
							}
						}
						
					} 
					
					// Updated progress width
					updProgWidth = (curTime / $duration) * progWidth;
					
					// Set a zero before the number if its less than 10.
					if(seconds < 10) { seconds = '0'+seconds; }
					if(tseconds < 10) { tseconds = '0'+tseconds; }
					
					// A variable set which we'll use later on
					if($ignore != true) {
						$that.find('.progress-bar').css({'width' : updProgWidth+'px'});
						$that.find('.progress-button').css({'left' : (updProgWidth-$that.find('.progress-button').width())+'px'});
					}
					
					// Update times
					$that.find('.ctime').html(minutes+':'+seconds) ;
					$that.find('.ttime').html(tminutes+':'+tseconds);
				
					// If playing update buffer value
					if($spc.currentTime > 0 && $spc.paused == false && $spc.ended == false) {
						bufferLength();
					}
					
				};
				
				// Run the timing function twice, once on init and again when the time updates.
				timeUpdate(false);
				$spc.addEventListener('timeupdate', timeUpdate);
								
				// When the user clicks play, bind a click event	
				$('.fa-play,.fa-pause , #media-video, .play-btn , .thumbnail-content').off('click').on('click', function(){arreteDemarre();});
				//$('#media-video').off('click').on('click', function(){arreteDemarre();});
				
				function arreteDemarre(){
					console.log("arrete demarre");
					$( "#btn-play-centre" ).hide();
					if($spc.currentTime > 0 && $spc.paused == false && $spc.ended == false && !$(this).hasClass('thumbnail-content')) {
						$playing = false;
					} else { $playing = true;}
					//alert("playing devient? "+$playing);
					// If playing, etc, change classes to show pause or play button
					if($playing == false) {
						$spc.pause();
						
						$(".fa-pause").addClass('fa-play').removeClass('.fa-pause');
						bufferLength();
						//$( ".play-btn" ).show();
						$( "#btn-play-centre" ).show();
						$( "#media-play-list" ).show();						
					} else {
						console.log("devrait lire");
							
						$begin = true;
						$spc.play();
						
						$(".fa-play").addClass('fa-pause').removeClass('fa-play');
						
						$( "#btn-play-centre" ).hide();
						//$( "#media-play-list" ).hide();
					}
						if ((navigator.userAgent.indexOf('iPhone') != -1) || (navigator.userAgent.indexOf('iPod') != -1) || (navigator.userAgent.indexOf('iPad') != -1)) {
					//alert($('source').attr('src'));
						document.location = $('source').attr('src');//"http://www.syncstats.com/lookatthis/" + getValue('videofile');
						} 
					
					}
				
				
				
				
				// Bind a function to the progress bar so the user can select a point in the video
				$that.find('.progress').bind('mousedown', function(e) {
					
					// Progress bar is being clicked
					$mclicking = true;
					// If video is playing then pause while we change time of the video
					if($playing == true) {
						$spc.pause();
					}
					
					// The x position of the mouse in the progress bar 
					x = e.pageX - $that.find('.progress').offset().left;
					
					// Update current time
					currentTime = (x / progWidth) * $duration;
					
					$spc.currentTime = currentTime;
					
				});
				
				// When the user clicks on the volume bar holder, initiate the volume change event
				$that.find('.volume-bar-holder').bind('mousedown', function(e) {
					
					// Clicking of volume is true
					$vclicking = true;
					
					// Y position of mouse in volume slider
					y = $that.find('.volume-bar-holder').height() - (e.pageY - $that.find('.volume-bar-holder').offset().top);
					
					// Return false if user tries to click outside volume area
					if(y < 0 || y > $(this).height()) {
						$vclicking = false;
						return false;
					}
					
					// Update CSS to reflect what's happened
					$that.find('.volume-bar').css({'height' : y+'px'});
					$that.find('.volume-button').css({'top' : (y-($that.find('.volume-button').height()/2))+'px'});
					 
					// Update some variables
					$spc.volume = $that.find('.volume-bar').height() / $(this).height();
					$storevol = $that.find('.volume-bar').height() / $(this).height();
					$volume = $that.find('.volume-bar').height() / $(this).height();
					
					// Run a little animation for the volume icon.
					volanim();
					
				});
				
				// A quick function for binding the animation of the volume icon
				var volanim = function() {
				
					// Check where volume is and update class depending on that.
					for(var i = 0; i < 1; i += 0.1) {
									
						var fi = parseInt(Math.floor(i*10)) / 10;
						var volid = (fi * 10)+1;
						
						if($volume == 1) {
							if($volhover == true) {
								$that.find('.volume-icon').removeClass().addClass('volume-icon volume-icon-hover v-change-11');
							} else {
								$that.find('.volume-icon').removeClass().addClass('volume-icon v-change-11');
							}
						}
						else if($volume == 0) {
							if($volhover == true) {
								$that.find('.volume-icon').removeClass().addClass('volume-icon volume-icon-hover v-change-1');
							} else {
								$that.find('.volume-icon').removeClass().addClass('volume-icon v-change-1');
							}
						}
						else if($volume > (fi-0.1) && volume < fi && !$that.find('.volume-icon').hasClass('v-change-'+volid)) {
							if($volhover == true) {
								$that.find('.volume-icon').removeClass().addClass('volume-icon volume-icon-hover v-change-'+volid);	
							} else {
								$that.find('.volume-icon').removeClass().addClass('volume-icon v-change-'+volid);	
							}
						}		
						
					}
				};
				// Run the volanim function
				volanim();
				
				// Check if the user is hovering over the volume button
				$that.find('.volume').hover(function() {
					$volhover = true;
				}, function() {
					$volhover = false;
				});
				
				//timer-visibility
				$('.progress-button').hover(function(){	
					$('.timer-wrap').css({'visibility' : 'visible'});						
				},function(){
					$('.timer-wrap').css({'visibility' : 'hidden'});				
				});	
				
				
				
				// For usability purposes then bind a function to the body assuming that the user has clicked mouse
				// down on the progress bar or volume bar
				$('body, html').bind('mousemove', function(e) {
					
					// Hide the player if video has been played and user hovers away from video
					if($begin == true) {
						$that.hover(function() {
							$that.find('.player').stop(true, false).animate({'opacity' : '1'}, 0.5);
							$('#media-play-list').stop(true, false).animate({'opacity' : '1'}, 0.5);
						}, function() {
							$that.find('.player').stop(true, false).animate({'opacity' : '0'}, 0.5);
							$('#media-play-list').stop(true, false).animate({'opacity' : '0'}, 0.5);
						});
						
						$('#media-play-list').hover(function() {
							$that.find('.player').stop(true, false).animate({'opacity' : '1'}, 0.5);
							$('#media-play-list').stop(true, false).animate({'opacity' : '1'}, 0.5);
						}, function() {
							$that.find('.player').stop(true, false).animate({'opacity' : '0'}, 0.5);
							$('#media-play-list').stop(true, false).animate({'opacity' : '0'}, 0.5);
						});						
					}
					
					// For the progress bar controls
					if($mclicking == true) {	
						$('.timer-wrap').css({'visibility' : 'visible'});
						// Dragging is happening
						$draggingProgress = true;
						// The thing we're going to apply to the CSS (changes based on conditional statements);
						var progMove = 0;
						// Width of the progress button (a little button at the end of the progress bar)
						var buttonWidth = $that.find('.progress-button').width();
						
						// Updated x posititon the user is at
						x = e.pageX - $that.find('.progress').offset().left;
						
						// If video is playing
						if($playing == true) {
							// And the current time is less than the duration				
							if(currentTime < $duration) {		
								// Then the play-pause icon should definitely be a pause button 
								$that.find('.fa-play').addClass('fa-pause').removeClass('fa-play');
							}
						}
						
						
						if(x < 0) { // If x is less than 0 then move the progress bar 0px
							progMove = 0;
							$spc.currentTime = 0;
						} 
						else if(x > progWidth) { // If x is more than the progress bar width then set progMove to progWidth
							$spc.currentTime = $duration;
							progMove = progWidth;	
						}
						else { // Otherwise progMove is equal to the mouse x coordinate
							progMove = x;
							currentTime = (x / progWidth) * $duration;
							$spc.currentTime = currentTime;	
						}
						
						// Change CSS based on previous conditional statement
						$that.find('.progress-bar').css({'width' : progMove+'px'});
						$that.find('.progress-button').css({'left' : (progMove-buttonWidth)+'px'});
						
					}
					
					// For the volume controls
					if($vclicking == true) {	
						
						// The position of the mouse on the volume slider
						y = $that.find('.volume-bar-holder').height() - (e.pageY - $that.find('.volume-bar-holder').offset().top);
						
						// The position the user is moving to on the slider.
						var volMove = 0;
						
						// If the volume holder box is hidden then just return false
						if($that.find('.volume-holder').css('display') == 'none') {
							$vclicking = false;
							return false;
						}
						
						// Add the hover class to the volume icon
						if(!$that.find('.volume-icon').hasClass('volume-icon-hover')) {
							$that.find('.volume-icon').addClass('volume-icon-hover');
						}
						
						
						if(y < 0 || y == 0) { // If y is less than 0 or equal to 0 then volMove is 0.
							
							$volume = 0; 
							volMove = 0;
							
							$that.find('.volume-icon').removeClass().addClass('volume-icon volume-icon-hover v-change-11');
							
						} else if(y > $(this).find('.volume-bar-holder').height() || (y / $that.find('.volume-bar-holder').height()) == 1) { // If y is more than the height then volMove is equal to the height
							
							$volume = 1; 
							volMove = $that.find('.volume-bar-holder').height();
							
							$that.find('.volume-icon').removeClass().addClass('volume-icon volume-icon-hover v-change-1');
							
						} else { // Otherwise volMove is just y
						
							$volume = $that.find('.volume-bar').height() / $that.find('.volume-bar-holder').height();
							volMove = y;
							
						}
					
						// Adjust the CSS based on the previous conditional statmeent
						$that.find('.volume-bar').css({'height' : volMove+'px'});
						$that.find('.volume-button').css({'top' : (volMove+$that.find('.volume-button').height())+'px'});
						
						// Run the animation function
						volanim();
						
						// Change the volume and store volume
						// Store volume is the volume the user last had in place
						// in case they want to mute the video, unmuting will then
						// return the user to their previous volume.
						$spc.volume = $volume;
						$storevol = $volume;
						
						
					}
					
					// If the user hovers over the volume controls, then fade in or out the volume
					// icon hover class
					
					if($volhover == false) {
						
						$that.find('.volume-holder').stop(true, false).fadeOut(100);
						$that.find('.volume-icon').removeClass('volume-icon-hover');	
						
					}
					
					else {
						$that.find('.volume-icon').addClass('volume-icon-hover');
						$that.find('.volume-holder').fadeIn(100);			
					}
					
						
				})	;
				
				// When the video ends the play button becomes a pause button
				$spc.addEventListener('ended', function() {
					
					$playing = false;
					
					// If the user is not dragging
					if($draggingProgress == false) {
						$that.find('.fa-pause').addClass('fa-play').removeClass('fa-pause');
						$( ".play-btn" ).show();
						$( "#media-play-list" ).show();						
					}
					
				});
				
				// If the user clicks on the volume icon, mute the video, store previous volume, and then
				// show previous volume should they click on it again.
				$that.find('.volume-icon').bind('mousedown', function() {
					
					$volume = $spc.volume; // Update volume
					
					// If volume is undefined then the store volume is the current volume
					if(typeof $storevol == 'undefined') {
						 $storevol = $spc.volume;
					}
					
					// If volume is more than 0
					if($volume > 0) {
						// then the user wants to mute the video, so volume will become 0
						$spc.volume = 0; 
						$volume = 0;
						$that.find('.volume-bar').css({'height' : '0'});
						volanim();
					}
					else {
						// Otherwise user is unmuting video, so volume is now store volume.
						$spc.volume = $storevol;
						$volume = $storevol;
						$that.find('.volume-bar').css({'height' : ($storevol*100)+'%'});
						volanim();
					}
					
					
				});
				
				
				// If the user lets go of the mouse, clicking is false for both volume and progress.
				// Also the video will begin playing if it was playing before the drag process began.
				// We're also running the bufferLength function
				$('body, html').bind('mouseup', function(e) {
					
					$mclicking = false;
					$vclicking = false;
					$draggingProgress = false;
					
					if($playing == true) {	
						$spc.play();
					}
					
					bufferLength();
					
					
				});
				
				// Check if fullscreen supported. If it's not just don't show the fullscreen icon.
				if(!$spc.requestFullscreen && !$spc.mozRequestFullScreen && !$spc.webkitRequestFullScreen) {
					$('.fullscreen2').hide();
				}
				
				// Requests fullscreen based on browser.
				$('.fullscreen2').click(function() {
					console.log("Clique FS");
				
					if ($spc.requestFullscreen) {
						$spc.requestFullscreen();
					}
				
					else if ($spc.mozRequestFullScreen) {
						$spc.mozRequestFullScreen();
					}
					
					else if ($spc.webkitRequestFullScreen) {
						$spc.webkitRequestFullScreen();
					}
				
				});
				
					
				$('#fbLogo').on("click",function(e) {
  					 e.preventDefault();  //stop the browser from following
  				 	e.stopPropagation();
					
				if(window.logoOverride==false||window.logoOverride==undefined){
					var loc = location.href;
					var t = document.title;}
					else{
					var loc = window.logoOverrideLoc;
					var t = window.logoOverrideT;
                    }
                    var mSource = $('#vidPrincId').attr("src");
                   
                    var feed = {
                        method: 'share_open_graph',
                        href:loc+'?type=video'
                       /* picture: 'http://www.syncstats.com/images/logoSeul.png',
                        link: loc,
                        name: 'SyncStats',
                        description: 'DESCRIPTION',
                        source: mSource,
                        type: 'video',*/
                    };

                    function callback(response) {
                        if (response && response.post_id !== undefined) {
                            alert('published');
                        }
                    }
                     
                    //FB.ui(feed, callback);
                    FB.ui({
                        method: 'share_open_graph',
                        action_type: 'video.other',
                        action_properties: JSON.stringify({
                            object: loc,
                        })
                    }, function (response) { });




                 //  window.open('http://www.facebook.com/sharer.php?u=' + encodeURIComponent(mSource) + '&t=' + encodeURIComponent(t), 'sharer', 'status=0,width=626,height=436, top=' + ($(window).height() / 2 - 225) + ', left=' + ($(window).width() / 2 - 313) + ', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');




                });
				$('#googleLogo').click(function(e) {
  					 e.preventDefault();  //stop the browser from following
  				 	e.stopPropagation();
				if(logoOverride==undefined){
					var loc = location.href;
					var t = document.title;}
					else{
					var loc = window.logoOverrideLoc;
					var t = window.logoOverrideT;
					}
					window.open('https://plus.google.com/share?url='+encodeURIComponent(loc),'Google Share','status=0,width=626,height=436, top='+($(window).height()/2 - 225) +', left='+($(window).width()/2 - 313 ) +', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');	
					});
				$('#twitterLogo').click(function(e) {
  					 e.preventDefault();  //stop the browser from following
  				 	e.stopPropagation();
				if(logoOverride==undefined){
					var loc = location.href;
					var t = document.title;}
					else{
					var loc = window.logoOverrideLoc;
					var t = window.logoOverrideT;
					}
					window.open('http://twitter.com/share?url=' + loc + '&text=' + t + ' - ' + loc + ' - via @twitter', 'twitterwindow', 'height=255, width=550, top='+($(window).height()/2 - 225) +', left='+($(window).width()/2 - 275 ) +', toolbar=0, location=0, menubar=0, directories=0, scrollbars=0');
					});
				
				
			});
			
		});
	
	};
	
	
})(jQuery);
/*
		//define a progress abstraction
		function onprogress(vidSource)
		{
			
			var media = document.getElementsByTagName('video')[0];
//			$('.player').append($('<div></div>').attr("id", "progress"));

			var progress = document.getElementById('progress');
			//get the buffered ranges data
			var ranges = [];
			for(var i = 0; i < media.buffered.length; i ++)
			{
				ranges.push([
					media.buffered.start(i),
					media.buffered.end(i)
					]);
			}
			
			//get the current collection of spans inside the container
			var spans = progress.getElementsByTagName('span');
			
			//then add or remove spans so we have the same number as time ranges
			while(spans.length < media.buffered.length)
			{
				progress.appendChild(document.createElement('span'));
			}
			while(spans.length > media.buffered.length)
			{
				progress.removeChild(progress.lastChild);
			}
				
			//now iterate through the ranges and convert each set of timings
			//to a percentage position and width for the corresponding span
			for(var i = 0; i < media.buffered.length; i ++)
			{
				spans[i].style.left = Math.round
				(
					(100 / media.duration) //the width of 1s
					* 
					ranges[i][0]
				) 
				+ '%';
				
				spans[i].style.width = Math.round
				(
					(100 / media.duration) //the width of 1s
					* 
					(ranges[i][1] - ranges[i][0])
				) 
				+ '%';
			}
		}
		
*/
