jQuery(function ($) {
  var scanId = null;
  var total = 0;
  var batch = 200;

  function setProgress(offset) {
    var pct = 0;
    if (total > 0) pct = Math.min(100, Math.round((offset / total) * 100));
    $(".moc-progress-fill").css("width", pct + "%");
    $(".moc-progress-text").text(pct + "% (" + offset + "/" + total + ")");
  }

  function scanBatch(offset) {
    $.post(MOC_Ajax.ajax_url, {
      action: "moc_scan_batch",
      nonce: MOC_Ajax.nonce,
      scan_id: scanId
    }).done(function (res) {
      if (!res.success) {
        $("#moc-scan-result").show().removeClass("notice-info").addClass("notice-error")
          .text("Error: " + res.data);
        return;
      }

      var data = res.data;
      setProgress(data.offset);

      if (data.done) {
        $("#moc-scan-result").show().removeClass("notice-info").addClass("notice-success")
          .text("Escaneo completado. Se ha actualizado la lista.");
        window.location.reload();
        return;
      }

      scanBatch(data.offset);
    });
  }

  $("#moc-start-scan").on("click", function (e) {
    e.preventDefault();

    $("#moc-scan-result").hide().removeClass("notice-error notice-success").addClass("notice-info").text("");
    $("#moc-progress").show();
    setProgress(0);

    $.post(MOC_Ajax.ajax_url, {
      action: "moc_start_scan",
      nonce: MOC_Ajax.nonce
    }).done(function (res) {
      if (!res.success) {
        $("#moc-scan-result").show().removeClass("notice-info").addClass("notice-error")
          .text("Error: " + res.data);
        return;
      }

      scanId = res.data.scan_id;
      total = res.data.total;
      batch = res.data.batch;

      scanBatch(0);
    });
  });
});
