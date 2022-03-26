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
					<label for="ticket_title${i}">Title</label>
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
					<label for="ticket_price${i}">Price</label>
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
					<label for="ticket_stock${i}">Stock</label>
				</th>
				<td>
					<input type="text" size="50" id="ticket_stock${i}" name="ticket[${i}][stock]" value="" class="text" />
				</td>
			</tr></tbody></table>
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
		$j('#events #' + rowId + ' .htext-loc').replaceWith('<td class="htext-loc"><select name="badge_text_horizontal_location" class="htext-loc"><option value="left">left</option><option value="center" selected>center</option><option value="right">right</option></select></td>');
		$j('#events #' + rowId + ' select.htext-loc').val(hTextLocTdValue);

		var vTextLocTdValue = $j('#events #' + rowId + ' .vtext-loc').html();
		$j('#events #' + rowId + ' .vtext-loc').replaceWith('<td class="vtext-loc"><select name="badge_text_vertical_location" class="vtext-loc"><option value="top">top</option><option value="center" selected>center</option><option value="bottom">bottom</option></select></td>');
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

	$j("input[name*='badge_primary_text_fontsize'], input[name*='badge_secondary_text_fontsize']").each(function() {
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