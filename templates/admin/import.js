var $j = jQuery.noConflict();

$j(function() {

	$j("#skip_overview").click(function(){
		$j("#skip_overview_form").submit();
	});

	$j("#new-event-button").click(function(){
		$j(".popups-overlay").show();
		$j("#new-event-popup").show();
		$j("html, body").animate({ scrollTop: 0 }, "slow");
	});

	$j("#cancel-new-event-request-button").click(function(){
		$j(".popups-overlay").hide();
		$j("#new-event-popup").hide();
	});

	$j("#new-ticket-button").click(function(){
		$j(".popups-overlay").show();
		$j("#new-ticket-popup").show();
		$j("html, body").animate({ scrollTop: 0 }, "slow");
	});

	$j("#cancel-new-ticket-request-button").click(function(){
		$j(".popups-overlay").hide();
		$j("#new-ticket-popup").hide();
	});

	$j(".close-form").click(function(){
		$j(".popups-overlay").hide();
		$j("#new-event-popup").hide();
		$j("#new-ticket-popup").hide();
	});

	$j(document).on("click", ".close-icon", function() {
		$j(this).closest("table.table-ticket").remove();
	});

	// Upload methods
	$j("input[name=upload_method]").click(function () {
		$j(".upload-method").hide();
		switch($j("input[name=upload_method]:checked").val()) {

			case "upload":
				$j("#import-products-filters-upload").show();
				break;

			case "file_path":
				$j("#import-products-filters-file_path").show();
				break;

			case "url":
				$j("#import-products-filters-url").show();
				break;

			case "ftp":
				$j("#import-products-filters-ftp").show();
				break;

		}
	});

	// Unselect all field options for this export type
	$j(".unselectall").click(function () {
		$j(this).closest(".widefat").find("option:selected").attr("selected", false);
	});

	i = 1;
	$j("#new-event-ticket-button").click(function () {
		$j("#new-ticket-anchor").before(`<table class="form-table table-ticket"><tbody>
			<tr id="new-event-ticket-settings">
				<td colspan="2">
					<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Ticket #${i+1}</h3>
					<span class="close-icon right"></span>
				</td>
			</tr>
			<tr>
				<th>
					<label for="ticket_title${i}">Title *</label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_title${i}" name="ticket[${i}][primary_text_pl]" value="" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_description${i}">Description</label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_description${i}" name="ticket[${i}][secondary_text_pl]" value="" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_price${i}">Price *</label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_price${i}" name="ticket[${i}][price]" value="" class="text" />
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_currency${i}">Currency</label>
				</th>
				<td>
					<select name="ticket[${i}][currency]" id="ticket_currency${i}">
						<option value="BGN">BGN</option>
						<option value="EUR">EUR</option>
						<option value="USD">USD</option>
					</select>
				</td>
			</tr>

			<tr>
				<th>
					<label for="ticket_stock${i}">Stock *</label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_stock${i}" name="ticket[${i}][stock]" value="" class="text" />
				</td>
			</tr><tr><td>* - required fields</td></tr></tbody></table>
		`);

		setValidators();

		i++;
	});

	$j(document).ready(function() {
		var type = $j("input:radio[name=upload_method]:checked").val();
		$j("#file-filters-"+type).trigger("click");
	});

	var rowTicketHtml = [];
	$j(document).on('click', '.edit-ticket-row', function() {
		var rowId = $j(this).parent().attr('id');
		var id = rowId.substring(4);
		rowTicketHtml[id] = $j(this).parent().html();

		var titleTdValue = $j('#tickets #' + rowId + ' .ticket-title').html();
		$j('#tickets #' + rowId + ' .ticket-title').replaceWith('<td class="ticket-title"><input type="text" name="ticket_primary_text_pl" value="' + titleTdValue + '"></td>');

		var priceTdValue = $j('#tickets #' + rowId + ' .price').html();
		$j('#tickets #' + rowId + ' .price').replaceWith('<td class="price"><input type="text" name="ticket_price" value="' + priceTdValue + '"></td>');

		var currencyTdValue = $j('#tickets #' + rowId + ' .currency').html();
		$j('#tickets #' + rowId + ' .currency').replaceWith('<td class="currency"><select name="ticket_currency" class="currency"><option value="BGN">BGN</option><option value="EUR">EUR</option><option value="USD">USD</option></select></td>');
		$j('#tickets #' + rowId + ' select.currency').val(currencyTdValue);

		var stockTdValue = $j('#tickets #' + rowId + ' .stock').html();
		$j('#tickets #' + rowId + ' .stock').replaceWith('<td class="stock"><input type="text" name="ticket_stock" value="' + stockTdValue + '"></td>');

		var skuTdValue = $j('#tickets #' + rowId + ' .sku').html();
		$j('#tickets #' + rowId).append('<td class="hidden"><input type="hidden" name="ticket_sku" value="' + skuTdValue + '"></td>');

		$j('#tickets #' + rowId + ' .edit-ticket-row').replaceWith('<td><input type="submit" id="request-ticket-change" class="button button-primary" value="Request" name="request-ticket-change"><input type="button" id="cancel-ticket-change" class="button button-primary" value="Cancel" name="cancel-ticket-change"></td>');

		setValidators();
	});

	$j(document).on('click', '#cancel-ticket-change', function() {
		var rowId = $j(this).parent().parent().attr('id');
		var id = rowId.substring(4);
		$j('#tickets #' + rowId).replaceWith('<tr id="' + rowId + '">' + rowTicketHtml[id] + '</tr>');
	});

	var rowEventHtml = '';
	$j(document).on('click', '.edit-event-row', function() {
		rowEventHtml = $j(this).parent().html();
		var rowId = $j(this).parent().attr('id');

		var titleTdValue = $j('#events #' + rowId + ' .title').html();
		$j('#events #' + rowId + ' .title').replaceWith('<td class="title"><input type="text" name="event_primary_text_pl" value="' + titleTdValue + '"></td>');

		var hTextLocTdValue = $j('#events #' + rowId + ' .htext-loc').html();
		$j('#events #' + rowId + ' .htext-loc').replaceWith('<td class="htext-loc"><select name="primary_text_horizontal_location" class="htext-loc"><option value="left">left</option><option value="center" selected>center</option><option value="right">right</option></select></td>');
		$j('#events #' + rowId + ' select.htext-loc').val(hTextLocTdValue);

		var vTextLocTdValue = $j('#events #' + rowId + ' .vtext-loc').html();
		$j('#events #' + rowId + ' .vtext-loc').replaceWith('<td class="vtext-loc"><select name="primary_text_vertical_location" class="vtext-loc"><option value="top">top</option><option value="center" selected>center</option><option value="bottom">bottom</option></select></td>');
		$j('#events #' + rowId + ' select.vtext-loc').val(vTextLocTdValue);

		var ptFontSizeTdValue = $j('#events #' + rowId + ' .htext-fontsize').html();
		$j('#events #' + rowId + ' .htext-fontsize').replaceWith('<td class="htext-fontsize"><input type="text" name="badge_primary_text_fontsize" value="' + ptFontSizeTdValue + '"></td>');

		var stFontSizeTdValue = $j('#events #' + rowId + ' .vtext-fontsize').html();
		$j('#events #' + rowId + ' .vtext-fontsize').replaceWith('<td class="vtext-fontsize"><input type="text" name="badge_secondary_text_fontsize" value="' + stFontSizeTdValue + '"></td>');

		var ptFontColorTdValue = $j('#events #' + rowId + ' .htext-color').html();
		$j('#events #' + rowId + ' .htext-color').replaceWith('<td class="htext-color"><input type="text" name="badge_primary_text_color" value="' + ptFontColorTdValue + '"></td>');

		var stFontColorTdValue = $j('#events #' + rowId + ' .vtext-color').html();
		$j('#events #' + rowId + ' .vtext-color').replaceWith('<td class="vtext-color"><input type="text" name="badge_secondary_text_color" value="' + stFontColorTdValue + '"></td>');

		var eventIdTdValue = $j('#events #' + rowId + ' .event-id').html();
		$j('#events #' + rowId).append('<td class="hidden"><input type="hidden" name="event_id" value="' + eventIdTdValue + '"></td>');

		$j('#events #' + rowId + ' .edit-event-row').replaceWith('<td><input type="submit" id="request-event-change" class="button button-primary" value="Request"><input type="button" id="cancel-event-change" class="button button-primary" value="Cancel"></td>');

		setValidators();
	});

	$j(document).on('click', '#cancel-event-change', function() {
		var rowId = $j(this).parent().parent().attr('id');
		$j('#events #' + rowId).replaceWith('<tr id="' + rowId + '">' + rowEventHtml + '</tr>');
	});

	setValidators()
});

