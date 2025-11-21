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

  function displayLogs(logs) {
    if (!logs || logs.length === 0) return;
    
    var html = '';
    logs.forEach(function(log) {
      html += '<div class="moc-log-entry">';
      html += '<strong>' + log.time + ':</strong> ' + log.message;
      if (log.data) {
        html += '<pre>' + JSON.stringify(log.data, null, 2) + '</pre>';
      }
      html += '</div>';
    });
    
    $("#moc-logs-content").html(html);
    $("#moc-logs").show();
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
        var sizeMB = (data.total_size / 1024 / 1024).toFixed(2);
        var msg = "✅ Escaneo completado. Encontradas " + data.orphans.length + " huérfanas.";
        if (data.total_size > 0) {
          msg += " Espacio a liberar: " + sizeMB + " MB.";
        }
        
        $("#moc-scan-result").show().removeClass("notice-info").addClass("notice-success")
          .text(msg);
        
        if (data.logs) {
          displayLogs(data.logs);
        }
        
        setTimeout(function() {
          window.location.reload();
        }, 2000);
        return;
      }

      scanBatch(data.offset);
    });
  }

  $("#moc-start-scan").on("click", function (e) {
    e.preventDefault();

    $("#moc-scan-result").hide().removeClass("notice-error notice-success").addClass("notice-info").text("");
    $("#moc-progress").show();
    $("#moc-logs").hide();
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
  
  // Select all checkbox
  $("#moc-select-all").on("change", function() {
    $(".moc-checkbox").prop("checked", $(this).prop("checked"));
  });
});

// Funciones de selección inteligente (scope global para onclick)
function mocSelectAll() {
  jQuery('.moc-checkbox').prop('checked', true);
  jQuery('#moc-select-all').prop('checked', true);
}

function mocDeselectAll() {
  jQuery('.moc-checkbox').prop('checked', false);
  jQuery('#moc-select-all').prop('checked', false);
}

function mocSelectPhysical() {
  // Deseleccionar todo primero
  mocDeselectAll();
  
  // Seleccionar solo filas que NO tienen clase moc-status-no-file
  jQuery('tr:not(.moc-status-no-file)').each(function() {
    var checkbox = jQuery(this).find('.moc-checkbox');
    if (checkbox.length) {
      checkbox.prop('checked', true);
    }
  });
}

function mocSelectGhosts() {
  // Deseleccionar todo primero
  mocDeselectAll();
  
  // Seleccionar solo filas con clase moc-status-no-file
  jQuery('tr.moc-status-no-file').each(function() {
    var checkbox = jQuery(this).find('.moc-checkbox');
    if (checkbox.length) {
      checkbox.prop('checked', true);
    }
  });
}

function mocSelectInvalidParent() {
  // Deseleccionar todo primero
  mocDeselectAll();
  
  // Seleccionar solo filas con clase moc-invalid-parent
  jQuery('tr.moc-invalid-parent').each(function() {
    var checkbox = jQuery(this).find('.moc-checkbox');
    if (checkbox.length) {
      checkbox.prop('checked', true);
    }
  });
}
