// IIFE (Immediately Invoked Function Expression)
// Anonymous function (no named function), can be executed only once
// Wraps the code so that $ is jQuery inside that closure, even if $ means something else outside of it					
// Closure. Protect the global scope
// If function would be assigned to a var (not IIFE), it is a mean of  Namespacing the javascript. var scu_shortcodes = scu_shortcodes || {};

(function($) {
	var isSubmitting = false;		
	$(function() {	
		"use strict";	// Throws more exceptions

		$("#scu-backup-admin-notices").on("click", ".notice-dismiss", function(e) {
			$(this).parent().fadeOut(1000, function(){ $(this).remove();});
		});

		$(".tab-div").hide();
		$("#scu-backup-div").show();
		$("#scu-backupmenu-content").css("visibility", "visible");

		$(".nav-tab-wrapper").on("click", ".nav-tab", function (event) {
			$(".nav-tab").removeClass("nav-tab-active");
			$(".tab-div").hide();
			$(event.target).addClass("nav-tab-active");
			$('#'+event.target.id.replace("tab", "div")).show();
			switch(event.target.id) {
				case 'scu-backup-tab':
					$("#title").prop('disabled', false);
					
					break;
				case 'scu-restore-tab':
					$("#title").prop('disabled', false);
					$					
					break;
				default:
					break;
			}
		});

		$("#scu-backup-download-button").on("click", function (e) {
			e.preventDefault();
			var data = {
				action: 'scu_backup_download',
				ajaxNonce: scu_ajax_backup.ajaxNonce
			};
			$.ajax({
				method: "POST",
				url: scu_ajax_backup.ajaxurl,
				dataType: "json",
				data: data
			})
			.done(function(response) {				
				var url_redirect = scu_ajax_backup.plugin_url+'/admin/download.php?file='+response;
				window.location = url_redirect;

				// Not really need to delete the backup file in temp dir. 
				var data = {
					action: 'scu_backup_file_remove',
					ajaxNonce: scu_ajax_backup.ajaxNonce,
					file: response
				};
				$.ajax({
					method: "POST",
					url: scu_ajax_backup.ajaxurl,
					dataType: "json",
					data: data
				})
				.done(function(response) {
					if(!response) {
					var html ='<div id="scu-backup-menu-notice" class="notice settings-error is-dismissible notice-error">';
					html += '<p><strong>Error: Something went wrong</strong></p>';
					html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
					html += 'Descartar este aviso.</span></button></div>';
					$('#scu-backup-admin-notices').append(html);
					}
					else {
						var html ='<div id="scu-add-menu-notice" class="notice settings-success is-dismissible notice-success">';
						html += '<p><strong>Backup file downloaded succesfully</strong></p>';
						html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
						html += 'Descartar este aviso.</span></button></div>';
						$('#scu-backup-admin-notices').append(html);
					}
				});
				/*
				if(response.OK) {
					console.log(response.url_redirect);
					window.location.replace(response.url_redirect);
				}
				else {
					var html ='<div id="scu-add-menu-notice" class="notice settings-error is-dismissible notice-error" style="display:block">';
					html += '<p><strong>Error: '+response.error.message+'</strong></p>';
					html += '<button type="button" class="notice-dismiss"><span class="screen-reader-text">';
					html += 'Descartar este aviso.</span></button></div>';
					$('#scu-add-menu-notices').append(html);
				}
				*/
			});
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
    		//$("#yourprogressbar").progressbar().show();
			plupload.each(files, function(file) {
				if (max > hundredmb && file.size > hundredmb && up.runtime != 'html5') {
					// file size error?
				}
				else if (up.files.length > 1) {				// Max files in a time
					var fileCount = up.files.length;
					var i = 0;
        			var ids = $.map(up.files, function (item) { return item.id; });
					for (i = 0; i < fileCount; i++) {
						up.removeFile(up.getFile(ids[i]));
					}
					//console.log('only one file, please');
					throw 'Error: only one file, please';	
				}
				else {
					// a file was added, you may want to update your DOM here...
					$("#plupload-browse-button-js").attr('disabled', true);
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
			//console.log(response);	
			var responseAjax = JSON.parse(response['response']);
			responseAjax.forEach(function (item, index) {
				$('#scu-backup-result').append('<p>* '+item.message+'</p>');
				//console.log(item);
				//console.log(index);	
			});
		/*	
			if(!responseAjax.OK) {
				console.log(response);				
				$('#' + file.id).remove();
				$('#scu-backup-admin-notices').addClass('notice-error');
				$('#scu-backup-admin-notices p strong').html(responseAjax.error.message);
				$('#scu-backup-admin-notices').show();
				$("#plupload-browse-button-js").attr('disabled', false);
				//<div class="notice notice-error is-dismissible">
    			//	<p>There has been an error.</p>
				//</div>
			}
			else {
				//var responseFile = responseAjax.file.split(/[\\/]/).pop();
				console.log(response);	
				$('#' + file.id + " .scu-file-name b").html(responseAjax.shortcode);
				$('#scu-backup-admin-notices').addClass('notice-success');
				$('#scu-backup-admin-notices p').html('<strong>Shortcode: '+responseAjax.shortcode+'.</strong> Imported succesfully');
				$('#scu-backup-admin-notices').show();
				/*
				setTimeout(function() {
					$('#' + file.id).append('<a href="#" class="scu-file-action">Delete</a>');
					$('#' + file.id + " .scu-file-progress").remove();
				}, 2000);	
				*/			
		//	}	
		
		});
		uploader_js.bind('Error', function(up, error) {
			alert(' (code '+error.code+') : '+error.message);
		});		
	});
})(jQuery);