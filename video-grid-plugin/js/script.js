jQuery(document).ready(function($) {
    var mediaUploader;

   // Admin: Handle the Add Video button click
   $('.add-video').on('click', function(e) {
    e.preventDefault();
    var button = $(this);
    var videoGrid = button.siblings('.video-grid');
    var optionName = button.parent().attr('id');

    if (mediaUploader) {
        mediaUploader.open();
        return;
    }

    mediaUploader = wp.media.frames.file_frame = wp.media({
        title: 'Choose Video',
        button: {
            text: 'Choose Video'
        },
        library: {
            type: 'video'
        },
        multiple: false
    });

    mediaUploader.on('select', function() {
        var attachment = mediaUploader.state().get('selection').first().toJSON();
        var newIndex = 0; // Always set to 0 to add at the beginning
        
        // Increment all existing indices
        videoGrid.find('.video-grid-item').each(function() {
            $(this).find('input').each(function() {
                var name = $(this).attr('name');
                var newName = name.replace(/\[(\d+)\]/, function(match, p1) {
                    return '[' + (parseInt(p1) + 1) + ']';
                });
                $(this).attr('name', newName);
            });
        });
    
        var newVideoHtml = `
            <div class="video-grid-item">
                <input type="hidden" name="${optionName}[${newIndex}][url]" value="${attachment.url}" />
                <video width="320" height="240">
                    <source src="${attachment.url}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div class="video-overlay">
                    <div class="video-title"></div>
                    <div class="video-subtitle"></div>
                </div>
                <input type="text" name="${optionName}[${newIndex}][title]" value="" placeholder="Enter video title" />
                <input type="text" name="${optionName}[${newIndex}][subtitle]" value="" placeholder="Enter video subtitle" />
                <button type="button" class="button button-link remove-video">Remove</button>
            </div>
        `;
        videoGrid.prepend(newVideoHtml);
    });

    mediaUploader.open();
});

    // Admin: Handle the Remove button click
    $(document).on('click', '.remove-video', function(e) {
        e.preventDefault();
        $(this).closest('.video-grid-item').remove();
    });

    // Admin: Add event listener for title input
    // $(document).on('input', '.video-grid-item input[type="text"]', function() {
    //     $(this).siblings('.video-overlay').find('.video-title').text($(this).val());
    //     $(this).siblings('.video-overlay').find('.video-subtitle').text($(this).val());
    // });
    // Admin: Add event listener for title and subtitle input
    $(document).on('input', '.video-grid-item input[type="text"]', function() {
        var $item = $(this).closest('.video-grid-item');
        var $title = $item.find('input[name$="[title]"]');
        var $subtitle = $item.find('input[name$="[subtitle]"]');
        $item.find('.video-overlay .video-title').text($title.val());
        $item.find('.video-overlay .video-subtitle').text($subtitle.val());
    });

    // Replace button code
    function initializeMediaUploader(callback) {
        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Video',
            button: {
                text: 'Choose Video'
            },
            library: {
                type: 'video'
            },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            callback(attachment);
        });

        mediaUploader.open();
    }

    // Handle the Replace Video button click
    $(document).on('click', '.replace-video', function(e) {
        e.preventDefault();
        var $videoItem = $(this).closest('.video-grid-item');
        
        initializeMediaUploader(function(attachment) {
            $videoItem.find('input[type="hidden"]').val(attachment.url);
            $videoItem.find('video source').attr('src', attachment.url);
            $videoItem.find('video')[0].load(); // Reload the video element
        });
    });

    // Frontend: Add click event for playing videos
    $(document).on('click', '.video-grid-item-frontend .play-button', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var videoItem = $(this).closest('.video-grid-item-frontend');
        var videoSrc = videoItem.find('video').data('src');
        var videoTitle = videoItem.find('.video-title-frontend').text();
        var videoSubtitle = videoItem.find('.video-subtitle-frontend').text();
        
        // Create and show the fullscreen overlay
        var overlay = $('<div class="video-fullscreen-overlay"></div>');
        var closeButton = $('<button class="close-video">×</button>');
        var videoWrapper = $('<div class="video-wrapper"></div>');
        var video = $('<video><source src="' + videoSrc + '" type="video/mp4"></video>');
        var title = $('<div class="fullscreen-video-title">' + videoTitle + '</div>');
        var subtitle = $('<div class="fullscreen-video-subtitle">' + videoSubtitle + '</div>');
        
        videoWrapper.append(video).append(title).append(subtitle);
        overlay.append(closeButton).append(videoWrapper);
        $('#video-grid-fullscreen-overlay-container').html(overlay);
        
        var fullscreenVideo = video[0];
        fullscreenVideo.play();
        
        // Handle close button click
        closeButton.on('click', function() {
            $('#video-grid-fullscreen-overlay-container').empty();
        });

        // Toggle title visibility and overlay class
        $(fullscreenVideo).on('play', function() {
            overlay.addClass('playing');
        }).on('pause ended', function() {
            overlay.removeClass('playing');
        });

        // Toggle video play/pause on click
        $(fullscreenVideo).on('click', function() {
            if (this.paused) {
                this.play();
            } else {
                this.pause();
            }
        });
    });

    // Remove the fullscreen overlay when the video ends
    $(document).on('ended', '.video-fullscreen-overlay video', function() {
        $('#video-grid-fullscreen-overlay-container').empty();
    });
});