function setValidators() {
	$j.validator.addMethod(
		"htmlcolor",
		function(value, element, regexp) {
		  var re = new RegExp(regexp);
		  return this.optional(element) || re.test(value);
		},
		"Please enter a valid html color."
	  );

	jQuery.validator.setDefaults({
		success: "valid"
	});

	$j("#events-list").validate({
		errorElement: "p",
		errorClass: "form-error"
	});

	$j("#tickets-list").validate({
		errorElement: "p",
		errorClass: "form-error"
	});

	$j("#submit-new-event-request-form").validate({
		errorElement: "p",
		errorClass: "form-error"
	});

	$j("#submit-new-ticket-request-form").validate({
		errorElement: "p",
		errorClass: "form-error"
	});

	$j("#events-list").on('submit', function(e) {
        var isvalid = $j("#events-list").valid();
        if (!isvalid) {
            e.preventDefault();
            alert("Please fix the validation errors in the form");
        }
    });

	$j("#tickets-list").on('submit', function(e) {
        var isvalid = $j("#tickets-list").valid();
        if (!isvalid) {
            e.preventDefault();
            alert("Please fix the validation errors in the form");
        }
    });

	$j("#submit-new-event-request-form").on('submit', function(e) {
        var isvalid = $j("#submit-new-event-request-form").valid();
        if (!isvalid) {
            e.preventDefault();
            alert("Please fix the validation errors in the form");
        }
    });

	$j("#submit-new-ticket-request-form").on('submit', function(e) {
        var isvalid = $j("#submit-new-ticket-request-form").valid();
        if (!isvalid) {
            e.preventDefault();
            alert("Please fix the validation errors in the form");
        }
    });

	$j("input[name*='badge_primary_text_fontsize'], input[name*='badge_secondary_text_fontsize'], input[name*='badge_primary_text_fontsize'], input[name*='badge_secondary_text_fontsize'], input[name*='badge_primary_text_break_distance'], input[name*='badge_secondary_text_break_distance']").each(function() {
		$j(this).rules('add', {
			number: true
		});
	});

	$j("input[name*='badge_primary_text_color'], input[name*='badge_secondary_text_color']").each(function() {
		$j(this).rules('add', {
			htmlcolor: "^#[0-9a-f]{6}$"
		});
	});

	$j("input[name*='event_title'], input[name*='primary_text_pl'], input[name*='badge_file']").each(function() {
		$j(this).rules('add', {
			required: true
		});
	});

	$j("input[name*='stock']").each(function() {
		$j(this).rules('add', {
			required: true,
			digits: true
		});
	});

	$j("input[name*='price']").each(function() {
		$j(this).rules('add', {
			required: true,
			number: true
		});
	});

	$j("#generate_preview").click(function() {
		const file = $j('#badge_file')[0].files[0];
		console.log(file);
		if (file) {
			let reader = new FileReader();
			reader.onload = function(event) {
				var height = 0;
				var width = 0;

				// pixels per millimeter density
				var ppmm = 11.8110;

				var canvas = document.getElementById("badge_preview");
				ctx = canvas.getContext("2d");

				var background = new Image();
				background.onload = function() {
					var badgeSize = $j("#badge_size").val();
					switch (badgeSize) {
						case "A4":
							width = 210 * ppmm;
							height = 297 * ppmm;
							break;
						case "A5":
							width = 148 * ppmm;
							height = 210 * ppmm;
							break;
						case "A6":
							width = 105 * ppmm;
							height = 148 * ppmm;
							break;
						case "A7":
							width = 74 * ppmm;
							height = 105 * ppmm;
							break;
						case "A8":
							width = 52 * ppmm;
							height = 74 * ppmm;
							break;
						case "A9":
							width = 37 * ppmm;
							height = 52 * ppmm;
							break;
						case "A10":
							width = 26 * ppmm;
							height = 37 * ppmm;
							break;
					}

					ctx.canvas.width  = width;
  					ctx.canvas.height = height;

					ctx.drawImage(background, 0, 0);

					var primaryText = $j("#badge_primary_test_text").val();
					var primaryTextBreakDistance = parseFloat($j("#badge_primary_text_break_distance").val());
					var primaryTextFontSize = parseFloat($j("#badge_primary_text_fontsize").val());
					var primaryTextFontColor = $j("#badge_primary_text_color").val();

					var primaryTextHorizontalOffset = $j("#badge_primary_text_horizontal_offset").val();
					var primaryTextVerticalOffset = $j("#badge_primary_text_vertical_offset").val();
					var primaryTextHorizontalLocation = $j("#badge_primary_text_horizontal_location").val();
					var primaryTextVerticalLocation = $j("#badge_primary_text_vertical_location").val();

					var font = primaryTextFontSize + 'pt  Arial';
					ctx.font = font;
					
					ctx.fillStyle = primaryTextFontColor;
					if (primaryTextBreakDistance) {
						primaryText = primaryText.split(' ');
						for (var i = 0; i < primaryText.length; i++) {
							let coordinates = getTextCoordinates(
								ctx,
								primaryText[i],
								width,
								height,
								primaryTextHorizontalLocation,
								primaryTextVerticalLocation,
								primaryTextHorizontalOffset,
								primaryTextVerticalOffset
							);
    						ctx.fillText(
								primaryText[i],
								coordinates.x,
								coordinates.y + (i * (primaryTextFontSize + primaryTextBreakDistance))
							);
						}
					} else {
						let coordinates = getTextCoordinates(
							ctx,
							primaryText,
							width,
							height,
							primaryTextHorizontalLocation,
							primaryTextVerticalLocation,
							primaryTextHorizontalOffset,
							primaryTextVerticalOffset
						);
						ctx.fillText(primaryText, coordinates.x, coordinates.y);
					}
					
					var secondaryText = $j("#badge_secondary_test_text").val();
					var secondaryTextBreakDistance = parseFloat($j("#badge_secondary_text_break_distance").val());
					var secondaryTextFontSize = parseFloat($j("#badge_secondary_text_fontsize").val());
					var secondaryTextFontColor = $j("#badge_secondary_text_color").val();

					var secondaryTextHorizontalOffset = $j("#badge_secondary_text_horizontal_offset").val();
					var secondaryTextVerticalOffset = $j("#badge_secondary_text_vertical_offset").val();
					var secondaryTextHorizontalLocation = $j("#badge_secondary_text_horizontal_location").val();
					var secondaryTextVerticalLocation = $j("#badge_secondary_text_vertical_location").val();

					var font = secondaryTextFontSize + 'pt  Arial';
					ctx.font = font;

					ctx.fillStyle = secondaryTextFontColor;
					if (secondaryTextBreakDistance > 0) {
						secondaryText = secondaryText.split(' ');
						for (var i = 0; i < secondaryText.length; i++) {
							let coordinates = getTextCoordinates(
								ctx,
								secondaryText[i],
								width,
								height,
								secondaryTextHorizontalLocation,
								secondaryTextVerticalLocation,
								secondaryTextHorizontalOffset,
								secondaryTextVerticalOffset
							);
    						ctx.fillText(
								secondaryText[i],
								coordinates.x,
								coordinates.y + (i * (secondaryTextFontSize + secondaryTextBreakDistance))
							);
						}

					} else {
						let coordinates = getTextCoordinates(
							ctx,
							secondaryText,
							width,
							height,
							secondaryTextHorizontalLocation,
							secondaryTextVerticalLocation,
							secondaryTextHorizontalOffset,
							secondaryTextVerticalOffset
						);
						ctx.fillText(secondaryText, coordinates.x, coordinates.y);
					}
				}

				background.src = event.target.result;
			}

			reader.readAsDataURL(file);
		}
	});
}

