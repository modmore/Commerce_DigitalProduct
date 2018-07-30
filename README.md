# Digital Products for Commerce

Digital products for commerce allows you to create products that link to resources, files, and user groups upon order completion. It allows you to set expirations on downloading a file and how many times the user can download a file.

This is a more advanced version of the Commerce_ResourceStore, which stored basic resource information in the user's TV. This module can be used as a enhanced replacement.

## Setup

Download the transport package and install it on your site. If you haven't created a custom Commerce theme, do so now. Read how to here: https://docs.modmore.com/en/Commerce/v1/Front-end_Theming.html

Since the digital resources/files will appear on the thank you page, you'll need to copy the thank-you.twig file from `default/frontend/checkout/thank-you.twig` to `yourTheme/frontend/checkout/thank-you.twig`.

In this twig file you'll have the digitalProducts variable accessible when there are digitalProducts on the order. The three accessible sub-arrays you can access, resources, files, and all (containing resources and files). Within files and resources, you have access to all columns within DigitalproductFile (id, class\_key, properties, digitalproduct_id, secret, name, resource, file, download\_count, download\_limit, and download\_expiry (unix time)) There is also access to the product instance.

Here is a very basic example which loops through resources and files seperately:

```HTML
<div class="c-digital-products">
    {% for digitalProduct in digitalProducts %}
        <h4>{{ digitalProduct.product.name }} {{ lex('commerce_digitalproduct.pages') }}</h4>

        {% for resource in digitalProduct.resources %}
            <p><a href="[[~DOWNLOADRESOURCE]]?secret={{ resource.secret }}">{{ resource.name }}</a></p>
        {% endfor %}
            
        <h4>{{ digitalProduct.product.name }} {{ lex('commerce_digitalproduct.files') }}</h4>
        {% for file in digitalProduct.files %}
            <p><a href="[[~DOWNLOADRESOURCE]]?secret={{ resource.secret }}">{{ file.name }}</a></p>
        {% endfor %}
    {% endfor %}
</div>
```

If you want to use resources, configure the parents to look under for resources in the system setting `commerce_digitalproduct.resource_parents`.

Finally, enable the module in Commerce -> Configuration -> Modules. You can now make a delivery type with the shipment type of digital shipment and create digital products!

## Options

- `commerce_digitalproduct.download_method`: method that will be used to download the file. Out of the box, it supports redirect (redirects to a URL), forced (force download the file with PHP, slow for large files), sendfile (X-Accel-Redirect, requires additional server setup). Defaults to redirect.
- `commerce_digitalproduct.expiration_times`: Labels and values for expiration times that appear in the product form. Uses a TV checkbox like format. Values need to be strtotime compatible.
- `commerce_digitalproduct.resource_parents`: Parents to look under for the resources input. Comma delimited.

## `digitalproduct.get_file` Snippet

This snippet generates the URL to download the file after purchase.

Supported properties:

- checkUser: checks for the user who made the order to verify, defaults to 0.
- checkCount: checks if user is under their max downloads, defaults to 1.
- checkExpiry: checks if file download is expired, defaults to 1.

## Roadmap

### 1.0.0-pl

- Snippet to protect file URLs, count downloads, and enforce download_expiry. Also the option to remove user group at the end of expiry.
- Disable/enable either files and resources display in the product form via a system setting.


### ??? Future

- Option to let user customize the secret for the URL as well as customizing the amount of bytes openssl\_random\_pseudo\_bytes uses. Method is already implemented, just needs form fields.
- Commerce dashboard reports (products with most downloads, more?).