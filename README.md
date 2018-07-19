# Digital Products for Commerce

Digital products for commerce allows you to create products that link to resources, files, and user groups upon order completion. It allows you to set expirations on downloading a file and how many times the user can download a file.

## Setup

Download the transport package and install it on your site. If you haven't created a custom Commerce theme, do so now. Read how to here: https://docs.modmore.com/en/Commerce/v1/Front-end_Theming.html

Since the digital resources/files will appear on the thank you page, you'll need to copy the thank-you.twig file from `default/frontend/checkout/thank-you.twig` to `yourTheme/frontend/checkout/thank-you.twig`.

In this twig file you'll have the digitalProducts variable accessible when there are digitalProducts on the order. The two accessible sub-arrays you'll want to loop through are digitalProducts.resources and digitalProducts.files.

Here is a very basic example to get setup:

```HTML
<div class="c-digital-products">
    {# The product object is available under digitalProducts.product #}
    {% for digitalProduct in digitalProducts.resources %}
        <h4>{{ digitalProduct.product.name }} {{ lex('commerce_digitalproduct.pages') }}</h4>
        {# Has the the full modResource object #}
        {% for resource in digitalProduct.resources %}
            <p><a href="[[~{{ resource.id }}]]">{{ resource.pagetitle }}</a></p>
        {% endfor %}
    {% endfor %}
        
    {% for digitalProduct in digitalProducts.files %}
        <h4>{{ digitalProduct.product.name }} {{ lex('commerce_digitalproduct.files') }}</h4>
        {% for file in digitalProduct.files %}
            <p><a href="{{ file.url }}">{{ file.display_name }}</a></p>
        {% endfor %}
    {% endfor %}
</div>
```

If you want to use resources, configure the parents to look under for resources in the system setting `commerce_digitalproduct.resource_parents`.

Finally, enable the module in Commerce -> Configuration -> Modules. You can now make a delivery type with the shipment type of digital shipment and create digital products!