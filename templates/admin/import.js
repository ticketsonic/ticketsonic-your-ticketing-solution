var $j = jQuery.noConflict();

$j(function() {

    $j("#skip_overview").click(function(){
        $j("#skip_overview_form").submit();
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
    $j("#new-ticket-button").click(function () {
        $j("form#create table tbody").append(`
        <tr>
                <th>
                    <label for="ticket_title${i}">Ticket title${i+1}</label>
                </th>
                <td>
                    <input type="text" size="50" id="ticket_title${i}" name="ticket[${i}][primary_text_pl]" value="" class="text" />
                </td>
            </tr>

            <tr>
                <th>
                    <label for="ticket_description${i}">Ticket description${i+1}</label>
                </th>
                <td>
                    <input type="text" size="50" id="ticket_description${i}" name="ticket[${i}][secondary_text_pl]" value="" class="text" />
                </td>
            </tr>

            <tr>
                <th>
                    <label for="ticket_price${i}">Ticket price${i+1}</label>
                </th>
                <td>
                    <input type="text" size="50" id="ticket_price${i}" name="ticket[${i}][price]" value="" class="text" />
                </td>
            </tr>

            <tr>
                <th>
                    <label for="ticket_currency${i}">Ticket currency${i+1}</label>
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
                    <label for="ticket_stock${i}">Ticket stock${i+1}</label>
                </th>
                <td>
                    <input type="text" size="50" id="ticket_stock${i}" name="ticket[${i}][stock]" value="" class="text" />
                </td>
            </tr>
        `);
        i++;
    });

    $j(document).ready(function() {
        var type = $j("input:radio[name=upload_method]:checked").val();
        $j("#file-filters-"+type).trigger("click");
    });

});