# community_store_mailchimp_subscribing
Subscribe Community Store customers to MailChimp lists based on products purchased.

## Setup
Install Community Store First.

Download a 'release' zip of the add-on, unzip this to the packages folder of your concrete5 install (alongside the community_store folder) and install via the dashboard.

A new dashboard page is created under Store->MailChimp Subscribing where a MailChimp API key and optional default list ID can be added along with a checkbox to enable product list subscriptions.

To add customers to specific MailChimp lists for specific products, create a text product attribute with the handle 'mailchimp_list_id', then enter a list ID when editing a product. A list ID is found under the MailChimp settings, list name and defaults section of a list.
