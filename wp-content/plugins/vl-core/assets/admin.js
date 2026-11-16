(function ($) {
  function refreshGalleryPreview(ids) {
    var $preview = $("#vl-gallery-preview");
    $preview.empty();
    if (!ids.length) return;

    ids.forEach(function (id) {
      var attachment = wp.media.attachment(id);
      attachment.fetch().then(function () {
        var url =
          (attachment.attributes.sizes &&
            attachment.attributes.sizes.thumbnail &&
            attachment.attributes.sizes.thumbnail.url) ||
          attachment.attributes.url;
        if (url) {
          $preview.append($("<img/>", { src: url, width: 72, height: 72 }));
        }
      });
    });
  }

  function setFeaturedPreview(attachment) {
    var $preview = $("#vl-featured-preview");
    var url =
      (attachment.attributes.sizes &&
        attachment.attributes.sizes.medium &&
        attachment.attributes.sizes.medium.url) ||
      attachment.attributes.url;
    $preview.empty();
    if (url) {
      $preview.append($("<img/>", { src: url, alt: "" }));
    } else {
      $preview.append(
        $("<span/>", { class: "vl-featured-empty", text: vlAdmin.noImage })
      );
    }
    $("#vl-featured-remove").show();
  }

  $(document).on("click", "#vl-featured-upload", function (e) {
    e.preventDefault();
    var frame = wp.media({
      title: vlAdmin.featuredTitle,
      button: { text: vlAdmin.featuredBtn },
      multiple: false,
      library: { type: "image" },
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first();
      $("#vl_featured_id").val(attachment.id);
      setFeaturedPreview(attachment);
      // Keep WP side meta box in sync if present.
      $("#_thumbnail_id").val(attachment.id);
    });

    frame.open();
  });

  $(document).on("click", "#vl-featured-remove", function (e) {
    e.preventDefault();
    $("#vl_featured_id").val("-1");
    $("#_thumbnail_id").val("-1");
    $("#vl-featured-preview")
      .empty()
      .append(
        $("<span/>", { class: "vl-featured-empty", text: vlAdmin.noImage })
      );
    $(this).hide();
  });

  $(document).on("click", "#vl-gallery-upload", function (e) {
    e.preventDefault();
    var frame = wp.media({
      title: vlAdmin.title,
      button: { text: vlAdmin.button },
      multiple: true,
      library: { type: "image" },
    });

    frame.on("select", function () {
      var selection = frame.state().get("selection");
      var ids = selection.map(function (model) {
        return model.id;
      });
      $("#vl_gallery_ids").val(ids.join(","));
      refreshGalleryPreview(ids);
    });

    frame.open();
  });

  $(document).on("click", "#vl-gallery-clear", function (e) {
    e.preventDefault();
    $("#vl_gallery_ids").val("");
    $("#vl-gallery-preview").empty();
  });
})(jQuery);
