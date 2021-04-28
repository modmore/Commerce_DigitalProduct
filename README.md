# Digital Products for Commerce

Digital products for commerce allows you to create products that link to resources, files, and user groups upon order completion. It allows you to set expirations on downloading a file and how many times the user can download a file.

This is a more advanced version of the Commerce_ResourceStore, which stored basic resource information in the user's TV. This module can be used as a enhanced replacement.

> This extension was originally built and maintained by [Tony Klapatch](https://github.com/tonyklapatch), providing a wide range of functionality for selling digital products in Commerce, as part of a third-party extension not covered by modmore's support.
>
> In April 2021 modmore adopted the project in an effort to make it compatible with recent Commerce releases, and to include it in modmore's standard support as an official extension. By mid-end April we'll release v2.0 that makes it official and adds that compatibility with Commerce v1.1+.
>
> _Tony, thank you for your hard work and innovation!_

## Setup

Download the transport package from the MODX.com or modmore package provider and install it on your site. If you haven't created a custom Commerce theme, do so now. [Learn how to in the documentation](https://docs.modmore.com/en/Commerce/v1/Front-end_Theming.html).

Since the digital resources/files will typically appear on the thank you page, copy the thank-you.twig file from `default/frontend/checkout/thank-you.twig` to `yourTheme/frontend/checkout/thank-you.twig`.

In this twig file you'll have the `digitalProducts` variable accessible when there are digital products on the order. There are three accessible sub-arrays you can access: `resources`, `files`, and `all` (containing resources and files).

Within files and resources, you have access to all columns within DigitalproductFile (`id`, `class_key`, `properties`, `digitalproduct_id`, `secret`, `name`, `resource`, `file`, `download_count`, `download_limit`, and `download_expiry` (unix time)). There is also access to the product instance.

Here is a very basic example which loops through resources and files seperately, only showing headings if there are results:

> Note: point the `commerce_digitalproduct.download_resource` system setting to the ID of the resource you place the `digitalproduct.get_file` snippet on, described below.

```HTML
<div class="c-digital-products">
    {% for digitalProduct in digitalProducts %}

        {% if digitalProduct.resources|length > 0 %}
            <h4>{{ digitalProduct.product.name }} {{ lex('commerce_digitalproduct.pages') }}</h4>
            {% for resource in digitalProduct.resources %}
                <p><a href="[[~[[++commerce_digitalproduct.download_resource]]]]?secret={{ resource.secret }}">{{ resource.name }}</a></p>
            {% endfor %}
        {% endif %}

        {% if digitalProduct.files|length > 0 %}
            <h4>{{ digitalProduct.product.name }} {{ lex('commerce_digitalproduct.files') }}</h4>
            {% for file in digitalProduct.files %}
                <p><a href="[[~[[++commerce_digitalproduct.download_resource]]]]?secret={{ file.secret }}">{{ file.name }}</a></p>
            {% endfor %}
        {% endif %}

    {% endfor %}
</div>
```

If you want to use resources, configure the parents to look under for resources in the system setting `commerce_digitalproduct.resource_parents`.

Finally, enable the module in Commerce -> Configuration -> Modules. You can now make a delivery type with the shipment type of digital shipment and create digital products!

## Options

- `commerce_digitalproduct.download_methods`: methods that can be used to download the file. Out of the box, it supports redirect (redirects to a URL) and forced (force download the file with PHP, not good for large files. Only works for resources if the resource is a web link). Additionally, you can use a custom snippet download method. Check out the `digitalproduct.get_file` snippet for how to implement a custom snippet download method.
- `commerce_digitalproduct.expiration_times`: Labels and values for expiration times that appear in the product form. Uses a TV checkbox like format. Values need to be strtotime compatible.
- `commerce_digitalproduct.resource_parents`: Parents to look under for the resources input. Comma delimited.

## `digitalproduct.get_file` Snippet

This snippet generates the URL to download the file after purchase.

Supported properties:

- checkUser: checks for the user who made the order to verify, defaults to 0.
- checkCount: checks if user is under their max downloads, defaults to 1.
- checkExpiry: checks if file download is expired, defaults to 1.

## Planned Features

- X-Accel-Redirect support.
- Option to remove user group at the end of expiry.
- Disable/enable either files and resources display in the product form via a system setting.
- Commerce dashboard reports (products with most downloads, more?).
- Option to let user customize the secret for the URL as well as customizing the amount of bytes openssl_random_pseudo_bytes uses. Method is already implemented, just needs form fields.
