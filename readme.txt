=== TicketSonic - your Ticketing Solution ===
Contributors: ticketsonic
Requires at least: 4.7
Tested up to: 6.1
Stable tag: 1.3.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

**TicketSonic** is the ticketing engine that integrates with your platform behind the scenes enabling you to become a full featured ticket seller.

== Description ==
TicketSonic brings together everything required for you to become a standalone ticket seller and have a successful event. TicketSonic's products provide solutions for every scenario. Electronic tickets, free scanner mobile app, auto printed badge for accelerated reception and many more to support all kinds of events and venues like conferences, museums, city tours, stadiums, etc.

This is a WooCommerce based plugin for integrating the TicketSonic Engine with your WooCommerce platform.

What you get from this plugin?

*   Instantly become a full featured ticket seller. No coding required!
*   Effortless integration.
*   No more redirects to third party ticketing providers.
*   Super fast response times.

### Prerequisites

*   [WooCommerce Plugin](https://wordpress.org/plugins/woocommerce) installed and activated
*   Ensure wp-content/uploads dir is writable. In most cases it is by default. The plugin will create a subdirectory called ticketsonic-your-ticketing-solution and it will be used for storage of ticket files when the admin manually generates ones


### Installation

1. Get API credentials at [https://www.ticketsonic.com/user/register](https://www.ticketsonic.com/user/register)

2. Download and enable the plugin
Once installed the plugin page is available as one of the WooCommerce menu items.

3. Enter your API credentials in `/wp-admin/admin.php?page=ts_yts&tab=settings`

![TicketSonic Demo Screenshot](/path/to/screenshot-1.png "TicketSonic Demo Screenshot")


### General settings
The settings that have to be set are:

*    **API credentials** - the API Key and API Email identifiers available at your [TicketSonic account](https://www.ticketsonic.com/user).


### E-mail settings
You can customize the e-mail containing the tickets that will be sent upon successful ticket purchase.

*   **E-mail subject** - set the subject of the e-mail. The following tokens could be used [ticket_number], [ticket_title], [ticket_description], [ticket_price] for the current ticket number, its title, description and formatted price.

*   **E-mail body** - set the html contents of the e-mail. The following tokens could be used [ticket_qr], [ticket_number], [ticket_title], [ticket_description], [ticket_price] for the ticket QR code, current ticket number, its title, description and formatted price.


## Syncing
Once the settings are set you have to sync the tickets from TicketSonic with your WooCommerce store and be ready for sales!
You should go to `/wp-admin/admin.php?page=ts_yts&tab=sync` and click the Sync button
![TicketSonic Sync](/path/to/screenshot-2.png "TicketSonic Sync")

_For more examples, please refer to the [Developer Documentation](https://www.ticketsonic.com/developer)_


## Roadmap

*   [x] Request new Tickets
*   [x] Request new Event
*   [ ] Improve the UI

See the [open issues](https://github.com/ticketsonic/ticketsonic-your-ticketing-solution/issues) for a full list of proposed features (and known issues).


## Contact

TicketSonic Team - [Contact us](https://www.ticketsonic.com/contact-us)

Project Link: [https://github.com/ticketsonic/ticketsonic-your-ticketing-solution/](https://github.com/ticketsonic/ticketsonic-your-ticketing-solution/)
