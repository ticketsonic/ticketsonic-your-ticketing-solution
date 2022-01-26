<!-- ABOUT THE PROJECT -->
## About The Project


**TicketSonic** is the ticketing engine that integrates with your platform behind the scenes enabling you to become a full featured ticket seller.

**WooCommerce TicketSonic** is a WooCommerce based plugin for integrating the TicketSonic Engine with any WooCommerce platform.

What you get from this plugin?
* No more redirects to third party ticketing providers
* Effortless integration
* Super fast response times

<p align="right">(<a href="#top">back to top</a>)</p>

### Built With

* [Guzzle](https://github.com/guzzle/guzzle/)
* [QR Code generator](https://github.com/Bacon/BaconQrCode/)

<p align="right">(<a href="#top">back to top</a>)</p>

<!-- GETTING STARTED -->
## Getting Started

### Prerequisites

You have to be able to use composer
* composer
  ```sh
  composer install
  ```

* [WordPress installation](https://wordpress.org)
* [WooCommerce Plugin](https://wordpress.org/plugins/woocommerce) installed and activated
Ensure wp-content/uploads dir is writable. In most cases it is by default. The plugin will create a subdirectory called woocommerce-ticketsonic and it will be used for storage of ticket files when the admin manually generates ones.
### Installation

1. Enable the TicketSonic plugin
2. Get a API credentials at [https://www.ticketsonic.com/user/registration](https://www.ticketsonic.com/user/registration)
3. Go to the TicketSonic plugin
   ```sh
   cd <WordPress folder>/wp-content/plugins
4. Clone the repo
   ```sh
   git clone https://github.com/ticketsonic/woocommerce-ticketsonic.git .
   ```
   Or get it from the WordPress plugins page and unpack at the plugins folder
5. Install dependencies
   ```sh
   composer install
   ```
6. Enter your API credentials in `/wp-admin/admin.php?page=woo_ts&tab=settings`

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- USAGE EXAMPLES -->
## Usage

Once installed the plugin page is available as one of the WooCommerce menu items.

<img width="918" alt="TS Screenshot" src="https://user-images.githubusercontent.com/88324390/149328283-d8fee905-48ca-441b-aca2-046cf276ff7a.png">


The settings that have to be set are the API credentials, the e-mail subject and body.
Once the settings are set you have to sync the tickets from TicketSonic with your WooCommerce store and be ready for sales!

_For more examples, please refer to the [Developer Documentation](https://www.ticketsonic.com/developer)_

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- ROADMAP -->
## Roadmap

- [x] Request new Tickets
- [x] Request new Event
- [ ] Improve the UI

See the [open issues](https://github.com/ticketsonic/woocommerce-ticketsonic/issues) for a full list of proposed features (and known issues).

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- CONTRIBUTING -->
## Contributing

Contributions are what make the open source community such an amazing place to learn, inspire, and create. Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. You can also simply open an issue with the tag "enhancement".
Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#top">back to top</a>)</p>



<!-- LICENSE -->
## License

Distributed under the GPL2 License. See `LICENSE.txt` for more information.

<p align="right">(<a href="#top">back to top</a>)</p>

<!-- CONTACT -->
## Contact

TicketSonic Team - [Contact us](https://www.ticketsonic.com/contact-us)

Project Link: [https://github.com/ticketsonic/woocommerce-ticketsonic/](https://github.com/ticketsonic/woocommerce-ticketsonic/)

<p align="right">(<a href="#top">back to top</a>)</p>

<!-- ACKNOWLEDGMENTS -->
## Acknowledgments

* [Guzzle](https://github.com/guzzle/guzzle/)
* [QR Code generator](https://github.com/Bacon/BaconQrCode/)

<p align="right">(<a href="#top">back to top</a>)</p>
