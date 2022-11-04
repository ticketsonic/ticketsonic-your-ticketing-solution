var $j = jQuery.noConflict();

$j(function() {

	$j("#skip_overview").click(function(){
		$j("#skip_overview_form").submit();
	});

	$j("#new-event-button").click(function() {
		$j(".popups-overlay").show();
		insertEventForm();

		$j("html, body").animate({ scrollTop: 0 }, "slow");
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

	$j("body").on("click", "#close-event-request-popup, #cancel-new-event-request-button", function(){
		$j(".popups-overlay").hide();
		$j("#event-request-popup").remove();
	});

	$j(document).on("click", ".close-icon", function() {
		$j(this).closest("table.table-ticket").remove();
	});

	i = 1;
	$j("body").on("click", "#new-event-ticket-button", function () {
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
		var eventId = $j('#events #' + rowId + ' .event-id').html();
		var eventStarttime = $j('#events #' + rowId + ' .event-start-time').html();
		var eventLocation = $j('#events #' + rowId + ' .event-location').html();
		var eventTitle = $j('#events #' + rowId + ' .event-primary').html();
		var eventDescription = $j('#events #' + rowId + ' .event-secondary').html();

		var badgeSize = $j('#events #' + rowId + ' .badge-size').html();
		var badgeBackgroundFilePath = $j('#events #' + rowId + ' img.badge-background').attr('src');

		var badgePrHtextloc = $j('#events #' + rowId + ' .badge-pr-htext-loc').html();
		var badgePrHtextOffset = $j('#events #' + rowId + ' .badge-pr-htext-offset').html();
		var badgePrVtextLoc = $j('#events #' + rowId + ' .badge-pr-vtext-loc').html();
		var badgePrVtextOffset = $j('#events #' + rowId + ' .badge-pr-vtext-offset').html();
		var badgePrFontsize = $j('#events #' + rowId + ' .badge-pr-fontsize').html();
		var badgePrColor = $j('#events #' + rowId + ' .badge-pr-color').html();
		var badgePrBrDistance = $j('#events #' + rowId + ' .badge-pr-br-distance').html();
		var badgePrTestText = $j('#events #' + rowId + ' .badge-pr-test-text').html();

		var badgeScHtextloc = $j('#events #' + rowId + ' .badge-sc-htext-loc').html();
		var badgeScHtextOffset = $j('#events #' + rowId + ' .badge-sc-htext-offset').html();
		var badgeScVtextLoc = $j('#events #' + rowId + ' .badge-sc-vtext-loc').html();
		var badgeScVtextOffset = $j('#events #' + rowId + ' .badge-sc-vtext-offset').html();
		var badgeScFontsize = $j('#events #' + rowId + ' .badge-sc-fontsize').html();
		var badgeScColor = $j('#events #' + rowId + ' .badge-sc-color').html();
		var badgeScTestText = $j('#events #' + rowId + ' .badge-sc-test-text').html();
		var badgeScBrDistance = $j('#events #' + rowId + ' .badge-sc-br-distance').html();

		insertEventForm(
			'edit',
			eventId,
			eventStarttime,
			eventLocation,
			eventTitle,
			eventDescription,
			badgeSize,
			badgeBackgroundFilePath,
			badgePrHtextloc,
			badgePrHtextOffset,
			badgePrVtextLoc,
			badgePrVtextOffset,
			badgePrFontsize,
			badgePrColor,
			badgePrBrDistance,
			badgePrTestText,
			badgeScHtextloc,
			badgeScHtextOffset,
			badgeScVtextLoc,
			badgeScVtextOffset,
			badgeScFontsize,
			badgeScColor,
			badgeScBrDistance,
			badgeScTestText
		);

		setValidators();

		$j("html, body").animate({ scrollTop: 0 }, "slow");
	});

	$j(document).on('click', '#cancel-event-change', function() {
		var rowId = $j(this).parent().parent().attr('id');
		$j('#events #' + rowId).replaceWith('<tr id="' + rowId + '">' + rowEventHtml + '</tr>');
	});

	$j("body").on("click", "#generate_preview", function() {
		var file = $j('#badge_file')[0].files[0];
		var badgeBackgroundFilePath = $j('#event-request-popup img#badge_file_preview').attr('src');
		var badgeSize = $j("#badge_size").val();

		var primaryText = $j("#badge_primary_test_text").val();
		var primaryTextBreakDistance = parseFloat($j("#badge_primary_text_break_distance").val());
		var primaryTextFontSize = parseFloat($j("#badge_primary_text_fontsize").val());
		var primaryTextFontColor = $j("#badge_primary_text_color").val();
		var primaryTextHorizontalOffset = $j("#badge_primary_text_horizontal_offset").val();
		var primaryTextVerticalOffset = $j("#badge_primary_text_vertical_offset").val();
		var primaryTextHorizontalLocation = $j("#badge_primary_text_horizontal_location").val();
		var primaryTextVerticalLocation = $j("#badge_primary_text_vertical_location").val();

		var secondaryText = $j("#badge_secondary_test_text").val();
		var secondaryTextBreakDistance = parseFloat($j("#badge_secondary_text_break_distance").val());
		var secondaryTextFontSize = parseFloat($j("#badge_secondary_text_fontsize").val());
		var secondaryTextFontColor = $j("#badge_secondary_text_color").val();
		var secondaryTextHorizontalOffset = $j("#badge_secondary_text_horizontal_offset").val();
		var secondaryTextVerticalOffset = $j("#badge_secondary_text_vertical_offset").val();
		var secondaryTextHorizontalLocation = $j("#badge_secondary_text_horizontal_location").val();
		var secondaryTextVerticalLocation = $j("#badge_secondary_text_vertical_location").val();

		badgeBuilder(
			"badge_preview",
			file,
			badgeBackgroundFilePath,
			badgeSize,
			primaryText,
			primaryTextBreakDistance,
			primaryTextFontSize,
			primaryTextFontColor,
			primaryTextHorizontalOffset,
			primaryTextVerticalOffset,
			primaryTextHorizontalLocation,
			primaryTextVerticalLocation,
			secondaryText,
			secondaryTextBreakDistance,
			secondaryTextFontSize,
			secondaryTextFontColor,
			secondaryTextHorizontalOffset,
			secondaryTextVerticalOffset,
			secondaryTextHorizontalLocation,
			secondaryTextVerticalLocation
		);
	});

	$j("body").on("click", "#toggle-badge-details", function() {
		$j(".badge-foldable").toggle();
		if ($j(".badge-foldable").is(":hidden")) {
			$j(".heading-badge").attr("colspan", 1);
			$j("#events-list #events").removeClass("bordered-table");
			$j(this).text("Show details");
		} else {
			$j(".heading-badge").attr("colspan", 19);
			$j("#events-list #events").addClass("bordered-table");
			$j(this).text("Hide details");
		}
	});

	$j(".badge-show-preview").click(function() {
		$j(this).parent().find(".badge-canvas").toggle();
		if ($j(this).parent().find(".badge-canvas").is(":hidden")) {
			$j(this).text("Show preview");
		} else {
			$j(this).text("Hide preview");
		}
	});

	$j(document).ready(function() {
		var eventRows = $j("table#events tr.event-row");
		eventRows.each(function(i) {
			var badgeSize = $j(this).find("td.badge-size").text();
			var badgeBackgroundFilePath = $j(this).find("img.badge-background").attr("src");
			var badgePrimaryText = $j(this).find("td.badge-pr-test-text").text();
			var badgePrimaryTextBreakDistance = parseInt($j(this).find("td.badge-pr-br-distance").text());
			var badgePrimaryTextHorizontalLocation = $j(this).find("td.badge-pr-htext-loc").text();
			var badgePrimaryTextHorizontalOffset = parseInt($j(this).find("td.badge-pr-htext-offset").text());
			var badgePrimaryTextVerticalLocation = $j(this).find("td.badge-pr-vtext-loc").text();
			var badgePrimaryTextVerticalOffset = parseInt($j(this).find("td.badge-pr-vtext-offset").text());
			var badgePrimaryTextFontSize = parseInt($j(this).find("td.badge-pr-fontsize").text());
			var badgePrimaryTextFontColor = $j(this).find("td.badge-pr-color").text();

			var badgeSecondaryTextHorizontalLocation = $j(this).find("td.badge-sc-htext-loc").text();
			var badgeSecondaryTextHorizontalOffset = parseInt($j(this).find("td.badge-sc-htext-offset").text());
			var badgeSecondaryTextVerticalLocation = $j(this).find("td.badge-sc-vtext-loc").text();
			var badgeSecondaryTextVerticalOffset = parseInt($j(this).find("td.badge-sc-vtext-offset").text());
			var badgeSecondaryTextFontSize = parseInt($j(this).find("td.badge-sc-fontsize").text());
			var badgeSecondaryTextFontColor = $j(this).find("td.badge-sc-color").text();
			var badgeSecondaryTextBreakDistance = parseInt($j(this).find("td.badge-sc-br-distance").text());
			var badgeSecondaryText = $j(this).find("td.badge-sc-test-text").text();


			badgeBuilder(
				"badge-preview-" + i,
				null,
				badgeBackgroundFilePath,
				badgeSize,
				badgePrimaryText,
				badgePrimaryTextBreakDistance,
				badgePrimaryTextFontSize,
				badgePrimaryTextFontColor,
				badgePrimaryTextHorizontalOffset,
				badgePrimaryTextVerticalOffset,
				badgePrimaryTextHorizontalLocation,
				badgePrimaryTextVerticalLocation,
				badgeSecondaryText,
				badgeSecondaryTextBreakDistance,
				badgeSecondaryTextFontSize,
				badgeSecondaryTextFontColor,
				badgeSecondaryTextHorizontalOffset,
				badgeSecondaryTextVerticalOffset,
				badgeSecondaryTextHorizontalLocation,
				badgeSecondaryTextVerticalLocation
			)
		});

		$j("#toggle-badge-details").click();
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

	$j("input[name*='event_title'], input[name*='primary_text_pl']").each(function() {
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
}

function badgeBuilder(
	targetCanvasId,
	file,
	path,
	badgeSize,
	primaryText,
	primaryTextBreakDistance,
	primaryTextFontSize,
	primaryTextFontColor,
	primaryTextHorizontalOffset,
	primaryTextVerticalOffset,
	primaryTextHorizontalLocation,
	primaryTextVerticalLocation,
	secondaryText,
	secondaryTextBreakDistance,
	secondaryTextFontSize,
	secondaryTextFontColor,
	secondaryTextHorizontalOffset,
	secondaryTextVerticalOffset,
	secondaryTextHorizontalLocation,
	secondaryTextVerticalLocation
) {
	if (file) {
		let reader = new FileReader();
		reader.onload = function(event) {
			var image = new Image();
			image.onload = function() {
				drawBadge(
					targetCanvasId,
					image,
					badgeSize,
					primaryText,
					primaryTextBreakDistance,
					primaryTextFontSize,
					primaryTextFontColor,
					primaryTextHorizontalOffset,
					primaryTextVerticalOffset,
					primaryTextHorizontalLocation,
					primaryTextVerticalLocation,
					secondaryText,
					secondaryTextBreakDistance,
					secondaryTextFontSize,
					secondaryTextFontColor,
					secondaryTextHorizontalOffset,
					secondaryTextVerticalOffset,
					secondaryTextHorizontalLocation,
					secondaryTextVerticalLocation
				);
			};

			image.src = event.target.result;
		}

		reader.readAsDataURL(file);
	} else if (path) {
		var background = new Image();
		background.onload = function() {
			drawBadge(
				targetCanvasId,
				background,
				badgeSize,
				primaryText,
				primaryTextBreakDistance,
				primaryTextFontSize,
				primaryTextFontColor,
				primaryTextHorizontalOffset,
				primaryTextVerticalOffset,
				primaryTextHorizontalLocation,
				primaryTextVerticalLocation,
				secondaryText,
				secondaryTextBreakDistance,
				secondaryTextFontSize,
				secondaryTextFontColor,
				secondaryTextHorizontalOffset,
				secondaryTextVerticalOffset,
				secondaryTextHorizontalLocation,
				secondaryTextVerticalLocation
			);
		}

		background.src = path;
	} else {
		drawBadge(
			targetCanvasId,
			null,
			badgeSize,
			primaryText,
			primaryTextBreakDistance,
			primaryTextFontSize,
			primaryTextFontColor,
			primaryTextHorizontalOffset,
			primaryTextVerticalOffset,
			primaryTextHorizontalLocation,
			primaryTextVerticalLocation,
			secondaryText,
			secondaryTextBreakDistance,
			secondaryTextFontSize,
			secondaryTextFontColor,
			secondaryTextHorizontalOffset,
			secondaryTextVerticalOffset,
			secondaryTextHorizontalLocation,
			secondaryTextVerticalLocation
		);
	}
}

function drawBadge(
	targetCanvasId,
	background,
	badgeSize,
	primaryText,
	primaryTextBreakDistance,
	primaryTextFontSize,
	primaryTextFontColor,
	primaryTextHorizontalOffset,
	primaryTextVerticalOffset,
	primaryTextHorizontalLocation,
	primaryTextVerticalLocation,
	secondaryText,
	secondaryTextBreakDistance,
	secondaryTextFontSize,
	secondaryTextFontColor,
	secondaryTextHorizontalOffset,
	secondaryTextVerticalOffset,
	secondaryTextHorizontalLocation,
	secondaryTextVerticalLocation
) {
	var height = 0;
	var width = 0;

	// pixels per millimeter density
	var ppmm = 11.8110;

	var canvas = document.getElementById(targetCanvasId);
	ctx = canvas.getContext("2d");

	// var badgeSize = $j("#badge_size").val();
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

	if (background)
		ctx.drawImage(background, 0, 0);
	else {
		ctx.fillStyle = "#FFFFFF";
		ctx.fillRect(0, 0, width, height);
	}

	// var primaryText = $j("#badge_primary_test_text").val();
	// var primaryTextBreakDistance = parseFloat($j("#badge_primary_text_break_distance").val());
	// var primaryTextFontSize = parseFloat($j("#badge_primary_text_fontsize").val());
	// var primaryTextFontColor = $j("#badge_primary_text_color").val();

	// var primaryTextHorizontalOffset = $j("#badge_primary_text_horizontal_offset").val();
	// var primaryTextVerticalOffset = $j("#badge_primary_text_vertical_offset").val();
	// var primaryTextHorizontalLocation = $j("#badge_primary_text_horizontal_location").val();
	// var primaryTextVerticalLocation = $j("#badge_primary_text_vertical_location").val();

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
	
	// var secondaryText = $j("#badge_secondary_test_text").val();
	// var secondaryTextBreakDistance = parseFloat($j("#badge_secondary_text_break_distance").val());
	// var secondaryTextFontSize = parseFloat($j("#badge_secondary_text_fontsize").val());
	// var secondaryTextFontColor = $j("#badge_secondary_text_color").val();

	// var secondaryTextHorizontalOffset = $j("#badge_secondary_text_horizontal_offset").val();
	// var secondaryTextVerticalOffset = $j("#badge_secondary_text_vertical_offset").val();
	// var secondaryTextHorizontalLocation = $j("#badge_secondary_text_horizontal_location").val();
	// var secondaryTextVerticalLocation = $j("#badge_secondary_text_vertical_location").val();

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

function getTextCoordinates(
	ctx,
	text,
	width,
	height,
	textHorizontalLocation,
	textVerticalLocation,
	textHorizontalOffset,
	textVerticalOffset
) {
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

function insertEventForm(
	mode,
	eventId,
	eventStarttime,
	eventLocation,
	eventTitle,
	eventDescription,
	badgeSize,
	badgeBackgroundFilePath,
	badgePrHtextloc,
	badgePrHtextOffset,
	badgePrVtextLoc,
	badgePrVtextOffset,
	badgePrFontsize,
	badgePrColor,
	badgePrBrDistance,
	badgePrTestText,
	badgeScHtextloc,
	badgeScHtextOffset,
	badgeScVtextLoc,
	badgeScVtextOffset,
	badgeScFontsize,
	badgeScColor,
	badgeScBrDistance,
	badgeScTestText
) {
	$j('.popups').append(`
		<div id="event-request-popup" class="popup">
			<button id="close-event-request-popup" type="button" class="close-form">
				<span class="screen-reader-text">Close</span>
				<span class="tb-close-icon"></span>
			</button>
			<div class="form-title-bar">
				<span id="popup-title" class="popup-title">Request new event</span>
			</div>
			<div class="popup-form" id="submit-new-event-request">
				<form id="submit-new-event-request-form" enctype="multipart/form-data" method="post">
					<table class="form-table table-event table-first">
						<tbody>
							<tr id="new-event-ticket-settings1">
								<td colspan="2">
									<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Event</h3>
								</td>
							</tr>

							<tr>
								<th>
									<label for="event_primary_text_pl">Title *</label>
								</th>
								<td>
									<input type="text" size="50" id="event_primary_text_pl" name="event_primary_text_pl" value="" class="text" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="event_secondary_text_pl">Description</label>
								</th>
								<td>
									<input type="text" size="50" id="event_secondary_text_pl" name="event_secondary_text_pl" value="" class="text" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="event_date">Date</label>
								</th>
								<td>
									<input type="text" size="50" id="event_date" name="event_date" value="" class="text" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="event_location">Location</label>
								</th>
								<td>
									<input type="text" size="50" id="event_location" name="event_location" value="" class="text" />
								</td>
							</tr>
							<tr><td>* - required fields</td></tr>
						</tbody>
					</table>

					<table id="table-ticket" class="form-table table-ticket">
						<tbody>
							<tr id="new-event-ticket-settings2">
								<td colspan="2">
									<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Ticket #1</h3>
								</td>
							</tr>

							<tr>
								<th>
									<label for="ticket_title0">Title *</label>
								</th>
								<td>
									<input type="text" size="50" id="ticket_title0" name="ticket[0][primary_text_pl]" value="" class="text" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="ticket_description0">Description</label>
								</th>
								<td>
									<input type="text" size="50" id="ticket_description0" name="ticket[0][secondary_text_pl]" value="" class="text" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="ticket_price0">Price *</label>
								</th>
								<td>
									<input type="text" size="50" id="ticket_price0" name="ticket[0][price]" value="" class="text" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="ticket_currency0">Currency</label>
								</th>
								<td>
									<select name="ticket[0][currency]" id="ticket_currency0">
										<option value="BGN">BGN</option>
										<option value="EUR">EUR</option>
										<option value="USD">USD</option>
									</select>
								</td>
							</tr>

							<tr>
								<th>
									<label for="ticket_stock0">Stock *</label>
								</th>
								<td>
									<input type="text" size="50" id="ticket_stock0" name="ticket[0][stock]" value="" class="text" />
								</td>
							</tr>
							<tr><td>* - required fields</td></tr>
						</tbody>
					</table>
					<div id="new-ticket-anchor"></div>
					<table id="table-ticket-submit" class="form-table submit-button">
						<tbody>
							<tr id="new-event-ticket-settings3">
								<td colspan="2">
									<p class="submit">
										<input type="button" id="new-event-ticket-button" class="button button-primary" value="Add new ticket">
									</p>
								</td>
							</tr>
						</tbody>
					</table>

					<table class="form-table table-badge">
						<tbody>
							<tr>
								<td colspan="2" style="padding:0;">
									<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Badge settings</h3>
									<p class="description">Set badge background and text location for autoprinting badges.</p>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_size">Badge size</label>
								</th>
								<td>
									<select name="badge_size" id="badge_size">
										<option value="A4">A4 - 210 x 297 mm</option>
										<option value="A5">A5 - 148 x 210 mm</option>
										<option value="A6" selected>A6 - 105 x 148 mm</option>
										<option value="A7">A7 - 74 x 105 mm</option>
										<option value="A8">A8 - 52 x 74 mm</option>
										<option value="A8">A9 - 37 x 52 mm</option>
										<option value="A8">A10 - 26 x 37 mm</option>
									</select>
									<p class="description">The physical size of the printed badge.</p>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge-background">Badge Background</label>
								</th>
								<td>
									<br>
									<input type="file" name="badge_file" id="badge_file">
									<p class="description">Only jpeg files are accepted.</p>
									<p>A4 - 2480x3508, A5 - 1748x2480,<br>A6 - 1240x1748, A7 - 874x1240,<br>A8 - 614x874, A9 - 437x614,<br>A10 - 307x437</p>
									<img id="badge_file_preview" src="" />
								</td>
							</tr>

							<tr>
								<td colspan="2" style="padding:0;">
									<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Primary text settings</h3>
									<p class="description">Set primary text location for autoprinting badges.</p>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_primary_text_horizontal_location">Horizontal location</label>
								</th>
								<td>
									<select name="badge_primary_text_horizontal_location" id="badge_primary_text_horizontal_location">
										<option value="left">Left</option>
										<option value="center" selected>Center</option>
										<option value="right">Right</option>
									</select>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_primary_text_horizontal_offset">Horizontal offset</label>
								</th>
								<td>
									<select name="badge_primary_text_horizontal_offset" id="badge_primary_text_horizontal_offset">
										<option value="-50">-50%</option>
										<option value="-10">-10%</option>
										<option value="-9">-9%</option>
										<option value="-8">-8%</option>
										<option value="-7">-7%</option>
										<option value="-6">-6%</option>
										<option value="-5">-5%</option>
										<option value="-4">-4%</option>
										<option value="-3">-3%</option>
										<option value="-2">-2%</option>
										<option value="-1">-1%</option>
										<option value="0" selected>0%</option>
										<option value="1">1%</option>
										<option value="2">2%</option>
										<option value="3">3%</option>
										<option value="4">4%</option>
										<option value="5">5%</option>
										<option value="6">6%</option>
										<option value="7">7%</option>
										<option value="8">8%</option>
										<option value="9">9%</option>
										<option value="10">10%</option>
										<option value="50">50%</option>
									</select>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_primary_text_vertical_location">Vertical location</label>
								</th>
								<td>
									<select name="badge_primary_text_vertical_location" id="badge_primary_text_vertical_location">
										<option value="top">Top</option>
										<option value="center" selected>Center</option>
										<option value="bottom">Bottom</option>
									</select>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_primary_text_vertical_offset">Vertical offset</label>
								</th>
								<td>
									<select name="badge_primary_text_vertical_offset" id="badge_primary_text_vertical_offset">
										<option value="-10">-10%</option>
										<option value="-9">-9%</option>
										<option value="-8">-8%</option>
										<option value="-7">-7%</option>
										<option value="-6">-6%</option>
										<option value="-5">-5%</option>
										<option value="-4">-4%</option>
										<option value="-3">-3%</option>
										<option value="-2">-2%</option>
										<option value="-1">-1%</option>
										<option value="0" selected>0%</option>
										<option value="1">1%</option>
										<option value="2">2%</option>
										<option value="3">3%</option>
										<option value="4">4%</option>
										<option value="5">5%</option>
										<option value="6">6%</option>
										<option value="7">7%</option>
										<option value="8">8%</option>
										<option value="9">9%</option>
										<option value="10">10%</option>
										<option value="50">50%</option>
									</select>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_primary_text_fontsize">Font size</label>
								</th>
								<td>
									<input type="text" size="50" id="badge_primary_text_fontsize" name="badge_primary_text_fontsize" value="70" class="text" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_primary_text_color">Text color</label>
								</th>
								<td>
									<input type="text" size="50" id="badge_primary_text_color" name="badge_primary_text_color" value="#000000" class="text" />
								</td>
							</tr>

							<tr>
								<td colspan="2" style="padding:0;">
									<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Secondary text settings</h3>
									<p class="description">Set secondary text location for autoprinting badges.</p>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_secondary_text_horizontal_location">Horizontal location</label>
								</th>
								<td>
									<select name="badge_secondary_text_horizontal_location" id="badge_secondary_text_horizontal_location">
										<option value="left">Left</option>
										<option value="center" selected>Center</option>
										<option value="right">Right</option>
									</select>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_secondary_text_horizontal_offset">Horizontal offset</label>
								</th>
								<td>
									<select name="badge_secondary_text_horizontal_offset" id="badge_secondary_text_horizontal_offset">
										<option value="-50">-50%</option>
										<option value="-10">-10%</option>
										<option value="-9">-9%</option>
										<option value="-8">-8%</option>
										<option value="-7">-7%</option>
										<option value="-6">-6%</option>
										<option value="-5">-5%</option>
										<option value="-4">-4%</option>
										<option value="-3">-3%</option>
										<option value="-2">-2%</option>
										<option value="-1">-1%</option>
										<option value="0" selected>0%</option>
										<option value="1">1%</option>
										<option value="2">2%</option>
										<option value="3">3%</option>
										<option value="4">4%</option>
										<option value="5">5%</option>
										<option value="6">6%</option>
										<option value="7">7%</option>
										<option value="8">8%</option>
										<option value="9">9%</option>
										<option value="10">10%</option>
										<option value="50">50%</option>
									</select>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_secondary_text_vertical_location">Vertical location</label>
								</th>
								<td>
									<select name="badge_secondary_text_vertical_location" id="badge_secondary_text_vertical_location">
										<option value="top">Top</option>
										<option value="center" selected>Center</option>
										<option value="bottom">Bottom</option>
									</select>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_secondary_text_vertical_offset">Vertical offset</label>
								</th>
								<td>
									<select name="badge_secondary_text_vertical_offset" id="badge_secondary_text_vertical_offset">
										<option value="-10">-10%</option>
										<option value="-9">-9%</option>
										<option value="-8">-8%</option>
										<option value="-7">-7%</option>
										<option value="-6">-6%</option>
										<option value="-5">-5%</option>
										<option value="-4">-4%</option>
										<option value="-3">-3%</option>
										<option value="-2">-2%</option>
										<option value="-1">-1%</option>
										<option value="0">0%</option>
										<option value="1">1%</option>
										<option value="2">2%</option>
										<option value="3">3%</option>
										<option value="4">4%</option>
										<option value="5" selected>5%</option>
										<option value="6">6%</option>
										<option value="7">7%</option>
										<option value="8">8%</option>
										<option value="9">9%</option>
										<option value="10">10%</option>
										<option value="50">50%</option>
									</select>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_secondary_text_fontsize">Font size</label>
								</th>
								<td>
									<input type="text" size="50" id="badge_secondary_text_fontsize" name="badge_secondary_text_fontsize" value="50" class="text" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_secondary_text_color">Text color</label>
								</th>
								<td>
									<input type="text" size="50" id="badge_secondary_text_color" name="badge_secondary_text_color" value="#000000" class="text" />
								</td>
							</tr>

							<tr>
								<td colspan="2" style="padding:0;">
									<h3><div class="dashicons dashicons-admin-settings"></div>&nbsp;Badge Preview</h3>
									<p class="description">Generate a preview before sending your new event for processing.</p>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_primary_test_text">Primary Test Text</label>
								</th>
								<td>
									<input type="text" size="50" id="badge_primary_test_text" name="badge_primary_test_text" value="FirstName LastName" class="text" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_primary_text_break_distance">Break Primary Text Distance</label>
								</th>
								<td>
									<input type="text" id="badge_primary_text_break_distance" name="badge_primary_text_break_distance" value="0" class="text" />
									<p class="description">Positive value will divide every word on a new line with the set vertical distance between them. Set 0 for single lined output..</p>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_secondary_test_text">Secondary Test Text</label>
								</th>
								<td>
									<input type="text" size="50" id="badge_secondary_test_text" name="badge_secondary_test_text" value="My Awesome Company" class="text" />
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_secondary_text_break_distance">Break Secondary Text Distance</label>
								</th>
								<td>
									<input type="text" id="badge_secondary_text_break_distance" name="badge_secondary_text_break_distance" value="0" class="text" />
									<p class="description">Positive value will divide every word on a new line with the set vertical distance between them. Set 0 for single lined output..</p>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_background">Badge Background</label>
								</th>
								<td>
									<br>
									<input type="button" name="generate_preview" value="Preview Badge" id="generate_preview"/>
									<p class="description">Generate badge preview.</p>
								</td>
							</tr>

							<tr>
								<th>
									<label for="badge_background">Badge Preview</label>
								</th>
								<td>
									<canvas id="badge_preview"></canvas>
									<p class="description">This is a demo and the results may slightly vary on the mobile device that will actually send the badge for printing.</p>
									<p class="description">To get 100% accurate results check the badge preview on your mobile scanner device.</p>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="form-table submit-button">
						<tbody>
							<tr id="new-event-ticket-settings4">
								<td colspan="2">
									<p class="submit">
									<input type="submit" name="submit" id="new-event-request" class="button button-primary" value="Request new event" />
									<span id="cancel-new-event-request-button" class="button button-primary">Cancel</span>
									</p>
								</td>
							</tr>
						</tbody>
					</table>
					<input id="action_type" type="hidden" name="action" value="create-event" />
				</form>
			</div>
		</div>`
	);

	if (mode === 'edit') {
		$j("#table-ticket").remove();
		$j("#new-ticket-anchor").remove();
		$j("#table-ticket-submit").remove();
		$j("#event-request-popup #popup-title").text('Request event change');
		$j("#event-request-popup #new-event-request").val('Request event change');
		$j("#event-request-popup #event_primary_text_pl").attr('value', eventTitle);
		$j("#event-request-popup #event_secondary_text_pl").attr('value', eventDescription);
		$j("#event-request-popup #event_date").attr('value', eventStarttime);
		$j("#event-request-popup #event_location").attr('value', eventLocation);

		$j("#event-request-popup #badge_size").val(badgeSize).change();
		$j("#event-request-popup #badge_file_preview").attr('src', badgeBackgroundFilePath);
		$j("#event-request-popup #badge_primary_text_horizontal_location").val(badgePrHtextloc).change();
		$j("#event-request-popup #badge_primary_text_horizontal_offset").val(badgePrHtextOffset).change();
		$j("#event-request-popup #badge_primary_text_vertical_location").val(badgePrVtextLoc).change();
		$j("#event-request-popup #badge_primary_text_vertical_offset").val(badgePrVtextOffset).change();
		$j("#event-request-popup #badge_primary_text_fontsize").attr('value', badgePrFontsize);
		$j("#event-request-popup #badge_primary_text_color").attr('value', badgePrColor);

		$j("#event-request-popup #badge_secondary_text_horizontal_location").val(badgeScHtextloc).change();
		$j("#event-request-popup #badge_secondary_text_horizontal_offset").val(badgeScHtextOffset).change();
		$j("#event-request-popup #badge_secondary_text_vertical_location").val(badgeScVtextLoc).change();
		$j("#event-request-popup #badge_secondary_text_vertical_offset").val(badgeScVtextOffset).change();
		$j("#event-request-popup #badge_secondary_text_fontsize").attr('value', badgeScFontsize);
		$j("#event-request-popup #badge_secondary_text_color").attr('value', badgeScColor);

		$j("#event-request-popup #badge_primary_test_text").attr('value', badgePrTestText);
		$j("#event-request-popup #badge_primary_text_break_distance").attr('value', badgePrBrDistance);

		$j("#event-request-popup #badge_secondary_test_text").attr('value', badgeScTestText);
		$j("#event-request-popup #badge_secondary_text_break_distance").attr('value', badgeScBrDistance);

		$j("#event-request-popup #action_type").attr('value', 'event-change');
		$j("#event-request-popup #action_type").append(`<input id="event_id" type="hidden" name="event_id" value="${eventId}" />`);

		// $j("#generate_preview").click();
	}
	
	$j(".popups-overlay").show();
	$j("#event-request-popup").show();
	$j("#event_date").datepicker();
}
