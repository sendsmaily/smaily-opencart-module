(function($) {
  $(window).on("load", function() {
    // Open first tab.
    $("#sections a:first").tab("show");
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
            password: password
          },
          success: function(response) {
            $.each(response, function(index, value) {
              $("#abandoned-autoresponder").append(
                $("<option>", {
                  value: JSON.stringify({ name: value, id: index }),
                  text: value
                })
              );
            });
          }
        });
      }
    }
    // Validate autoresponders.
    $("#validate").on("click", function(e) {
      // Scroll top.
      $("html, body").animate(
        {
          scrollTop: "0px"
        },
        "slow"
      );
      // Validate form button section.
      var validateSection = $("#validate-form-group");
      // Spinner
      var spinner = $("#smaily-validate-loader");
      // Smaily credentials.
      var subdomain = $("#subdomain").val();
      var username = $("#username").val();
      var password = $("#password").val();
      var user_token = $("#user_token").val();
      // Display error if empty values.
      if (!subdomain) {
        $("#subdomain")
          .parent()
          .addClass("has-error");
      }
      if (!username) {
        $("#username")
          .parent()
          .addClass("has-error");
      }
      if (!password) {
        $("#password")
          .parent()
          .addClass("has-error");
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
          password: password
        },
        success: function(response) {
          // Hide spinner.
          spinner.hide();
          // Error message
          if (response["error"]) {
            $("#validate-message").text(response["error"]);
            $("#validate-div")
              .addClass("alert-danger")
              .show();
          } else if (!response) {
            $("#validate-message").text(
              "Something went wrong with request to smaily"
            );
            $("#validate-div")
              .addClass("alert-danger")
              .show();
          }
          // Success message.
          if (response["success"]) {
            // Get autoresponders.
            getAutoresponders();
            // Remove alert messages.
            $("div.alert-danger, div.text-danger").hide();
            // Remove form group has-error
            $("div.has-error")
              .removeClass("has-error")
              .addClass("has-success");
            // Add text, remove danger class had errors.
            $("#validate-message").text(response["success"]);
            $("#validate-div").removeClass("alert-danger");
            // Show response
            $("#validate-div")
              .addClass("alert-success")
              .show();
            // Hide validate button section.
            validateSection.hide();
          }
        },
        error: function(error) {
          // Hide spinner.
          spinner.hide();
          $("#validate-message").text("No connection to smaily");
          $("#validate-div")
            .addClass("alert-danger")
            .show();
        }
      });
    });
  });
})(jQuery);
