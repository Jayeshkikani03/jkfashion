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

  // Background catalog import — continues on any wp-admin page (no meta-refresh).
  if (
    typeof vlImportRunner !== "undefined" &&
    vlImportRunner.running &&
    vlImportRunner.ajaxUrl
  ) {
    var $bar = $(
      '<div id="vl-import-bg-bar" style="position:fixed;z-index:100000;left:0;right:0;bottom:0;padding:10px 16px;background:#1d2327;color:#fff;font-size:13px;box-shadow:0 -2px 8px rgba(0,0,0,.2);"></div>'
    );
    $bar.text(vlImportRunner.i18n.bar);
    $("body").append($bar);

    var tickBusy = false;
    var tickTimer = null;

    function scheduleTick(delay) {
      if (tickTimer) {
        clearTimeout(tickTimer);
      }
      tickTimer = setTimeout(runTick, delay || 2500);
    }

    function runTick() {
      if (tickBusy) {
        scheduleTick(3000);
        return;
      }
      tickBusy = true;
      $.post(vlImportRunner.ajaxUrl, {
        action: "vl_import_catalog_tick",
        nonce: vlImportRunner.nonce,
      })
        .done(function (res) {
          if (!res || !res.success || !res.data) {
            scheduleTick(5000);
            return;
          }
          var data = res.data;
          if (data.status === "busy") {
            scheduleTick(4000);
            return;
          }
          if (data.status === "progress") {
            $bar.text(
              vlImportRunner.i18n.bar +
                " (" +
                (data.step || "?") +
                "/" +
                (data.of || "?") +
                (data.last_source ? " · " + data.last_source : "") +
                ")"
            );
            var $notice = $("#vl-import-progress-notice p");
            if ($notice.length) {
              $notice.text(
                "Working in background… source " +
                  data.step +
                  " of " +
                  data.of +
                  (data.last_source ? " (" + data.last_source + ")" : "") +
                  " — Images total so far: " +
                  (data.images || 0) +
                  ". You can open other wp-admin pages."
              );
            }
            scheduleTick(1500);
            return;
          }
          if (data.status === "imported") {
            $bar.css("background", "#00a32a").text(vlImportRunner.i18n.done);
            setTimeout(function () {
              window.location.href =
                vlImportRunner.pageUrl + "&vl_msg=imported";
            }, 800);
            return;
          }
          $bar.remove();
        })
        .fail(function () {
          scheduleTick(8000);
        })
        .always(function () {
          tickBusy = false;
        });
    }

    scheduleTick(1200);
  }
})(jQuery);
