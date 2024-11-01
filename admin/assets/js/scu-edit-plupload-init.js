// IIFE (Immediately Invoked Function Expression)
// Anonymous function (no named function), can be executed only once
// Wraps the code so that $ is jQuery inside that closure, even if $ means something else outside of it					
// Closure. Protect the global scope
// If function would be assigned to a var (not IIFE), it is a mean of  Namespacing the javascript. var scu_shortcodes = scu_shortcodes || {};

(function($) {
	var isSubmitting = false;		
	$(function() {	
		"use strict";	// Throws more exceptions
/*
		// Create the pupload(s)	Example at: https://wordpress.org/plugins/upload-larger-plugins
		var uploader = new plupload.Uploader(scu_plupload_config_js); //<?php echo json_encode($plupload_init); ?>
		// checks if browser supports drag and drop upload, makes some css adjustments if necessary		
		uploader.bind('Init', function(up) {
			var uploaddiv = $('#plupload-upload-ui-js');
			if(up.features.dragdrop) {
				uploaddiv.addClass('drag-drop');
				$('#drag-drop-area-js')
					.bind('dragover.wp-uploader', function(){ uploaddiv.addClass('drag-over'); })
					.bind('dragleave.wp-uploader, drop.wp-uploader', function() { uploaddiv.removeClass('drag-over'); });

			} else {
				uploaddiv.removeClass('drag-drop');
				$('#drag-drop-area-js').unbind('.wp-uploader');
			}
		});

		uploader.init();
*/
		


		// Create the pupload(s)	Example at: https://wordpress.org/plugins/upload-larger-plugins
		var uploader_css = new plupload.Uploader(scu_plupload_config_css);
		uploader_css.bind('Init', function(up) {
			var uploaddiv_css = $('#plupload-upload-ui-css');
			if(up.features.dragdrop) {
				uploaddiv_css.addClass('drag-drop');
				$('#drag-drop-area-css')
					.bind('dragover.wp-uploader', function(){ uploaddiv_css.addClass('drag-over'); })
					.bind('dragleave.wp-uploader, drop.wp-uploader', function() { uploaddiv_css.removeClass('drag-over'); });

			} else {
				uploaddiv_css.removeClass('drag-drop');
				$('#drag-drop-area-css').unbind('.wp-uploader');
			}
		});
		uploader_css.init();
		uploader_css.bind('FilesAdded', function(up, files) {
			var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
			plupload.each(files, function(file) {
				if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5') {
					// file size error?
				} else {
					// a file was added, you may want to update your DOM here...					
					var output = '<div class="scu-file" id="'+file.id+'">';
					output += '<div class="scu-file-name"><b>'+file.name+'</b>';
					output += ' (<span>'+plupload.formatSize(0)+'</span>/'+plupload.formatSize(file.size)+') </div>';
					output += '<div class="scu-file-progress">';
					output += '<div class="scu-file-progress-percent">0%</div>';
					output += '<div class="scu-file-progress-bar"></div></div></div>';					
					$('#scu-filelist-css').append(output);					
				}
			});
			up.refresh();
			up.start();
		});
		uploader_css.bind('UploadProgress', function(up, file) {			
			$('#' + file.id + " .scu-file-name span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
			$('#' + file.id + " .scu-file-progress-percent").html(file.percent + "%");
			$('#' + file.id + " .scu-file-progress-bar").width(file.percent + "%");			
		});		
		uploader_css.bind('FileUploaded', function(up, file, response) {
			//console.log(response['response']);		
			var responseAjax = JSON.parse(response['response']);			
			if(responseAjax.error) {
				$('#' + file.id).remove();
			}
			else {				
				var responseFile = responseAjax.file.split(/[\\/]/).pop();			
				$('#' + file.id + " .scu-file-name b").html(responseFile);				
				setTimeout(function() {
					$('#' + file.id).append('<a href="#" class="scu-file-action">Delete</a>');
					$('#' + file.id + " .scu-file-progress").remove();
				}, 2000);
			}		
		});
		uploader_css.bind('Error', function(up, error) {
			alert(' (code '+error.code+') : '+error.message);
		});


		// Create the pupload(s)	Example at: https://wordpress.org/plugins/upload-larger-plugins
		var uploader_js = new plupload.Uploader(scu_plupload_config_js);
		uploader_js.bind('Init', function(up) {
			var uploaddiv_js = $('#plupload-upload-ui-js');
			if(up.features.dragdrop) {
				uploaddiv_js.addClass('drag-drop');
				$('#drag-drop-area-js')
					.bind('dragover.wp-uploader', function(){ uploaddiv_js.addClass('drag-over'); })
					.bind('dragleave.wp-uploader, drop.wp-uploader', function() { uploaddiv_js.removeClass('drag-over'); });

			} else {
				uploaddiv_js.removeClass('drag-drop');
				$('#drag-drop-area-css').unbind('.wp-uploader');
			}
		});
		uploader_js.init();
		uploader_js.bind('FilesAdded', function(up, files) {
			var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
			plupload.each(files, function(file) {
				if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5') {
					// file size error?
				} else {
					// a file was added, you may want to update your DOM here...					
					var output = '<div class="scu-file" id="'+file.id+'">';
					output += '<div class="scu-file-name"><b>'+file.name+'</b>';
					output += ' (<span>'+plupload.formatSize(0)+'</span>/'+plupload.formatSize(file.size)+') </div>';
					output += '<div class="scu-file-progress">';
					output += '<div class="scu-file-progress-percent">0%</div>';
					output += '<div class="scu-file-progress-bar"></div></div></div>';					
					$('#scu-filelist-js').append(output);
				}
			});
			up.refresh();
			up.start();
		});
		uploader_js.bind('UploadProgress', function(up, file) {			
			$('#' + file.id + " .scu-file-name span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
			$('#' + file.id + " .scu-file-progress-percent").html(file.percent + "%");
			$('#' + file.id + " .scu-file-progress-bar").width(file.percent + "%");			
		});		
		uploader_js.bind('FileUploaded', function(up, file, response) {
			//console.log(response['response']);
			var responseAjax = JSON.parse(response['response']);			
			if(responseAjax.error) {
				$('#' + file.id).remove();
			}
			else {				
				var responseFile = responseAjax.file.split(/[\\/]/).pop();			
				$('#' + file.id + " .scu-file-name b").html(responseFile);				
				setTimeout(function() {
					$('#' + file.id).append('<a href="#" class="scu-file-action">Delete</a>');
					$('#' + file.id + " .scu-file-progress").remove();
				}, 2000);
			}		
		});
		uploader_js.bind('Error', function(up, error) {
			alert(' (code '+error.code+') : '+error.message);
		});


		// Create the pupload(s)	Example at: https://wordpress.org/plugins/upload-larger-plugins
		var uploader_assets = new plupload.Uploader(scu_plupload_config_assets); //<?php echo json_encode($plupload_init); ?>
		// checks if browser supports drag and drop upload, makes some css adjustments if necessary		
		uploader_assets.bind('Init', function(up) {
			var uploaddiv_assets = $('#plupload-upload-ui-assets');
			if(up.features.dragdrop) {
				uploaddiv_assets.addClass('drag-drop');
				$('#drag-drop-area-assets')
					.bind('dragover.wp-uploader', function(){ uploaddiv_assets.addClass('drag-over'); })
					.bind('dragleave.wp-uploader, drop.wp-uploader', function() { uploaddiv_assets.removeClass('drag-over'); });

			} else {
				uploaddiv_assets.removeClass('drag-drop');
				$('#drag-drop-area-assets').unbind('.wp-uploader');
			}
		});
		uploader_assets.init();
		uploader_assets.bind('FilesAdded', function(up, files) {
			var hundredmb = 100 * 1024 * 1024, max = parseInt(up.settings.max_file_size, 10);
			plupload.each(files, function(file) {
				if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5') {
					// file size error?
				} else {
					// a file was added, you may want to update your DOM here...					
					var output = '<div class="scu-file" id="'+file.id+'">';
					output += '<div class="scu-file-name"><b>'+file.name+'</b>';
					output += ' (<span>'+plupload.formatSize(0)+'</span>/'+plupload.formatSize(file.size)+') </div>';
					output += '<div class="scu-file-progress">';
					output += '<div class="scu-file-progress-percent">0%</div>';
					output += '<div class="scu-file-progress-bar"></div></div></div>';					
					$('#scu-filelist-assets').append(output);
					/*
					var output = '<div class="file" id="' + file.id + '"><b>' + file.name;
					output += '</b> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') ';
					output += '<div class="fileprogress"></div>';
					output += '<button type="button" class="file-dismiss"><span class="screen-reader-text">Descartar este aviso.</span></button></div>';
					$('#filelist').append(output);
					*/
				}
			});
			up.refresh();
			up.start();
		});
		uploader_assets.bind('UploadProgress', function(up, file) {			
			$('#' + file.id + " .scu-file-name span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
			$('#' + file.id + " .scu-file-progress-percent").html(file.percent + "%");
			$('#' + file.id + " .scu-file-progress-bar").width(file.percent + "%");			
		});		
		uploader_assets.bind('FileUploaded', function(up, file, response) {
			console.log(response['response']);
		
			var responseAjax = JSON.parse(response['response']);			
			
			if(responseAjax.error) {
				$('#' + file.id).remove();
			}
			else {				
				var responseFile = responseAjax.file.split(/[\\/]/).pop();			
				$('#' + file.id + " .scu-file-name b").html(responseFile);				
				setTimeout(function() {
					$('#' + file.id).append('<a href="#" class="scu-file-action">Delete</a>');
					$('#' + file.id + " .scu-file-progress").remove();
				}, 2000);
			}		
		});
		uploader_assets.bind('Error', function(up, error) {
			alert(' (code '+error.code+') : '+error.message);
		});
	});
})(jQuery);