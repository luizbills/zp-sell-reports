/*
 * Legacy script
 * Ensures back-compat with ZP < 1.8
 *
 * @todo To be removed in a future version.
 */
;(function ($) {
  // Disable Next button until ajax response is ready
  $("#zp-fetch-offset").prop("disabled", true)

  // Hide Add to Cart button until form is ready for final submit
  $(".single_add_to_cart_button").hide()

  // Remove the submit button and action field.
  $("#zp-fetch-birthreport").remove()
  $("input[name='action']").remove()

  // Autocomplete city

  $("#place").autocomplete({
    source: function (request, response) {
      $(".ui-state-error").hide() // Hide the geonames error message, if any, in case they are trying again

      $.ajax({
        url: zp_ajax_object.autocomplete_ajaxurl,
        dataType: zp_ajax_object.dataType,
        type: zp_ajax_object.type,
        data: {
          featureClass: "P",
          style: "full",
          maxRows: 12,
          username: zp_ajax_object.geonames_user,
          action: zp_ajax_object.autocomplete_action
            ? zp_ajax_object.autocomplete_action
            : undefined,
          name_startsWith: request.term,
          lang: zp_ajax_object.lang,
        },
        success: function (data) {
          $("#zp-fetch-offset").prop("disabled", true)
          // disable also the Add to Cart button in case of changing city after offset is calculated
          $(".single_add_to_cart_button").prop("disabled", true)

          response(
            $.map(data.geonames, function (item) {
              return {
                value:
                  item.name +
                  (item.adminName1 ? ", " + item.adminName1 : "") +
                  ", " +
                  item.countryName,
                label:
                  item.name +
                  (item.adminName1 ? ", " + item.adminName1 : "") +
                  ", " +
                  item.countryName,
                lngdeci: item.lng,
                latdeci: item.lat,
                timezoneid: item.timezone.timeZoneId,
              }
            })
          )
        },
      })
    },
    minLength: 2,
    select: function (event, ui) {
      $(".ui-state-error").hide()

      // Show loading gif so user will patiently wait for the Next button
      $("#zp-ajax-loader").css({ visibility: "visible" })

      // Insert hidden inputs with timezone ID and birthplace coordinates
      var hiddenInputs = {
        geo_timezone_id: ui.item.timezoneid,
        zp_lat_decimal: ui.item.latdeci,
        zp_long_decimal: ui.item.lngdeci,
      }
      for (var elID in hiddenInputs) {
        // Remove any previous in case they're changing the city
        var exists = document.getElementById(elID)
        if (null !== exists) {
          exists.remove()
        }
        // Insert hidden inputs
        elInput = document.createElement("input")
        elInput.setAttribute("type", "hidden")
        elInput.id = elID
        elInput.setAttribute("name", elID)
        elInput.setAttribute("value", hiddenInputs[elID])
        document.getElementById("zp-timezone-id").appendChild(elInput)
      }

      // Reset the Offset section in case of changing city.
      $("#zp-offset-wrap").hide()
      $(".single_add_to_cart_button").hide()
      $("#zp-form-tip").hide()
      $("#zp-fetch-offset").show()
      $("#zp-ajax-loader").css({ visibility: "hidden" })

      // Enable the button
      $("#zp-fetch-offset").prop("disabled", false)
    },
  })

  // Fill in time offset upon clicking Next.

  $("#zp-fetch-offset").on("click", function (e) {
    var data = {
      action: "zp_tz_offset",
      post_data: $("#zp-ajax-birth-data :input").serialize(),
    }
    $.ajax({
      url: zp_ajax_object.ajaxurl,
      type: "POST",
      data: data,
      dataType: "json",
      success: function (data) {
        if (data.error) {
          $(".ui-state-error").hide()
          var span = $("<span />")
          span.attr("class", "ui-state-error")
          span.text(data.error)
          $("#zp-ajax-birth-data").append(span)
        } else {
          // if not null, blank, nor false
          if ($.trim(data.offset_geo) && "false" != $.trim(data.offset_geo)) {
            $(".ui-state-error").hide()

            // Display offset.
            $("#zp-offset-wrap").show()
            $("#zp-offset-label").text(zp_ajax_object.utc + " ")
            $("#zp_offset_geo").val(data.offset_geo)
            $("#zp-form-tip").show()

            // Switch buttons
            $("#zp-fetch-offset").hide()
            $(".single_add_to_cart_button").show()
            $(".single_add_to_cart_button").prop("disabled", false)

            // Move the Add to cart button up
            $("form.cart").appendTo("#zp-submit-wrap")
          }
        }
      },
    })
    return false
  })

  // Upon clicking Add to Cart, save the form data
  $(".single_add_to_cart_button").on("click", onAddToCart)

  function onAddToCart(evt) {
    evt.preventDefault()

    $(".single_add_to_cart_button").prop("disabled", true)

    var zpFormData = $("#zp-birthreport-form").serialize()

    const req = $.ajax({
      url: zp_ajax_object.ajaxurl,
      type: "POST",
      data: {
        action: "zpsr_cart_item_form_data",
        zp_form_data: zpFormData,
      },
    })

    req.success((res) => {
      console.log(res)
    })
  }

  // Reset the Offset if date or time is changed.

  $("#month, #day, #year, #hour, #minute").on("change", function () {
    var changed = !this.options[this.selectedIndex].defaultSelected
    if (changed) {
      $("#zp-offset-wrap").hide()
      $(".single_add_to_cart_button").hide()
      $("#zp-form-tip").hide()
      $("#zp-fetch-offset").show()
    }
  })
})(jQuery)
