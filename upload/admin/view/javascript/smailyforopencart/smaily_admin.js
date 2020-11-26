(function ($) {
  $(window).on("load", function () {
    // Open first tab or abandoned cart status tab;
    if (isCustomerInAbandonedCartStatusTab() == true) {
      $("#sections a:last").tab("show");
    } else {
      $("#sections a:first").tab("show");
    }
    // Hide validate display messages.
    $("#validate-alert button").on("click", function () {
      $("#validate-alert").hide();
    });
    // Populate autoresponders list
    getAutoresponders();
    function getAutoresponders() {
      // Smaily credentials.
      var subdomain = $("#subdomain").val();
      var username = $("#username").val();
      var password = $("#password").val();
      var user_token = $("#user_token").val();
      if (subdomain != "" && username != "" && password != "") {
        $.ajax({
          url:
            "index.php?route=extension/module/smaily_for_opencart/ajaxGetAutoresponders&user_token=" +
            user_token,
          dataType: "json",
          method: "POST",
          data: {
            subdomain: subdomain,
            username: username,
            password: password,
          },
          success: function (response) {
            $.each(response, function (index, value) {
              $("#abandoned-autoresponder").append(
                $("<option>", {
                  value: JSON.stringify({ name: value, id: index }),
                  text: value,
                })
              );
            });
          },
        });
      }
    }
    function switchValidateResetSection(currently_validated = false) {
      if (currently_validated) {
        // Switch reset section to validate section.
        $("#reset-title").hide();
        $("#validate-title").show();
        $("#reset-credentials").hide();
        $("#validate").show();
      } else {
        // Switch validate section to reset section.
        $("#reset-title").show();
        $("#validate-title").hide();
        $("#reset-credentials").show();
        $("#validate").hide();
      }
    }
    // Reset credentials.
    $("#reset-credentials").on("click", function () {
      // Scroll top.
      $("html, body").animate(
        {
          scrollTop: "0px",
        },
        "slow"
      );
      var user_token = $("#user_token").val();
      var spinner = $("#smaily-reset-loader");
      spinner.show();
      $.ajax({
        url:
          "index.php?route=extension/module/smaily_for_opencart/ajaxResetCredentials&user_token=" +
          user_token,
        dataType: "json",
        method: "POST",
        success: function (response) {
          spinner.hide();
          if (response["success"]) {
            // Remove success style from credentials input.
            $("div.has-success").removeClass("has-success");
            // Show response
            $("#validate-message").text(response["success"]);
            $("#validate-alert").addClass("alert-success").show();
            // Disable module functions.
            $("#input-status").val("0");
            $("#input-subscriber-status").val("0");
            $("#input-abandoned-status").val("0");
            // Reset Smaily credentials.
            $("#subdomain").val("");
            $("#username").val("");
            $("#password").val("");
            switchValidateResetSection(true);
          }
        },
        error: function (error) {
          spinner.hide();
          $("#validate-message").text("Something went wrong!");
          $("#validate-alert").addClass("alert-danger").show();
        },
      });
    });
    // Validate autoresponders.
    $("#validate").on("click", function (e) {
      // Scroll top.
      $("html, body").animate(
        {
          scrollTop: "0px",
        },
        "slow"
      );
      // Spinner
      var spinner = $("#smaily-validate-loader");
      // Smaily credentials.
      var subdomain = $("#subdomain").val();
      var username = $("#username").val();
      var password = $("#password").val();
      var user_token = $("#user_token").val();
      // Display error if empty values.
      if (!subdomain) {
        $("#subdomain").parent().addClass("has-error");
      }
      if (!username) {
        $("#username").parent().addClass("has-error");
      }
      if (!password) {
        $("#password").parent().addClass("has-error");
      }

      // Start spinner.
      spinner.show();
      $.ajax({
        url:
          "index.php?route=extension/module/smaily_for_opencart/ajaxValidateCredentials&user_token=" +
          user_token,
        dataType: "json",
        method: "POST",
        data: {
          subdomain: subdomain,
          username: username,
          password: password,
        },
        success: function (response) {
          // Hide spinner.
          spinner.hide();
          // Error message
          if (response["error"]) {
            $("#validate-message").text(response["error"]);
            $("#validate-alert").addClass("alert-danger").show();
          } else if (!response) {
            $("#validate-message").text(
              "Something went wrong with request to smaily"
            );
            $("#validate-alert").addClass("alert-danger").show();
          }
          // Success message.
          if (response["success"]) {
            // Get autoresponders.
            getAutoresponders();
            // Remove alert messages.
            $("div.alert-danger, div.text-danger").hide();
            // Remove form group has-error
            $("div.has-error").removeClass("has-error").addClass("has-success");
            // Add text, remove danger class had errors.
            $("#validate-message").text(response["success"]);
            $("#validate-alert").removeClass("alert-danger");
            // Show response
            $("#validate-alert").addClass("alert-success").show();
            switchValidateResetSection();
            // Set module status to enabled.
            $("#input-status").val("1");
          }
        },
        error: function (error) {
          // Hide spinner.
          spinner.hide();
          $("#validate-message").text("No connection to smaily");
          $("#validate-alert").addClass("alert-danger").show();
        },
      });
    });
    $(".smaily-rss-options").change(function (event) {
      var rss_url_base = smaily_rss_url_base + '&';
      var parameters = {};

      var rss_category = $('#rss-category').val();
      if (rss_category != "") {
        parameters.category = rss_category;
      }

      var rss_sort_by = $('#rss-sort-by').val();
      if (rss_sort_by != "none") {
        parameters.sort_by = rss_sort_by;
      }

      var rss_sort_order = $('#rss-sort-order').val();
      if (rss_sort_order != "") {
        parameters.sort_order = rss_sort_order;
      }

      var rss_limit = $('#rss-limit').val();
      if (rss_limit != "") {
        parameters.limit = rss_limit;
      }
      $('#smaily-rss-feed-url').html(rss_url_base + $.param(parameters));
    });
  });
  function isCustomerInAbandonedCartStatusTab() {
    var search_params = ['sort', 'order', 'page'];
    var query_parameters = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    var i;
    for (i = 0; i < query_parameters.length; i++) {
      parameter = query_parameters[i].split('=')[0];
      if ($.inArray(parameter, search_params) !== -1) {
        return true;
      }
    }
    return false;
  }
})(jQuery);
