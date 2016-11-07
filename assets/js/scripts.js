

jQuery(document).ready(function($) {

	var $body = $('body');
    var $orderSummaryLayer = $('.order-summary-top-layer');

	$body.on('change', '#billing_country', function() {
		taxamocheckipmatch();
		var euCountry = isEUCountry($(this).val());
		var vatElem = $("#edd-vat-reg-number-wrap, .vat-note");
		if (euCountry) {
			vatElem.css('display', 'inline-block');
		} else {
			vatElem.hide();
			vatElem.find('#vat_number').val('');
		}

		$orderSummaryLayer.show();
	});

    $body.on('submit', '#edd_purchase_form', function () {
        if (!$("#vat_number").val().trim()) {
            $('#edd-vatreg').attr('name', '');
        }
    });

    var $error = $('<div class="edd_errors edd-alert edd-alert-error mm-invalid-vat-number"><p class="edd_error"><strong>Error</strong>: The VAT number is invalid.</p></div>');

	var timeout = null,
        checkVatFired = false;
	$body.on('keyup', '#vat_number', function () {
		var that = this;
		if (timeout !== null) {
			clearTimeout(timeout);
		}
		timeout = setTimeout(function () {
			var val = $(that).val().trim();
			var id = val ? 'VAT' : '';
			$orderSummaryLayer.show();
			recalculate_taxes(id);
            checkVatFired = true;
            checkVat($('#vat_number').val(), function(response) {
                $('.mm-invalid-vat-number').remove();

                if (response == 'vat_not_valid') {
                    $('#edd_checkout_wrap #edd-purchase-button').attr('disabled', 'disabled');

                    if ($('#edd_error_taxedd-invalid-vat-number').length == 0) {
                        $error.clone().prependTo('#braintree_purchase_submit');
                    }
                } else if (response == 'vat_valid' || response == 'vat_empty') {

                    $('#edd_checkout_wrap #edd-purchase-button').removeAttr('disabled');

                    var $defaultVatError = $('#edd_error_taxedd-invalid-vat-number');
                    var $defaultVatErrorParent = $defaultVatError.parent();
                    $defaultVatError.remove();
                    if ($defaultVatErrorParent.children().length == 0) {
                        $defaultVatErrorParent.remove()
                    }
                }
                checkVatFired = false;
                $orderSummaryLayer.hide();
            })
		}, 650);
	});

	if ($('.edd_cart_tax_amount').data('tax') > 0) {
		$('body').prepend(
			'<style>' +
				'#edd_purchase_form #edd-vat-reg-number-wrap { display: inline-block; }' +
                '#edd_checkout_wrap .vat-note { display: block }' +
			'</style>');
	}

	$('body').on('edd_taxes_recalculated', function (e, data) {
		var $mm_tax_amount = $('.mm_cart_tax_amount');
		var newTax = data.response.tax;
		$mm_tax_amount.text(newTax);
        if (!checkVatFired) {
            $orderSummaryLayer.hide();
        }

	});

    $('body').on('keypress', 'form#edd_purchase_form :input', function(e) {
        return e.which !== 13;
    });
});

function taxamocheckipmatch() {
	var iplocation = jQuery( "#edd-country").val();
    var billinglocation = jQuery("#billing_country").val();

    if (window.console) console.log('IP Location: ' + iplocation);
    if (window.console) console.log('Billing Location: ' + billinglocation);
    
    if (iplocation == billinglocation) {
    	jQuery( "#edd-confirmation-checkbox" ).hide(1);
    } else {
    	jQuery( "#edd-confirmation-checkbox" ).show(1);
    }
}

function checkVat(vatNum, callback) {
    var postData = {
        action: 'mm_check_vat',
        vat_number: vatNum
    };

    jQuery.ajax({
        type: "POST",
        data: postData,
        dataType: "text",
        url: edd_global_vars.ajaxurl,
        xhrFields: {
            withCredentials: true
        },
        success: function (vat_response) {
            if (callback != undefined && typeof callback == "function") {
                callback(vat_response)
            }
        }
    }).fail(function (data) {
            console.log( data );
    });
}



function isEUCountry(countryCode) {
    var EUCOUNTRIES = [
        'AT',
        'BE',
        'BG',
        'CY',
        'CZ',
        'DE',
        'DK',
        'EE',
        'EL',
        'ES',
        'FI',
        'FR',
        'GB',
        'GR',
        'HR',
        'HU',
        'IE',
        'IT',
        'LT',
        'LU',
        'LV',
        'MT',
        'NL',
        'PL',
        'PT',
        'RO',
        'SE',
        'SI',
        'SK'
    ];

    return EUCOUNTRIES.indexOf(countryCode) > -1;
}

/*
jQuery(document).ready(function($)
{
    var func = function(e, data)
    {
        //data.data is a string with &seperated values, e.g a=b&c=d&.. .
        //Append additional variables to it and they'll be submitted with the request:
        if (/action=edd_recalculate_taxes/.test(data.data) && /state=VAT/.test(data.data)) {
            data.data += "&vat_number=" + $('#vat_number').val();
        }

        return true;
    };
    jQuery.ajaxSetup( {beforeSend: func} );
} );
*/