function getTextCoordinates(ctx, text, width, height, textHorizontalLocation, textVerticalLocation, textHorizontalOffset, textVerticalOffset) {
	let xCoordinate = 0;
	let yCoordinate = 0;
	let textWidthMeasured;
	let textHeightMeasured;
	switch (textHorizontalLocation) {
		case "left":
			xCoordinate = 0 + width * textHorizontalOffset / 100;
			break;

		case "center":
			textWidthMeasured = ctx.measureText(text);
			xCoordinate = width / 2 - textWidthMeasured.width / 2 + width * textHorizontalOffset / 100;
			break;

		case "right":
			textWidthMeasured = ctx.measureText(text);
			xCoordinate = width - textWidthMeasured.width - width * textHorizontalOffset / 100;
			break;
	}

	switch (textVerticalLocation) {
		case "top":
			textHeightMeasured = parseInt(ctx.font.match(/\d+/), 10);
			yCoordinate = 0 + textHeightMeasured + height * textVerticalOffset / 100;
			break;

		case "center":
			textHeightMeasured = parseInt(ctx.font.match(/\d+/), 10);
			yCoordinate = height / 2 + textHeightMeasured / 2 + height * textVerticalOffset / 100;
			break;

		case "bottom":
			textHeightMeasured = parseInt(ctx.font.match(/\d+/), 10);
			yCoordinate = height  - height * textVerticalOffset / 100;
			break;
	}

	return { x: xCoordinate, y: yCoordinate };
}

// function getTextHeight(t, font) {
// 	var text = $j('<span>' + t + '</span>').css({ fontFamily: font });
// 	var block = $j('<div style="display: inline-block; width: 1px; height: 0px;"></div>');

// 	var div = $j('<div></div>');
// 	div.append(text, block);

// 	var body = $j('body');
// 	body.append(div);

// 	try {
// 		var result = {};

// 		block.css({ verticalAlign: 'baseline' });
// 		result.ascent = block.offset().top - text.offset().top;

// 		block.css({ verticalAlign: 'bottom' });
// 		result.height = block.offset().top - text.offset().top;

// 		result.descent = result.height - result.ascent;
// 	} finally {
// 		div.remove();
// 	}

// 	return result;
// }