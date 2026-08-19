jQuery(document).ready(function($) {
    // Handle both UMKM & Galeri meta boxes
    var configs = [
        { container: '#desa_umkm_gallery_container', hidden: '#desa_umkm_gallery_ids', addBtn: '#desa_umkm_add_gallery' },
        { container: '#desa_galeri_images_container', hidden: '#desa_galeri_gallery_ids', addBtn: '#desa_galeri_add_gallery' }
    ];

    function appendImage($container, attachment) {
        var url = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
        var html = `
            <div class="gallery-item" data-id="${attachment.id}" style="display: inline-block; margin: 5px; position: relative;">
                <img src="${url}" style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc;">
                <button class="remove-image" style="position: absolute; top: 0; right: 0; background: red; color: white; border: none; cursor: pointer; padding: 2px 5px;">&times;</button>
            </div>
        `;
        $container.append(html);
    }

    configs.forEach(function(cfg) {
        var frame = null;
        var $imageContainer = $(cfg.container);
        var $hiddenInput = $(cfg.hidden);
        var $addBtn = $(cfg.addBtn);

        if (!$imageContainer.length) {
            return;
        }

        // Add Images
        $addBtn.on('click', function(e) {
            e.preventDefault();

            if (frame) {
                frame.open();
                return;
            }

            frame = wp.media({
                title: 'Pilih Gambar',
                button: {
                    text: 'Gunakan Gambar Ini'
                },
                multiple: true
            });

            frame.on('select', function() {
                var attachments = frame.state().get('selection').toJSON();
                var currentIds = $hiddenInput.val() ? $hiddenInput.val().split(',') : [];

                attachments.forEach(function(attachment) {
                    // Avoid duplicates
                    if (currentIds.indexOf(attachment.id.toString()) === -1) {
                        currentIds.push(attachment.id);
                        appendImage($imageContainer, attachment);
                    }
                });

                $hiddenInput.val(currentIds.join(','));
            });

            frame.open();
        });

        // Remove Image
        $imageContainer.on('click', '.remove-image', function(e) {
            e.preventDefault();
            var $wrapper = $(this).closest('.gallery-item');
            var idToRemove = $wrapper.data('id');
            var currentIds = $hiddenInput.val().split(',');

            // Remove from array
            var index = currentIds.indexOf(idToRemove.toString());
            if (index > -1) {
                currentIds.splice(index, 1);
            }

            $hiddenInput.val(currentIds.join(','));
            $wrapper.remove();
        });
    });
});
