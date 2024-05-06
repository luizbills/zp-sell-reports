/* Disable Add to Cart button until form is filled */
var zpAddToCart = document.querySelector(".single_add_to_cart_button")
zpAddToCart.setAttribute("disabled", true)

/* Move the Add to Cart button up under city field */
jQuery("form.cart").appendTo("#zp-submit-wrap")

/**
 * Ajax request to get time offset
 */
function zpGetOffset() {
  const zpFormData =
    jQuery("#zp-ajax-birth-data :input").serialize() + "&action=zp_tz_offset"
  const xhr = new XMLHttpRequest()
  xhr.open("POST", zp_ajax_object.ajaxurl)
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
  xhr.responseType = "json"

  xhr.onload = function () {
    if (xhr.status === 200 && xhr.response) {
      if (xhr.response.error) {
        /* remove previous errors if any */
        zpRemoveError()

        /* show new error */
        var span = document.createElement("span")
        span.setAttribute("class", "ui-state-error")
        span.textContent = xhr.response.error
        document.getElementById("zp-ajax-birth-data").appendChild(span)
      } else {
        /* if not null, blank, nor false, but 0 is okay  */
        if (
          null !== xhr.response.offset_geo &&
          "" !== xhr.response.offset_geo &&
          "false" != xhr.response.offset_geo
        ) {
          /* remove previous errors if any */
          zpRemoveError()

          /* Display offset. */
          document.getElementById("zp-offset-wrap").style.display = "block"
          document.getElementById("zp-offset-label").textContent =
            zp_ajax_object.utc + " "
          document.getElementById("zp_offset_geo").value =
            xhr.response.offset_geo
          document.getElementById("zp-form-tip").style.display = "block"

          /* Enable Add to Cart button */
          zpAddToCart.removeAttribute("disabled")
        }
      }
    }
  }

  xhr.send(zpFormData)
}

;(function ($) {
  /* Remove the submit button and core ZP action field. */
  $("#zp-fetch-birthreport").remove()
  $("input[name='action']").remove()

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
      const error = res.data.error
      $(".single_add_to_cart_button").prop("disabled", false)
      if (error) {
        const div = document.createElement("div")
        div.setAttribute("class", "ui-state-error")
        div.textContent = error
        document.querySelector("form.cart").prepend(div)
      } else {
        $(".single_add_to_cart_button").off("click", onAddToCart)
        $(".single_add_to_cart_button").trigger("click")
      }
    })
  }

  /* Resets the Offset if date or time is changed. */
  $("#month, #day, #year, #hour, #minute").on("change", function () {
    var changed = !this.options[this.selectedIndex].defaultSelected
    if (changed) {
      /* Only do ajax (get offset) if (partial) required fields are entered. */
      if (zpFieldsFilled()) {
        zpGetOffset()
      }
    }
  })
})(jQuery)

/**
 * Check that the fields required to get offset are entered.
 */
function zpFieldsFilled() {
  var ids = [
    "geo_timezone_id",
    "zp_long_decimal",
    "zp_lat_decimal",
    "place",
    "minute",
    "hour",
    "year",
    "day",
    "month",
  ]

  for (var i of ids) {
    var el = document.getElementById(i)

    if (null === el) {
      return false
    }

    if (el.value.length === 0 || !el.value.trim()) {
      /* fail */
      return false
    }
  }

  return true
}

/**
 * Remove form error notices
 */
function zpRemoveError() {
  var el = document.querySelector(".ui-state-error")
  if (el !== null) {
    el.parentNode.removeChild(el)
  }
}
